<?php
/** Переиспользуемые блоки страниц. */
declare(strict_types=1);

function block_price_row(array $it): void
{
    ?>
<div class="prow">
  <div class="prow__name"><?= e($it['name'] ?? '') ?></div>
  <div class="prow__price"><span class="price-tag"><?= money($it['price'] ?? 0) ?></span></div>
  <div class="prow__desc"><?= e($it['desc'] ?? '') ?></div>
  <?php if (!empty($it['time']) || !empty($it['popular']) || !empty($it['best'])): ?>
  <div class="prow__meta">
    <?php if (!empty($it['time'])): ?><span class="chip"><?= icon('clock') ?><?= e($it['time']) ?></span><?php endif; ?>
    <?php if (!empty($it['popular'])): ?><span class="chip chip--accent">Чаще всего берут</span><?php endif; ?>
    <?php if (!empty($it['best'])): ?><span class="chip chip--accent">Выгоднее всего</span><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
    <?php
}

function block_advantages(): void
{
    $items = advantages();
    if (!$items) {
        return;
    }
    ?>
<section class="section shell" id="why">
  <div class="section-head">
    <div>
      <span class="eyebrow">Почему к нам</span>
      <h2>Мы закрыли то, на что жалуются клиенты других студий</h2>
    </div>
  </div>
  <div class="grid">
    <?php foreach ($items as $a): ?>
    <article class="feature reveal">
      <span class="feature__icon"><?= icon($a['icon'] ?? 'shield') ?></span>
      <h3><?= e(tpl($a['title'] ?? '')) ?></h3>
      <p><?= e(tpl($a['text'] ?? '')) ?></p>
    </article>
    <?php endforeach; ?>
  </div>
</section>
    <?php
}

function block_rating(): void
{
    $b = biz();
    ?>
<div class="rating-box">
  <div class="rating-box__score">
    <b><?= rating_avg() ?></b>
    <?= stars(5) ?>
    <p class="small" style="margin-top:.4rem"><?= reviews_total() ?> отзыва</p>
  </div>
  <div>
    <p style="font-weight:700">Яндекс.Карты</p>
    <p class="muted"><?= e((string)($b['ratingYandex'] ?? '')) ?> из 5 · <?= e((string)($b['reviewsYandex'] ?? '')) ?> отзывов</p>
    <p style="margin-top:.7rem"><a class="btn btn--ghost" href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">Смотреть на Яндексе</a></p>
  </div>
  <div>
    <p style="font-weight:700">2ГИС</p>
    <p class="muted"><?= e((string)($b['rating2gis'] ?? '')) ?> из 5 · <?= e((string)($b['reviews2gis'] ?? '')) ?> отзыв</p>
    <p style="margin-top:.7rem"><a class="btn btn--ghost" href="<?= e($b['gis2ReviewsUrl'] ?? '') ?>" target="_blank" rel="noopener">Смотреть в 2ГИС</a></p>
  </div>
</div>
    <?php
}

function block_faq(array $items, string $title = 'Частые вопросы'): void
{
    if (!$items) {
        return;
    }
    ?>
<section class="section shell" id="faq">
  <div class="section-head">
    <div>
      <span class="eyebrow">FAQ</span>
      <h2><?= e($title) ?></h2>
    </div>
  </div>
  <div class="faq">
    <?php foreach ($items as $f): ?>
    <details>
      <summary><?= e($f['q'] ?? '') ?></summary>
      <div class="faq__a"><?= e($f['a'] ?? '') ?></div>
    </details>
    <?php endforeach; ?>
  </div>
</section>
    <?php
}

function block_cta(string $title, string $text): void
{
    $b = biz();
    ?>
<section class="section shell">
  <div class="cta">
    <span class="eyebrow">Запись</span>
    <h2><?= e($title) ?></h2>
    <p><?= e($text) ?></p>
    <div class="btn-row" style="justify-content:center">
      <a class="btn btn--primary btn--lg" href="<?= url('booking') ?>"><?= icon('calendar') ?>Записаться онлайн</a>
      <a class="btn btn--ghost btn--lg" href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?><?= e($b['phone'] ?? '') ?></a>
    </div>
    <p class="small">Подтверждаем запись заранее. Работаем <?= e($b['hours'] ?? '') ?>.</p>
  </div>
</section>
    <?php
}

function block_map(): void
{
    $b = biz();
    ?>
<div class="map-wrap" id="map">
  <div class="map-stub" id="mapStub" role="button" tabindex="0" aria-label="Показать карту проезда">
    <?= icon('pin') ?>
    <b><?= e($b['city'] ?? '') ?>, <?= e($b['street'] ?? '') ?></b>
    <span class="small"><?= e($b['addressNote'] ?? '') ?></span>
    <span class="btn btn--primary">Показать карту</span>
  </div>
</div>
<div class="btn-row" style="margin-top:.6rem">
  <a class="btn btn--ghost" href="<?= e($b['yandexRouteUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('route') ?>Построить маршрут</a>
  <a class="btn btn--ghost" href="<?= e($b['gis2Url'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('pin') ?>Открыть в 2ГИС</a>
</div>
    <?php
}

function block_contacts(): void
{
    $b = biz();
    ?>
<ul class="contact-list">
  <li><a href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?><span><b><?= e($b['phone'] ?? '') ?></b><span>Звоните — ответим сразу</span></span></a></li>
  <li><a href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('wa') ?><span><b>WhatsApp</b><span><?= e($b['whatsapp'] ?? '') ?></span></span></a></li>
  <li><a href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('tg') ?><span><b>Telegram</b><span>@<?= e($b['telegram'] ?? '') ?></span></span></a></li>
  <li><a href="<?= e($b['vkUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('vk') ?><span><b>ВКонтакте</b><span><?= e(str_replace('https://', '', $b['vkUrl'] ?? '')) ?></span></span></a></li>
  <li><a href="<?= e($b['yandexRouteUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('pin') ?><span><b><?= e($b['street'] ?? '') ?></b><span><?= e($b['city'] ?? '') ?>, район <?= e($b['district'] ?? '') ?>. <?= e($b['addressNote'] ?? '') ?></span></span></a></li>
  <li><div><?= icon('clock') ?><span><b><?= e($b['hours'] ?? '') ?></b><span>Воскресенье — выходной</span></span></div></li>
</ul>
    <?php
}

function block_gallery(array $items, bool $withCat = false): void
{
    ?>
<div class="gallery"<?= $withCat ? ' id="galleryGrid"' : '' ?>>
  <?php foreach ($items as $g): ?>
  <figure class="gallery__item"<?= $withCat ? ' data-cat="' . e($g['cat'] ?? '') . '"' : '' ?>
          data-full="<?= gallery_img($g['f'] ?? '') ?>" data-alt="<?= e($g['alt'] ?? '') ?>">
    <img src="<?= gallery_img($g['f'] ?? '', true) ?>" alt="<?= e($g['alt'] ?? '') ?>"
         title="<?= e($g['cat'] ?? '') ?> — NLeveL, Екатеринбург"
         width="600" height="750" loading="lazy" decoding="async">
    <figcaption class="gallery__cap"><?= e($g['cat'] ?? '') ?></figcaption>
  </figure>
  <?php endforeach; ?>
</div>
    <?php
}
