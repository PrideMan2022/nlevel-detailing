<?php
/** Отзывы. */
declare(strict_types=1);
$c = content();
$h = $c['reviewsHonesty'] ?? [];
?>
<form method="post" class="a-inline" style="margin-bottom:1rem">
  <?= csrf_field() ?>
  <input type="hidden" name="_section" value="reviews"><input type="hidden" name="_action" value="add_review">
  <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=reviews') ?>">
  <button class="a-btn a-btn--ghost" type="submit">+ Добавить отзыв</button>
</form>

<?php a_form_open('reviews'); ?>
<?php a_area('reviewsLede', 'Вводный текст страницы отзывов', $c['reviewsLede'] ?? '', '', 3); ?>

<?php foreach (reviews() as $i => $r): ?>
<div class="a-card">
  <div class="a-cols a-cols--4">
    <label class="a-field"><span class="a-field__label">Имя</span>
      <input type="text" name="r_author[<?= $i ?>]" value="<?= e($r['author'] ?? '') ?>"></label>
    <label class="a-field"><span class="a-field__label">Дата</span>
      <input type="date" name="r_date[<?= $i ?>]" value="<?= e($r['date'] ?? '') ?>"></label>
    <label class="a-field"><span class="a-field__label">Подпись даты</span>
      <input type="text" name="r_datetext[<?= $i ?>]" value="<?= e($r['dateText'] ?? '') ?>" placeholder="25 июня"></label>
    <label class="a-field"><span class="a-field__label">Оценка</span>
      <select name="r_rating[<?= $i ?>]">
        <?php for ($s = 5; $s >= 1; $s--): ?><option value="<?= $s ?>"<?= (int)($r['rating'] ?? 5) === $s ? ' selected' : '' ?>><?= $s ?> ★</option><?php endfor; ?>
      </select></label>
  </div>
  <div class="a-cols">
    <label class="a-field"><span class="a-field__label">Источник</span>
      <input type="text" name="r_source[<?= $i ?>]" value="<?= e($r['source'] ?? '') ?>" placeholder="Яндекс.Карты"></label>
    <label class="a-field"><span class="a-field__label">Метка услуги</span>
      <input type="text" name="r_tag[<?= $i ?>]" value="<?= e($r['tag'] ?? '') ?>" placeholder="Мойка"></label>
  </div>
  <label class="a-field"><span class="a-field__label">Текст отзыва</span>
    <textarea name="r_text[<?= $i ?>]" rows="3"><?= e($r['text'] ?? '') ?></textarea></label>
  <div class="a-row a-row--end"><?php a_del_button('reviews', 'del_review', (string)$i, 'Удалить отзыв?'); ?></div>
</div>
<?php endforeach; ?>

<?php a_group('Блок «О наших минусах»', 'Честный разбор негатива внизу страницы отзывов. Оставьте заголовок пустым, чтобы убрать блок.'); ?>
<?php a_text('honesty_title', 'Заголовок', $h['title'] ?? ''); ?>
<?php a_lines('honesty_paragraphs', 'Абзацы', $h['paragraphs'] ?? [], 'По одному абзацу на строку.', 5); ?>
<?php a_form_close('Сохранить отзывы'); ?>
