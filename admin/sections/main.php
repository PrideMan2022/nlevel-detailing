<?php
/** Главная страница сайта. */
declare(strict_types=1);
$c = content();
$pr = $c['promise'] ?? [];
$cmp = $c['comparison'] ?? [];
$ab = $c['about'] ?? [];
a_form_open('main');
?>
<?php a_group('Первый экран', 'То, что человек видит сразу, ещё не прокрутив страницу.'); ?>
<?php a_area('heroSub', 'Подзаголовок под названием студии', $c['heroSub'] ?? '', '', 3); ?>
<?php a_lines('heroPhotos', 'Три фото первого экрана', $c['heroPhotos'] ?? [], 'Имена файлов из раздела «Фотографии», по одному на строку. Первое — крупное слева.', 3); ?>

<?php a_group('Блок обещания', 'Красная плашка сразу под первым экраном — самое сильное место на странице.'); ?>
<?php a_text('promise_eyebrow', 'Надпись сверху', $pr['eyebrow'] ?? ''); ?>
<?php a_text('promise_title', 'Заголовок', $pr['title'] ?? ''); ?>
<?php a_area('promise_text', 'Текст', $pr['text'] ?? ''); ?>
<?php a_lines('promise_points', 'Пункты списка справа', $pr['points'] ?? []); ?>

<?php a_group('Блок услуг'); ?>
<?php a_area('servicesLede', 'Вводный текст', $c['servicesLede'] ?? '', '', 2); ?>

<div class="a-repeat">
<?php foreach (($c['homeCards'] ?? []) as $i => $card): ?>
  <div class="a-card">
    <div class="a-card__num">Карточка <?= $i + 1 ?></div>
    <div class="a-cols">
      <label class="a-field"><span class="a-field__label">Заголовок</span>
        <input type="text" name="card_title[<?= $i ?>]" value="<?= e($card['title'] ?? '') ?>"></label>
      <label class="a-field"><span class="a-field__label">Адрес страницы</span>
        <input type="text" name="card_url[<?= $i ?>]" value="<?= e($card['url'] ?? '') ?>"></label>
      <label class="a-field"><span class="a-field__label">Раздел прайса (для цены «от»)</span>
        <select name="card_group[<?= $i ?>]">
          <?php foreach (price_groups() as $g): ?>
          <option value="<?= e($g['id']) ?>"<?= ($card['group'] ?? '') === $g['id'] ? ' selected' : '' ?>><?= e($g['title']) ?></option>
          <?php endforeach; ?>
        </select></label>
    </div>
    <label class="a-field"><span class="a-field__label">Описание</span>
      <textarea name="card_text[<?= $i ?>]" rows="2"><?= e($card['text'] ?? '') ?></textarea></label>
  </div>
<?php endforeach; ?>
</div>

<?php a_group('Сравнение цен с рынком'); ?>
<?php a_text('cmp_title', 'Заголовок', $cmp['title'] ?? ''); ?>
<?php a_area('cmp_lede', 'Вводный текст', $cmp['lede'] ?? '', '', 2); ?>
<div class="a-repeat">
<?php $rows = $cmp['rows'] ?? []; $rows[] = ['name'=>'','our'=>'','market'=>'']; ?>
<?php foreach ($rows as $i => $r): ?>
  <div class="a-cols a-cols--3">
    <label class="a-field"><span class="a-field__label">Услуга</span>
      <input type="text" name="cmp_name[<?= $i ?>]" value="<?= e($r['name'] ?? '') ?>" placeholder="<?= $i === count($rows)-1 ? 'добавить строку…' : '' ?>"></label>
    <label class="a-field"><span class="a-field__label">У нас</span>
      <input type="text" name="cmp_our[<?= $i ?>]" value="<?= e($r['our'] ?? '') ?>"></label>
    <label class="a-field"><span class="a-field__label">По городу</span>
      <input type="text" name="cmp_market[<?= $i ?>]" value="<?= e($r['market'] ?? '') ?>"></label>
  </div>
<?php endforeach; ?>
</div>
<?php a_area('cmp_note', 'Примечание мелким шрифтом', $cmp['note'] ?? '', '', 2); ?>

<?php a_group('Блок «О студии»'); ?>
<?php a_text('about_title', 'Заголовок', $ab['title'] ?? ''); ?>
<?php a_lines('about_paragraphs', 'Абзацы', $ab['paragraphs'] ?? [], 'По одному абзацу на строку. Можно использовать <strong>жирный</strong>.', 6); ?>
<?php a_text('about_steps_title', 'Заголовок списка', $ab['stepsTitle'] ?? ''); ?>
<?php a_lines('about_steps', 'Пункты списка', $ab['steps'] ?? [], 'По одному на строку. Можно <strong>жирный</strong>.', 6); ?>
<?php a_form_close(); ?>
