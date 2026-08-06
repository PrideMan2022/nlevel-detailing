'use strict';

// Версию менять при значительных изменениях ассетов, чтобы сбросить старый кэш.
const CACHE_NAME = 'nlevel-v3';

const PRECACHE = [
  '/',
  '/index.html',
  '/manifest.json',
  '/favicon.png',
  '/icons/Icon-192.png',
  '/icons/Icon-512.png',
  '/icons/Icon-maskable-192.png',
  '/icons/Icon-maskable-512.png',
  '/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(PRECACHE))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((names) => Promise.all(
        names.filter((n) => n !== CACHE_NAME).map((n) => caches.delete(n))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Только GET-запросы к своему origin (не Firebase/Firestore/CDN).
  if (req.method !== 'GET' || url.origin !== self.location.origin) return;

  // Навигационные запросы: network-first, чтобы обновления подхватывались.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req)
        .then((res) => {
          if (res.ok) {
            caches.open(CACHE_NAME).then((c) => c.put(req, res.clone()));
          }
          return res;
        })
        .catch(() =>
          caches.match(req).then((cached) => cached || caches.match('/index.html'))
        )
    );
    return;
  }

  // Всё остальное (JS, шрифты, картинки, Flutter assets): cache-first.
  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;
      return fetch(req).then((res) => {
        if (res.ok) {
          caches.open(CACHE_NAME).then((c) => c.put(req, res.clone()));
        }
        return res;
      });
    })
  );
});
