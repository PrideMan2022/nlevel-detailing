<?php
/** Общий шаблон юридического документа. */
declare(strict_types=1);

$slug = $page['slug'] ?? '';
$doc = null;
foreach (content()['legal']['docs'] ?? [] as $d) {
    if (($d['slug'] ?? '') === $slug) {
        $doc = $d;
        break;
    }
}
if (!$doc) {
    http_response_code(404);
    exit('Документ не найден');
}

$op = content()['legal']['operator'] ?? [];
$updated = $op['updated'] ?? date('Y-m-d');

/** Реквизиты подставляются в текст вместо метки. Не заполнены — честно об этом пишем. */
$who = trim((string)($op['name'] ?? ''));
$parts = array_filter([
    $who,
    // У самозанятых ОГРН нет — поле просто выпадает
    !empty($op['status']) ? $op['status'] : '',
    !empty($op['inn']) ? 'ИНН ' . $op['inn'] : '',
    !empty($op['ogrn']) ? 'ОГРН ' . $op['ogrn'] : '',
]);
$operatorLine = $parts ? implode(', ', $parts) : '';

/**
 * Подставляет реквизиты. Если они не заполнены, строка-реквизит целиком
 * выпадает из документа — лучше, чем оставить в тексте пустое место.
 */
$fill = function (string $s) use ($operatorLine): ?string {
    if (!str_contains($s, '[РЕКВИЗИТЫ НЕ ЗАПОЛНЕНЫ]')) {
        return $s;
    }
    if ($operatorLine === '') {
        return null;
    }
    return str_replace('[РЕКВИЗИТЫ НЕ ЗАПОЛНЕНЫ]', $operatorLine, $s);
};

render_page($page, function () use ($doc, $op, $updated, $operatorLine, $fill) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Документы</span>
  <h1><?= e($doc['title'] ?? '') ?></h1>
  <?php if (!empty($doc['lede'])): ?>
  <p class="lede" style="margin-top:.7rem"><?= e($doc['lede']) ?></p>
  <?php endif; ?>
</section>

<section class="section--tight shell">
  <div class="split split--aside">
    <div class="prose legal-doc">
      <?php if ($operatorLine === ''): ?>
      <div class="panel panel--accent" style="margin-bottom:var(--gap-md)">
        <p class="small" style="margin:0"><strong>Реквизиты владельца сайта пока не заполнены.</strong>
        Документ действует как проект: чтобы он имел юридическую силу, укажите наименование, ИНН и ОГРН
        в админке — раздел «Документы».</p>
      </div>
      <?php endif; ?>

      <?php foreach ($doc['sections'] ?? [] as $s): ?>
      <h2><?= e($s['h'] ?? '') ?></h2>
      <?php foreach ($s['p'] ?? [] as $p): $t = $fill($p); if ($t === null) { continue; } ?>
      <p><?= e($t) ?></p>
      <?php endforeach; ?>
      <?php if (!empty($s['list'])): ?>
      <ul>
        <?php foreach ($s['list'] as $li): $t = $fill($li); if ($t === null) { continue; } ?><li><?= e($t) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php if (!empty($s['after'])): $t = $fill($s['after']); ?><?php if ($t !== null): ?><p><?= e($t) ?></p><?php endif; endif; ?>
      <?php endforeach; ?>

      <hr style="border:0;border-top:1px solid var(--line);margin-block:var(--gap-md)">
      <p class="small">
        Редакция от <?= e(ru_date($updated)) ?> <?= e(date('Y', strtotime($updated) ?: time())) ?> года.
        <?php if ($operatorLine !== ''): ?><br>Оператор: <?= e($operatorLine) ?>.<?php endif; ?>
        <?php if (!empty($op['address'])): ?><br>Адрес: <?= e($op['address']) ?>.<?php endif; ?>
        <?php if (!empty($op['email'])): ?><br>Электронная почта: <a href="mailto:<?= e($op['email']) ?>"><?= e($op['email']) ?></a>.<?php endif; ?>
      </p>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Все документы</h3>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.2rem">
          <?php foreach (content()['legal']['docs'] ?? [] as $d): ?>
          <li><a href="<?= url($d['slug']) ?>" style="display:block;padding:.5rem 0;color:<?= ($d['slug'] ?? '') === ($doc['slug'] ?? '') ? 'var(--accent)' : 'var(--text-2)' ?>"><?= e($d['nav'] ?? $d['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="panel" style="margin-top:var(--gap-sm)">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Вопрос по данным?</h3>
        <p class="small" style="margin-bottom:.8rem">Напишите или позвоните — ответим и при необходимости удалим ваши данные.</p>
        <?php block_contacts(); ?>
      </div>
    </aside>
  </div>
</section>

<?php });
