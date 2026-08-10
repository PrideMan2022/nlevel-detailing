<?php
/** Оболочка и поля админки. */
declare(strict_types=1);

function admin_shell_head(string $title): void
{
    ?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — админка NLeveL</title>
<link rel="icon" href="<?= asset('assets/icons/favicon-32.png') ?>" sizes="32x32" type="image/png">
<link rel="stylesheet" href="<?= asset_v('admin/style.css') ?>">
</head>
<body>
<?php
}

/* ---------- Экран первичной настройки ---------- */
function admin_setup_screen(?string $error): void
{
    admin_shell_head('Настройка');
    ?>
<div class="a-auth">
  <form class="a-auth__card" method="post" autocomplete="off">
    <img class="a-auth__logo" src="<?= asset('assets/icons/logo.webp') ?>" width="64" height="64" alt="NLeveL">
    <h1>Первый запуск</h1>
    <p class="a-muted">Придумайте логин и пароль администратора. Они сохранятся в файле <code>data/admin.php</code> на вашем сервере и никуда больше не отправятся.</p>
    <?php if ($error): ?><p class="a-alert a-alert--bad"><?= e($error) ?></p><?php endif; ?>
    <label>Логин<input type="text" name="login" required minlength="3" autofocus></label>
    <label>Пароль<input type="password" name="password" required minlength="8" placeholder="минимум 8 символов"></label>
    <label>Повторите пароль<input type="password" name="password2" required minlength="8"></label>
    <button class="a-btn a-btn--primary" type="submit">Создать администратора</button>
  </form>
</div>
</body></html>
    <?php
}

/* ---------- Экран входа ---------- */
function admin_login_screen(?string $error, bool $created = false): void
{
    admin_shell_head('Вход');
    $lock = login_locked_for();
    ?>
<div class="a-auth">
  <form class="a-auth__card" method="post" autocomplete="off">
    <img class="a-auth__logo" src="<?= asset('assets/icons/logo.webp') ?>" width="64" height="64" alt="NLeveL">
    <h1>Вход в админку</h1>
    <?php if ($created): ?><p class="a-alert a-alert--ok">Администратор создан. Войдите под своим логином.</p><?php endif; ?>
    <?php if ($error): ?><p class="a-alert a-alert--bad"><?= e($error) ?></p><?php endif; ?>
    <?php if ($lock > 0): ?><p class="a-alert a-alert--bad">Вход временно заблокирован ещё на <?= ceil($lock / 60) ?> мин.</p><?php endif; ?>
    <label>Логин<input type="text" name="login" required autofocus <?= $lock > 0 ? 'disabled' : '' ?>></label>
    <label>Пароль<input type="password" name="password" required <?= $lock > 0 ? 'disabled' : '' ?>></label>
    <button class="a-btn a-btn--primary" type="submit" <?= $lock > 0 ? 'disabled' : '' ?>>Войти</button>
    <p class="a-muted" style="text-align:center"><a href="<?= url() ?>">← на сайт</a></p>
  </form>
</div>
</body></html>
    <?php
}

/* ---------- Каркас админки ---------- */
function admin_head(string $section, array $sections, ?string $notice, ?string $error): void
{
    admin_shell_head($sections[$section][0] ?? 'Админка');
    ?>
<div class="a-layout">
  <aside class="a-side">
    <a class="a-brand" href="<?= url() ?>" target="_blank" rel="noopener">
      <img src="<?= asset('assets/icons/logo.webp') ?>" width="34" height="34" alt="">
      <span><b>N</b>LeveL<small>админка</small></span>
    </a>
    <nav class="a-nav">
      <?php foreach ($sections as $key => [$label, $ic]): ?>
      <a href="?s=<?= e($key) ?>"<?= $key === $section ? ' class="is-active"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="a-side__foot">
      <a class="a-btn a-btn--ghost" href="<?= url() ?>" target="_blank" rel="noopener"><?= icon('external') ?>Открыть сайт</a>
      <a class="a-btn a-btn--ghost" href="?do=logout"><?= icon('close') ?>Выйти</a>
    </div>
  </aside>

  <main class="a-main">
    <header class="a-topbar">
      <button class="a-burger" type="button" aria-label="Меню">☰</button>
      <h1><?= e($sections[$section][0] ?? '') ?></h1>
    </header>

    <?php if ($notice): ?><p class="a-alert a-alert--ok"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="a-alert a-alert--bad"><?= e($error) ?></p><?php endif; ?>
    <?php
}

function admin_foot(): void
{
    ?>
    </main>
</div>
<script src="<?= asset_v('admin/admin.js') ?>" defer></script>
</body></html>
    <?php
}

/* ---------- Поля ---------- */

function a_form_open(string $section, string $action = 'save'): void
{
    echo '<form method="post" enctype="multipart/form-data" class="a-form">';
    echo csrf_field();
    echo '<input type="hidden" name="_section" value="' . e($section) . '">';
    echo '<input type="hidden" name="_action" value="' . e($action) . '">';
    echo '<input type="hidden" name="_back" value="' . e(base_path() . '/admin/?s=' . $section) . '">';
}

function a_form_close(string $label = 'Сохранить'): void
{
    echo '<div class="a-sticky-save"><button class="a-btn a-btn--primary" type="submit">' . e($label) . '</button></div></form>';
}

function a_text(string $name, string $label, string $value, string $hint = ''): void
{
    ?>
<label class="a-field">
  <span class="a-field__label"><?= e($label) ?></span>
  <input type="text" name="<?= e($name) ?>" value="<?= e($value) ?>">
  <?php if ($hint): ?><span class="a-field__hint"><?= e($hint) ?></span><?php endif; ?>
</label>
    <?php
}

function a_num(string $name, string $label, $value, string $hint = ''): void
{
    ?>
<label class="a-field">
  <span class="a-field__label"><?= e($label) ?></span>
  <input type="number" name="<?= e($name) ?>" value="<?= e((string)$value) ?>" step="any">
  <?php if ($hint): ?><span class="a-field__hint"><?= e($hint) ?></span><?php endif; ?>
</label>
    <?php
}

function a_area(string $name, string $label, string $value, string $hint = '', int $rows = 4): void
{
    ?>
<label class="a-field">
  <span class="a-field__label"><?= e($label) ?></span>
  <textarea name="<?= e($name) ?>" rows="<?= $rows ?>"><?= e($value) ?></textarea>
  <?php if ($hint): ?><span class="a-field__hint"><?= e($hint) ?></span><?php endif; ?>
</label>
    <?php
}

/** Список строк — по строке на элемент. */
function a_lines(string $name, string $label, array $value, string $hint = '', int $rows = 5): void
{
    a_area($name, $label, implode("\n", $value), $hint ?: 'По одному пункту на строку', $rows);
}

function a_check(string $name, string $label, bool $checked): void
{
    ?>
<label class="a-check">
  <input type="checkbox" name="<?= e($name) ?>" value="1"<?= $checked ? ' checked' : '' ?>>
  <span><?= e($label) ?></span>
</label>
    <?php
}

function a_select(string $name, string $label, array $options, string $value, string $hint = ''): void
{
    ?>
<label class="a-field">
  <span class="a-field__label"><?= e($label) ?></span>
  <select name="<?= e($name) ?>">
    <?php foreach ($options as $k => $v): ?>
    <option value="<?= e((string)$k) ?>"<?= (string)$k === $value ? ' selected' : '' ?>><?= e((string)$v) ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($hint): ?><span class="a-field__hint"><?= e($hint) ?></span><?php endif; ?>
</label>
    <?php
}

/** Заголовок группы полей. */
function a_group(string $title, string $desc = ''): void
{
    echo '<div class="a-group"><h2>' . e($title) . '</h2>';
    if ($desc) {
        echo '<p class="a-muted">' . e($desc) . '</p>';
    }
    echo '</div>';
}

/** Кнопка удаления внутри повторяющегося блока. */
function a_del_button(string $section, string $action, string $idx, string $confirm): void
{
    ?>
<form method="post" class="a-inline" onsubmit="return confirm('<?= e($confirm) ?>')">
  <?= csrf_field() ?>
  <input type="hidden" name="_section" value="<?= e($section) ?>">
  <input type="hidden" name="_action" value="<?= e($action) ?>">
  <input type="hidden" name="idx" value="<?= e($idx) ?>">
  <input type="hidden" name="_back" value="<?= e(base_path() . '/admin/?s=' . $section) ?>">
  <button class="a-btn a-btn--danger a-btn--sm" type="submit">Удалить</button>
</form>
    <?php
}
