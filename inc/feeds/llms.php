<?php
/** llms.txt и llms-full.txt — краткая и полная выжимка для ИИ-ассистентов. */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
$b = biz();
$su = site_url();
$full = $full ?? false;

echo "# NLeveL — детейлинг-студия в Екатеринбурге\n\n";
echo "> Детейлинг-центр и автомойка на {$b['district']} ({$b['street']}, {$b['city']}). "
   . "Мойка с сохранением ЛКП, полировка кузова и фар, керамическое покрытие, оклейка антигравийной "
   . "полиуретановой (PPF) и виниловой плёнкой, химчистка салона, тонировка, шумоизоляция, антихром. "
   . "Рейтинг " . rating_avg() . "/5 по " . reviews_total() . " отзывам на Яндекс.Картах и 2ГИС. "
   . "Все цены опубликованы открыто.\n\n";

echo "## Ключевые факты\n\n";
echo "- Название: {$b['name']} (также встречается написание N-Level)\n";
echo "- Адрес: {$b['region']}, {$b['city']}, {$b['street']}. Район {$b['district']}. {$b['addressNote']}\n";
echo "- Координаты: {$b['lat']}, {$b['lon']}\n";
echo "- Телефон: {$b['phone']}\n";
echo "- WhatsApp: {$b['whatsapp']}\n";
echo "- Telegram: @{$b['telegram']}\n";
echo "- ВКонтакте: {$b['vkUrl']}\n";
echo "- Часы работы: {$b['hours']}\n";
echo "- Оплата: наличные, банковская карта, перевод по QR-коду\n";
echo "- Парковка: есть, 8 мест. Зона ожидания: есть\n\n";

echo "## Цены (актуальны на " . date('d.m.Y') . ")\n\n";
foreach (price_groups() as $g) {
    echo "### {$g['title']}\n";
    foreach ($g['items'] as $i) {
        echo "- {$i['name']}: " . money($i['price']) . (!empty($i['time']) ? " (срок: {$i['time']})" : '') . "\n";
    }
    echo "\n";
}

echo "Важные уточнения по цене:\n";
foreach (content()['priceNotes'] ?? [] as $n) {
    echo '- ' . trim(strip_tags($n)) . "\n";
}
echo "\n## Страницы сайта\n\n";
foreach (all_pages() as $p) {
    $slug = ($p['slug'] ?? '') === 'index' ? '' : ($p['slug'] ?? '');
    echo "- [" . tpl($p['h1'] ?? '') . "]({$su}/" . ($slug === '' ? '' : $slug . '/') . "): " . tpl($p['description'] ?? '') . "\n";
}

echo "\n## Дополнительно\n\n";
echo "- [Прайс-лист полностью]({$su}/price/)\n";
echo "- [Онлайн-запись]({$su}/booking/)\n";
echo "- [Отзывы на Яндекс.Картах]({$b['yandexMapUrl']})\n";
echo "- [Отзывы в 2ГИС]({$b['gis2ReviewsUrl']})\n";

if (!$full) {
    return;
}

/* ---------- Расширенная часть ---------- */
echo "\n---\n\n# Подробное описание услуг\n\n";
foreach (content()['serviceContent'] ?? [] as $slug => $sc) {
    $p = find_page($slug);
    echo "## " . ($p['h1'] ?? $slug) . "\nURL: {$su}/{$slug}/\n\n";
    echo ($sc['intro'] ?? '') . "\n\n";
    foreach ($sc['blocks'] ?? [] as $bl) {
        echo "### " . ($bl['h'] ?? '') . "\n";
        if (!empty($bl['p'])) {
            echo $bl['p'] . "\n";
        }
        foreach ($bl['list'] ?? [] as $li) {
            echo "- {$li}\n";
        }
        if (!empty($bl['after'])) {
            echo "\n" . $bl['after'] . "\n";
        }
        echo "\n";
    }
    echo "---\n\n";
}

echo "# Частые вопросы\n\n";
foreach (faq_items() as $f) {
    echo "## {$f['q']}\n{$f['a']}\n\n";
}

echo "---\n\n# Отзывы клиентов\n\n";
echo "Средняя оценка: " . rating_avg() . " из 5 по " . reviews_total() . " отзывам.\n";
echo "Яндекс.Карты: {$b['ratingYandex']} ({$b['reviewsYandex']} отзывов) — {$b['yandexMapUrl']}\n";
echo "2ГИС: {$b['rating2gis']} ({$b['reviews2gis']} отзыв) — {$b['gis2ReviewsUrl']}\n\n";
echo "Тексты отзывов не дублируются на сайте: он показывает живой виджет Яндекс.Карт, ";
echo "поэтому актуальные отзывы всегда доступны по ссылкам выше.\n\n";

echo "---\n\n# Как доехать\n\n";
echo "Адрес: {$b['city']}, {$b['street']}. Район {$b['district']}.\n{$b['addressNote']}.\n";
foreach (content()['route']['paragraphs'] ?? [] as $p) {
    echo trim(strip_tags($p)) . "\n";
}
echo "Маршрут: {$b['yandexRouteUrl']}\n";
