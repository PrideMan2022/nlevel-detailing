<?php
/** Отзывы клиентов. */
declare(strict_types=1);

$b = biz();
$rv = reviews();

$schema = [[
    '@context' => 'https://schema.org',
    '@type'    => 'Product',
    'name'     => 'Услуги детейлинг-студии NLeveL, Екатеринбург',
    'brand'    => ['@type' => 'Brand', 'name' => 'NLeveL'],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => rating_avg(),
        'reviewCount' => reviews_total(),
        'bestRating'  => 5,
        'worstRating' => 1,
    ],
    'review' => array_map(fn($r) => [
        '@type'         => 'Review',
        'author'        => ['@type' => 'Person', 'name' => $r['author'] ?? ''],
        'datePublished' => $r['date'] ?? '',
        'reviewRating'  => ['@type' => 'Rating', 'ratingValue' => $r['rating'] ?? 5, 'bestRating' => 5],
        'reviewBody'    => $r['text'] ?? '',
        'publisher'     => ['@type' => 'Organization', 'name' => $r['source'] ?? ''],
    ], $rv),
]];

render_page($page, function () use ($b, $rv) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Отзывы</span>
  <h1><?= e($page['h1'] ?? 'Отзывы клиентов') ?></h1>
  <p class="lede" style="margin-top:.7rem"><?= e(content()['reviewsLede'] ?? 'Отзывы собраны с Яндекс.Карт и 2ГИС. Мы ничего не удаляем и не накручиваем — если что-то пошло не так, разбираемся публично.') ?></p>
</section>

<section class="section--tight shell">
  <?php block_rating(); ?>
</section>

<section class="section--tight shell">
  <div class="grid">
    <?php foreach ($rv as $r) { block_review($r); } ?>
  </div>
</section>

<?php $hon = content()['reviewsHonesty'] ?? null; if ($hon): ?>
<section class="section shell">
  <div class="panel panel--accent prose">
    <h2 style="font-size:var(--step-2)"><?= e($hon['title'] ?? '') ?></h2>
    <?php foreach ($hon['paragraphs'] ?? [] as $p): ?><p><?= $p ?></p><?php endforeach; ?>
    <p class="small">Оставить свой отзыв можно на <a href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">Яндекс.Картах</a> или в <a href="<?= e($b['gis2ReviewsUrl'] ?? '') ?>" target="_blank" rel="noopener">2ГИС</a> — мы читаем все.</p>
  </div>
</section>
<?php endif; ?>

<?php block_cta('Проверьте нас сами', 'Начните с мойки за 1 000 ₽ — и решайте, доверять ли нам полировку и плёнку.'); ?>

<?php }, $schema);
