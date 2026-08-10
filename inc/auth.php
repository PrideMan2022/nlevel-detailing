<?php
/**
 * Вход в админку. Пользователь один, поэтому базы нет:
 * логин и хеш пароля лежат в data/admin.php, который не коммитится.
 */
declare(strict_types=1);

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (($_SERVER['HTTPS'] ?? '') === 'on');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Учётные данные администратора. Пусто — значит админка ещё не настроена. */
function admin_account(): array
{
    if (!is_file(ADMIN_FILE)) {
        return [];
    }
    $data = require ADMIN_FILE;
    return is_array($data) ? $data : [];
}

function admin_configured(): bool
{
    $a = admin_account();
    return !empty($a['login']) && !empty($a['hash']);
}

/** Первичная настройка: сохранить логин и пароль. */
function create_admin(string $login, string $password): bool
{
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0775, true);
    }
    $payload = [
        'login'   => $login,
        'hash'    => password_hash($password, PASSWORD_DEFAULT),
        'created' => date('c'),
    ];
    $php = "<?php\n// Учётные данные администратора NLeveL. Не публиковать и не коммитить.\nreturn "
        . var_export($payload, true) . ";\n";
    return file_put_contents(ADMIN_FILE, $php, LOCK_EX) !== false;
}

function change_password(string $password): bool
{
    $a = admin_account();
    if (!$a) {
        return false;
    }
    return create_admin($a['login'], $password);
}

/* ---------- Защита от подбора ---------- */

function login_throttle_file(): string
{
    return DATA_DIR . '/.login_attempts';
}

function login_attempts(): array
{
    $f = login_throttle_file();
    if (!is_file($f)) {
        return ['count' => 0, 'until' => 0];
    }
    $d = json_decode((string)file_get_contents($f), true);
    return is_array($d) ? $d + ['count' => 0, 'until' => 0] : ['count' => 0, 'until' => 0];
}

function login_locked_for(): int
{
    $a = login_attempts();
    $left = (int)$a['until'] - time();
    return $left > 0 ? $left : 0;
}

function register_failed_login(): void
{
    $a = login_attempts();
    $a['count'] = (int)$a['count'] + 1;
    if ($a['count'] >= LOGIN_MAX_ATTEMPTS) {
        $a['until'] = time() + LOGIN_LOCK_SECONDS;
        $a['count'] = 0;
    }
    @file_put_contents(login_throttle_file(), json_encode($a), LOCK_EX);
}

function reset_login_attempts(): void
{
    @unlink(login_throttle_file());
}

/* ---------- Сессия ---------- */

function attempt_login(string $login, string $password): bool
{
    $a = admin_account();
    if (!$a) {
        return false;
    }
    // hash_equals — чтобы по времени ответа нельзя было угадать логин
    $loginOk = hash_equals((string)$a['login'], $login);
    $passOk  = password_verify($password, (string)$a['hash']);
    if (!$loginOk || !$passOk) {
        register_failed_login();
        return false;
    }
    reset_login_attempts();
    session_regenerate_id(true);
    $_SESSION['admin'] = $a['login'];
    $_SESSION['since'] = time();
    return true;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . base_path() . '/admin/?need=login');
        exit;
    }
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function check_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(419);
        exit('Сессия устарела. Обновите страницу и повторите.');
    }
}
