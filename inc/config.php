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
