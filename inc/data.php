<?php
/**
 * Чтение и запись контента. Один JSON-файл — вся начинка сайта.
 * При каждом сохранении делается резервная копия, чтобы случайную правку
 * можно было откатить.
 */
declare(strict_types=1);

/** Весь контент сайта. Читается один раз за запрос. */
function content(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    if (!is_file(CONTENT_FILE)) {
        http_response_code(500);
        exit('Не найден файл контента: data/content.json');
    }
    $raw = file_get_contents(CONTENT_FILE);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(500);
        exit('Файл контента повреждён: ' . json_last_error_msg());
    }
    return $cache = $data;
}

/** Сохранить контент: сначала бэкап, потом атомарная запись. */
function save_content(array $data): bool
{
    if (!is_dir(BACKUP_DIR)) {
        @mkdir(BACKUP_DIR, 0775, true);
    }

    // Бэкап текущей версии
    if (is_file(CONTENT_FILE)) {
        $stamp = date('Y-m-d_H-i-s');
        @copy(CONTENT_FILE, BACKUP_DIR . "/content_{$stamp}.json");
        prune_backups(30);
    }

    $data['site']['updated'] = date('c');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    // Пишем во временный файл и подменяем — чтобы при обрыве не осталось битого JSON
    $tmp = CONTENT_FILE . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    if (!rename($tmp, CONTENT_FILE)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/** Оставляем только N последних резервных копий. */
function prune_backups(int $keep): void
{
    $files = glob(BACKUP_DIR . '/content_*.json') ?: [];
    if (count($files) <= $keep) {
        return;
    }
    sort($files);
    foreach (array_slice($files, 0, count($files) - $keep) as $old) {
        @unlink($old);
    }
}

/** Список резервных копий, свежие сверху. */
function list_backups(): array
{
    $files = glob(BACKUP_DIR . '/content_*.json') ?: [];
    rsort($files);
    return array_map(fn($f) => [
        'file' => basename($f),
        'time' => date('d.m.Y H:i:s', (int)filemtime($f)),
        'size' => round(filesize($f) / 1024, 1),
    ], $files);
}

/* ---------- Выборки ---------- */

function biz(): array
{
    return content()['biz'] ?? [];
}

function price_groups(): array
{
    return content()['priceGroups'] ?? [];
}

function find_group(string $id): ?array
{
    foreach (price_groups() as $g) {
        if (($g['id'] ?? '') === $id) {
            return $g;
        }
    }
    return null;
}

function all_pages(): array
{
    return content()['pages'] ?? [];
}

function find_page(string $slug): ?array
{
    foreach (all_pages() as $p) {
        if (($p['slug'] ?? '') === $slug) {
            return $p;
        }
    }
    return null;
}

function reviews(): array
{
    return content()['reviews'] ?? [];
}

function gallery(): array
{
    return content()['gallery'] ?? [];
}

function advantages(): array
{
    return content()['advantages'] ?? [];
}

function faq_items(): array
{
    return content()['faq'] ?? [];
}

/** Средняя оценка и общее число отзывов — считаются, а не хранятся. */
function rating_avg(): float
{
    $b = biz();
    $ry = (float)($b['ratingYandex'] ?? 0);
    $ny = (int)($b['reviewsYandex'] ?? 0);
    $rg = (float)($b['rating2gis'] ?? 0);
    $ng = (int)($b['reviews2gis'] ?? 0);
    $total = $ny + $ng;
    if ($total === 0) {
        return 0.0;
    }
    return round(($ry * $ny + $rg * $ng) / $total, 1);
}

function reviews_total(): int
{
    $b = biz();
    return (int)($b['reviewsYandex'] ?? 0) + (int)($b['reviews2gis'] ?? 0);
}
