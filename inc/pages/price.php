<?php
/** Прайс-лист. */
declare(strict_types=1);

$b = biz();
$groups = price_groups();

$offers = [];
foreach ($groups as $g) {
    foreach ($g['items'] as $it) {
        $offers[] = [
            '@type'        => 'Offer',
            'name'         => $it['name'] ?? '',
            'description'  => $it['desc'] ?? '',
            'price'        => $it['price'] ?? 0,
            'priceCurrency' => 'RUB',
            'availability' => 'https://schema.org/InStock',
            'areaServed'   => $b['city'] ?? 'Екатеринбург',
            'seller'       => ['@id' => site_url() . '/#org'],
        ];
    }
}
$schema = [[
    '@context' => 'https://schema.org',
    '@type'    => 'OfferCatalog',
    'name'     => 'Прайс-лист детейлинг-студии NLeveL, Екатеринбург',
    'url'      => site_url() . '/price/',
    'itemListElement' => $offers,
]];

render_page($page, function () use ($b, $groups) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Прайс-лист</span>
  <h1><?= e($page['h1'] ?? 'Прайс-лист') ?></h1>
  <p class="lede" style="margin-top:.7rem"><?= e(content()['priceLede'] ?? 'Все цены открыты и указаны за конкретный объём работ. Итоговая стоимость согласуется до заезда в бокс — доплат «за сильное загрязнение» у нас не бывает.') ?></p>

  <div class="btn-row" style="margin-top:var(--gap-md)">
    <a class="btn btn--primary" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться</a>
    <a class="btn btn--ghost" href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?><?= e($b['phone'] ?? '') ?></a>
  </div>

  <nav class="filters" style="margin-top:var(--gap-md)" aria-label="Разделы прайса">
    <?php foreach ($groups as $g): ?>
    <a class="btn btn--ghost" href="#<?= e($g['id'] ?? '') ?>"><?= e($g['title'] ?? '') ?></a>
    <?php endforeach; ?>
  </nav>
</section>

<section class="section--tight shell">
  <?php foreach ($groups as $g): ?>
  <div class="price-group" id="<?= e($g['id'] ?? '') ?>">
    <div class="price-group__head">
      <h2><?= e($g['title'] ?? '') ?></h2>
      <?php if (!empty($g['note'])): ?><p class="small" style="flex:1 1 20ch"><?= e($g['note']) ?></p><?php endif; ?>
    </div>
    <div class="ptable">
      <?php foreach ($g['items'] as $it) { block_price_row($it); } ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php $notes = content()['priceNotes'] ?? []; if ($notes): ?>
  <div class="panel panel--accent">
    <h3 style="font-size:var(--step-1);margin-bottom:.6rem">Что важно знать про цены</h3>
    <ul class="tick-list">
      <?php foreach ($notes as $n): ?><li><?= $n ?></li><?php endforeach; ?>
    </ul>
    <p class="small" style="margin-top:.8rem">Прайс актуален на <?= ru_date(date('Y-m-d')) ?> <?= date('Y') ?> года. Информация на сайте не является публичной офертой.</p>
  </div>
  <?php endif; ?>
</section>

<?php block_cta('Готовы записаться?', 'Выберите услугу и удобное время — подтвердим запись и назовём точный срок.'); ?>

<?php }, $schema);
