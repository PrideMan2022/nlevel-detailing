<?php
/**
 * Точка входа. Все публичные страницы проходят через этот файл:
 * .htaccess отправляет сюда любой адрес, которому не соответствует реальный файл.
 */
declare(strict_types=1);

/**
 * Встроенный сервер PHP (php -S) не знает про .htaccess и шлёт сюда всё подряд,
 * включая картинки и стили. Реальные файлы отдаём ему напрямую.
 * На боевом Apache этот блок не выполняется.
 */
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $real = __DIR__ . urldecode($path);
    if (is_file($real)) {
        return false;
    }
    $dir = rtrim($real, '/');
    // Админка и другие подпапки со своим index.php.
    // Корень сюда попадать не должен — иначе файл подключит сам себя.
    if ($dir !== __DIR__ && is_dir($real) && is_file($dir . '/index.php')) {
        require $dir . '/index.php';
        return true;
    }
    // Папка приложения: /app/ → /app/index.html
    if (is_dir($real) && is_file($dir . '/index.html')) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($dir . '/index.html');
        return true;
    }
}

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/data.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/layout.php';
require_once __DIR__ . '/inc/blocks.php';

/* Какую страницу просят */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$bp = base_path();
if ($bp !== '' && str_starts_with($uri, $bp)) {
    $uri = substr($uri, strlen($bp));
}
$slug = trim($uri, '/');
if ($slug === '' || $slug === 'index.php') {
    $slug = 'index';
}

/* Служебные адреса, которые генерируются на лету */
if ($slug === 'sitemap.xml') {
    require __DIR__ . '/inc/feeds/sitemap.php';
    exit;
}
if ($slug === 'robots.txt') {
    require __DIR__ . '/inc/feeds/robots.php';
    exit;
}
if ($slug === 'llms.txt' || $slug === 'llms-full.txt') {
    $full = ($slug === 'llms-full.txt');
    require __DIR__ . '/inc/feeds/llms.php';
    exit;
}
if ($slug === 'manifest.webmanifest') {
    require __DIR__ . '/inc/feeds/manifest.php';
    exit;
}

/* Ищем страницу в контенте */
$page = find_page($slug);

if (!$page) {
    http_response_code(404);
    $page = [
        'slug'        => '404',
        'nav'         => 'Страница не найдена',
        'h1'          => 'Такой страницы нет',
        'title'       => 'Страница не найдена — NLeveL, детейлинг Екатеринбург',
        'description' => 'Запрошенная страница не найдена. Перейдите на главную или в прайс детейлинг-студии NLeveL в Екатеринбурге.',
        'keywords'    => '',
    ];
    render_page($page, function () { ?>
      <section class="section shell" style="text-align:center">
        <span class="eyebrow" style="justify-content:center">404</span>
        <h1 style="margin-block:.6rem">Такой страницы нет</h1>
        <p class="lede" style="margin-inline:auto">Возможно, ссылка устарела. Загляните в прайс или запишитесь — там точно всё на месте.</p>
        <div class="btn-row" style="justify-content:center;margin-top:var(--gap-md)">
          <a class="btn btn--primary btn--lg" href="<?= url() ?>">На главную</a>
          <a class="btn btn--ghost btn--lg" href="<?= url('price') ?>">Прайс</a>
          <a class="btn btn--ghost btn--lg" href="<?= url('booking') ?>">Записаться</a>
        </div>
      </section>
    <?php });
    exit;
}

/* Свой шаблон или общий услуговый */
$tpl = __DIR__ . '/inc/pages/' . $slug . '.php';
if (is_file($tpl)) {
    require $tpl;
} elseif (!empty($page['service'])) {
    require __DIR__ . '/inc/pages/service.php';
} else {
    http_response_code(500);
    exit('Для страницы «' . e($slug) . '» не найден шаблон.');
}
