'use strict';

const fs = require('fs');
const path = require('path');

const { SITE_URL, biz, priceGroups, reviews, gallery, advantages, faq, pages } = require('./data');
const { esc, money, rel, icon, stars, layout, serviceNav } = require('./templates');

const ROOT = path.resolve(__dirname, '..');
const findPage = (slug) => pages.find((p) => p.slug === slug);
const findGroup = (id) => priceGroups.find((g) => g.id === id);

/* ============================================================
   ПЕРЕИСПОЛЬЗУЕМЫЕ БЛОКИ
   ============================================================ */

/** Карточка услуги (для сеток) */
function serviceCard(item, r, href) {
  const img = item.img
    ? `<div class="card__media"><img src="${r}assets/img/services/${item.img}.webp"
         alt="${esc(item.name)} — детейлинг-студия NLeveL, Екатеринбург"
         title="${esc(item.name)} в NLeveL, Екатеринбург"
         width="771" height="1024" loading="lazy" decoding="async"></div>`
    : '';
  const flag = item.best
    ? '<span class="card__flag">Выгоднее всего</span>'
    : item.popular
    ? '<span class="card__flag">Чаще всего берут</span>'
    : '';
  return `<${href ? 'a' : 'div'} class="card${href ? '' : ' card--hover'}"${href ? ` href="${href}"` : ''}>
  ${flag}${img}
  <div class="card__body">
    <h3 class="card__title">${esc(item.name)}</h3>
    <p class="card__text">${esc(item.desc)}</p>
    <div class="card__foot">
      <span class="price-tag">${money(item.price)}</span>
      ${item.time ? `<span class="chip">${icon('clock')}${item.time}</span>` : ''}
    </div>
  </div>
</${href ? 'a' : 'div'}>`;
}

/** Строка прайса */
function priceRow(item) {
  return `<div class="prow">
  <div class="prow__name">${esc(item.name)}</div>
  <div class="prow__price"><span class="price-tag">${money(item.price)}</span></div>
  <div class="prow__desc">${esc(item.desc)}</div>
  ${
    item.time || item.popular || item.best
      ? `<div class="prow__meta">
    ${item.time ? `<span class="chip">${icon('clock')}${item.time}</span>` : ''}
    ${item.popular ? '<span class="chip chip--accent">Чаще всего берут</span>' : ''}
    ${item.best ? '<span class="chip chip--accent">Выгоднее всего</span>' : ''}
  </div>`
      : ''
  }
</div>`;
}

/** Блок преимуществ */
function advantagesBlock() {
  return `<section class="section shell" id="why">
  <div class="section-head">
    <div>
      <span class="eyebrow">Почему к нам</span>
      <h2>Мы закрыли то, на что жалуются клиенты других студий</h2>
    </div>
  </div>
  <div class="grid">
    ${advantages
      .map(
        (a) => `<article class="feature reveal">
      <span class="feature__icon">${icon(a.icon)}</span>
      <h3>${esc(a.title)}</h3>
      <p>${esc(a.text)}</p>
    </article>`
      )
      .join('\n    ')}
  </div>
</section>`;
}

/** Сводный рейтинг */
function ratingBlock() {
  return `<div class="rating-box">
  <div class="rating-box__score">
    <b>${biz.ratingAvg}</b>
    ${stars(5)}
    <p class="small" style="margin-top:.4rem">${biz.reviewsTotal} отзыва</p>
  </div>
  <div>
    <p style="font-weight:700">Яндекс.Карты</p>
    <p class="muted">${biz.ratingYandex} из 5 · ${biz.reviewsYandex} отзывов</p>
    <p style="margin-top:.7rem"><a class="btn btn--ghost" href="${biz.yandexMapUrl}" target="_blank" rel="noopener">Смотреть на Яндексе</a></p>
  </div>
  <div>
    <p style="font-weight:700">2ГИС</p>
    <p class="muted">${biz.rating2gis} из 5 · ${biz.reviews2gis} отзыв</p>
    <p style="margin-top:.7rem"><a class="btn btn--ghost" href="${biz.gis2ReviewsUrl}" target="_blank" rel="noopener">Смотреть в 2ГИС</a></p>
  </div>
</div>`;
}

/** Карточка отзыва */
function reviewCard(rv) {
  return `<article class="review reveal">
  <div class="review__top">
    <span class="review__ava" aria-hidden="true">${esc(rv.author.trim()[0])}</span>
    <span class="review__who">
      <b>${esc(rv.author)}</b>
      <span>${esc(rv.dateText)}</span>
    </span>
    <span style="margin-inline-start:auto">${stars(rv.rating)}</span>
  </div>
  <p class="review__text">${esc(rv.text)}</p>
  <div class="review__src">
    <span>${esc(rv.source)}</span>
    <span class="chip">${esc(rv.tag)}</span>
  </div>
</article>`;
}

/** FAQ с разметкой */
function faqBlock(items = faq, title = 'Частые вопросы') {
  return `<section class="section shell" id="faq">
  <div class="section-head">
    <div>
      <span class="eyebrow">FAQ</span>
      <h2>${esc(title)}</h2>
    </div>
  </div>
  <div class="faq">
    ${items
      .map(
        (f) => `<details>
      <summary>${esc(f.q)}</summary>
      <div class="faq__a">${esc(f.a)}</div>
    </details>`
      )
      .join('\n    ')}
  </div>
</section>`;
}

const faqSchema = (items = faq) => ({
  '@context': 'https://schema.org',
  '@type': 'FAQPage',
  mainEntity: items.map((f) => ({
    '@type': 'Question',
    name: f.q,
    acceptedAnswer: { '@type': 'Answer', text: f.a },
  })),
});

/** CTA-баннер */
function ctaBlock(r, title, text) {
  return `<section class="section shell">
  <div class="cta">
    <span class="eyebrow">Запись</span>
    <h2>${esc(title)}</h2>
    <p>${esc(text)}</p>
    <div class="btn-row" style="justify-content:center">
      <a class="btn btn--primary btn--lg" href="${r}booking/">${icon('calendar')}Записаться онлайн</a>
      <a class="btn btn--ghost btn--lg" href="tel:${biz.phoneRaw}">${icon('phone')}${biz.phone}</a>
    </div>
    <p class="small">Подтверждаем запись заранее. Работаем ${biz.hours}.</p>
  </div>
</section>`;
}

/** Карта */
function mapBlock() {
  return `<div class="map-wrap" id="map">
  <div class="map-stub" id="mapStub" role="button" tabindex="0" aria-label="Показать карту проезда">
    ${icon('pin')}
    <b>${biz.city}, ${biz.street}</b>
    <span class="small">${biz.addressNote}</span>
    <span class="btn btn--primary">Показать карту</span>
  </div>
</div>
<div class="btn-row" style="margin-top:.6rem">
  <a class="btn btn--ghost" href="${biz.yandexRouteUrl}" target="_blank" rel="noopener">${icon('route')}Построить маршрут</a>
  <a class="btn btn--ghost" href="${biz.gis2Url}" target="_blank" rel="noopener">${icon('pin')}Открыть в 2ГИС</a>
</div>`;
}

/** Блок контактов списком */
function contactList() {
  return `<ul class="contact-list">
  <li><a href="tel:${biz.phoneRaw}">${icon('phone')}<span><b>${biz.phone}</b><span>Звоните — ответим сразу</span></span></a></li>
  <li><a href="${biz.whatsappUrl}" target="_blank" rel="noopener">${icon('wa')}<span><b>WhatsApp</b><span>${biz.whatsapp}</span></span></a></li>
  <li><a href="${biz.telegramUrl}" target="_blank" rel="noopener">${icon('tg')}<span><b>Telegram</b><span>@${biz.telegram}</span></span></a></li>
  <li><a href="${biz.vkUrl}" target="_blank" rel="noopener">${icon('vk')}<span><b>ВКонтакте</b><span>vk.com/n_level</span></span></a></li>
  <li><a href="${biz.yandexRouteUrl}" target="_blank" rel="noopener">${icon('pin')}<span><b>${biz.street}</b><span>${biz.city}, район ЖБИ. ${biz.addressNote}</span></span></a></li>
  <li><div>${icon('clock')}<span><b>${biz.hours}</b><span>Воскресенье — выходной</span></span></div></li>
</ul>`;
}

/* ============================================================
   ГЛАВНАЯ
   ============================================================ */
function pageIndex() {
  const page = findPage('index');
  const r = rel(page.url);

  // work-1 и work-16 — авто в сатиновой плёнке, work-2 — седан после мойки.
  // Общие планы боксов в герой не берём.
  const pick = (name) => gallery.find((g) => g.f === name);
  const heroPhotos = [pick('work-1'), pick('work-16'), pick('work-2')];

  const body = `
<section class="hero">
  <div class="shell hero__grid">
    <div>
      <span class="eyebrow">Екатеринбург · ЖБИ · 40-летия Комсомола, 4Д</span>
      <h1>Детейлинг-студия <mark>NLeveL</mark> в Екатеринбурге</h1>
      <p class="hero__sub">Мойка с сохранением ЛКП, полировка, керамика, оклейка полиуретаном и винилом, химчистка, тонировка и шумоизоляция. Весь прайс открыт — без «уточняйте по телефону».</p>

      <div class="hero__cta">
        <a class="btn btn--primary btn--lg" href="${r}booking/">${icon('calendar')}Записаться онлайн</a>
        <a class="btn btn--ghost btn--lg" href="${r}price/">${icon('price')}Смотреть прайс</a>
      </div>

      <div class="hero__stats">
        <div class="hero__stat"><b>${biz.ratingAvg}</b><span>рейтинг на Яндекс.Картах и 2ГИС</span></div>
        <div class="hero__stat"><b>${biz.reviewsTotal}</b><span>отзыва клиентов</span></div>
        <div class="hero__stat"><b>от 1 000 ₽</b><span>комплексная мойка</span></div>
        <div class="hero__stat"><b>20</b><span>услуг с открытой ценой</span></div>
      </div>
    </div>

    <div class="hero__media">
      ${heroPhotos
        .map(
          (g, i) => `<figure>
        <img src="${r}assets/img/gallery/${g.f}.webp" alt="${esc(g.alt)}" title="${esc(g.cat)} — NLeveL, Екатеринбург"
             width="578" height="768" ${i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'} decoding="async">
        ${
          i === 0
            ? `<figcaption class="hero__badge">${icon('star')}<b>${biz.ratingAvg}</b> · ${biz.reviewsTotal} отзыва</figcaption>`
            : ''
        }
      </figure>`
        )
        .join('\n      ')}
    </div>
  </div>
</section>

<!-- Гарантия записи — закрывает главную боль рынка -->
<section class="section--tight shell">
  <div class="panel panel--accent">
    <div class="split" style="gap:var(--gap-md);align-items:center">
      <div>
        <span class="eyebrow">Наше главное правило</span>
        <h2 style="font-size:var(--step-2);margin-block:.4rem .6rem">Записались на 19:00 — работаем в 19:00</h2>
        <p class="muted">Самая частая жалоба на детейлинг в городе звучит так: «приехал по записи, а там закрыто, на звонки не отвечают». Мы подтверждаем каждую запись заранее — звонком или сообщением. Если планы меняются с нашей стороны, вы узнаете об этом первым, а не у закрытых ворот.</p>
      </div>
      <ul class="tick-list">
        <li>Подтверждаем визит до вашего приезда</li>
        <li>Цену фиксируем до заезда в бокс — доплат «по ходу дела» нет</li>
        <li>Пишем в Telegram или WhatsApp, когда машина готова</li>
        <li>Снимаем фото до и после работ</li>
      </ul>
    </div>
  </div>
</section>

<!-- Услуги -->
<section class="section shell" id="services">
  <div class="section-head">
    <div>
      <span class="eyebrow">Услуги</span>
      <h2>Что мы делаем</h2>
      <p class="lede">Девять направлений — от мойки за 1 000 ₽ до полной оклейки кузова полиуретаном. У каждой услуги открытая цена и честный срок.</p>
    </div>
    <a class="btn btn--ghost" href="${r}price/">Весь прайс</a>
  </div>

  <div class="grid">
    ${serviceNav
      .map((s) => {
        const g = findGroup(s.url.includes('antigraviynaya') ? 'plenka' : null) || null;
        return '';
      })
      .join('')}
    ${[
      { g: 'mojka', url: 'mojka/', img: 'work-8', title: 'Автомойка на ЖБИ', text: 'Ручная мойка в две фазы с сохранением ЛКП. Без щёток. Чернение резины в подарок с Комплекса №3.' },
      { g: 'polirovka', url: 'polirovka/', img: 'polirovka-kuzova', title: 'Полировка кузова и фар', text: 'Убираем царапины, голограммы и помутнение оптики. Замер толщины ЛКП до работ.', svc: true },
      { g: 'keramika', url: 'keramika/', img: 'keramika', title: 'Керамическое покрытие', text: 'Двухэтапная полировка и подготовка уже включены в цену. Два слоя состава, гидрофоб и защита ЛКП.', svc: true },
      { g: 'plenka', url: 'antigraviynaya-plenka/', img: 'zona-riska', title: 'Антигравийная плёнка (PPF)', text: 'Полиуретан на весь кузов, зону риска, фары и лобовое. Она же бронеплёнка.', svc: true },
      { g: 'styling', url: 'oklejka-plenkoj/', img: 'styling', title: 'Оклейка винилом, смена цвета', text: 'Полная смена цвета — 60 000 ₽. В премиум-студиях города за то же самое просят от 190 000 ₽.', svc: true },
      { g: 'himchistka', url: 'himchistka/', img: 'himchistka', title: 'Химчистка салона', text: 'Сиденья, потолок, пластик, ковры и багажник. Отдаём только сухой салон.', svc: true },
      { g: 'styling', url: 'tonirovka/', img: 'tonirovka', title: 'Тонировка стёкол', text: 'Светопропускание 5 %, 15 %, 20 %, 35 % или 50 %. Подскажем, что можно ставить без штрафа.', svc: true },
      { g: 'shumoizolyaciya', url: 'shumoizolyaciya/', img: 'shumka-dveri', title: 'Шумоизоляция', text: 'Двери в три слоя — вибро, тепло-шумо, звуко. Арки снаружи против шума от дороги.', svc: true },
    ]
      .map((s) => {
        const grp = findGroup(s.g);
        const min = Math.min(...grp.items.map((i) => i.price));
        const src = s.svc ? `services/${s.img}.webp` : `gallery/${s.img}.webp`;
        return `<a class="card reveal" href="${r}${s.url}">
      <div class="card__media">
        <img src="${r}assets/img/${src}" alt="${esc(s.title)} в Екатеринбурге — детейлинг-студия NLeveL"
             title="${esc(s.title)} — NLeveL, Екатеринбург" width="771" height="1024" loading="lazy" decoding="async">
      </div>
      <div class="card__body">
        <h3 class="card__title">${esc(s.title)}</h3>
        <p class="card__text">${esc(s.text)}</p>
        <div class="card__foot">
          <span class="price-tag"><small>от </small>${money(min)}</span>
          <span class="chip">Подробнее →</span>
        </div>
      </div>
    </a>`;
      })
      .join('\n    ')}
  </div>
</section>

${advantagesBlock()}

<!-- Цены дешевле рынка -->
<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Сравнение</span>
      <h2>Столько же, но дешевле центра</h2>
      <p class="lede">Мы на ЖБИ, а не в Гринвиче — и не платим за аренду в центре. Материалы и руки те же, разница видна только в чеке.</p>
    </div>
  </div>
  <div class="grid grid--narrow">
    ${[
      ['Смена цвета винилом', '60 000 ₽', 'от 190 000 ₽'],
      ['Оклейка кузова полиуретаном', '165 000 ₽', 'от 200 000 ₽'],
      ['Абразивная полировка кузова', '12 000 ₽', '~17 000 ₽'],
      ['Тонировка', '3 000 ₽', '~10 000 ₽'],
      ['Шумоизоляция арок', '16 000 ₽', '~25 000 ₽'],
      ['Комплексная мойка', 'от 1 000 ₽', '~2 550 ₽'],
    ]
      .map(
        ([n, our, mkt]) => `<div class="feature reveal">
      <h3 style="font-size:var(--step-0)">${n}</h3>
      <p style="margin-block:.3rem"><span class="price-tag">${our}</span></p>
      <p class="small">по городу ${mkt}</p>
    </div>`
      )
      .join('\n    ')}
  </div>
  <p class="small" style="margin-top:var(--gap-sm)">Цены конкурентов — по открытым прайсам детейлинг-центров Екатеринбурга на август 2026. Сравнение приведено для ориентира, комплектация услуг у студий может отличаться.</p>
</section>

<!-- Работы -->
<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Портфолио</span>
      <h2>Наши работы</h2>
    </div>
    <a class="btn btn--ghost" href="${r}works/">Все работы</a>
  </div>
  <div class="gallery">
    ${gallery
      .slice(0, 8)
      .map(
        (g, i) => `<figure class="gallery__item" data-full="${r}assets/img/gallery/${g.f}.webp" data-alt="${esc(g.alt)}">
      <img src="${r}assets/img/gallery/thumb/${g.f}.webp" alt="${esc(g.alt)}" title="${esc(g.cat)} — NLeveL, Екатеринбург"
           width="600" height="750" loading="lazy" decoding="async">
      <figcaption class="gallery__cap">${esc(g.cat)}</figcaption>
    </figure>`
      )
      .join('\n    ')}
  </div>
</section>

<!-- Отзывы -->
<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Отзывы</span>
      <h2>Что говорят клиенты</h2>
    </div>
    <a class="btn btn--ghost" href="${r}reviews/">Все отзывы</a>
  </div>
  ${ratingBlock()}
  <div class="grid" style="margin-top:var(--gap-md)">
    ${reviews.slice(0, 6).map(reviewCard).join('\n    ')}
  </div>
</section>

<!-- О студии -->
<section class="section shell">
  <div class="split split--aside">
    <div class="prose">
      <span class="eyebrow">О студии</span>
      <h2>Детейлинг на ЖБИ, а не «где-то в городе»</h2>
      <p>NLeveL — детейлинг-центр в Екатеринбурге на улице 40-летия Комсомола, 4Д. Это район ЖБИ: заезд за супермаркетом «Магнит», прямо с парковки. Не нужно ехать в центр, искать место и стоять в очереди — у нас 8 машиномест и зона ожидания, где можно спокойно подождать мойку.</p>
      <p>Мы занимаемся полным циклом ухода за автомобилем: от еженедельной мойки с сохранением лакокрасочного покрытия до защиты кузова полиуретановой плёнкой и полной смены цвета. <strong>Каждую услугу делаем сами, в своём боксе, без передачи на сторону.</strong></p>
      <h3>Как мы работаем</h3>
      <ul>
        <li><strong>Осмотр и расчёт.</strong> Смотрим машину, показываем проблемные места, называем точную цену и срок.</li>
        <li><strong>Согласование.</strong> Вы соглашаетесь с ценой до того, как машина заезжает в бокс. После этого цена не меняется.</li>
        <li><strong>Работа.</strong> Снимаем фото до и после. По сложным работам показываем промежуточный результат.</li>
        <li><strong>Выдача.</strong> Пишем, когда готово. Принимаете работу при нас — если что-то не так, переделываем сразу.</li>
      </ul>
      <p>Средняя оценка студии — <strong>${biz.ratingAvg} из 5</strong> по ${biz.reviewsTotal} отзывам на <a href="${biz.yandexMapUrl}" target="_blank" rel="noopener">Яндекс.Картах</a> и в <a href="${biz.gis2ReviewsUrl}" target="_blank" rel="noopener">2ГИС</a>. Отзывы мы не удаляем и не накручиваем.</p>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h3 style="font-size:var(--step-1);margin-bottom:.8rem">Контакты</h3>
        ${contactList()}
      </div>
    </aside>
  </div>
</section>

<!-- Карта -->
<section class="section shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Как доехать</span>
      <h2>${biz.city}, ${biz.street}</h2>
      <p class="lede">${biz.addressNote}. Ближайший ориентир — «Заводоуправление», 5 минут пешком.</p>
    </div>
  </div>
  ${mapBlock()}
</section>

${faqBlock()}
${ctaBlock(r, 'Запишитесь — и приезжайте спокойно', 'Подтвердим запись заранее, назовём точную цену и время выдачи. Никаких «мы вам перезвоним».')}
`;

  return { page, html: layout(page, body, { schema: [faqSchema()] }) };
}

/* ============================================================
   ПРАЙС
   ============================================================ */
function pagePrice() {
  const page = findPage('price');
  const r = rel(page.url);

  const offerSchema = {
    '@context': 'https://schema.org',
    '@type': 'OfferCatalog',
    name: 'Прайс-лист детейлинг-студии NLeveL, Екатеринбург',
    url: SITE_URL + page.url,
    itemListElement: priceGroups.flatMap((g) =>
      g.items.map((it) => ({
        '@type': 'Offer',
        name: it.name,
        description: it.desc,
        price: it.price,
        priceCurrency: 'RUB',
        availability: 'https://schema.org/InStock',
        areaServed: 'Екатеринбург',
        seller: { '@id': SITE_URL + '/#org' },
      }))
    ),
  };

  const body = `
<section class="section--tight shell">
  <span class="eyebrow">Прайс-лист</span>
  <h1>${esc(page.h1)}</h1>
  <p class="lede" style="margin-top:.7rem">Все цены открыты и указаны за конкретный объём работ. Итоговая стоимость согласуется до заезда в бокс — доплат «за сильное загрязнение» у нас не бывает.</p>

  <div class="btn-row" style="margin-top:var(--gap-md)">
    <a class="btn btn--primary" href="${r}booking/">${icon('calendar')}Записаться</a>
    <a class="btn btn--ghost" href="tel:${biz.phoneRaw}">${icon('phone')}${biz.phone}</a>
  </div>

  <nav class="filters" style="margin-top:var(--gap-md)" aria-label="Разделы прайса">
    ${priceGroups.map((g) => `<a class="btn btn--ghost" href="#${g.id}">${esc(g.title)}</a>`).join('\n    ')}
  </nav>
</section>

<section class="section--tight shell">
  ${priceGroups
    .map(
      (g) => `<div class="price-group" id="${g.id}">
    <div class="price-group__head">
      <h2>${esc(g.title)}</h2>
      ${g.note ? `<p class="small" style="flex:1 1 20ch">${esc(g.note)}</p>` : ''}
    </div>
    <div class="ptable">
      ${g.items.map(priceRow).join('\n      ')}
    </div>
  </div>`
    )
    .join('\n  ')}

  <div class="panel panel--accent">
    <h3 style="font-size:var(--step-1);margin-bottom:.6rem">Что важно знать про цены</h3>
    <ul class="tick-list">
      <li><strong>Антихром — 1 500 ₽ за один элемент.</strong> Полный комплект накладок считаем по количеству деталей и называем сумму до начала работ.</li>
      <li><strong>Плёнка считается по кузову.</strong> Цены указаны для легкового автомобиля среднего класса. Для внедорожников и авто с нестандартной геометрией стоимость уточняется на осмотре.</li>
      <li><strong>Керамика 30 000 ₽ — уже с подготовкой.</strong> Чистка кузова и двухэтапная полировка входят в цену, отдельно доплачивать не нужно.</li>
      <li><strong>Цена фиксируется до заезда в бокс.</strong> Если в процессе выясняется, что нужен дополнительный объём, мы останавливаемся и согласовываем — а не ставим перед фактом при выдаче.</li>
    </ul>
    <p class="small" style="margin-top:.8rem">Прайс актуален на ${new Date().toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })}. Информация на сайте не является публичной офертой.</p>
  </div>
</section>

${ctaBlock(r, 'Готовы записаться?', 'Выберите услугу и удобное время — подтвердим запись и назовём точный срок.')}
`;

  return { page, html: layout(page, body, { schema: [offerSchema] }) };
}

/* ============================================================
   РАБОТЫ
   ============================================================ */
function pageWorks() {
  const page = findPage('works');
  const r = rel(page.url);
  const cats = [...new Set(gallery.map((g) => g.cat))];

  const body = `
<section class="section--tight shell">
  <span class="eyebrow">Портфолио</span>
  <h1>${esc(page.h1)}</h1>
  <p class="lede" style="margin-top:.7rem">Реальные фотографии студии на ЖБИ: боксы, оборудование, автомобили в работе и результаты оклейки плёнкой. Ничего не взято со стоков — всё снято у нас.</p>
</section>

<section class="section--tight shell">
  <div class="filters" role="group" aria-label="Фильтр работ по услуге">
    <button type="button" data-filter="all" aria-pressed="true">Все работы</button>
    ${cats.map((c) => `<button type="button" data-filter="${esc(c)}" aria-pressed="false">${esc(c)}</button>`).join('\n    ')}
  </div>

  <div class="gallery" id="galleryGrid">
    ${gallery
      .map(
        (g) => `<figure class="gallery__item" data-cat="${esc(g.cat)}" data-full="${r}assets/img/gallery/${g.f}.webp" data-alt="${esc(g.alt)}">
      <img src="${r}assets/img/gallery/thumb/${g.f}.webp" alt="${esc(g.alt)}" title="${esc(g.cat)} — NLeveL, Екатеринбург"
           width="600" height="750" loading="lazy" decoding="async">
      <figcaption class="gallery__cap">${esc(g.cat)}</figcaption>
    </figure>`
      )
      .join('\n    ')}
  </div>
</section>

${ctaBlock(r, 'Хотите так же?', 'Запишитесь на осмотр — покажем варианты, назовём точную цену и срок.')}
`;

  const schema = {
    '@context': 'https://schema.org',
    '@type': 'ImageGallery',
    name: 'Примеры работ детейлинг-студии NLeveL',
    url: SITE_URL + page.url,
    image: gallery.map((g) => ({
      '@type': 'ImageObject',
      contentUrl: SITE_URL + '/assets/img/gallery/' + g.f + '.webp',
      caption: g.alt,
    })),
  };

  return { page, html: layout(page, body, { schema: [schema] }) };
}

/* ============================================================
   ОТЗЫВЫ
   ============================================================ */
function pageReviews() {
  const page = findPage('reviews');
  const r = rel(page.url);

  const body = `
<section class="section--tight shell">
  <span class="eyebrow">Отзывы</span>
  <h1>${esc(page.h1)}</h1>
  <p class="lede" style="margin-top:.7rem">Отзывы собраны с Яндекс.Карт и 2ГИС. Мы ничего не удаляем и не накручиваем — если что-то пошло не так, разбираемся публично.</p>
</section>

<section class="section--tight shell">
  ${ratingBlock()}
</section>

<section class="section--tight shell">
  <div class="grid">
    ${reviews.map(reviewCard).join('\n    ')}
  </div>
</section>

<section class="section shell">
  <div class="panel panel--accent prose">
    <h2 style="font-size:var(--step-2)">О двух наших минусах — честно</h2>
    <p>В отзывах на 2ГИС есть две жалобы на одно и то же: клиент приехал по записи, а студия была закрыта, и на звонки никто не ответил. Это была наша ошибка, и мы не делаем вид, что её не было.</p>
    <p><strong>Что мы поменяли:</strong> теперь каждая запись подтверждается до вашего приезда — звонком или сообщением в WhatsApp либо Telegram. Если у нас что-то меняется в графике, вы узнаёте об этом заранее, а не у закрытых ворот. Если дозвониться не удалось — напишите в мессенджер, там мы отвечаем быстрее всего.</p>
    <p class="small">Оставить свой отзыв можно на <a href="${biz.yandexMapUrl}" target="_blank" rel="noopener">Яндекс.Картах</a> или в <a href="${biz.gis2ReviewsUrl}" target="_blank" rel="noopener">2ГИС</a> — мы читаем все.</p>
  </div>
</section>

${ctaBlock(r, 'Проверьте нас сами', 'Начните с мойки за 1 000 ₽ — и решайте, доверять ли нам полировку и плёнку.')}
`;

  const schema = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: 'Услуги детейлинг-студии NLeveL, Екатеринбург',
    brand: { '@type': 'Brand', name: 'NLeveL' },
    aggregateRating: {
      '@type': 'AggregateRating',
      ratingValue: biz.ratingAvg,
      reviewCount: biz.reviewsTotal,
      bestRating: 5,
      worstRating: 1,
    },
    review: reviews.map((rv) => ({
      '@type': 'Review',
      author: { '@type': 'Person', name: rv.author },
      datePublished: rv.date,
      reviewRating: { '@type': 'Rating', ratingValue: rv.rating, bestRating: 5 },
      reviewBody: rv.text,
      publisher: { '@type': 'Organization', name: rv.source },
    })),
  };

  return { page, html: layout(page, body, { schema: [schema] }) };
}

/* ============================================================
   ОНЛАЙН-ЗАПИСЬ
   ============================================================ */
function pageBooking() {
  const page = findPage('booking');
  const r = rel(page.url);

  const allServices = priceGroups.flatMap((g) =>
    g.items.map((i) => ({ name: i.name, price: i.price, group: g.title }))
  );

  const body = `
<section class="section--tight shell">
  <span class="eyebrow">Онлайн-запись</span>
  <h1>${esc(page.h1)}</h1>
  <p class="lede" style="margin-top:.7rem">Записывайтесь прямо здесь — в нашем приложении. Выберите услугу, свободный слот и мастера, а мы подтвердим визит. Вы точно не приедете к закрытым воротам.</p>
</section>

<!-- Приложение онлайн-записи NLeveL -->
<section class="section--tight shell" id="app">
  <div class="appframe">
    <div class="appframe__bar">
      <span class="appframe__dots" aria-hidden="true"><i></i><i></i><i></i></span>
      <span class="appframe__url">nlevel-detailing.web.app — онлайн-запись NLeveL</span>
      <a class="chip" href="${biz.appUrl}" target="_blank" rel="noopener" aria-label="Открыть приложение в новой вкладке">${icon('external')}</a>
    </div>
    <div class="appframe__body">
      <div class="appframe__stub"${biz.appEmbed ? ` id="appStub" role="button" tabindex="0" data-src="${biz.appUrl}"` : ''}
           aria-label="Приложение онлайн-записи NLeveL">
        ${icon('app')}
        <h3>Приложение онлайн-записи</h3>
        <p>Вход по номеру телефона или через Telegram. Внутри — выбор услуги, свободные слоты, история визитов и статус работ. Отдельные входы для администратора и мастера.</p>
        ${
          biz.appEmbed
            ? `<span class="btn btn--primary btn--lg">${icon('calendar')}Открыть приложение</span>
        <span class="small">Загрузим по клику — чтобы не тормозить страницу</span>`
            : `<a class="btn btn--primary btn--lg" href="${biz.appUrl}" target="_blank" rel="noopener">${icon('calendar')}Открыть приложение</a>
        <span class="small">Откроется в новой вкладке. На телефоне ставится на рабочий стол как обычное приложение.</span>`
        }
      </div>
    </div>
  </div>

  <div class="split" style="margin-top:var(--gap-md);gap:var(--gap-md)">
    <div>
      <h2 style="font-size:var(--step-2);margin-bottom:.6rem">Установите на телефон</h2>
      <p class="muted" style="margin-bottom:.9rem">Приложение ставится как обычное — с иконкой на рабочем столе, без App Store и Google Play.</p>
      <div class="store-row">
        <a class="store-btn" href="${biz.appUrl}" target="_blank" rel="noopener">
          ${icon('apple')}<span><b>Для iPhone</b><span>Поделиться → «На экран Домой»</span></span>
        </a>
        <a class="store-btn" href="${biz.appUrl}" target="_blank" rel="noopener">
          ${icon('android')}<span><b>Для Android</b><span>Меню → «Установить приложение»</span></span>
        </a>
      </div>
    </div>
    <div class="panel">
      <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Что умеет приложение</h3>
      <ul class="tick-list">
        <li>Выбор услуги из полного прайса</li>
        <li>Свободные слоты в реальном времени</li>
        <li>Вход по номеру телефона или через Telegram</li>
        <li>История визитов и статус текущих работ</li>
        <li>Кабинеты администратора и мастера</li>
      </ul>
    </div>
  </div>
</section>

<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Или проще</span>
      <h2>Заявка без регистрации</h2>
      <p class="lede">Не хотите ставить приложение — оставьте заявку здесь. Мы свяжемся и подтвердим время.</p>
    </div>
  </div>
  <div class="split split--aside">
    <div>
      <form class="form panel" id="bookingForm" novalidate>
        <fieldset class="fieldset">
          <legend>1. Какая услуга нужна</legend>
          <div class="field">
            <label for="svc">Услуга <span class="req">*</span></label>
            <select id="svc" name="service" required>
              <option value="">— выберите услугу —</option>
              ${priceGroups
                .map(
                  (g) => `<optgroup label="${esc(g.title)}">
                ${g.items
                  .map((i) => `<option value="${esc(i.name)} — ${money(i.price)}">${esc(i.name)} — ${money(i.price)}</option>`)
                  .join('\n                ')}
              </optgroup>`
                )
                .join('\n              ')}
              <option value="Не знаю, нужна консультация">Не знаю — нужна консультация</option>
            </select>
            <span class="field__hint">Не уверены, что выбрать? Возьмите «нужна консультация» — подскажем на осмотре.</span>
          </div>
        </fieldset>

        <fieldset class="fieldset">
          <legend>2. Когда вам удобно</legend>
          <div class="form__grid">
            <div class="field">
              <label for="date">Дата <span class="req">*</span></label>
              <input type="date" id="date" name="date" required>
              <span class="field__hint">Работаем ${biz.hours}</span>
            </div>
            <div class="field">
              <label for="car">Марка и модель авто</label>
              <input type="text" id="car" name="car" placeholder="Например, Toyota Camry" autocomplete="off">
            </div>
          </div>

          <div class="field">
            <label>Время</label>
            <div class="picker">
              ${['10:00', '12:00', '14:00', '16:00', '18:00', '19:30']
                .map(
                  (t, i) =>
                    `<input type="radio" name="time" id="t${i}" value="${t}"${i === 0 ? ' checked' : ''}><label for="t${i}">${t}<small>${
                      i < 3 ? 'обычно свободно' : 'вечерний слот'
                    }</small></label>`
                )
                .join('\n              ')}
            </div>
          </div>
        </fieldset>

        <fieldset class="fieldset">
          <legend>3. Как с вами связаться</legend>
          <div class="form__grid">
            <div class="field">
              <label for="name">Ваше имя <span class="req">*</span></label>
              <input type="text" id="name" name="name" required autocomplete="name" placeholder="Как к вам обращаться">
            </div>
            <div class="field">
              <label for="tel">Телефон <span class="req">*</span></label>
              <input type="tel" id="tel" name="phone" required autocomplete="tel" inputmode="tel" placeholder="+7 ___ ___-__-__">
            </div>
          </div>

          <div class="field">
            <label>Куда прислать подтверждение</label>
            <div class="picker">
              <input type="radio" name="channel" id="ch1" value="WhatsApp" checked><label for="ch1">WhatsApp<small>ответим быстрее всего</small></label>
              <input type="radio" name="channel" id="ch2" value="Telegram"><label for="ch2">Telegram<small>@${biz.telegram}</small></label>
              <input type="radio" name="channel" id="ch3" value="Звонок"><label for="ch3">Звонок<small>перезвоним сами</small></label>
            </div>
          </div>

          <div class="field">
            <label for="comment">Комментарий</label>
            <textarea id="comment" name="comment" placeholder="Что беспокоит: царапины, пятна на сиденьях, сколы на капоте…"></textarea>
          </div>
        </fieldset>

        <label class="consent">
          <input type="checkbox" name="consent" required>
          <span>Согласен на обработку персональных данных для связи по заявке. Данные используются только для подтверждения записи и никому не передаются.</span>
        </label>

        <button class="btn btn--primary btn--lg btn--block" type="submit">${icon('calendar')}Отправить заявку</button>
        <p class="small" style="text-align:center">Отправка не списывает деньги и ни к чему не обязывает. Мы свяжемся, чтобы подтвердить время.</p>
      </form>

      <div class="form-result" id="formResult" role="status" aria-live="polite">
        <h3>Заявка собрана</h3>
        <p class="muted" id="resultText"></p>
        <div class="btn-row">
          <a class="btn btn--wa" id="waSend" href="${biz.whatsappUrl}" target="_blank" rel="noopener">${icon('wa')}Отправить в WhatsApp</a>
          <a class="btn btn--tg" id="tgSend" href="${biz.telegramUrl}" target="_blank" rel="noopener">${icon('tg')}Отправить в Telegram</a>
          <a class="btn btn--ghost" href="tel:${biz.phoneRaw}">${icon('phone')}Позвонить</a>
        </div>
        <p class="small">Нажмите кнопку — заявка подставится в сообщение, останется только отправить.</p>
      </div>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h2 style="font-size:var(--step-1);margin-bottom:.8rem">Как проходит запись</h2>
        <ol class="steps" style="grid-template-columns:1fr">
          <li><b>Записываетесь в приложении</b>Или оставляете заявку формой — как удобнее.</li>
          <li><b>Мы подтверждаем</b>Пишем или звоним, называем точную цену и срок работ.</li>
          <li><b>Напоминаем перед визитом</b>Чтобы вы не приехали зря — и чтобы слот не пропал.</li>
          <li><b>Пишем, когда готово</b>Не нужно звонить и спрашивать.</li>
        </ol>
      </div>

      <div class="panel" style="margin-top:var(--gap-sm)">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Быстрее — в мессенджер</h3>
        ${contactList()}
      </div>
    </aside>
  </div>
</section>

${faqBlock(
  [
    faq.find((f) => f.q.startsWith('Можно ли приехать')),
    faq.find((f) => f.q.startsWith('Где вы находитесь')),
    faq.find((f) => f.q.startsWith('Сколько по времени')),
  ].filter(Boolean),
  'Вопросы про запись'
)}
`;

  const schema = {
    '@context': 'https://schema.org',
    '@type': 'ReserveAction',
    name: 'Онлайн-запись в детейлинг-студию NLeveL',
    target: {
      '@type': 'EntryPoint',
      urlTemplate: SITE_URL + page.url,
      actionPlatform: [
        'https://schema.org/DesktopWebPlatform',
        'https://schema.org/MobileWebPlatform',
      ],
    },
    result: { '@type': 'Reservation', name: 'Запись на услугу детейлинга' },
    provider: { '@id': SITE_URL + '/#org' },
  };

  return { page, html: layout(page, body, { schema: [schema] }) };
}

/* ============================================================
   КОНТАКТЫ
   ============================================================ */
function pageContacts() {
  const page = findPage('contacts');
  const r = rel(page.url);

  const body = `
<section class="section--tight shell">
  <span class="eyebrow">Контакты</span>
  <h1>${esc(page.h1)}</h1>
  <p class="lede" style="margin-top:.7rem">Мы на ЖБИ — ${biz.street}. ${biz.addressNote}, на территории есть парковка на 8 машин и зона ожидания.</p>
</section>

<section class="section--tight shell">
  <div class="split">
    <div>
      ${contactList()}
      <div class="btn-row" style="margin-top:var(--gap-sm)">
        <a class="btn btn--primary" href="${r}booking/">${icon('calendar')}Записаться онлайн</a>
      </div>
    </div>
    <div class="panel prose">
      <h2 style="font-size:var(--step-2)">Как доехать</h2>
      <p><strong>На машине.</strong> Ориентир — супермаркет «Магнит» на 40-летия Комсомола. Въезд во двор за магазином, дальше с парковки к боксам. Места для клиентов есть всегда.</p>
      <p><strong>Общественным транспортом.</strong> Ближайшая остановка — «Заводоуправление», 5 минут пешком (около 400 метров).</p>
      <p><strong>Из каких районов к нам удобно ехать:</strong> ЖБИ, Комсомольский, Пионерский, Втузгородок, Шарташский рынок, Синие Камни. Из центра — 15–20 минут по улице Малышева и Сыромолотова.</p>
      <h3>Оплата</h3>
      <p>Наличные, банковская карта, перевод по QR-коду.</p>
      <h3>График</h3>
      <p>${biz.hours}. На мойку можно заехать без записи, если свободен пост. На полировку, керамику, химчистку и оклейку плёнкой — только по предварительной записи.</p>
    </div>
  </div>
</section>

<section class="section--tight shell">
  ${mapBlock()}
</section>

${ctaBlock(r, 'Приезжайте на ЖБИ', 'Запишитесь заранее — подтвердим время и подготовим бокс к вашему приезду.')}
`;

  return { page, html: layout(page, body) };
}

/* ============================================================
   УСЛУГОВЫЕ СТРАНИЦЫ
   ============================================================ */
const serviceContent = {
  polirovka: {
    intro:
      'Абразивная полировка возвращает кузову заводской блеск: убираем риски от щёток автомоек, «паутинку» на тёмных авто, следы от веток и мелкие царапины, которые не задевают грунт. Перед работой замеряем толщину лакокрасочного покрытия — если лака мало, честно об этом скажем и предложим щадящий вариант вместо того, чтобы «протереть до грунта».',
    blocks: [
      {
        h: 'Что входит в абразивную полировку кузова за 12 000 ₽',
        list: [
          'Мойка и обезжиривание кузова, чистка от инородных тел',
          'Замер толщины ЛКП по всем элементам',
          'Абразивный этап — снятие рисок и царапин',
          'Финишный этап — выведение блеска, удаление голограмм',
          'Фотоотчёт: снимаем проблемные зоны до и после',
        ],
      },
      {
        h: 'Полировка фар — 2 000 ₽',
        p: 'Пожелтевший и помутневший пластик фар режет световой поток вдвое: свет рассеивается, ночью хуже видно дорогу. Абразивная полировка снимает мутный слой и возвращает прозрачность. Чтобы результат держался дольше, фары после полировки имеет смысл закрыть полиуретаном — это ещё 6 000 ₽, зато повторно полировать не придётся.',
      },
      {
        h: 'Когда полировка не нужна',
        p: 'Если царапина цепляется ногтем — это уже глубже лака, полировка её не уберёт. Если по кузову идут сколы до металла — сначала имеет смысл подумать о защите антигравийной плёнкой, а не о полировке. Мы скажем об этом на осмотре, а не после того, как возьмём деньги.',
      },
    ],
  },
  'oklejka-plenkoj': {
    intro:
      'Оклейка плёнкой решает две разные задачи. Полиуретановая (антигравийная) плёнка — это защита: она принимает на себя удары камней и песка, сохраняя заводскую краску. Виниловая плёнка — это внешний вид: полная смена цвета или отдельные детали. Мы делаем и то, и другое в своём боксе.',
    blocks: [
      {
        h: 'Сколько стоит обклеить машину плёнкой',
        list: [
          'Весь кузов антигравийным полиуретаном — 165 000 ₽',
          'Весь кузов матовым полиуретаном PPF Matte — 150 000 ₽',
          '«Зона риска» — фронтальная часть — 60 000 ₽',
          'Полная смена цвета виниловой плёнкой — 60 000 ₽',
          'Лобовое стекло — 19 000 ₽, фары — 6 000 ₽',
          'Антихром (затемнение хромовых накладок) — 1 500 ₽ за элемент',
        ],
      },
      {
        h: 'Полиуретан или винил — что выбрать',
        p: 'Полиуретан прозрачный и толстый, он защищает краску от сколов и сам восстанавливается от мелких царапин. Винил тоньше и нужен ради цвета — он меняет внешний вид, но от камня не спасёт. Если задача «чтобы не било камнями» — берите полиуретан. Если «надоел цвет» — винил. Если хочется и то и другое — есть цветной полиуретан.',
      },
      {
        h: 'Что такое «зона риска»',
        p: 'Это фронтальная часть автомобиля: капот, передний бампер, крылья, зеркала и фары — то есть всё, что первым принимает камни из-под колёс встречных машин. По ней приходится примерно девять из десяти реальных прилётов. Оклейка зоны риска стоит 60 000 ₽ против 165 000 ₽ за весь кузов и закрывает большую часть проблемы.',
      },
    ],
  },
  'antigraviynaya-plenka': {
    intro:
      'Антигравийная плёнка, бронеплёнка, полиуретановая плёнка и PPF — это разные названия одного материала. Прозрачный полиуретан наклеивается поверх заводской краски и принимает на себя удары камней, песка и веток. Снаружи его не видно, а краска под ним остаётся такой же, как в день покупки автомобиля.',
    blocks: [
      {
        h: 'Варианты оклейки и цены',
        list: [
          'Весь кузов — 165 000 ₽, срок 5–7 дней',
          'Матовый полиуретан PPF Matte на весь кузов — 150 000 ₽',
          '«Зона риска» (капот, бампер, крылья, зеркала) — 60 000 ₽, срок 2–3 дня',
          'Лобовое стекло — 19 000 ₽',
          'Фары — 6 000 ₽',
          'Притемнение оптики полиуретаном — 8 000 ₽',
        ],
      },
      {
        h: 'Зачем защищать фары и лобовое отдельно',
        p: 'Стёкла фар делают из прозрачного пластика: при сильном попадании камня они не полируются, а меняются целиком — и это дороже, чем плёнка. Лобовое стекло от скола тоже не лечится: трещина расходится, и стекло идёт под замену. Плёнка на этих двух зонах окупается с первого предотвращённого скола.',
      },
      {
        h: 'Как мы клеим',
        p: 'Кузов моем, обезжириваем и осматриваем: если под плёнкой останется соринка или дефект краски, потом это уже не исправить. Поэтому все кратеры, крошку и пыль в лаке выводим до оклейки — именно об этом наш отзыв на Яндекс.Картах: «оклеили авто лучше, чем был покрашен с завода». Плёнку подворачиваем за кромки, где позволяет геометрия, чтобы край не собирал грязь.',
      },
    ],
  },
  himchistka: {
    intro:
      'Химчистка салона — это не «пропылесосили и протёрли». Мы вычищаем ткань и кожу до основания: сиденья, потолок, карты дверей, пластик, ковровое покрытие, багажник. Уходят пятна, следы от обуви, разводы от пролитого кофе и запахи, которые не выветриваются.',
    blocks: [
      {
        h: 'Что входит в химчистку за 15 000 ₽',
        list: [
          'Сиденья — ткань или кожа, с подбором состава под материал',
          'Потолок и стойки — аккуратно, без провисания обивки',
          'Карты дверей, пластик, торпедо',
          'Ковровое покрытие пола и коврики',
          'Багажник',
          'Сушка с контролем влажности — салон отдаём только сухим',
        ],
      },
      {
        h: 'Почему это занимает 1–2 дня',
        p: 'Самая частая жалоба на химчистку в городе — «отдали мокрым, через неделю в машине запахло плесенью». Это происходит, когда салон не досушили и влага ушла в поролон сидений и под обшивку. Мы не отдаём машину в тот же день, если она не высохла: лучше вы подождёте сутки, чем потом будете бороться с запахом.',
      },
      {
        h: 'Если нужно быстрее и дешевле',
        p: 'Полная химчистка нужна не всегда. Если салон просто запылился, подойдёт Комплекс №3 за 1 600 ₽ (влажная уборка пластика, мойка ковров, пылесос, протирка стёкол) — это 1–1,5 часа. Мы скажем на осмотре, что реально нужно вашей машине, и не будем продавать химчистку там, где хватит приборки.',
      },
    ],
  },
  keramika: {
    intro:
      'Керамическое покрытие — защитный слой на лакокрасочном покрытии, который даёт глубину цвета, выраженный блеск и гидрофобный эффект: вода собирается в капли и скатывается, забирая с собой грязь. Мыть машину становится проще, а лак защищён от ультрафиолета и дорожной химии.',
    blocks: [
      {
        h: 'В цену 30 000 ₽ уже входит подготовка',
        list: [
          'Чистка кузова от инородных тел (битум, металлические вкрапления)',
          'Двухэтапная полировка кузова',
          'Нанесение керамического состава в 2 слоя',
        ],
        after:
          'Это важный момент: в большинстве студий города керамика продаётся отдельно, а обязательная полировка перед ней — ещё плюс 17–18 тысяч сверху. У нас подготовка включена в стоимость, поэтому 30 000 ₽ — это итоговая сумма, а не первый платёж.',
      },
      {
        h: 'Зачем полировать перед керамикой',
        p: 'Керамика не убирает царапины — она их запечатывает. Если нанести состав на кузов с рисками и голограммами, все дефекты останутся под покрытием и станут заметнее из-за возросшего блеска. Поэтому полировка обязательна: сначала выводим лак в порядок, потом закрываем результат керамикой.',
      },
      {
        h: 'Керамика или антигравийная плёнка',
        p: 'Керамика — про блеск, гидрофоб и защиту от химии и ультрафиолета. От камня она не спасёт: скол будет такой же, как без неё. Плёнка — про механическую защиту от камней, но стоит в разы дороже. Если машина ездит по трассе и вы боитесь сколов на капоте — это плёнка. Если хочется, чтобы машина дольше выглядела свежей и легче мылась, — это керамика. Часто берут и то и другое: плёнка на зону риска, керамика на остальной кузов.',
      },
    ],
  },
  tonirovka: {
    intro:
      'Тонировка защищает салон от выгорания на солнце, снижает нагрев летом и добавляет автомобилю законченный вид. Мы работаем с разной светопропускаемостью — 5 %, 15 %, 20 %, 35 % и 50 % — и подскажем, что можно ставить без риска получить штраф.',
    blocks: [
      {
        h: 'Какая тонировка разрешена по ГОСТ',
        list: [
          'Лобовое стекло — светопропускание не менее 75 %',
          'Передние боковые стёкла — не менее 70 %',
          'Задние боковые и заднее стекло — можно любую плёнку',
        ],
        after:
          'То есть плотную тонировку 5 % или 15 % без вопросов можно ставить на заднюю полусферу. На передние боковые — только светлые варианты. Мы предупредим об этом до работы, а не после того, как вас остановят.',
      },
      {
        h: 'Притемнение оптики — 8 000 ₽',
        p: 'Отдельная услуга: фары затемняются полиуретаном. Это не только внешний вид — плёнка одновременно защищает стекло фары от камней. Степень притемнения подбираем так, чтобы свет оставался в норме.',
      },
      {
        h: 'Сколько занимает',
        p: 'Тонировка стёкол — 2–3 часа. Плёнку кроим по стеклу, сушим и даём выстояться: первые 2–3 дня стёкла лучше не опускать, чтобы клей окончательно схватился.',
      },
    ],
  },
  mojka: {
    intro:
      'Автомойка NLeveL на ЖБИ — это ручная мойка в две фазы с сохранением лакокрасочного покрытия. Мы не используем щётки и не гоняем по кругу один и тот же грязный раствор: сначала бесконтактный шампунь размягчает грязь, потом ручная мойка со свежей микрофиброй. Именно поэтому на тёмных машинах после нас не появляется «паутинка».',
    blocks: [
      {
        h: 'Четыре комплекса на выбор',
        list: [
          'Комплекс №1 — кузов, 1 000 ₽, 40–60 минут',
          'Комплекс №2 — кузов + ковры, 1 200 ₽',
          'Комплекс №3 — кузов + салон, 1 600 ₽, чернение резины в подарок',
          'Комплекс №4 — кузов + салон + кварц, 2 200 ₽, чернение в подарок',
        ],
        after:
          'Кварцевое покрытие в Комплексе №4 — это защитный слой, который держится несколько недель: вода скатывается, грязь липнет меньше, следующая мойка проходит быстрее.',
      },
      {
        h: 'Мойка на ЖБИ без очереди',
        p: 'Мы находимся на 40-летия Комсомола, 4Д — въезд за супермаркетом «Магнит», прямо с парковки. Это удобно жителям ЖБИ, Комсомольского, Пионерского, Втузгородка и Синих Камней: не нужно ехать в центр и стоять в очереди в торговом центре. На территории 8 машиномест и зона ожидания.',
      },
      {
        h: 'Почему у нас дешевле',
        p: 'Комплексная мойка в детейлинг-центрах в центре города стоит от 2 500 ₽, у нас сопоставимый набор — от 1 000 ₽. Разница не в качестве материалов и не в руках мастеров, а в аренде: мы на ЖБИ, а не в Гринвиче.',
      },
    ],
  },
  shumoizolyaciya: {
    intro:
      'Шумоизоляция делает салон тише, а музыку — чище. Основной шум в машину приходит через двери и колёсные арки: гул от дороги, шум покрышек, стук камней по аркам. Мы работаем именно с этими зонами — там эффект от вложенных денег самый заметный.',
    blocks: [
      {
        h: 'Что мы делаем',
        list: [
          'Шумоизоляция дверей — 25 000 ₽, три слоя: вибро, тепло-шумо, звуко',
          'Шумоизоляция арок снаружи — 16 000 ₽',
        ],
        after:
          'Двери проклеиваются в три разных слоя. Такая схема одновременно гасит вибрацию металла, глушит внешний шум и превращает дверь в акустический короб — динамики начинают звучать заметно плотнее и глубже.',
      },
      {
        h: 'С чего начать, если бюджет ограничен',
        p: 'Начинайте с арок. Они дешевле дверей и дают самый очевидный результат на трассе: пропадает гул от покрышек и стук гравия. Двери имеет смысл делать следующим этапом — особенно если для вас важен звук музыки.',
      },
      {
        h: 'Сколько это стоит по городу',
        p: 'Шумоизоляция двух колёсных арок в студиях Екатеринбурга обходится примерно в 25 000 ₽. У нас та же работа — 16 000 ₽. Полная шумоизоляция всего автомобиля у конкурентов доходит до 90 000 ₽; мы предлагаем сфокусироваться на зонах, которые реально шумят, вместо того чтобы обклеивать машину целиком.',
      },
    ],
  },
};

function pageService(slug) {
  const page = findPage(slug);
  const r = rel(page.url);
  const c = serviceContent[slug];
  const group = findGroup(page.service);

  // Для страниц плёнки показываем только релевантные позиции
  let items = group.items;
  if (slug === 'oklejka-plenkoj') {
    items = [...findGroup('plenka').items, ...findGroup('styling').items.filter((i) => i.name.includes('Styling') || i.name.includes('Антихром'))];
  } else if (slug === 'antigraviynaya-plenka') {
    items = findGroup('plenka').items;
  } else if (slug === 'tonirovka') {
    items = findGroup('styling').items.filter((i) => i.name.includes('Тонировка') || i.name.includes('Притемнение'));
  }

  const heroImg = page.hero
    ? `<div class="card__media" style="border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--line)">
    <img src="${r}assets/img/services/${page.hero}.webp" alt="${esc(page.h1)} — детейлинг-студия NLeveL"
         title="${esc(page.h1)} в NLeveL, Екатеринбург" width="771" height="1024" fetchpriority="high" decoding="async">
  </div>`
    : `<div class="card__media" style="border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--line)">
    <img src="${r}assets/img/gallery/work-8.webp" alt="${esc(page.h1)} — детейлинг-студия NLeveL, Екатеринбург"
         title="${esc(page.h1)} в NLeveL, Екатеринбург" width="578" height="768" fetchpriority="high" decoding="async">
  </div>`;

  const minPrice = Math.min(...items.map((i) => i.price));

  // FAQ, релевантный услуге
  const relFaq = faq.filter((f) => {
    const k = (f.q + f.a).toLowerCase();
    if (slug === 'polirovka') return k.includes('полир');
    if (slug === 'himchistka') return k.includes('химчист');
    if (slug === 'keramika') return k.includes('керамик');
    if (slug.includes('plenk')) return k.includes('плён') || k.includes('плен') || k.includes('оклеи');
    if (slug === 'tonirovka') return k.includes('тониров');
    if (slug === 'mojka') return k.includes('мойк') || k.includes('записи');
    return false;
  });
  const faqItems = relFaq.length ? relFaq : faq.slice(0, 4);

  const body = `
<section class="section--tight shell">
  <div class="split" style="align-items:center">
    <div>
      <span class="eyebrow">${esc(page.nav)} · Екатеринбург</span>
      <h1 style="margin-block:.5rem .7rem">${esc(page.h1)}</h1>
      <p class="lede">${esc(c.intro)}</p>
      <div class="btn-row" style="margin-top:var(--gap-md)">
        <a class="btn btn--primary btn--lg" href="${r}booking/">${icon('calendar')}Записаться</a>
        <a class="btn btn--ghost btn--lg" href="tel:${biz.phoneRaw}">${icon('phone')}${biz.phone}</a>
      </div>
      <p class="small" style="margin-top:.8rem">от ${money(minPrice)} · ${biz.city}, ${biz.street} · ${biz.hours}</p>
    </div>
    ${heroImg}
  </div>
</section>

<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Цены</span>
      <h2>Сколько это стоит</h2>
    </div>
    <a class="btn btn--ghost" href="${r}price/">Весь прайс</a>
  </div>
  <div class="ptable">
    ${items.map(priceRow).join('\n    ')}
  </div>
</section>

<section class="section shell">
  <div class="split split--aside">
    <div class="prose">
      ${c.blocks
        .map(
          (b) => `<h2>${esc(b.h)}</h2>
      ${b.p ? `<p>${esc(b.p)}</p>` : ''}
      ${b.list ? `<ul>${b.list.map((l) => `<li>${esc(l)}</li>`).join('')}</ul>` : ''}
      ${b.after ? `<p>${esc(b.after)}</p>` : ''}`
        )
        .join('\n      ')}

      <h2>Почему к нам</h2>
      <ul>
        <li><strong>Цена фиксируется до начала работ.</strong> Мы называем сумму на осмотре, и она не меняется при выдаче.</li>
        <li><strong>Подтверждаем запись заранее.</strong> Вы не приедете к закрытым воротам.</li>
        <li><strong>Фотоотчёт до и после.</strong> Показываем проблемные места до того, как взялись за работу.</li>
        <li><strong>Рейтинг ${biz.ratingAvg} по ${biz.reviewsTotal} отзывам</strong> на Яндекс.Картах и в 2ГИС.</li>
      </ul>
    </div>

    <aside class="sticky-aside">
      <div class="panel">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Записаться на ${esc(page.nav.toLowerCase())}</h3>
        <p class="small" style="margin-bottom:.9rem">Подтвердим время и назовём точную цену до начала работ.</p>
        <a class="btn btn--primary btn--block" href="${r}booking/">${icon('calendar')}Онлайн-запись</a>
        <div class="btn-row" style="margin-top:.5rem">
          <a class="btn btn--wa" style="flex:1" href="${biz.whatsappUrl}" target="_blank" rel="noopener">${icon('wa')}WhatsApp</a>
          <a class="btn btn--tg" style="flex:1" href="${biz.telegramUrl}" target="_blank" rel="noopener">${icon('tg')}Telegram</a>
        </div>
      </div>

      <div class="panel" style="margin-top:var(--gap-sm)">
        <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Другие услуги</h3>
        <ul class="footer" style="background:none;border:0;margin:0;padding:0">
          ${serviceNav
            .filter((s) => s.url !== page.url)
            .map((s) => `<li style="list-style:none;padding:.35rem 0"><a href="${r}${s.url.slice(1)}" style="color:var(--text-2)">${icon(s.icon)} ${s.label}</a></li>`)
            .join('\n          ')}
        </ul>
      </div>
    </aside>
  </div>
</section>

<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Работы</span>
      <h2>Как это выглядит</h2>
    </div>
    <a class="btn btn--ghost" href="${r}works/">Все работы</a>
  </div>
  <div class="gallery">
    ${(function () {
      // Категории берутся из реального содержимого снимков. Если под услугу
      // профильных фото ещё нет — показываем бокс и оборудование, а не чужую услугу.
      const want = {
        'oklejka-plenkoj': ['Оклейка'],
        'antigraviynaya-plenka': ['Оклейка'],
        tonirovka: ['Оклейка', 'Детали'],
        mojka: ['Мойка'],
        polirovka: ['Детали', 'Оклейка'],
        keramika: ['Оклейка', 'Мойка'],
        himchistka: ['Мойка'],
        shumoizolyaciya: ['Детали'],
      }[slug] || [];
      const primary = gallery.filter((g) => want.includes(g.cat));
      const filler = gallery.filter((g) => g.cat === 'Студия');
      return primary.concat(filler).slice(0, 6);
    })()
      .map(
        (g) => `<figure class="gallery__item" data-full="${r}assets/img/gallery/${g.f}.webp" data-alt="${esc(g.alt)}">
      <img src="${r}assets/img/gallery/thumb/${g.f}.webp" alt="${esc(g.alt)}" title="${esc(g.cat)} — NLeveL, Екатеринбург"
           width="600" height="750" loading="lazy" decoding="async">
      <figcaption class="gallery__cap">${esc(g.cat)}</figcaption>
    </figure>`
      )
      .join('\n    ')}
  </div>
</section>

${faqBlock(faqItems, 'Частые вопросы')}
${ctaBlock(r, `Запишитесь на ${page.nav.toLowerCase()}`, 'Приезжайте на осмотр — назовём точную цену и срок, а решать будете вы.')}
`;

  const svcSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    name: page.h1,
    serviceType: page.nav,
    description: page.description,
    url: SITE_URL + page.url,
    provider: { '@id': SITE_URL + '/#org' },
    areaServed: { '@type': 'City', name: 'Екатеринбург' },
    offers: items.map((i) => ({
      '@type': 'Offer',
      name: i.name,
      description: i.desc,
      price: i.price,
      priceCurrency: 'RUB',
      availability: 'https://schema.org/InStock',
    })),
  };

  return { page, html: layout(page, body, { schema: [svcSchema, faqSchema(faqItems)] }) };
}

/* ============================================================
   404
   ============================================================ */
function page404() {
  const page = {
    slug: '404',
    url: '/404.html',
    nav: 'Страница не найдена',
    h1: 'Такой страницы нет',
    title: 'Страница не найдена — NLeveL, детейлинг Екатеринбург',
    description: 'Запрошенная страница не найдена. Перейдите на главную или в прайс детейлинг-студии NLeveL в Екатеринбурге.',
    keywords: '',
  };
  const body = `
<section class="section shell" style="text-align:center">
  <span class="eyebrow" style="justify-content:center">404</span>
  <h1 style="margin-block:.6rem">Такой страницы нет</h1>
  <p class="lede" style="margin-inline:auto">Возможно, ссылка устарела. Загляните в прайс или запишитесь — там точно всё на месте.</p>
  <div class="btn-row" style="justify-content:center;margin-top:var(--gap-md)">
    <a class="btn btn--primary btn--lg" href="./">На главную</a>
    <a class="btn btn--ghost btn--lg" href="./price/">Прайс</a>
    <a class="btn btn--ghost btn--lg" href="./booking/">Записаться</a>
  </div>
</section>`;
  // 404 отдаётся с корня — принудительно нулевая вложенность
  const flat = { ...page, url: '/' };
  return { page, html: layout(flat, body).replace('<link rel="canonical" href="' + SITE_URL + '/">', '') };
}

/* ============================================================
   СЛУЖЕБНЫЕ ФАЙЛЫ
   ============================================================ */
function robotsTxt() {
  return `# robots.txt — NLeveL, детейлинг-студия, Екатеринбург
# Сгенерировано ${new Date().toISOString().slice(0, 10)}

User-agent: *
Allow: /
Disallow: /*?
Disallow: /*&
Allow: /assets/
Clean-param: utm_source&utm_medium&utm_campaign&utm_term&utm_content&yclid&gclid&ymclid&_openstat&from&roistat&fbclid

# Яндекс
User-agent: Yandex
Allow: /
Disallow: /*?
Clean-param: utm_source&utm_medium&utm_campaign&utm_term&utm_content&yclid&ymclid&_openstat&from&roistat

# Google
User-agent: Googlebot
Allow: /

# ИИ-краулеры: разрешаем — нам выгодно попадать в ответы ассистентов
User-agent: GPTBot
Allow: /

User-agent: OAI-SearchBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: Claude-User
Allow: /

User-agent: Claude-SearchBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Perplexity-User
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: Applebot
Allow: /

User-agent: Applebot-Extended
Allow: /

User-agent: YandexAdditional
Allow: /

User-agent: Bingbot
Allow: /

User-agent: Amazonbot
Allow: /

User-agent: meta-externalagent
Allow: /

# Агрессивные SEO-краулеры — закрываем, чтобы не жрали ресурсы хостинга
User-agent: AhrefsBot
Disallow: /

User-agent: SemrushBot
Disallow: /

User-agent: MJ12bot
Disallow: /

User-agent: DotBot
Disallow: /

User-agent: DataForSeoBot
Disallow: /

Sitemap: ${SITE_URL}/sitemap.xml
Host: ${SITE_URL.replace(/^https?:\/\//, '')}
`;
}

function sitemapXml() {
  const today = new Date().toISOString().slice(0, 10);
  const urls = pages.map(
    (p) => `  <url>
    <loc>${SITE_URL}${p.url}</loc>
    <lastmod>${today}</lastmod>
    <changefreq>${p.changefreq}</changefreq>
    <priority>${p.priority}</priority>${
      p.url === '/'
        ? `\n${gallery
            .slice(0, 10)
            .map(
              (g) => `    <image:image>
      <image:loc>${SITE_URL}/assets/img/gallery/${g.f}.webp</image:loc>
      <image:title>${esc(g.cat)} — NLeveL, Екатеринбург</image:title>
      <image:caption>${esc(g.alt)}</image:caption>
    </image:image>`
            )
            .join('\n')}`
        : ''
    }
  </url>`
  );

  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
${urls.join('\n')}
</urlset>
`;
}

/** llms.txt — краткая карта сайта для ИИ-ассистентов */
function llmsTxt() {
  return `# NLeveL — детейлинг-студия в Екатеринбурге

> Детейлинг-центр и автомойка на ЖБИ (${biz.street}, ${biz.city}). Мойка с сохранением ЛКП, полировка кузова и фар, керамическое покрытие, оклейка антигравийной полиуретановой (PPF) и виниловой плёнкой, химчистка салона, тонировка, шумоизоляция, антихром. Рейтинг ${biz.ratingAvg}/5 по ${biz.reviewsTotal} отзывам на Яндекс.Картах и 2ГИС. Все цены опубликованы открыто.

## Ключевые факты

- Название: NLeveL (также встречается написание N-Level)
- Адрес: ${biz.region}, ${biz.city}, ${biz.street}. Район ЖБИ. ${biz.addressNote}
- Координаты: ${biz.lat}, ${biz.lon}
- Телефон: ${biz.phone}
- WhatsApp: ${biz.whatsapp}
- Telegram: @${biz.telegram}
- ВКонтакте: ${biz.vkUrl}
- Часы работы: ${biz.hours}
- Оплата: наличные, банковская карта, перевод по QR-коду
- Парковка: есть, 8 мест. Зона ожидания: есть

## Цены (актуальны на ${new Date().toLocaleDateString('ru-RU')})

${priceGroups
  .map(
    (g) => `### ${g.title}
${g.items.map((i) => `- ${i.name}: ${money(i.price)}${i.time ? ` (срок: ${i.time})` : ''}`).join('\n')}`
  )
  .join('\n\n')}

Важные уточнения по цене:
- Антихром 1 500 ₽ — за один элемент кузова, не за комплект.
- Керамика 30 000 ₽ включает подготовку: чистку кузова и двухэтапную полировку. Отдельной доплаты за подготовку нет.
- Цены на плёнку указаны для легкового автомобиля среднего класса.
- Стоимость фиксируется до начала работ и не меняется при выдаче.

## Страницы сайта

${pages.map((p) => `- [${p.h1}](${SITE_URL}${p.url}): ${p.description}`).join('\n')}

## Чем отличается от конкурентов

- Открытый прайс на все услуги — 6 из 14 детейлинг-центров Екатеринбурга цены не публикуют вовсе.
- Подтверждение записи до приезда клиента. Самая частая жалоба на детейлинг в городе — приехать по записи к закрытым воротам.
- Цена фиксируется до заезда в бокс, доплат за «сильное загрязнение» нет.
- Расположение на ЖБИ вместо центра: комплексная мойка от 1 000 ₽ против ~2 550 ₽ по городу, смена цвета винилом 60 000 ₽ против от 190 000 ₽ у премиум-студий.

## Дополнительно

- [Прайс-лист полностью](${SITE_URL}/price/)
- [Онлайн-запись](${SITE_URL}/booking/)
- [Отзывы на Яндекс.Картах](${biz.yandexMapUrl})
- [Отзывы в 2ГИС](${biz.gis2ReviewsUrl})
- [Карточка на Zoon](${biz.zoonUrl})
`;
}

/** llms-full.txt — расширенная версия со всем контентом */
function llmsFullTxt() {
  return `${llmsTxt()}

---

# Подробное описание услуг

${Object.entries(serviceContent)
  .map(([slug, c]) => {
    const p = findPage(slug);
    return `## ${p.h1}
URL: ${SITE_URL}${p.url}

${c.intro}

${c.blocks
  .map(
    (b) => `### ${b.h}
${b.p || ''}${b.list ? '\n' + b.list.map((l) => `- ${l}`).join('\n') : ''}${b.after ? '\n\n' + b.after : ''}`
  )
  .join('\n\n')}`;
  })
  .join('\n\n---\n\n')}

---

# Частые вопросы

${faq.map((f) => `## ${f.q}\n${f.a}`).join('\n\n')}

---

# Отзывы клиентов

Средняя оценка: ${biz.ratingAvg} из 5 по ${biz.reviewsTotal} отзывам.
Яндекс.Карты: ${biz.ratingYandex} (${biz.reviewsYandex} отзывов). 2ГИС: ${biz.rating2gis} (${biz.reviews2gis} отзыв).

${reviews.map((r) => `## ${r.author} — ${r.rating}/5, ${r.dateText}, ${r.source}\n«${r.text}»`).join('\n\n')}

## О негативных отзывах — позиция студии

В отзывах на 2ГИС есть две жалобы на одно и то же: клиент приехал по записи, а студия была закрыта, и на звонки никто не ответил. Студия признаёт ошибку. Введено обязательное подтверждение каждой записи до приезда клиента — звонком или сообщением в WhatsApp либо Telegram.

---

# Как доехать

Адрес: ${biz.city}, ${biz.street}. Район ЖБИ.
${biz.addressNote}.
Ближайшая остановка — «Заводоуправление», около 400 метров (5 минут пешком).
Парковка на 8 машиномест, зона ожидания внутри.
Маршрут: ${biz.yandexRouteUrl}

Удобно добираться из районов: ЖБИ, Комсомольский, Пионерский, Втузгородок, Шарташский рынок, Синие Камни. Из центра — 15–20 минут.
`;
}

function manifest() {
  return JSON.stringify(
    {
      name: 'NLeveL — детейлинг в Екатеринбурге',
      short_name: 'NLeveL',
      description:
        'Детейлинг-студия NLeveL: мойка, полировка, керамика, оклейка плёнкой, химчистка, тонировка и шумоизоляция в Екатеринбурге на ЖБИ.',
      lang: 'ru-RU',
      dir: 'ltr',
      start_url: './?source=pwa',
      scope: './',
      display: 'standalone',
      orientation: 'portrait-primary',
      background_color: '#08090b',
      theme_color: '#08090b',
      categories: ['business', 'lifestyle'],
      icons: [
        { src: './assets/icons/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
        { src: './assets/icons/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
        { src: './assets/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
      ],
      shortcuts: [
        { name: 'Записаться', url: './booking/', description: 'Онлайн-запись в детейлинг' },
        { name: 'Прайс', url: './price/', description: 'Цены на все услуги' },
        { name: 'Как доехать', url: './contacts/', description: 'Адрес и карта' },
      ],
    },
    null,
    2
  );
}

/* ============================================================
   ЗАПИСЬ НА ДИСК
   ============================================================ */
function write(relPath, content) {
  const full = path.join(ROOT, relPath);
  fs.mkdirSync(path.dirname(full), { recursive: true });
  fs.writeFileSync(full, content, 'utf8');
  const kb = (Buffer.byteLength(content, 'utf8') / 1024).toFixed(1);
  console.log(`  ✓ ${relPath.padEnd(42)} ${kb.padStart(7)} КБ`);
}

function build() {
  console.log('\n▸ Сборка сайта NLeveL\n');

  const built = [
    pageIndex(),
    pagePrice(),
    pageWorks(),
    pageReviews(),
    pageBooking(),
    pageContacts(),
    ...['polirovka', 'oklejka-plenkoj', 'antigraviynaya-plenka', 'himchistka', 'keramika', 'tonirovka', 'mojka', 'shumoizolyaciya'].map(
      pageService
    ),
  ];

  console.log('Страницы:');
  built.forEach(({ page, html }) => {
    const out = page.url === '/' ? 'index.html' : page.url.replace(/^\//, '') + 'index.html';
    write(out, html);
  });

  const nf = page404();
  write('404.html', nf.html);

  console.log('\nСлужебные файлы:');
  write('robots.txt', robotsTxt());
  write('sitemap.xml', sitemapXml());
  write('llms.txt', llmsTxt());
  write('llms-full.txt', llmsFullTxt());
  write('manifest.webmanifest', manifest());

  console.log(`\n▸ Готово: ${built.length + 1} HTML + 5 служебных файлов\n`);
}

build();
