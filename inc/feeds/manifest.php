<?php
/** Манифест PWA. */
declare(strict_types=1);
header('Content-Type: application/manifest+json; charset=utf-8');
echo json_encode([
    'name'        => 'NLeveL — детейлинг в Екатеринбурге',
    'short_name'  => 'NLeveL',
    'description' => 'Детейлинг-студия NLeveL: мойка, полировка, керамика, оклейка плёнкой, химчистка, тонировка и шумоизоляция в Екатеринбурге на ЖБИ.',
    'lang' => 'ru-RU', 'dir' => 'ltr',
    'start_url' => './?source=pwa', 'scope' => './',
    'display' => 'standalone', 'orientation' => 'portrait-primary',
    'background_color' => '#08090b', 'theme_color' => '#08090b',
    'categories' => ['business', 'lifestyle'],
    'icons' => [
        ['src' => './assets/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => './assets/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => './assets/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
    'shortcuts' => [
        ['name' => 'Записаться', 'url' => './booking/', 'description' => 'Онлайн-запись в детейлинг'],
        ['name' => 'Прайс', 'url' => './price/', 'description' => 'Цены на все услуги'],
        ['name' => 'Как доехать', 'url' => './contacts/', 'description' => 'Адрес и карта'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
