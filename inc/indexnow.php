<?php
/**
 * IndexNow — уведомление поисковиков об изменениях сразу после правки.
 *
 * Обычный порядок такой: правим цену → ждём, пока робот сам заглянет на сайт →
 * через неделю-другую поиск показывает новую цену. Переобход в Вебмастере
 * ускоряет это, но он ручной и с суточным лимитом.
 *
 * IndexNow переворачивает схему: сайт сам стучится в поисковик и говорит
 * «вот эти адреса изменились». Протокол поддерживают Яндекс и Bing, запрос
 * уходит на общий адрес и раздаётся всем участникам сразу.
 *
 * Подтверждение владения — файл с ключом в корне сайта. Ключ не секрет:
 * он по замыслу протокола лежит в открытом доступе, иначе поисковик не сможет
 * его проверить.
 */
declare(strict_types=1);

/** Общая точка приёма: раздаёт уведомление всем поисковикам-участникам. */
const INDEXNOW_ENDPOINT = 'https://api.indexnow.org/indexnow';

/**
 * Минимальный промежуток между уведомлениями.
 * Владелец правит контент пачками — поправил цену, следом текст, следом фото.
 * Без паузы поисковик получил бы десяток одинаковых запросов за минуту
 * и мог счесть это злоупотреблением.
 */
const INDEXNOW_MIN_INTERVAL = 120;

/** Ключ из контента. Пусто — механизм выключен, и это нормальный режим. */
function indexnow_key(): string
{
    return trim((string)(content()['seoFiles']['indexnowKey'] ?? ''));
}

/** Файл с отметкой о последней отправке. Лежит в закрытом каталоге data. */
function indexnow_state_file(): string
{
    return DATA_DIR . '/.indexnow';
}

/** Что и когда отправляли в прошлый раз — показывается в админке. */
function indexnow_last(): array
{
    $raw = @file_get_contents(indexnow_state_file());
    $d = $raw ? json_decode($raw, true) : null;
    return is_array($d) ? $d : [];
}

/**
 * Адреса страниц для отправки. Логика та же, что у карты сайта:
 * страницу, убранную из карты в админке, поисковику не навязываем.
 */
function indexnow_urls(array $data, string $base): array
{
    $out = [];
    foreach ($data['pages'] ?? [] as $p) {
        if (!empty($p['noSitemap'])) {
            continue;
        }
        $slug = ($p['slug'] ?? '') === 'index' ? '' : ($p['slug'] ?? '');
        $out[] = $base . '/' . ($slug === '' ? '' : $slug . '/');
    }
    return array_values(array_unique($out));
}

/**
 * Сообщить поисковикам, что контент изменился.
 *
 * Ошибки намеренно проглатываются: уведомление — вещь вспомогательная,
 * и падение чужого сервиса не должно мешать владельцу сохранить цену.
 * Результат кладём в файл состояния, чтобы в админке было видно,
 * дошло уведомление или нет.
 */
function indexnow_notify(array $data): void
{
    // Ключ берём из сохраняемых данных, а не через content(): та отдаёт версию,
    // прочитанную в начале запроса, и только что введённого ключа там ещё нет
    $key = trim((string)($data['seoFiles']['indexnowKey'] ?? ''));
    if ($key === '' || !function_exists('curl_init')) {
        return;
    }

    $base = rtrim((string)($data['site']['url'] ?? ''), '/');
    $host = parse_url($base, PHP_URL_HOST) ?: '';
    // На локальной машине уведомлять некого: поисковик до неё не достучится
    if ($host === '' || str_starts_with($host, 'localhost') || str_starts_with($host, '127.')) {
        return;
    }

    $state = indexnow_last();
    if (isset($state['time']) && (time() - (int)$state['time']) < INDEXNOW_MIN_INTERVAL) {
        return; // слишком часто — пропускаем, следующая правка отправится
    }

    $urls = indexnow_urls($data, $base);
    if (!$urls) {
        return;
    }

    $payload = json_encode([
        'host'        => $host,
        'key'         => $key,
        'keyLocation' => $base . '/' . $key . '.txt',
        'urlList'     => $urls,
    ], JSON_UNESCAPED_SLASHES);

    $ch = curl_init(INDEXNOW_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        // Короткие таймауты: админка не должна ждать чужой сервер.
        // Не ответили за три секунды — значит не сегодня.
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT        => 3,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    @file_put_contents(indexnow_state_file(), json_encode([
        'time'  => time(),
        'code'  => $code,
        'count' => count($urls),
        'error' => $err ?: null,
        'body'  => is_string($body) ? mb_substr($body, 0, 200) : null,
    ], JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/** Человеческое описание последней отправки — для админки. */
function indexnow_status_text(): string
{
    $s = indexnow_last();
    if (!$s) {
        return 'Уведомлений ещё не было.';
    }
    $when = date('d.m.Y H:i', (int)($s['time'] ?? 0));
    $code = (int)($s['code'] ?? 0);
    // 200 — принято, 202 — принято и поставлено в очередь на проверку ключа
    if ($code === 200 || $code === 202) {
        return sprintf('%s — отправлено %d адресов, поисковик принял (код %d).', $when, (int)($s['count'] ?? 0), $code);
    }
    if ($code === 403) {
        return $when . ' — поисковик не нашёл файл с ключом в корне сайта (код 403).';
    }
    if ($code === 422) {
        return $when . ' — адреса не совпадают с доменом или ключ неверный (код 422).';
    }
    if ($code === 429) {
        return $when . ' — слишком много уведомлений, поисковик просит подождать (код 429).';
    }
    if ($code === 0) {
        return $when . ' — не удалось связаться: ' . ($s['error'] ?: 'нет ответа');
    }
    return sprintf('%s — ответ поисковика: код %d.', $when, $code);
}
