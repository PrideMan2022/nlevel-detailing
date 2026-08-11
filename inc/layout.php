<?php
/** Каркас страницы: голова, шапка, меню, подвал, таб-панель. */
declare(strict_types=1);

/** Пункты верхнего меню. */
function main_nav(): array
{
    return [
        ['url' => '',          'label' => 'Главная'],
        ['url' => 'price',     'label' => 'Прайс'],
        ['url' => 'works',     'label' => 'Работы'],
        ['url' => 'reviews',   'label' => 'Отзывы'],
        ['url' => 'contacts',  'label' => 'Контакты'],
    ];
}

/** Услуги в меню — берутся из страниц контента, чтобы админ мог их менять. */
function service_nav(): array
{
    $out = [];
    foreach (all_pages() as $p) {
        if (empty($p['service'])) {
            continue;
        }
        $out[] = [
            'url'   => $p['slug'],
            'label' => $p['nav'] ?? $p['h1'] ?? $p['slug'],
            'icon'  => $p['navIcon'] ?? 'car',
            'hint'  => $p['navHint'] ?? '',
        ];
    }
    return $out;
}

/** Пункты нижней таб-панели. */
function tabbar_items(): array
{
    $out = [];
    foreach (all_pages() as $p) {
        if (!empty($p['inTabBar'])) {
            $out[] = $p;
        }
    }
    return $out;
}

function is_current(string $slug, array $page): bool
{
    return ($page['slug'] ?? '') === $slug
        || ($slug === '' && ($page['slug'] ?? '') === 'index');
}

function cur_attr(string $slug, array $page): string
{
    return is_current($slug, $page) ? ' aria-current="page"' : '';
}

/* ---------- Микроразметка ---------- */

function json_ld(array $page, array $extra = []): string
{
    $b = biz();
    $su = site_url();

    $org = [
        '@context'    => 'https://schema.org',
        '@type'       => 'AutoRepair',
        '@id'         => $su . '/#org',
        'name'        => $b['name'] ?? 'NLeveL',
        'alternateName' => $b['brandAliases'] ?? 'N-Level детейлинг',
        'description' => 'Детейлинг-студия в Екатеринбурге: мойка с сохранением ЛКП, полировка кузова и фар, керамическое покрытие, оклейка антигравийной и виниловой плёнкой, химчистка салона, тонировка, шумоизоляция.',
        'url'         => $su . '/',
        'telephone'   => $b['phoneRaw'] ?? '',
        'image'       => $su . '/assets/img/og.jpg',
        'logo'        => $su . '/assets/icons/icon-512.png',
        'priceRange'  => '₽₽',
        'currenciesAccepted' => 'RUB',
        'paymentAccepted'    => 'Наличные, банковская карта, перевод по QR-коду',
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $b['street'] ?? '',
            'addressLocality' => $b['city'] ?? '',
            'addressRegion'   => $b['region'] ?? '',
            'addressCountry'  => $b['country'] ?? 'RU',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $b['lat'] ?? null,
            'longitude' => $b['lon'] ?? null,
        ],
        'hasMap' => $b['yandexMapUrl'] ?? '',
        'openingHoursSpecification' => [[
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'opens'     => '10:00',
            'closes'    => '21:00',
        ]],
        'areaServed' => ['@type' => 'City', 'name' => $b['city'] ?? 'Екатеринбург'],
        'sameAs' => array_values(array_filter([
            $b['vkUrl'] ?? '', $b['telegramUrl'] ?? '', $b['yandexMapUrl'] ?? '',
            $b['gis2Url'] ?? '', $b['zoonUrl'] ?? '',
        ])),
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => rating_avg(),
            'reviewCount' => reviews_total(),
            'bestRating'  => 5,
            'worstRating' => 1,
        ],
    ];

    $site = [
        '@context'   => 'https://schema.org',
        '@type'      => 'WebSite',
        '@id'        => $su . '/#website',
        'url'        => $su . '/',
        'name'       => 'NLeveL — детейлинг в Екатеринбурге',
        'inLanguage' => 'ru-RU',
        'publisher'  => ['@id' => $su . '/#org'],
    ];

    $blocks = [$org, $site];

    if (($page['slug'] ?? '') !== 'index') {
        $blocks[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $su . '/'],
                ['@type' => 'ListItem', 'position' => 2,
                 'name' => $page['nav'] ?? $page['h1'] ?? '', 'item' => $su . '/' . ($page['slug'] ?? '') . '/'],
            ],
        ];
    }

    foreach ($extra as $x) {
        $blocks[] = $x;
    }

    $out = '';
    foreach ($blocks as $b2) {
        $out .= '<script type="application/ld+json">'
            . json_encode($b2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }
    return $out;
}

function faq_schema(array $items): array
{
    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type'          => 'Question',
            'name'           => $f['q'] ?? '',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? ''],
        ], array_values($items)),
    ];
}

/* ---------- Шапка ---------- */

function render_header(array $page): void
{
    $b = biz();
    $canonicalSlug = ($page['slug'] ?? '') === 'index' ? '' : ($page['slug'] ?? '');
    ?>
<header class="appbar" id="appbar">
  <div class="appbar__inner">
    <a class="logo" href="<?= url() ?>" aria-label="NLeveL — на главную">
      <?= logo_mark() ?>
      <span class="logo__text"><b>N</b>LeveL</span>
    </a>

    <nav class="nav" id="nav" aria-label="Основное меню">
      <span class="nav__pill" id="navPill" aria-hidden="true"></span>
      <a class="nav__link" href="<?= url() ?>"<?= cur_attr('index', $page) ?>>Главная</a>

      <div class="nav__group">
        <a class="nav__link" href="<?= url('price') ?>" aria-haspopup="true">Услуги ▾</a>
        <div class="nav__panel">
          <?php foreach (service_nav() as $s): ?>
          <a href="<?= url($s['url']) ?>"<?= cur_attr($s['url'], $page) ?>><?= icon($s['icon']) ?><b><?= e($s['label']) ?><span><?= e($s['hint']) ?></span></b></a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php foreach (array_slice(main_nav(), 1) as $n): ?>
      <a class="nav__link" href="<?= url($n['url']) ?>"<?= cur_attr($n['url'], $page) ?>><?= e($n['label']) ?></a>
      <?php endforeach; ?>
    </nav>

    <a class="btn btn--primary appbar__cta" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться</a>

    <button class="burger" id="burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="sheet">
      <?= icon('menu') ?>
    </button>
  </div>
</header>

<div class="sheet" id="sheet" data-open="false" role="dialog" aria-modal="true" aria-label="Меню сайта">
  <div class="sheet__scrim" data-close></div>
  <div class="sheet__panel">
    <div class="sheet__grab"></div>
    <div class="btn-row">
      <a class="btn btn--primary" style="flex:1" href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?>Позвонить</a>
      <a class="btn btn--wa" href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Написать в WhatsApp"><?= icon('wa') ?></a>
      <a class="btn btn--tg" href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Написать в Telegram"><?= icon('tg') ?></a>
    </div>

    <p class="sheet__title">Услуги</p>
    <ul class="sheet__list">
      <?php foreach (service_nav() as $s): ?>
      <li><a href="<?= url($s['url']) ?>"<?= cur_attr($s['url'], $page) ?>><?= icon($s['icon']) ?><?= e($s['label']) ?></a></li>
      <?php endforeach; ?>
    </ul>

    <p class="sheet__title">Разделы</p>
    <ul class="sheet__list">
      <?php foreach (main_nav() as $n): ?>
      <li><a href="<?= url($n['url']) ?>"<?= cur_attr($n['url'] === '' ? 'index' : $n['url'], $page) ?>><?= e($n['label']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('booking') ?>"<?= cur_attr('booking', $page) ?>><?= icon('calendar') ?>Онлайн-запись</a></li>
    </ul>
  </div>
</div>
    <?php
}

function render_crumbs(array $page): void
{
    if (($page['slug'] ?? '') === 'index') {
        return;
    }
    ?>
<nav class="crumbs shell" aria-label="Хлебные крошки">
  <ol>
    <li><a href="<?= url() ?>">Главная</a></li>
    <li aria-current="page"><?= e($page['nav'] ?? $page['h1'] ?? '') ?></li>
  </ol>
</nav>
    <?php
}

function render_footer(array $page): void
{
    $b = biz();
    ?>
<footer class="footer">
  <div class="shell">
    <div class="footer__grid">
      <div>
        <a class="logo" href="<?= url() ?>" style="margin-bottom:.8rem"><?= logo_mark() ?><span class="logo__text"><b>N</b>LeveL</span></a>
        <p class="small" style="max-width:32ch">Детейлинг-студия в Екатеринбурге на ЖБИ. Мойка с сохранением ЛКП, полировка, керамика, оклейка плёнкой, химчистка, тонировка и шумоизоляция.</p>
        <div class="socials" style="margin-top:1rem">
          <a href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Telegram-канал NLeveL"><?= icon('tg') ?></a>
          <a href="<?= e($b['vkUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Группа NLeveL во ВКонтакте"><?= icon('vk') ?></a>
          <a href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="WhatsApp NLeveL"><?= icon('wa') ?></a>
          <a href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="NLeveL на Яндекс.Картах"><?= icon('pin') ?></a>
        </div>
      </div>

      <div>
        <h3>Услуги</h3>
        <ul><?php foreach (service_nav() as $s): ?><li><a href="<?= url($s['url']) ?>"><?= e($s['label']) ?></a></li><?php endforeach; ?></ul>
      </div>

      <div>
        <h3>Разделы</h3>
        <ul>
          <?php foreach (main_nav() as $n): ?><li><a href="<?= url($n['url']) ?>"><?= e($n['label']) ?></a></li><?php endforeach; ?>
          <li><a href="<?= url('booking') ?>">Онлайн-запись</a></li>
          <li><a href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">Приложение NLeveL</a></li>
        </ul>
      </div>

      <div>
        <h3>Документы</h3>
        <ul>
          <?php foreach (content()['legal']['docs'] ?? [] as $d): ?>
          <li><a href="<?= url($d['slug'] ?? '') ?>"><?= e($d['nav'] ?? $d['title'] ?? '') ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h3>Контакты</h3>
        <ul>
          <li><a href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= e($b['phone'] ?? '') ?></a></li>
          <li><a href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener">WhatsApp</a></li>
          <li><a href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener">Telegram @<?= e($b['telegram'] ?? '') ?></a></li>
          <li><a href="<?= e($b['yandexRouteUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= e($b['city'] ?? '') ?>, <?= e($b['street'] ?? '') ?></a></li>
          <li><span class="small"><?= e($b['hours'] ?? '') ?></span></li>
        </ul>
      </div>
    </div>

    <?php render_requisites(); ?>

    <div class="footer__bottom">
      <span>© <?= date('Y') ?> NLeveL — детейлинг-студия, Екатеринбург</span>
      <span>Информация на сайте не является публичной офертой. Итоговая стоимость подтверждается до начала работ.</span>
    </div>
  </div>
</footer>
    <?php
}

function render_tabbar(array $page): void
{
    ?>
<nav class="tabbar" aria-label="Быстрая навигация">
  <?php foreach (tabbar_items() as $t):
      $slug = ($t['slug'] ?? '') === 'index' ? '' : ($t['slug'] ?? ''); ?>
  <a class="tabbar__item" href="<?= url($slug) ?>"<?= cur_attr($t['slug'] ?? '', $page) ?>><?= icon($t['navIcon'] ?? 'home') ?><span><?= e($t['nav'] ?? '') ?></span></a>
  <?php endforeach; ?>
</nav>
    <?php
}

function render_fab(): void
{
    $b = biz();
    ?>
<div class="fab">
  <a class="fab__btn btn--tg" href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Написать в Telegram"><?= icon('tg') ?></a>
  <a class="fab__btn btn--wa" href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Написать в WhatsApp"><?= icon('wa') ?></a>
</div>
    <?php
}

/* ---------- Полная страница ---------- */

function render_page(array $page, callable $body, array $schema = []): void
{
    $b = biz();
    $su = site_url();
    $slug = ($page['slug'] ?? '') === 'index' ? '' : ($page['slug'] ?? '');
    $canonical = $su . '/' . ($slug === '' ? '' : $slug . '/');
    $ogImage = $su . '/assets/img/og.jpg';
    $title = tpl($page['title'] ?? 'NLeveL');
    $desc  = tpl($page['description'] ?? '');
    ?><!DOCTYPE html>
<html lang="ru" prefix="og: https://ogp.me/ns#">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="keywords" content="<?= e($page['keywords'] ?? '') ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php if (!empty($b['yandexVerification'])): ?>
<meta name="yandex-verification" content="<?= e($b['yandexVerification']) ?>">
<?php endif; ?>
<?php if (!empty($b['googleVerification'])): ?>
<meta name="google-site-verification" content="<?= e($b['googleVerification']) ?>">
<?php endif; ?>

<meta name="author" content="NLeveL">
<meta name="geo.region" content="RU-SVE">
<meta name="geo.placename" content="<?= e($b['city'] ?? '') ?>">
<meta name="geo.position" content="<?= e((string)($b['lat'] ?? '')) ?>;<?= e((string)($b['lon'] ?? '')) ?>">
<meta name="ICBM" content="<?= e((string)($b['lat'] ?? '')) ?>, <?= e((string)($b['lon'] ?? '')) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="NLeveL — детейлинг в Екатеринбурге">
<meta property="og:locale" content="ru_RU">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="NLeveL — детейлинг-студия в Екатеринбурге на ЖБИ">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">

<meta name="theme-color" content="#08090b">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="NLeveL">
<meta name="format-detection" content="telephone=no">

<link rel="manifest" href="<?= asset('manifest.webmanifest') ?>">
<link rel="icon" href="<?= asset('assets/icons/favicon-32.png') ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?= asset('assets/icons/icon-180.png') ?>">
<link rel="preconnect" href="https://api-maps.yandex.ru" crossorigin>

<link rel="stylesheet" href="<?= asset_v('assets/css/style.css') ?>">
<?= json_ld($page, $schema) ?>
</head>
<body>
<a class="skip-link" href="#main">Перейти к содержимому</a>
<?php render_header($page); ?>
<?php render_crumbs($page); ?>
<main id="main">
<?php $body(); ?>
</main>
<?php render_footer($page); ?>
<?php render_tabbar($page); ?>
<?php render_fab(); ?>
<?php render_consent(); ?>
<?php render_app_modal(); ?>
<script src="<?= asset_v('assets/js/app.js') ?>" defer></script>
</body>
</html>
<?php
}

/* ---------- Уведомление о сборе статистики ---------- */

function render_consent(): void
{
    $b = content()['legal']['banner'] ?? [];
    if (empty($b['enabled'])) {
        return;
    }
    $metrika = trim((string)(content()['legal']['metrika'] ?? ''));
    ?>
<div class="consent-bar" id="consentBar" data-metrika="<?= e($metrika) ?>" hidden>
  <div class="consent-bar__box" role="dialog" aria-modal="false" aria-labelledby="consentTitle">
    <div class="consent-bar__text">
      <h2 id="consentTitle"><?= e($b['title'] ?? '') ?></h2>
      <p><?= e($b['text'] ?? '') ?>
        <a href="<?= url('privacy') ?>"><?= e($b['more'] ?? 'Подробнее') ?></a>
      </p>
    </div>
    <div class="consent-bar__actions">
      <button class="btn btn--ghost" type="button" data-consent="necessary"><?= e($b['decline'] ?? 'Только необходимые') ?></button>
      <button class="btn btn--primary" type="button" data-consent="all"><?= e($b['accept'] ?? 'Принимаю') ?></button>
    </div>
  </div>
</div>
    <?php
}

/**
 * Сведения об исполнителе.
 *
 * Закон «О защите прав потребителей» (ст. 9) требует довести до потребителя
 * наименование исполнителя, адрес и режим работы. Для самозанятого
 * наименование — это ФИО. Поэтому блок стоит в подвале каждой страницы,
 * а не только внутри документов.
 */
function render_requisites(): void
{
    $op = content()['legal']['operator'] ?? [];
    $b = biz();
    $name = trim((string)($op['name'] ?? ''));
    if ($name === '') {
        return;
    }
    ?>
<div class="requisites">
  <b>Исполнитель:</b> <?= e($name) ?><?php if (!empty($op['status'])): ?>, <?= e($op['status']) ?><?php endif; ?><?php if (!empty($op['inn'])): ?>, ИНН <?= e($op['inn']) ?><?php endif; ?><?php if (!empty($op['ogrn'])): ?>, ОГРНИП <?= e($op['ogrn']) ?><?php endif; ?>.
  <b>Адрес:</b> <?= e($b['city'] ?? '') ?>, <?= e($b['street'] ?? '') ?>.
  <b>Режим работы:</b> <?= e($b['hours'] ?? '') ?>.
  <b>Телефон:</b> <a href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= e($b['phone'] ?? '') ?></a><?php if (!empty($op['email'])): ?>.
  <b>Почта:</b> <a href="mailto:<?= e($op['email']) ?>"><?= e($op['email']) ?></a><?php endif; ?>.
</div>
    <?php
}

/**
 * Всплывающее окно с приложением записи.
 * Разметка есть на каждой странице, но приложение внутрь подгружается
 * только когда окно открывают — иначе страница тянула бы его зря.
 */
function render_app_modal(): void
{
    $b = biz();
    ?>
<div class="appmodal" id="appModal" hidden role="dialog" aria-modal="true" aria-labelledby="appModalTitle">
  <div class="appmodal__scrim" data-appclose></div>
  <div class="appmodal__box">
    <div class="appmodal__bar">
      <span class="appmodal__title" id="appModalTitle"><?= icon('calendar') ?>Онлайн-запись NLeveL</span>
      <a class="appmodal__ext" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener"
         aria-label="Открыть приложение в новой вкладке"><?= icon('external') ?></a>
      <button class="appmodal__close" type="button" data-appclose aria-label="Закрыть">
        <?= icon('close') ?>
      </button>
    </div>
    <div class="appmodal__body" id="appModalBody">
      <div class="appmodal__loading">
        <span class="appmodal__spinner" aria-hidden="true"></span>
        Загружаем приложение…
      </div>
    </div>
  </div>
</div>
    <?php
}
