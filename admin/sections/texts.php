<?php
/** Тексты страниц услуг. */
declare(strict_types=1);
$c = content();
$slugs = array_keys($c['serviceContent'] ?? []);
$cur = (string)($_GET['p'] ?? ($slugs[0] ?? ''));
if (!in_array($cur, $slugs, true)) { $cur = $slugs[0] ?? ''; }
$sc = $c['serviceContent'][$cur] ?? ['intro'=>'','blocks'=>[]];
$page = find_page($cur);
?>
<div class="a-tabs">
<?php foreach ($slugs as $s): $p = find_page($s); ?>
  <a href="?s=texts&p=<?= e($s) ?>"<?= $s === $cur ? ' class="is-active"' : '' ?>><?= e($p['nav'] ?? $s) ?></a>
<?php endforeach; ?>
</div>

<div class="a-card">
  <h3>Картинка страницы</h3>
  <div class="a-hero-edit">
    <img src="<?= service_img($page['hero'] ?? '') ?: gallery_img('work-8') ?>" alt="" width="180">
    <form method="post" enctype="multipart/form-data" class="a-form">
      <?= csrf_field() ?>
      <input type="hidden" name="_section" value="texts"><input type="hidden" name="_action" value="upload_service">
      <input type="hidden" name="slug" value="<?= e($cur) ?>">
      <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=texts&p='.$cur) ?>">
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
      <button class="a-btn a-btn--primary" type="submit">Заменить</button>
    </form>
  </div>
</div>

<?php a_form_open('texts'); ?>
<input type="hidden" name="slug" value="<?= e($cur) ?>">
<input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=texts&p='.$cur) ?>">
<?php a_area('intro', 'Вступление под заголовком', $sc['intro'] ?? '', 'Первый абзац страницы — самый читаемый.', 4); ?>

<?php a_group('Смысловые блоки', 'Заголовок + текст и/или список. Пустой заголовок = блок удаляется.'); ?>
<?php $blocks = $sc['blocks'] ?? []; $blocks[] = ['h'=>'','p'=>'','list'=>[],'after'=>'']; ?>
<?php foreach ($blocks as $i => $bl): ?>
<div class="a-card">
  <label class="a-field"><span class="a-field__label">Заголовок блока</span>
    <input type="text" name="b_h[<?= $i ?>]" value="<?= e($bl['h'] ?? '') ?>" placeholder="<?= $i === count($blocks)-1 ? 'новый блок…' : '' ?>"></label>
  <label class="a-field"><span class="a-field__label">Текст</span>
    <textarea name="b_p[<?= $i ?>]" rows="3"><?= e($bl['p'] ?? '') ?></textarea></label>
  <label class="a-field"><span class="a-field__label">Список</span>
    <textarea name="b_list[<?= $i ?>]" rows="4"><?= e(implode("\n", $bl['list'] ?? [])) ?></textarea>
    <span class="a-field__hint">По одному пункту на строку</span></label>
  <label class="a-field"><span class="a-field__label">Текст после списка</span>
    <textarea name="b_after[<?= $i ?>]" rows="2"><?= e($bl['after'] ?? '') ?></textarea></label>
</div>
<?php endforeach; ?>
<?php a_form_close('Сохранить страницу'); ?>
