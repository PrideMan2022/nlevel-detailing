<?php
/**
 * NLeveL — общие настройки.
 * Здесь нет ни паролей, ни ключей: учётные данные лежат в data/admin.php,
 * который не попадает в репозиторий.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('ADMIN_FILE', DATA_DIR . '/admin.php');
define('UPLOAD_DIR', ROOT . '/assets/img/uploads');
define('BACKUP_DIR', DATA_DIR . '/backups');

// Загрузка картинок
const MAX_UPLOAD_BYTES = 8 * 1024 * 1024; // 8 МБ
const ALLOWED_IMAGE_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

// Защита входа
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 900; // 15 минут

/** Базовый URL сайта относительно корня домена (для локального запуска — пусто). */
function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = rtrim($dir, '/');
    // Из /admin/index.php поднимаемся на уровень выше
    if (substr($dir, -6) === '/admin') {
        $dir = substr($dir, 0, -6);
    }
    return $dir === '/' ? '' : $dir;
}

/** Полный адрес сайта — берётся из контента, а при локальном запуске из запроса. */
function site_url(): string
{
    static $url = null;
    if ($url !== null) {
        return $url;
    }
    $c = content();
    $fromData = $c['site']['url'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // На локалке ссылки должны вести на локалку, иначе canonical уведёт на прод
    if ($host !== '' && (str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1'))) {
        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on') ? 'https' : 'http';
        return $url = $scheme . '://' . $host . base_path();
    }
    return $url = rtrim($fromData, '/');
}

session_name('nlevel_admin');

/**
 * Заголовки безопасности отдаём из PHP, а не только из .htaccess:
 * на nginx-хостингах .htaccess не читается, и защита бы потерялась.
 */
function security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
    header_remove('X-Powered-By');

    // Что странице разрешено загружать. Карту Яндекса и Метрику пускаем,
    // остальное — только со своего домена. Внешних скриптов у сайта нет.
    $csp = [
        "default-src 'self'",
        "script-src 'self' https://mc.yandex.ru https://api-maps.yandex.ru https://yastatic.net",
        "style-src 'self' 'unsafe-inline'",
        "img-src 'self' data: https://mc.yandex.ru https://*.maps.yandex.net https://*.yandex.ru https://*.gstatic.com",
        "font-src 'self' data: https://fonts.gstatic.com",
        "connect-src 'self' https://mc.yandex.ru https://*.googleapis.com https://*.firebaseio.com https://*.cloudfunctions.net https://www.gstatic.com wss://*.firebaseio.com",
        "frame-src 'self' https://yandex.ru https://*.yandex.ru https://oauth.telegram.org",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "object-src 'none'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

/** В бою подробности ошибок посетителю не показываем — только в лог. */
function configure_errors(): void
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $local = str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1');
    ini_set('display_errors', $local ? '1' : '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}
