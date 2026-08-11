<?php
/**
 * Отзывы. Ручного списка больше нет: показываем живой виджет Яндекс.Карт,
 * поэтому новый отзыв появляется на сайте сам, без участия администратора.
 */
declare(strict_types=1);

$b = biz();
$orgId = trim((string)($b['yandexOrgId'] ?? ''));

/* Оценки берём из настроек — они же уходят в микроразметку.
   Сами тексты отзывов отдаёт Яндекс, дублировать их у себя не нужно:
   поисковики не учитывают отзывы, размещённые на собственном сайте. */
$schema = [[
    '@context' => 'https://schema.org',
    '@type'    => 'LocalBusiness',
    '@id'      => site_url() . '/#org',
    'name'     => $b['name'] ?? 'NLeveL',
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => rating_avg(),
        'reviewCount' => reviews_total(),
        'bestRating'  => 5,
        'worstRating' => 1,
    ],
]];

render_page($page, function () use ($b, $orgId) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Отзывы</span>
  <h1><?= e($page['h1'] ?? 'Отзывы клиентов') ?></h1>
  <p class="lede" style="margin-top:.7rem"><?= e(content()['reviewsLede'] ?? '') ?></p>
</section>

<section class="section--tight shell">
  <?php block_rating(); ?>
</section>

<section class="section--tight shell">
  <?php if ($orgId !== ''): ?>
  <div class="reviews-widget">
    <div class="reviews-widget__head">
      <?= icon('star') ?>
      <span>Отзывы с Яндекс.Карт — обновляются автоматически</span>
      <a class="chip" href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">Открыть на Яндексе</a>
    </div>
    <iframe class="reviews-widget__frame"
            src="https://yandex.ru/maps-reviews-widget/<?= e($orgId) ?>?comments"
            title="Отзывы о детейлинг-студии NLeveL на Яндекс.Картах"
            loading="lazy"></iframe>
  </div>
  <?php else: ?>
  <div class="panel panel--accent">
    <p class="small" style="margin:0">Не указан номер организации на Яндекс.Картах — виджет отзывов не может загрузиться.
    Впишите его в админке, раздел «Контакты».</p>
  </div>
  <?php endif; ?>

  <div class="split" style="margin-top:var(--gap-md)">
    <div class="panel">
      <h2 style="font-size:var(--step-2);margin-bottom:.6rem">Отзывы в 2ГИС</h2>
      <p class="muted" style="margin-bottom:.9rem">Там ещё <?= e((string)($b['reviews2gis'] ?? '')) ?> отзыва и оценка <?= e((string)($b['rating2gis'] ?? '')) ?> из 5. 2ГИС не даёт встроить их на сайт, поэтому — по ссылке.</p>
      <a class="btn btn--ghost" href="<?= e($b['gis2ReviewsUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('external') ?>Читать в 2ГИС</a>
    </div>
    <div class="panel">
      <h2 style="font-size:var(--step-2);margin-bottom:.6rem">Оставить отзыв</h2>
      <p class="muted" style="margin-bottom:.9rem">Мы не удаляем отзывы и не накручиваем оценки. Напишите как есть — и хорошее, и плохое.</p>
      <div class="btn-row">
        <a class="btn btn--primary" href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">На Яндекс.Картах</a>
        <a class="btn btn--ghost" href="<?= e($b['gis2ReviewsUrl'] ?? '') ?>" target="_blank" rel="noopener">В 2ГИС</a>
      </div>
    </div>
  </div>
</section>

<?php $hon = content()['reviewsHonesty'] ?? null; if (!empty($hon['title'])): ?>
<section class="section shell">
  <div class="panel panel--accent prose">
    <h2 style="font-size:var(--step-2)"><?= e($hon['title']) ?></h2>
    <?php foreach ($hon['paragraphs'] ?? [] as $p): ?><p><?= $p ?></p><?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php block_cta('Проверьте нас сами', 'Начните с мойки — и решайте, доверять ли нам полировку и плёнку.'); ?>

<?php }, $schema);
