<?php
/** Документы, реквизиты и баннер согласия. */
declare(strict_types=1);
$l = content()['legal'] ?? [];
$op = $l['operator'] ?? [];
$bn = $l['banner'] ?? [];
$docs = $l['docs'] ?? [];
$cur = (string)($_GET['d'] ?? '');
$slugs = array_column($docs, 'slug');
?>
<?php a_form_open('legal'); ?>
<?php a_group('Реквизиты владельца сайта', 'Пока они не заполнены, документы выводятся с пометкой «проект» и не имеют юридической силы. Это же наименование по закону должно быть указано как оператор персональных данных.'); ?>
<div class="a-cols">
  <?php a_text('op_name', 'Полное наименование', $op['name'] ?? '', 'Например: Индивидуальный предприниматель Иванов Иван Иванович'); ?>
  <?php a_text('op_shortName', 'Сокращённо', $op['shortName'] ?? '', 'ИП Иванов И. И.'); ?>
</div>
<div class="a-cols">
  <?php a_text('op_inn', 'ИНН', $op['inn'] ?? ''); ?>
  <?php a_text('op_ogrn', 'ОГРН / ОГРНИП', $op['ogrn'] ?? ''); ?>
  <?php a_text('op_email', 'Электронная почта', $op['email'] ?? '', 'Для обращений по обработке данных'); ?>
</div>
<?php a_text('op_status', 'Налоговый статус', $op['status'] ?? '', 'Например: самозанятый, применяет специальный налоговый режим «Налог на профессиональный доход». У самозанятых ОГРН нет — поле оставьте пустым.'); ?>
<?php a_text('op_address', 'Адрес', $op['address'] ?? ''); ?>
<?php a_text('op_updated', 'Дата редакции документов', $op['updated'] ?? date('Y-m-d'), 'В формате ГГГГ-ММ-ДД'); ?>

<?php a_group('Уведомление о сборе статистики', 'Показывается при первом заходе. Пока посетитель не выбрал вариант, аналитика не подключается.'); ?>
<?php a_check('bn_enabled', 'Показывать уведомление', !empty($bn['enabled'])); ?>
<?php a_text('bn_title', 'Заголовок', $bn['title'] ?? ''); ?>
<?php a_area('bn_text', 'Текст', $bn['text'] ?? '', '', 3); ?>
<div class="a-cols a-cols--3">
  <?php a_text('bn_accept', 'Кнопка согласия', $bn['accept'] ?? ''); ?>
  <?php a_text('bn_decline', 'Кнопка отказа', $bn['decline'] ?? ''); ?>
  <?php a_text('bn_more', 'Ссылка на документ', $bn['more'] ?? ''); ?>
</div>
<?php a_text('metrika', 'Номер счётчика Яндекс.Метрики', $l['metrika'] ?? '', 'Только цифры. Счётчик подключится лишь после согласия посетителя. Пусто — аналитики нет.'); ?>
<?php a_form_close('Сохранить реквизиты и баннер'); ?>

<?php a_group('Тексты документов', 'Разделы документа: заголовок, абзацы и список. Пустой заголовок — раздел удаляется.'); ?>
<div class="a-tabs">
<?php foreach ($docs as $d): ?>
  <a href="?s=legal&d=<?= e($d['slug']) ?>"<?= $d['slug'] === $cur ? ' class="is-active"' : '' ?>><?= e($d['nav'] ?? $d['title']) ?></a>
<?php endforeach; ?>
</div>

<?php if ($cur === '' || !in_array($cur, $slugs, true)): ?>
<p class="a-empty">Выберите документ выше, чтобы отредактировать его текст.</p>
<?php else:
  $doc = $docs[array_search($cur, $slugs, true)];
  a_form_open('legal', 'save_doc'); ?>
  <input type="hidden" name="slug" value="<?= e($cur) ?>">
  <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=legal&d='.$cur) ?>">
  <div class="a-cols">
    <?php a_text('doc_title', 'Заголовок документа', $doc['title'] ?? ''); ?>
    <?php a_text('doc_nav', 'Название в подвале', $doc['nav'] ?? ''); ?>
  </div>
  <?php a_area('doc_lede', 'Краткое описание под заголовком', $doc['lede'] ?? '', '', 2); ?>

  <?php $secs = $doc['sections'] ?? []; $secs[] = ['h'=>'','p'=>[],'list'=>[],'after'=>'']; ?>
  <?php foreach ($secs as $i => $s): ?>
  <div class="a-card">
    <label class="a-field"><span class="a-field__label">Заголовок раздела</span>
      <input type="text" name="s_h[<?= $i ?>]" value="<?= e($s['h'] ?? '') ?>" placeholder="<?= $i === count($secs)-1 ? 'новый раздел…' : '' ?>"></label>
    <label class="a-field"><span class="a-field__label">Абзацы</span>
      <textarea name="s_p[<?= $i ?>]" rows="5"><?= e(implode("\n", $s['p'] ?? [])) ?></textarea>
      <span class="a-field__hint">По одному абзацу на строку</span></label>
    <label class="a-field"><span class="a-field__label">Список</span>
      <textarea name="s_list[<?= $i ?>]" rows="4"><?= e(implode("\n", $s['list'] ?? [])) ?></textarea>
      <span class="a-field__hint">По одному пункту на строку</span></label>
    <label class="a-field"><span class="a-field__label">Текст после списка</span>
      <textarea name="s_after[<?= $i ?>]" rows="2"><?= e($s['after'] ?? '') ?></textarea></label>
  </div>
  <?php endforeach; ?>
  <?php a_form_close('Сохранить документ'); ?>
<?php endif; ?>
