<?php
/** Контакты и проезд. */
declare(strict_types=1);

$b = biz();

render_page($page, function () use ($b) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Контакты</span>
  <h1><?= e($page['h1'] ?? 'Контакты и как доехать') ?></h1>
  <p class="lede" style="margin-top:.7rem">Мы на <?= e($b['district'] ?? '') ?> — <?= e($b['street'] ?? '') ?>. <?= e($b['addressNote'] ?? '') ?>, на территории есть парковка на 8 машин и зона ожидания.</p>
</section>

<section class="section--tight shell">
  <div class="split">
    <div>
      <?php block_contacts(); ?>
      <div class="btn-row" style="margin-top:var(--gap-sm)">
        <a class="btn btn--primary" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться онлайн</a>
      </div>
    </div>
    <div class="panel prose">
      <?php $r = content()['route'] ?? null; if ($r): ?>
      <h2 style="font-size:var(--step-2)"><?= e($r['title'] ?? 'Как доехать') ?></h2>
      <?php foreach ($r['paragraphs'] ?? [] as $p): ?><p><?= $p ?></p><?php endforeach; ?>
      <h3>Оплата</h3>
      <p><?= e($r['payment'] ?? 'Наличные, банковская карта, перевод по QR-коду.') ?></p>
      <h3>График</h3>
      <p><?= e($b['hours'] ?? '') ?>. <?= e($r['schedule'] ?? '') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section--tight shell">
  <?php block_map(); ?>
</section>

<?php block_cta('Приезжайте на ЖБИ', 'Запишитесь заранее — подтвердим время и подготовим бокс к вашему приезду.'); ?>

<?php });
