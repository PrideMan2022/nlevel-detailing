<?php
/** Смена пароля. */
declare(strict_types=1);
$acc = admin_account();
?>
<div class="a-card" style="max-width:32rem">
  <h3>Смена пароля</h3>
  <p class="a-muted">Логин: <code><?= e($acc['login'] ?? '') ?></code></p>
  <form method="post" class="a-form" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="_section" value="account"><input type="hidden" name="_action" value="password">
    <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=account') ?>">
    <label class="a-field"><span class="a-field__label">Текущий пароль</span>
      <input type="password" name="current" required></label>
    <label class="a-field"><span class="a-field__label">Новый пароль</span>
      <input type="password" name="password" required minlength="8" placeholder="минимум 8 символов"></label>
    <label class="a-field"><span class="a-field__label">Повторите новый</span>
      <input type="password" name="password2" required minlength="8"></label>
    <button class="a-btn a-btn--primary" type="submit">Изменить пароль</button>
  </form>
</div>
