<?php
/** Фотографии. */
declare(strict_types=1);
$c = content();
$cats = [];
foreach (gallery() as $g) { $t = $g['cat'] ?? ''; if ($t !== '' && !in_array($t, $cats, true)) { $cats[] = $t; } }
?>
<div class="a-card">
  <h3>Добавить фотографию</h3>
  <p class="a-muted">JPG, PNG или WebP до 8 МБ. Картинка автоматически сожмётся, пересоберётся в WebP и получит миниатюру.</p>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_section" value="gallery"><input type="hidden" name="_action" value="upload_gallery">
    <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=gallery') ?>">
    <div class="a-cols">
      <label class="a-field"><span class="a-field__label">Файл</span>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
      <label class="a-field"><span class="a-field__label">Категория</span>
        <input type="text" name="cat" list="catlist" value="Студия">
        <datalist id="catlist"><?php foreach ($cats as $t): ?><option value="<?= e($t) ?>"><?php endforeach; ?></datalist></label>
    </div>
    <label class="a-field"><span class="a-field__label">Подпись (alt) — что на снимке</span>
      <input type="text" name="alt" placeholder="Например: Кроссовер после оклейки сатиновой плёнкой — NLeveL, Екатеринбург">
      <span class="a-field__hint">Важно для поиска и для незрячих. Пишите по факту, что видно на фотографии.</span></label>
    <button class="a-btn a-btn--primary" type="submit">Загрузить</button>
  </form>
</div>

<?php a_form_open('gallery'); ?>
<?php a_area('worksLede', 'Вводный текст страницы «Работы»', $c['worksLede'] ?? '', '', 3); ?>

<div class="a-photos">
<?php foreach (gallery() as $i => $g): ?>
  <div class="a-photo">
    <img src="<?= gallery_img($g['f'] ?? '', true) ?>" alt="" loading="lazy">
    <input type="hidden" name="ph_f[<?= $i ?>]" value="<?= e($g['f'] ?? '') ?>">
    <label class="a-field"><span class="a-field__label">Подпись (alt)</span>
      <textarea name="ph_alt[<?= $i ?>]" rows="3"><?= e($g['alt'] ?? '') ?></textarea></label>
    <label class="a-field"><span class="a-field__label">Категория</span>
      <input type="text" name="ph_cat[<?= $i ?>]" value="<?= e($g['cat'] ?? '') ?>" list="catlist"></label>
    <div class="a-photo__foot">
      <code><?= e($g['f'] ?? '') ?></code>
      <?php a_del_button('gallery', 'del_photo', (string)$i, 'Удалить это фото?'); ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php a_form_close('Сохранить подписи'); ?>
