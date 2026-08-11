<?php
/** Общий шаблон услуговой страницы: полировка, плёнка, химчистка и т. д. */
declare(strict_types=1);

$b = biz();
$slug = $page['slug'] ?? '';
$c = content()['serviceContent'][$slug] ?? ['intro' => '', 'blocks' => []];

/* Какие позиции прайса показывать на этой странице */
$items = [];
if ($slug === 'oklejka-plenkoj') {
    // Эта страница — про внешний вид: смена цвета, антихром, обвесы.
    // Защитный полиуретан живёт на отдельной странице, чтобы две страницы
    // не делили один прайс и не мешали друг другу в выдаче.
    foreach ((find_group('styling')['items'] ?? []) as $i) {
        if (!str_contains($i['name'] ?? '', 'Тонировка') && !str_contains($i['name'] ?? '', 'Притемнение')) {
            $items[] = $i;
        }
    }
} elseif ($slug === 'antigraviynaya-plenka') {
    $items = find_group('plenka')['items'] ?? [];
} elseif ($slug === 'tonirovka') {
    foreach ((find_group('styling')['items'] ?? []) as $i) {
        if (str_contains($i['name'] ?? '', 'Тонировка') || str_contains($i['name'] ?? '', 'Притемнение')) {
            $items[] = $i;
        }
    }
} else {
    $items = find_group($page['service'] ?? '')['items'] ?? [];
}

$minPrice = PHP_INT_MAX;
foreach ($items as $i) {
    $minPrice = min($minPrice, (int)$i['price']);
}
if ($minPrice === PHP_INT_MAX) {
    $minPrice = 0;
}

/* Фото для галереи внизу: профильные, добираем видами студии */
$want = [
    'oklejka-plenkoj'        => ['Оклейка'],
    'antigraviynaya-plenka'  => ['Оклейка'],
    'tonirovka'              => ['Оклейка', 'Детали'],
    'mojka'                  => ['Мойка'],
    'polirovka'              => ['Детали', 'Оклейка'],
    'keramika'               => ['Оклейка', 'Мойка'],
    'himchistka'             => ['Мойка'],
    'shumoizolyaciya'        => ['Детали'],
][$slug] ?? [];
$primary = [];
$filler = [];
foreach (gallery() as $g) {
    if (in_array($g['cat'] ?? '', $want, true)) {
        $primary[] = $g;
    } elseif (($g['cat'] ?? '') === 'Студия') {
        $filler[] = $g;
    }
}
$photos = array_slice(array_merge($primary, $filler), 0, 6);

/* Релевантный FAQ */
$relFaq = [];
foreach (faq_items() as $f) {
    $k = mb_strtolower(($f['q'] ?? '') . ($f['a'] ?? ''), 'UTF-8');
    $hit = match ($slug) {
        'polirovka'      => str_contains($k, 'полир'),
        'himchistka'     => str_contains($k, 'химчист'),
        'keramika'       => str_contains($k, 'керамик'),
        'tonirovka'      => str_contains($k, 'тониров'),
        'mojka'          => str_contains($k, 'мойк') || str_contains($k, 'записи'),
        default          => str_contains($k, 'плён') || str_contains($k, 'плен') || str_contains($k, 'оклеи'),
    };
    if ($hit) {
        $relFaq[] = $f;
    }
}
if (!$relFaq) {
    $relFaq = array_slice(faq_items(), 0, 4);
}

$heroSrc = !empty($page['hero']) ? service_img($page['hero']) : null;
if (!$heroSrc) {
    $heroSrc = gallery_img('work-8');
}

$schema = [
    [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $page['h1'] ?? '',
        'serviceType' => $page['nav'] ?? '',
        'description' => $page['description'] ?? '',
        'url'         => site_url() . '/' . $slug . '/',
        'provider'    => ['@id' => site_url() . '/#org'],
        'areaServed'  => ['@type' => 'City', 'name' => $b['city'] ?? 'Екатеринбург'],
        'offers'      => array_map(fn($i) => [
            '@type'         => 'Offer',
            'name'          => $i['name'] ?? '',
            'description'   => $i['desc'] ?? '',
            'price'         => $i['price'] ?? 0,
            'priceCurrency' => 'RUB',
            'availability'  => 'https://schema.org/InStock',
        ], $items),
    ],
    faq_schema($relFaq),
];

render_page($page, function () use ($b, $page, $c, $items, $minPrice, $photos, $relFaq, $heroSrc, $slug) { ?>

<section class="section--tight shell">
  <div class="split" style="align-items:center">
    <div>
      <span class="eyebrow"><?= e($page['nav'] ?? '') ?> · <?= e($b['city'] ?? '') ?></span>
      <h1 style="margin-block:.5rem .7rem"><?= e($page['h1'] ?? '') ?></h1>
      <p class="lede"><?= e($c['intro'] ?? '') ?></p>
      <div class="btn-row" style="margin-top:var(--gap-md)">
        <a class="btn btn--primary btn--lg" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться</a>
        <a class="btn btn--ghost btn--lg" href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?><?= e($b['phone'] ?? '') ?></a>
      </div>
      <p class="small" style="margin-top:.8rem">от <?= money($minPrice) ?> · <?= e($b['city'] ?? '') ?>, <?= e($b['street'] ?? '') ?> · <?= e($b['hours'] ?? '') ?></p>
    </div>
    <div class="card__media" style="border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--line)">
      <img src="<?= $heroSrc ?>" alt="<?= e(($page['h1'] ?? '') . ' — детейлинг-студия NLeveL') ?>"
           title="<?= e(($page['h1'] ?? '') . ' в NLeveL, Екатеринбург') ?>"
           width="771" height="1024" fetchpriority="high" decoding="async">
    </div>
  </div>
</section>

<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Цены</span>
      <h2>Сколько это стоит</h2>
    </div>
    <a class="btn btn--ghost" href="<?= url('price') ?>">Весь прайс</a>
  </div>
  <div class="ptable">
    <?php foreach ($items as $it) { block_price_row($it); } ?>
  </div>
</section>

<?php
$crossRef = [
  'oklejka-plenkoj'       => ['antigraviynaya-plenka', 'Нужна защита от камней, а не смена цвета?',
                              'Антигравийный полиуретан — прозрачная плёнка, которая принимает удары камней на себя. Весь кузов 165 000 ₽, зона риска 60 000 ₽.'],
  'antigraviynaya-plenka' => ['oklejka-plenkoj', 'Хотите поменять цвет, а не защитить кузов?',
                              'Виниловая плёнка меняет внешний вид: полная смена цвета 60 000 ₽, антихром 1 500 ₽ за элемент, обвесы 17 000 ₽.'],
][$slug] ?? null;
if ($crossRef): $cp = find_page($crossRef[0]); ?>
<section class="section--tight shell">
  <div class="panel panel--accent">
    <div class="split" style="gap:var(--gap-sm);align-items:center">
      <div>
        <h2 style="font-size:var(--step-2);margin-bottom:.4rem"><?= e($crossRef[1]) ?></h2>
        <p class="muted" style="margin:0"><?= e($crossRef[2]) ?></p>
      </div>
      <div>
        <a class="btn btn--primary" href="<?= url($crossRef[0]) ?>"><?= e($cp['nav'] ?? '') ?> в Екатеринбурге →</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section shell">
  <div class="split split--aside">
    <div class="prose">
      <?php foreach ($c['blocks'] ?? [] as $bl): ?>
      <h2><?= e($bl['h'] ?? '') ?></h2>
      <?php if (!empty($bl['p'])): ?><p><?= e($bl['p']) ?></p><?php endif; ?>
      <?php if (!empty($bl['list'])): ?>
      <ul><?php foreach ($bl['list'] as $li): ?><li><?= e($li) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if (!empty($bl['after'])): ?><p><?= e($bl['after']) ?></p><?php endif; ?>
      <?php endforeach; ?>

      <h2>Почему к нам</h2>
      <ul>
        <li><strong>Цена фиксируется до начала работ.</strong> Мы называем сумму на осмотре, и она не меняется при выдаче.</li>
        <li><strong>Подтверждаем запись заранее.</strong> Вы не приедете к закрытым воротам.</li>
        <li><strong>Фотоотчёт до и после.</strong> Показываем проблемные места до того, как взялись за работу.</li>
        <li><strong>Рейтинг <?= rating_avg() ?> по <?= reviews_total() ?> отзывам</strong> на Яндекс.Картах и в 2ГИС.</li>
      </ul>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Записаться на <?= e($page['navAcc'] ?? mb_strtolower($page['nav'] ?? '', 'UTF-8')) ?></h3>
        <p class="small" style="margin-bottom:.9rem">Подтвердим время и назовём точную цену до начала работ.</p>
        <a class="btn btn--primary btn--block" href="<?= url('booking') ?>"><?= icon('calendar') ?>Онлайн-запись</a>
        <div class="btn-row" style="margin-top:.5rem">
          <a class="btn btn--wa" style="flex:1" href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('wa') ?>WhatsApp</a>
          <a class="btn btn--tg" style="flex:1" href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('tg') ?>Telegram</a>
        </div>
      </div>

      <div class="panel" style="margin-top:var(--gap-sm)">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Другие услуги</h3>
        <ul class="side-links">
          <?php foreach (service_nav() as $s): if ($s['url'] === $slug) { continue; } ?>
          <li><a href="<?= url($s['url']) ?>"><?= icon($s['icon']) ?><?= e($s['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>
  </div>
</section>

<?php if ($photos): ?>
<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Работы</span>
      <h2>Как это выглядит</h2>
    </div>
    <a class="btn btn--ghost" href="<?= url('works') ?>">Все работы</a>
  </div>
  <?php block_gallery($photos); ?>
</section>
<?php endif; ?>

<?php block_faq($relFaq); ?>
<?php block_cta('Запишитесь на ' . ($page['navAcc'] ?? mb_strtolower($page['nav'] ?? '', 'UTF-8')), 'Приезжайте на осмотр — назовём точную цену и срок, а решать будете вы.'); ?>

<?php }, $schema);
