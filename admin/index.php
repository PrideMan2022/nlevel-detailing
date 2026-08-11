<?php
/**
 * Админка NLeveL.
 * Один вход, вся начинка сайта правится отсюда.
 */
declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/data.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/upload.php';
require_once __DIR__ . '/ui.php';

security_headers();
configure_errors();

start_session();

$notice = null;
$error = null;

/* ---------- Первый запуск: создаём администратора ---------- */
if (!admin_configured()) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $login = trim((string)($_POST['login'] ?? ''));
        $p1 = (string)($_POST['password'] ?? '');
        $p2 = (string)($_POST['password2'] ?? '');
        if (mb_strlen($login) < 3) {
            $error = 'Логин — минимум 3 символа';
        } elseif (mb_strlen($p1) < 8) {
            $error = 'Пароль — минимум 8 символов';
        } elseif ($p1 !== $p2) {
            $error = 'Пароли не совпадают';
        } elseif (create_admin($login, $p1)) {
            header('Location: ' . base_path() . '/admin/?created=1');
            exit;
        } else {
            $error = 'Не удалось сохранить. Проверьте права на папку data/';
        }
    }
    admin_setup_screen($error);
    exit;
}

/* ---------- Вход ---------- */
if (!is_logged_in()) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $lock = login_locked_for();
        if ($lock > 0) {
            $error = 'Слишком много попыток. Попробуйте через ' . ceil($lock / 60) . ' мин.';
        } elseif (attempt_login(trim((string)($_POST['login'] ?? '')), (string)($_POST['password'] ?? ''))) {
            header('Location: ' . base_path() . '/admin/');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
    admin_login_screen($error, isset($_GET['created']));
    exit;
}

/* ---------- Выход ---------- */
if (($_GET['do'] ?? '') === 'logout') {
    logout();
    header('Location: ' . base_path() . '/admin/');
    exit;
}

/* ---------- Обработка сохранений ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_csrf();
    require __DIR__ . '/actions.php';
    [$notice, $error] = handle_action();
    // После сохранения — редирект, чтобы обновление страницы не повторяло отправку
    if ($notice !== null || $error !== null) {
        $_SESSION['flash'] = ['notice' => $notice, 'error' => $error];
        $back = $_POST['_back'] ?? (base_path() . '/admin/?s=' . urlencode((string)($_POST['_section'] ?? 'main')));
        header('Location: ' . $back);
        exit;
    }
}

if (!empty($_SESSION['flash'])) {
    $notice = $_SESSION['flash']['notice'] ?? null;
    $error = $_SESSION['flash']['error'] ?? null;
    unset($_SESSION['flash']);
}

$section = (string)($_GET['s'] ?? 'main');
$sections = [
    'main'      => ['Главная', 'home'],
    'services'  => ['Услуги и прайс', 'price'],
    'texts'     => ['Тексты страниц', 'app'],
    'gallery'   => ['Фотографии', 'gallery'],
    'reviews'   => ['Страница отзывов', 'star'],
    'faq'       => ['Вопросы и ответы', 'check-clock'],
    'contacts'  => ['Контакты', 'pin'],
    'seo'       => ['SEO страниц', 'route'],
    'legal'     => ['Документы', 'shield'],
    'files'     => ['robots и карта сайта', 'route'],
    'backups'   => ['Резервные копии', 'shield'],
    'account'   => ['Пароль', 'wallet'],
];
if (!isset($sections[$section])) {
    $section = 'main';
}

admin_head($section, $sections, $notice, $error);
$file = __DIR__ . '/sections/' . $section . '.php';
if (is_file($file)) {
    require $file;
} else {
    echo '<p class="a-empty">Раздел в разработке.</p>';
}
admin_foot();
