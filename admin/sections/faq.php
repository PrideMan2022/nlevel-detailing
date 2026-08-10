<?php
/** Вопросы и преимущества. */
declare(strict_types=1);
$iconChoices = ['check-clock'=>'Часы с галочкой','wallet'=>'Кошелёк','shield'=>'Щит','star'=>'Звезда','camera'=>'Камера','parking'=>'Парковка','clock'=>'Часы','drop'=>'Капля','spray'=>'Распылитель','sparkle'=>'Блеск','sound'=>'Звук','car'=>'Машина','film'=>'Плёнка'];
?>
<div class="a-row" style="margin-bottom:1rem">
  <form method="post" class="a-inline"><?= csrf_field() ?>
    <input type="hidden" name="_section" value="faq"><input type="hidden" name="_action" value="add_faq">
    <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=faq') ?>">
    <button class="a-btn a-btn--ghost" type="submit">+ Вопрос</button></form>
  <form method="post" class="a-inline"><?= csrf_field() ?>
    <input type="hidden" name="_section" value="faq"><input type="hidden" name="_action" value="add_adv">
    <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=faq') ?>">
    <button class="a-btn a-btn--ghost" type="submit">+ Преимущество</button></form>
</div>

<?php a_form_open('faq'); ?>
<?php a_group('Преимущества', 'Шесть плашек в блоке «Почему к нам» на главной.'); ?>
<?php foreach (advantages() as $i => $a): ?>
<div class="a-card">
  <div class="a-cols">
    <label class="a-field"><span class="a-field__label">Заголовок</span>
      <input type="text" name="adv_title[<?= $i ?>]" value="<?= e($a['title'] ?? '') ?>"></label>
    <label class="a-field"><span class="a-field__label">Значок</span>
      <select name="adv_icon[<?= $i ?>]">
        <?php foreach ($iconChoices as $k => $lbl): ?><option value="<?= e($k) ?>"<?= ($a['icon'] ?? '') === $k ? ' selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?>
      </select></label>
  </div>
  <label class="a-field"><span class="a-field__label">Текст</span>
    <textarea name="adv_text[<?= $i ?>]" rows="2"><?= e($a['text'] ?? '') ?></textarea></label>
  <div class="a-row a-row--end"><?php a_del_button('faq', 'del_adv', (string)$i, 'Удалить преимущество?'); ?></div>
</div>
<?php endforeach; ?>

<?php a_group('Частые вопросы', 'Показываются на главной и на страницах услуг, а также попадают в микроразметку FAQ для поисковиков.'); ?>
<?php foreach (faq_items() as $i => $f): ?>
<div class="a-card">
  <label class="a-field"><span class="a-field__label">Вопрос</span>
    <input type="text" name="q[<?= $i ?>]" value="<?= e($f['q'] ?? '') ?>"></label>
  <label class="a-field"><span class="a-field__label">Ответ</span>
    <textarea name="a[<?= $i ?>]" rows="3"><?= e($f['a'] ?? '') ?></textarea></label>
  <div class="a-row a-row--end"><?php a_del_button('faq', 'del_faq', (string)$i, 'Удалить вопрос?'); ?></div>
</div>
<?php endforeach; ?>
<?php a_form_close(); ?>
