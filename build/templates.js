'use strict';

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const { SITE_URL, biz, pages } = require('./data');

/* Хэш содержимого CSS/JS — чтобы браузер и CDN сразу подхватывали правки,
   а не отдавали старую версию из кэша */
const assetHash = (file) => {
  try {
    const buf = fs.readFileSync(path.resolve(__dirname, '..', file));
    return crypto.createHash('md5').update(buf).digest('hex').slice(0, 8);
  } catch {
    return String(Date.now());
  }
};
const CSS_V = assetHash('assets/css/style.css');
const JS_V = assetHash('assets/js/app.js');

/* ---------- Утилиты ---------- */
const esc = (s) =>
  String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

const money = (n) => n.toLocaleString('ru-RU').replace(/ /g, ' ') + ' ₽';

const depth = (url) => (url === '/' ? 0 : url.split('/').filter(Boolean).length);
// Все страницы лежат в подпапках вида /polirovka/index.html, поэтому корень — ../ на каждый уровень
const rel = (url) => (depth(url) === 0 ? '' : '../'.repeat(depth(url)));

/* ---------- Иконки (инлайн SVG, без внешних запросов) ---------- */
const I = {
  home: '<path d="M3 10.2 12 3l9 7.2V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
  price: '<path d="M3 6a3 3 0 0 1 3-3h5.2a2 2 0 0 1 1.4.6l7.8 7.8a2 2 0 0 1 0 2.8l-5.2 5.2a2 2 0 0 1-2.8 0L4.6 11.6A2 2 0 0 1 4 10.2z"/><circle cx="8.5" cy="8.5" r="1.6" fill="currentColor"/>',
  gallery:
    '<rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="m3.5 17.5 4.6-4.3a2 2 0 0 1 2.7 0l3.1 2.9 2.2-1.9a2 2 0 0 1 2.6 0l3.3 2.8"/>',
  calendar:
    '<rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M3 10h18M8 3v4M16 3v4"/><circle cx="8.5" cy="14.5" r="1.1" fill="currentColor" stroke="none"/><circle cx="12" cy="14.5" r="1.1" fill="currentColor" stroke="none"/>',
  pin: '<path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
  phone:
    '<path d="M6.6 3.5h3l1.5 4-2 1.5a12 12 0 0 0 5.9 5.9l1.5-2 4 1.5v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4.6 5.7a2 2 0 0 1 2-2.2z"/>',
  wa: '<path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.5-1.2A9 9 0 1 0 12 3z"/><path d="M8.9 8.3c.2-.4.4-.4.6-.4h.5c.2 0 .4 0 .6.5l.8 1.8c0 .2 0 .4-.1.5l-.5.6c-.2.2-.3.3-.1.6a7 7 0 0 0 3.2 2.8c.3.1.5.1.7-.1l.7-.8c.2-.2.3-.2.6-.1l1.8.9c.2.1.3.3.3.5v.5c0 .4-.4.9-.8 1a2 2 0 0 1-1.3.2 10 10 0 0 1-6.9-6.7c-.2-.7-.2-1.4-.1-1.8z"/>',
  tg: '<path d="M21.3 4.3 2.9 11.2c-.9.3-.9 1.5.1 1.7l4.6 1.2 1.7 5.1c.2.7 1.1.9 1.6.3l2.5-2.6 4.7 3.5c.7.5 1.6.1 1.8-.7l3-14c.2-.9-.7-1.7-1.6-1.4z"/><path d="m7.6 14.1 10.4-6.6-8.4 8"/>',
  vk: '<path d="M3 7.5h3c.4 3.3 1.8 5.9 3.2 6.6V7.5h2.9v4.4c1.3-.1 2.7-1.7 3.2-4.4h2.8c-.4 2.2-1.5 4-2.8 5.1 1.4.9 2.7 2.4 3.4 4.9h-3.1c-.6-1.7-1.7-3-3.5-3.2v3.2h-.4C7.8 17.5 4.4 13.2 3 7.5z"/>',
  star: '<path d="m12 2.6 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5-5.8-3-5.8 3 1.1-6.5L2.6 9.4l6.5-.9z" fill="currentColor" stroke="none"/>',
  menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
  close: '<path d="M6 6l12 12M18 6 6 18"/>',
  left: '<path d="m14.5 5-7 7 7 7"/>',
  right: '<path d="m9.5 5 7 7-7 7"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.2 2"/>',
  'check-clock':
    '<circle cx="12" cy="12" r="9"/><path d="m8.2 12.3 2.6 2.6 5-5.4"/>',
  wallet:
    '<rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18M16.5 14.5h1.5"/>',
  shield: '<path d="M12 3 5 6v6c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
  camera:
    '<path d="M4 8h3l1.5-2.2h7L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13.2" r="3.4"/>',
  parking:
    '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9.5 17V7.5h3.2a2.9 2.9 0 0 1 0 5.8H9.5"/>',
  spray:
    '<path d="M9 8h5.5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V8z"/><path d="M10.5 8V5.5h3.5M18 5h.01M20.5 8h.01M18 11h.01"/>',
  drop: '<path d="M12 3.5c3.2 3.6 5.5 6.4 5.5 9.2a5.5 5.5 0 0 1-11 0c0-2.8 2.3-5.6 5.5-9.2z"/>',
  film: '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M8 5v14M16 5v14M3 12h18"/>',
  sparkle:
    '<path d="M12 3.5 13.7 9l5.5 1.7-5.5 1.7L12 18l-1.7-5.6L4.8 10.7 10.3 9z"/><path d="M18.5 15.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
  sound:
    '<path d="M4 9.5h3.2L12 5.5v13l-4.8-4H4z"/><path d="M15.5 9.2a4 4 0 0 1 0 5.6M18.2 6.6a7.7 7.7 0 0 1 0 10.8"/>',
  car: '<path d="M4.5 16.5h15M5 16.5v2a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-2M15.5 16.5v2a1 1 0 0 0 1 1H18a1 1 0 0 0 1-1v-2"/><path d="M4.2 16.5v-4l1.9-4.6a2 2 0 0 1 1.9-1.2h8a2 2 0 0 1 1.9 1.2l1.9 4.6v4"/><path d="M5 12.5h14"/><circle cx="7.8" cy="14.3" r=".9" fill="currentColor" stroke="none"/><circle cx="16.2" cy="14.3" r=".9" fill="currentColor" stroke="none"/>',
  route:
    '<circle cx="6" cy="18" r="2.6"/><circle cx="18" cy="6" r="2.6"/><path d="M8.6 18h6.2a3.4 3.4 0 0 0 0-6.8H9.2a3.4 3.4 0 0 1 0-6.8h6.2"/>',
  apple:
    '<path d="M16.4 12.7c0-2.4 2-3.6 2.1-3.6-1.1-1.7-2.9-1.9-3.5-1.9-1.5-.2-2.9.9-3.6.9-.8 0-1.9-.9-3.1-.8-1.6 0-3.1.9-3.9 2.4-1.7 2.9-.4 7.2 1.2 9.5.8 1.2 1.7 2.4 3 2.4 1.2 0 1.6-.8 3.1-.8 1.4 0 1.8.8 3.1.7 1.3 0 2.1-1.2 2.9-2.3.9-1.3 1.3-2.6 1.3-2.7-.1 0-2.5-1-2.6-3.8z" fill="currentColor" stroke="none"/><path d="M14.2 5.7c.7-.8 1.1-1.9 1-3-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.9-1 3 1.1.1 2.2-.6 2.9-1.5z" fill="currentColor" stroke="none"/>',
  android:
    '<path d="M6 10.5h12v7.2a1.3 1.3 0 0 1-1.3 1.3H7.3A1.3 1.3 0 0 1 6 17.7z" fill="currentColor" stroke="none"/><rect x="2.6" y="10.3" width="2.5" height="6.4" rx="1.25" fill="currentColor" stroke="none"/><rect x="18.9" y="10.3" width="2.5" height="6.4" rx="1.25" fill="currentColor" stroke="none"/><rect x="8.6" y="19" width="2.4" height="4" rx="1.2" fill="currentColor" stroke="none"/><rect x="13" y="19" width="2.4" height="4" rx="1.2" fill="currentColor" stroke="none"/><path d="M6 9.3a6 6 0 0 1 12 0z" fill="currentColor" stroke="none"/><path d="m7.4 4.2 1 1.7M16.6 4.2l-1 1.7"/>',
  external:
    '<path d="M14 4h6v6M20 4l-8.5 8.5"/><path d="M18 13.5V19a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 19V7.5A1.5 1.5 0 0 1 5 6h5.5"/>',
  app: '<rect x="6" y="2.5" width="12" height="19" rx="3"/><path d="M10.5 5.5h3"/><path d="M9.5 12.5l1.8 1.8 3.4-3.6"/>',
};

const icon = (name, cls = '') =>
  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"${
    cls ? ` class="${cls}"` : ''
  } aria-hidden="true" focusable="false">${I[name] || I.car}</svg>`;

const stars = (n = 5) =>
  `<span class="stars" role="img" aria-label="Оценка ${n} из 5">${
    `<svg viewBox="0 0 24 24" aria-hidden="true">${I.star}</svg>`.repeat(n)
  }</span>`;

/* ---------- Логотип ----------
   Фирменная иконка из приложения (app/icons/Icon-512.png).
   Один источник на шапку, подвал, фавиконки и иконки установки. */
const logoSvg = (cls = 'logo__mark', r = '') =>
  `<img class="${cls}" src="${r}assets/icons/logo.webp" width="96" height="96"
        alt="Логотип детейлинг-студии NLeveL" title="NLeveL — детейлинг в Екатеринбурге"
        decoding="async">`;

/* ---------- Навигация ---------- */
const mainNav = [
  { url: '/', label: 'Главная' },
  { url: '/price/', label: 'Прайс' },
  { url: '/works/', label: 'Работы' },
  { url: '/reviews/', label: 'Отзывы' },
  { url: '/contacts/', label: 'Контакты' },
];

const serviceNav = [
  { url: '/polirovka/', label: 'Полировка', icon: 'sparkle', hint: 'кузов 12 000 ₽ · фары 2 000 ₽' },
  { url: '/oklejka-plenkoj/', label: 'Оклейка плёнкой', icon: 'film', hint: 'полиуретан и винил' },
  { url: '/antigraviynaya-plenka/', label: 'Антигравийная плёнка', icon: 'shield', hint: 'PPF, бронеплёнка' },
  { url: '/himchistka/', label: 'Химчистка салона', icon: 'spray', hint: '15 000 ₽ под ключ' },
  { url: '/keramika/', label: 'Керамика', icon: 'drop', hint: '30 000 ₽ с полировкой' },
  { url: '/tonirovka/', label: 'Тонировка', icon: 'car', hint: '3 000 ₽' },
  { url: '/mojka/', label: 'Автомойка на ЖБИ', icon: 'drop', hint: 'от 1 000 ₽' },
  { url: '/shumoizolyaciya/', label: 'Шумоизоляция', icon: 'sound', hint: 'от 16 000 ₽' },
];

const tabBarItems = pages.filter((p) => p.inTabBar);

/* ---------- Шапка ---------- */
function header(page) {
  const r = rel(page.url);
  const cur = (u) => (u === page.url ? ' aria-current="page"' : '');

  return `
<header class="appbar" id="appbar">
  <div class="appbar__inner">
    <a class="logo" href="${r || './'}" aria-label="NLeveL — на главную">
      ${logoSvg('logo__mark', r)}
      <span class="logo__text"><b>N</b>LeveL</span>
    </a>

    <nav class="nav" id="nav" aria-label="Основное меню">
      <span class="nav__pill" id="navPill" aria-hidden="true"></span>
      <a class="nav__link" href="${r || './'}"${cur('/')}>Главная</a>

      <div class="nav__group">
        <a class="nav__link" href="${r}price/" aria-haspopup="true">Услуги ▾</a>
        <div class="nav__panel">
          ${serviceNav
            .map(
              (s) => `<a href="${r}${s.url.slice(1)}"${cur(s.url)}>${icon(s.icon)}<b>${s.label}<span>${
                s.hint
              }</span></b></a>`
            )
            .join('\n          ')}
        </div>
      </div>

      ${mainNav
        .slice(1)
        .map((n) => `<a class="nav__link" href="${r}${n.url.slice(1)}"${cur(n.url)}>${n.label}</a>`)
        .join('\n      ')}
    </nav>

    <a class="btn btn--primary appbar__cta" href="${r}booking/">${icon('calendar')}Записаться</a>

    <button class="burger" id="burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="sheet">
      ${icon('menu')}
    </button>
  </div>
</header>

<!-- Мобильное меню-шторка -->
<div class="sheet" id="sheet" data-open="false" role="dialog" aria-modal="true" aria-label="Меню сайта">
  <div class="sheet__scrim" data-close></div>
  <div class="sheet__panel">
    <div class="sheet__grab"></div>
    <div class="btn-row">
      <a class="btn btn--primary" style="flex:1" href="tel:${biz.phoneRaw}">${icon('phone')}Позвонить</a>
      <a class="btn btn--wa" href="${biz.whatsappUrl}" target="_blank" rel="noopener" aria-label="Написать в WhatsApp">${icon('wa')}</a>
      <a class="btn btn--tg" href="${biz.telegramUrl}" target="_blank" rel="noopener" aria-label="Написать в Telegram">${icon('tg')}</a>
    </div>

    <p class="sheet__title">Услуги</p>
    <ul class="sheet__list">
      ${serviceNav
        .map(
          (s) =>
            `<li><a href="${r}${s.url.slice(1)}"${cur(s.url)}>${icon(s.icon)}${s.label}<em>${s.hint
              .replace(/ ·.*/, '')
              .replace('под ключ', '')
              .trim()}</em></a></li>`
        )
        .join('\n      ')}
    </ul>

    <p class="sheet__title">Разделы</p>
    <ul class="sheet__list">
      ${mainNav
        .map((n) => `<li><a href="${r}${n.url === '/' ? '' : n.url.slice(1)}"${cur(n.url)}>${n.label}</a></li>`)
        .join('\n      ')}
      <li><a href="${r}booking/"${cur('/booking/')}>${icon('calendar')}Онлайн-запись</a></li>
      <li><a href="${biz.appUrl}" target="_blank" rel="noopener">${icon('app')}Приложение<em>iOS · Android</em></a></li>
    </ul>
  </div>
</div>`;
}

/* ---------- Нижняя таб-панель (как в приложении) ---------- */
function tabbar(page) {
  const r = rel(page.url);
  return `
<nav class="tabbar" aria-label="Быстрая навигация">
  ${tabBarItems
    .map(
      (t) =>
        `<a class="tabbar__item" href="${r}${t.url === '/' ? '' : t.url.slice(1)}"${
          t.url === page.url ? ' aria-current="page"' : ''
        }>${icon(t.navIcon)}<span>${t.nav}</span></a>`
    )
    .join('\n  ')}
</nav>`;
}

/* ---------- Плавающие кнопки связи ---------- */
function fab() {
  return `
<div class="fab">
  <a class="fab__btn btn--tg" href="${biz.telegramUrl}" target="_blank" rel="noopener" aria-label="Написать в Telegram">${icon('tg')}</a>
  <a class="fab__btn btn--wa" href="${biz.whatsappUrl}" target="_blank" rel="noopener" aria-label="Написать в WhatsApp">${icon('wa')}</a>
</div>`;
}

/* ---------- Подвал ---------- */
function footer(page) {
  const r = rel(page.url);
  const year = new Date().getFullYear();
  return `
<footer class="footer">
  <div class="shell">
    <div class="footer__grid">
      <div>
        <a class="logo" href="${r || './'}" style="margin-bottom:.8rem">${logoSvg('logo__mark', r)}<span class="logo__text"><b>N</b>LeveL</span></a>
        <p class="small" style="max-width:32ch">Детейлинг-студия в Екатеринбурге на ЖБИ. Мойка с сохранением ЛКП, полировка, керамика, оклейка плёнкой, химчистка, тонировка и шумоизоляция.</p>
        <div class="socials" style="margin-top:1rem">
          <a href="${biz.telegramUrl}" target="_blank" rel="noopener" aria-label="Telegram-канал NLeveL">${icon('tg')}</a>
          <a href="${biz.vkUrl}" target="_blank" rel="noopener" aria-label="Группа NLeveL во ВКонтакте">${icon('vk')}</a>
          <a href="${biz.whatsappUrl}" target="_blank" rel="noopener" aria-label="WhatsApp NLeveL">${icon('wa')}</a>
          <a href="${biz.yandexMapUrl}" target="_blank" rel="noopener" aria-label="NLeveL на Яндекс.Картах">${icon('pin')}</a>
        </div>
      </div>

      <div>
        <h3>Услуги</h3>
        <ul>${serviceNav.map((s) => `<li><a href="${r}${s.url.slice(1)}">${s.label}</a></li>`).join('')}</ul>
      </div>

      <div>
        <h3>Разделы</h3>
        <ul>
          ${mainNav
            .map((n) => `<li><a href="${r}${n.url === '/' ? '' : n.url.slice(1)}">${n.label}</a></li>`)
            .join('')}
          <li><a href="${r}booking/">Онлайн-запись</a></li>
          <li><a href="${biz.appUrl}" target="_blank" rel="noopener">Приложение NLeveL</a></li>
        </ul>
      </div>

      <div>
        <h3>Контакты</h3>
        <ul>
          <li><a href="tel:${biz.phoneRaw}">${biz.phone}</a></li>
          <li><a href="${biz.whatsappUrl}" target="_blank" rel="noopener">WhatsApp</a></li>
          <li><a href="${biz.telegramUrl}" target="_blank" rel="noopener">Telegram @${biz.telegram}</a></li>
          <li><a href="${biz.yandexRouteUrl}" target="_blank" rel="noopener">${biz.city}, ${biz.street}</a></li>
          <li><span class="small">${biz.hours}</span></li>
        </ul>
      </div>
    </div>

    <div class="footer__bottom">
      <span>© ${year} NLeveL — детейлинг-студия, Екатеринбург</span>
      <span>Информация на сайте не является публичной офертой. Итоговая стоимость подтверждается до начала работ.</span>
    </div>
  </div>
</footer>`;
}

/* ---------- Хлебные крошки ---------- */
function crumbs(page) {
  if (page.url === '/') return '';
  const r = rel(page.url);
  return `
<nav class="crumbs shell" aria-label="Хлебные крошки">
  <ol>
    <li><a href="${r || './'}">Главная</a></li>
    <li aria-current="page">${esc(page.nav || page.h1)}</li>
  </ol>
</nav>`;
}

/* ---------- JSON-LD ---------- */
function jsonLd(page, extra = []) {
  const org = {
    '@context': 'https://schema.org',
    '@type': 'AutoRepair',
    '@id': SITE_URL + '/#org',
    name: biz.name,
    alternateName: 'N-Level детейлинг',
    description:
      'Детейлинг-студия в Екатеринбурге: мойка с сохранением ЛКП, полировка кузова и фар, керамическое покрытие, оклейка антигравийной и виниловой плёнкой, химчистка салона, тонировка, шумоизоляция.',
    url: SITE_URL + '/',
    telephone: biz.phoneRaw,
    image: SITE_URL + '/assets/img/gallery/work-1.webp',
    logo: SITE_URL + '/assets/icons/icon-512.png',
    priceRange: '₽₽',
    currenciesAccepted: 'RUB',
    paymentAccepted: 'Наличные, банковская карта, перевод по QR-коду',
    address: {
      '@type': 'PostalAddress',
      streetAddress: biz.street,
      addressLocality: biz.city,
      addressRegion: biz.region,
      addressCountry: biz.country,
    },
    geo: { '@type': 'GeoCoordinates', latitude: biz.lat, longitude: biz.lon },
    hasMap: biz.yandexMapUrl,
    openingHoursSpecification: [
      {
        '@type': 'OpeningHoursSpecification',
        dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        opens: '10:00',
        closes: '21:00',
      },
    ],
    areaServed: { '@type': 'City', name: 'Екатеринбург' },
    sameAs: [biz.vkUrl, biz.telegramUrl, biz.yandexMapUrl, biz.gis2Url, biz.zoonUrl],
    aggregateRating: {
      '@type': 'AggregateRating',
      ratingValue: biz.ratingAvg,
      reviewCount: biz.reviewsTotal,
      bestRating: 5,
      worstRating: 1,
    },
  };

  const site = {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    '@id': SITE_URL + '/#website',
    url: SITE_URL + '/',
    name: 'NLeveL — детейлинг в Екатеринбурге',
    inLanguage: 'ru-RU',
    publisher: { '@id': SITE_URL + '/#org' },
  };

  const blocks = [org, site];

  if (page.url !== '/') {
    blocks.push({
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Главная', item: SITE_URL + '/' },
        { '@type': 'ListItem', position: 2, name: page.nav || page.h1, item: SITE_URL + page.url },
      ],
    });
  }

  blocks.push(...extra);

  return blocks
    .map((b) => `<script type="application/ld+json">${JSON.stringify(b)}</script>`)
    .join('\n');
}

/* ---------- Базовый шаблон страницы ---------- */
function layout(page, body, opts = {}) {
  const r = rel(page.url);
  const canonical = SITE_URL + page.url;
  const ogImage = SITE_URL + '/assets/img/og.jpg';

  return `<!DOCTYPE html>
<html lang="ru" prefix="og: https://ogp.me/ns#">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>${esc(page.title)}</title>
<meta name="description" content="${esc(page.description)}">
<meta name="keywords" content="${esc(page.keywords || '')}">
<link rel="canonical" href="${canonical}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="yandex-verification" content="ЗАМЕНИТЬ_НА_КОД_ИЗ_ЯНДЕКС_ВЕБМАСТЕРА">
<meta name="google-site-verification" content="ЗАМЕНИТЬ_НА_КОД_ИЗ_SEARCH_CONSOLE">

<meta name="author" content="NLeveL">
<meta name="geo.region" content="RU-SVE">
<meta name="geo.placename" content="Екатеринбург">
<meta name="geo.position" content="${biz.lat};${biz.lon}">
<meta name="ICBM" content="${biz.lat}, ${biz.lon}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="NLeveL — детейлинг в Екатеринбурге">
<meta property="og:locale" content="ru_RU">
<meta property="og:title" content="${esc(page.title)}">
<meta property="og:description" content="${esc(page.description)}">
<meta property="og:url" content="${canonical}">
<meta property="og:image" content="${ogImage}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="NLeveL — детейлинг-студия в Екатеринбурге на ЖБИ">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="${esc(page.title)}">
<meta name="twitter:description" content="${esc(page.description)}">
<meta name="twitter:image" content="${ogImage}">

<meta name="theme-color" content="#08090b">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="NLeveL">
<meta name="format-detection" content="telephone=no">

<link rel="manifest" href="${r}manifest.webmanifest">
<link rel="icon" href="${r}assets/icons/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="${r}assets/icons/icon-180.png">
<link rel="preconnect" href="https://api-maps.yandex.ru" crossorigin>

<link rel="stylesheet" href="${r}assets/css/style.css?v=${CSS_V}">
${jsonLd(page, opts.schema || [])}
</head>
<body>
<a class="skip-link" href="#main">Перейти к содержимому</a>
${header(page)}
${crumbs(page)}
<main id="main">
${body}
</main>
${footer(page)}
${tabbar(page)}
${fab()}
<script src="${r}assets/js/app.js?v=${JS_V}" defer></script>
</body>
</html>`;
}

module.exports = {
  esc,
  money,
  rel,
  icon,
  stars,
  logoSvg,
  layout,
  serviceNav,
  mainNav,
  jsonLd,
};
