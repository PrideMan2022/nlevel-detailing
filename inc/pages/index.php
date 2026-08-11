<?php
/** Главная страница. */
declare(strict_types=1);

$b = biz();
$gal = gallery();

/** Первые три фото героя — из настроек, иначе первые из галереи. */
$heroNames = content()['heroPhotos'] ?? ['work-1', 'work-16', 'work-2'];
$heroPhotos = [];
foreach ($heroNames as $n) {
    foreach ($gal as $g) {
        if (($g['f'] ?? '') === $n) {
            $heroPhotos[] = $g;
            break;
        }
    }
}
if (!$heroPhotos) {
    $heroPhotos = array_slice($gal, 0, 3);
}

/** Карточки услуг на главной. */
$cards = content()['homeCards'] ?? [];

render_page($page, function () use ($b, $gal, $heroPhotos, $cards) { ?>

<section class="hero">
  <div class="shell hero__grid">
    <div>
      <span class="eyebrow"><?= e($b['city'] ?? '') ?> · <?= e($b['district'] ?? '') ?> · <?= e($b['street'] ?? '') ?></span>
      <h1>Детейлинг-студия <mark>NLeveL</mark> в Екатеринбурге</h1>
      <p class="hero__sub"><?= e(content()['heroSub'] ?? 'Мойка с сохранением ЛКП, полировка, керамика, оклейка полиуретаном и винилом, химчистка, тонировка и шумоизоляция. Весь прайс открыт — без «уточняйте по телефону».') ?></p>

      <div class="hero__cta">
        <a class="btn btn--primary btn--lg" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться онлайн</a>
        <a class="btn btn--ghost btn--lg" href="<?= url('price') ?>"><?= icon('price') ?>Смотреть прайс</a>
      </div>

      <div class="hero__stats">
        <div class="hero__stat"><b><?= rating_avg() ?></b><span>рейтинг на Яндекс.Картах и 2ГИС</span></div>
        <div class="hero__stat"><b><?= reviews_total() ?></b><span>отзыва клиентов</span></div>
        <?php
        $minPrice = PHP_INT_MAX;
        $count = 0;
        foreach (price_groups() as $g) {
            foreach ($g['items'] as $i) {
                $minPrice = min($minPrice, (int)$i['price']);
                $count++;
            }
        }
        ?>
        <div class="hero__stat"><b>от <?= money($minPrice) ?></b><span>комплексная мойка</span></div>
        <div class="hero__stat"><b><?= $count ?></b><span>услуг с открытой ценой</span></div>
      </div>
    </div>

    <div class="hero__media">
      <?php foreach ($heroPhotos as $i => $g): ?>
      <figure>
        <img src="<?= gallery_img($g['f'] ?? '') ?>" alt="<?= e($g['alt'] ?? '') ?>"
             title="<?= e($g['cat'] ?? '') ?> — NLeveL, Екатеринбург"
             width="578" height="768" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
        <?php if ($i === 0): ?>
        <figcaption class="hero__badge"><?= icon('star') ?><b><?= rating_avg() ?></b> · <?= reviews_total() ?> отзыва</figcaption>
        <?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $promise = content()['promise'] ?? null; if ($promise): ?>
<section class="section--tight shell">
  <div class="panel panel--accent">
    <div class="split" style="gap:var(--gap-md);align-items:center">
      <div>
        <span class="eyebrow"><?= e($promise['eyebrow'] ?? 'Наше главное правило') ?></span>
        <h2 style="font-size:var(--step-2);margin-block:.4rem .6rem"><?= e($promise['title'] ?? '') ?></h2>
        <p class="muted"><?= e($promise['text'] ?? '') ?></p>
      </div>
      <ul class="tick-list">
        <?php foreach ($promise['points'] ?? [] as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section shell" id="services">
  <div class="section-head">
    <div>
      <span class="eyebrow">Услуги</span>
      <h2>Что мы делаем</h2>
      <p class="lede"><?= e(content()['servicesLede'] ?? '') ?></p>
    </div>
    <a class="btn btn--ghost" href="<?= url('price') ?>">Весь прайс</a>
  </div>

  <div class="grid">
    <?php foreach ($cards as $c):
        $grp = find_group($c['group'] ?? '');
        if (!$grp) { continue; }
        $min = PHP_INT_MAX;
        foreach ($grp['items'] as $i) { $min = min($min, (int)$i['price']); }
        $src = !empty($c['serviceImg']) ? service_img($c['serviceImg']) : gallery_img($c['galleryImg'] ?? '');
        ?>
    <a class="card reveal" href="<?= url($c['url'] ?? '') ?>">
      <div class="card__media">
        <img src="<?= $src ?>" alt="<?= e(($c['title'] ?? '') . ' в Екатеринбурге — детейлинг-студия NLeveL') ?>"
             title="<?= e(($c['title'] ?? '') . ' — NLeveL, Екатеринбург') ?>"
             width="771" height="1024" loading="lazy" decoding="async">
      </div>
      <div class="card__body">
        <h3 class="card__title"><?= e($c['title'] ?? '') ?></h3>
        <p class="card__text"><?= e($c['text'] ?? '') ?></p>
        <div class="card__foot">
          <span class="price-tag"><small>от </small><?= money($min) ?></span>
          <span class="chip">Подробнее →</span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<?php block_advantages(); ?>

<?php $cmp = content()['comparison'] ?? null; if ($cmp): ?>
<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Сравнение</span>
      <h2><?= e($cmp['title'] ?? '') ?></h2>
      <p class="lede"><?= e($cmp['lede'] ?? '') ?></p>
    </div>
  </div>
  <div class="grid grid--narrow">
    <?php foreach ($cmp['rows'] ?? [] as $r): ?>
    <div class="feature reveal">
      <h3 style="font-size:var(--step-0)"><?= e($r['name'] ?? '') ?></h3>
      <p style="margin-block:.3rem"><span class="price-tag"><?= e($r['our'] ?? '') ?></span></p>
      <p class="small">по городу <?= e($r['market'] ?? '') ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="small" style="margin-top:var(--gap-sm)"><?= e($cmp['note'] ?? '') ?></p>
</section>
<?php endif; ?>

<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Портфолио</span>
      <h2>Наши работы</h2>
    </div>
    <a class="btn btn--ghost" href="<?= url('works') ?>">Все работы</a>
  </div>
  <?php block_gallery(array_slice($gal, 0, 8)); ?>
</section>

<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Отзывы</span>
      <h2>Что говорят клиенты</h2>
    </div>
    <a class="btn btn--ghost" href="<?= url('reviews') ?>">Все отзывы</a>
  </div>
  <?php block_rating(); ?>
  <?php $orgId = trim((string)($b['yandexOrgId'] ?? '')); if ($orgId !== ''): ?>
  <div class="reviews-widget" style="margin-top:var(--gap-md)">
    <div class="reviews-widget__head">
      <?= icon('star') ?>
      <span>Отзывы с Яндекс.Карт — обновляются автоматически</span>
      <a class="chip" href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">Открыть на Яндексе</a>
    </div>
    <iframe class="reviews-widget__frame reviews-widget__frame--short"
            src="https://yandex.ru/maps-reviews-widget/<?= e($orgId) ?>?comments"
            title="Отзывы о детейлинг-студии NLeveL на Яндекс.Картах"
            loading="lazy"></iframe>
  </div>
  <?php endif; ?>
</section>

<?php $about = content()['about'] ?? null; if ($about): ?>
<section class="section shell">
  <div class="split split--aside">
    <div class="prose">
      <span class="eyebrow">О студии</span>
      <h2><?= e($about['title'] ?? '') ?></h2>
      <?php foreach ($about['paragraphs'] ?? [] as $p): ?>
      <p><?= $p ?></p>
      <?php endforeach; ?>
      <?php if (!empty($about['stepsTitle'])): ?>
      <h3><?= e($about['stepsTitle']) ?></h3>
      <ul>
        <?php foreach ($about['steps'] ?? [] as $s): ?><li><?= $s ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h3 style="font-size:var(--step-1);margin-bottom:.8rem">Контакты</h3>
        <?php block_contacts(); ?>
      </div>
    </aside>
  </div>
</section>
<?php endif; ?>

<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Как доехать</span>
      <h2><?= e($b['city'] ?? '') ?>, <?= e($b['street'] ?? '') ?></h2>
      <p class="lede"><?= e($b['addressNote'] ?? '') ?>. Ближайший ориентир — «Заводоуправление», 5 минут пешком.</p>
    </div>
  </div>
  <?php block_map(); ?>
</section>

<?php block_faq(faq_items()); ?>
<?php block_cta('Запишитесь — и приезжайте спокойно', 'Подтвердим запись заранее, назовём точную цену и время выдачи. Никаких «мы вам перезвоним».'); ?>

<?php }, [faq_schema(faq_items())]);
