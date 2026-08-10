/* ==========================================================================
   Кэш сайта NLeveL.

   Задача: при повторном заходе страница появляется мгновенно, из памяти,
   и только потом тихо обновляется из сети. Плюс сайт открывается,
   даже если связь пропала.

   Чего этот файл НЕ делает намеренно:
   — не трогает /app/ (у приложения записи свой service worker, мешать нельзя);
   — не кэширует /admin/ (админ должен видеть свежие данные всегда);
   — не кэширует POST-запросы и любые адреса с параметрами.
   ========================================================================== */

const VERSION = 'nlevel-v1';
const SHELL = VERSION + '-shell';   // стили, скрипты, иконки — редко меняются
const PAGES = VERSION + '-pages';   // HTML страниц
const MEDIA = VERSION + '-media';   // фотографии

/* Что кладём в кэш сразу при установке */
const PRECACHE = [
  'assets/css/style.css',
  'assets/js/app.js',
  'assets/icons/logo.webp',
  'assets/icons/favicon-32.png',
];

const scopePath = new URL(self.registration.scope).pathname;

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(SHELL)
      .then((c) => c.addAll(PRECACHE.map((p) => scopePath + p)))
      .catch(() => {/* если что-то не скачалось — не блокируем установку */})
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

/** Адреса, которые кэшировать нельзя. */
function skip(url) {
  const p = url.pathname;
  return p.includes('/app/')
    || p.includes('/admin/')
    || p.includes('/data/')
    || url.search !== '';
}

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;   // чужие домены не наше дело
  if (skip(url)) return;

  const dest = req.destination;

  /* Картинки: сначала кэш, сеть только если нет. Они не меняются. */
  if (dest === 'image') {
    e.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(MEDIA).then((c) => c.put(req, copy));
        }
        return res;
      }).catch(() => hit))
    );
    return;
  }

  /* Стили и скрипты: отдаём из кэша мгновенно, в фоне обновляем. */
  if (dest === 'style' || dest === 'script' || dest === 'font') {
    e.respondWith(
      caches.match(req).then((hit) => {
        const net = fetch(req).then((res) => {
          if (res.ok) {
            const copy = res.clone();
            caches.open(SHELL).then((c) => c.put(req, copy));
          }
          return res;
        }).catch(() => hit);
        return hit || net;
      })
    );
    return;
  }

  /* Страницы: сначала пробуем сеть — цены должны быть свежими.
     Нет связи — отдаём последнюю сохранённую копию. */
  if (req.mode === 'navigate' || dest === 'document') {
    e.respondWith(
      fetch(req).then((res) => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(PAGES).then((c) => c.put(req, copy));
        }
        return res;
      }).catch(() => caches.match(req).then((hit) => hit || caches.match(scopePath)))
    );
  }
});

/* Команда из страницы: сбросить кэш (нужно после правок в админке) */
self.addEventListener('message', (e) => {
  if (e.data === 'clear-cache') {
    caches.keys().then((keys) => keys.forEach((k) => caches.delete(k)));
  }
});
