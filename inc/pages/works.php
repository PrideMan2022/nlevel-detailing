<?php
/** Портфолио работ. */
declare(strict_types=1);

$gal = gallery();
$cats = [];
foreach ($gal as $g) {
    $c = $g['cat'] ?? '';
    if ($c !== '' && !in_array($c, $cats, true)) {
        $cats[] = $c;
    }
}

$schema = [[
    '@context' => 'https://schema.org',
    '@type'    => 'ImageGallery',
    'name'     => 'Примеры работ детейлинг-студии NLeveL',
    'url'      => site_url() . '/works/',
    'image'    => array_map(fn($g) => [
        '@type'      => 'ImageObject',
        'contentUrl' => site_url() . '/assets/img/gallery/' . ($g['f'] ?? '') . '.webp',
        'caption'    => $g['alt'] ?? '',
    ], $gal),
]];

render_page($page, function () use ($gal, $cats) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Портфолио</span>
  <h1><?= e($page['h1'] ?? 'Наши работы') ?></h1>
  <p class="lede" style="margin-top:.7rem"><?= e(content()['worksLede'] ?? 'Реальные фотографии студии на ЖБИ: боксы, оборудование, автомобили в работе и результаты оклейки плёнкой. Ничего не взято со стоков — всё снято у нас.') ?></p>
</section>

<section class="section--tight shell">
  <div class="filters" role="group" aria-label="Фильтр работ по услуге">
    <button type="button" data-filter="all" aria-pressed="true">Все работы</button>
    <?php foreach ($cats as $c): ?>
    <button type="button" data-filter="<?= e($c) ?>" aria-pressed="false"><?= e($c) ?></button>
    <?php endforeach; ?>
  </div>

  <?php block_gallery($gal, true); ?>
</section>

<?php block_cta('Хотите так же?', 'Запишитесь на осмотр — покажем варианты, назовём точную цену и срок.'); ?>

<?php }, $schema);
