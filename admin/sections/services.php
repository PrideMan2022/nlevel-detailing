<?php
/** Услуги и прайс. */
declare(strict_types=1);
$c = content();
?>
<p class="a-muted">Цены отсюда попадают на главную, в прайс, на страницы услуг, в микроразметку и в файл для ИИ-ассистентов. Менять нужно только здесь.</p>

<form method="post" class="a-inline" style="margin-bottom:1rem">
  <?= csrf_field() ?>
  <input type="hidden" name="_section" value="services"><input type="hidden" name="_action" value="add_group">
  <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=services') ?>">
  <button class="a-btn a-btn--ghost" type="submit">+ Добавить раздел прайса</button>
</form>

<?php a_form_open('services'); ?>
<?php a_area('priceLede', 'Вводный текст страницы прайса', $c['priceLede'] ?? '', '', 3); ?>
<?php a_lines('priceNotes', 'Примечания под прайсом', $c['priceNotes'] ?? [], 'По одному на строку. Можно <strong>жирный</strong>.', 6); ?>

<?php foreach (price_groups() as $gi => $g): ?>
<div class="a-card a-card--group">
  <div class="a-card__head">
    <h3><?= e($g['title'] ?? '') ?> <span class="a-muted">— <?= count($g['items']) ?> услуг</span></h3>
  </div>
  <div class="a-cols">
    <label class="a-field"><span class="a-field__label">Название раздела</span>
      <input type="text" name="g_title[<?= $gi ?>]" value="<?= e($g['title'] ?? '') ?>"></label>
    <label class="a-field"><span class="a-field__label">Якорь (латиницей)</span>
      <input type="text" name="g_id[<?= $gi ?>]" value="<?= e($g['id'] ?? '') ?>"></label>
  </div>
  <label class="a-field"><span class="a-field__label">Пояснение к разделу</span>
    <input type="text" name="g_note[<?= $gi ?>]" value="<?= e($g['note'] ?? '') ?>"></label>

  <?php foreach ($g['items'] as $ii => $it): ?>
  <div class="a-item">
    <div class="a-cols a-cols--price">
      <label class="a-field"><span class="a-field__label">Название услуги</span>
        <input type="text" name="i_name[<?= $gi ?>][<?= $ii ?>]" value="<?= e($it['name'] ?? '') ?>"></label>
      <label class="a-field"><span class="a-field__label">Цена, ₽</span>
        <input type="number" name="i_price[<?= $gi ?>][<?= $ii ?>]" value="<?= (int)($it['price'] ?? 0) ?>"></label>
      <label class="a-field"><span class="a-field__label">Срок</span>
        <input type="text" name="i_time[<?= $gi ?>][<?= $ii ?>]" value="<?= e($it['time'] ?? '') ?>" placeholder="1–2 дня"></label>
    </div>
    <label class="a-field"><span class="a-field__label">Описание</span>
      <textarea name="i_desc[<?= $gi ?>][<?= $ii ?>]" rows="2"><?= e($it['desc'] ?? '') ?></textarea></label>
    <div class="a-row">
      <label class="a-check"><input type="checkbox" name="i_popular[<?= $gi ?>][<?= $ii ?>]" value="1"<?= !empty($it['popular'])?' checked':'' ?>><span>Чаще всего берут</span></label>
      <label class="a-check"><input type="checkbox" name="i_best[<?= $gi ?>][<?= $ii ?>]" value="1"<?= !empty($it['best'])?' checked':'' ?>><span>Выгоднее всего</span></label>
      <input type="hidden" name="i_img[<?= $gi ?>][<?= $ii ?>]" value="<?= e($it['img'] ?? '') ?>">
    </div>
  </div>
  <?php endforeach; ?>

  <div class="a-row a-row--end">
    <button class="a-btn a-btn--ghost a-btn--sm" type="submit" name="_action" value="add_item" formnovalidate
            onclick="this.form.gidx.value=<?= $gi ?>">+ Услуга в этот раздел</button>
    <button class="a-btn a-btn--danger a-btn--sm" type="submit" name="_action" value="del_group" formnovalidate
            onclick="this.form.idx.value=<?= $gi ?>; return confirm('Удалить раздел «<?= e($g['title'] ?? '') ?>» со всеми услугами?')">Удалить раздел</button>
  </div>
</div>
<?php endforeach; ?>
<input type="hidden" name="gidx" value=""><input type="hidden" name="idx" value="">
<?php a_form_close('Сохранить прайс'); ?>
