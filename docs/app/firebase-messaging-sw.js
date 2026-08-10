// Firebase Messaging Service Worker for Web Push (Flutter + Firebase Hosting)
// This file MUST be located at: /web/firebase-messaging-sw.js (so it ends up in build/web root).

importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyDHM1CgG0ujET9v9kiXEFL0m7OkE-63-jc",
  authDomain: "nlevel-detailing.firebaseapp.com",
  projectId: "nlevel-detailing",
  storageBucket: "nlevel-detailing.firebasestorage.app",
  messagingSenderId: "1015675987642",
  appId: "1:1015675987642:web:cd4d3ccf0c1cd5996b4483",
});

const messaging = firebase.messaging();

// Background messages: show a notification.
// (If your server sends a "notification" payload, some browsers may show it automatically,
// but we keep this handler to be consistent and to support data-only payloads.)
messaging.onBackgroundMessage((payload) => {
  // Avoid duplicate notifications on iOS/Safari PWA: when the message contains
  // a `notification` payload, the browser may display it automatically.
  // In that case, we must NOT call showNotification() again.
  if (payload && payload.notification) {
    return;
  }

  const data = payload.data || {};
  const title = (data.title || 'NLeveL Detailing');
  const options = {
    body: (data.body || ''),
    icon: '/icons/Icon-192.png',
    badge: '/icons/Icon-192.png',
    data: payload.data || {},
  };
  self.registration.showNotification(title, options);
});

// Optional: handle notification click (open the app).
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  event.waitUntil((async () => {
    // FCM may wrap data under `FCM_MSG.data`.
    const raw = (event.notification && event.notification.data) ? event.notification.data : {};
    const data = (raw && raw.FCM_MSG && raw.FCM_MSG.data) ? raw.FCM_MSG.data : raw;

    const target = (data && (data.click_action || data.link)) ? (data.click_action || data.link) : '/';

    const allClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });

    // Focus an existing tab if possible.
    for (const client of allClients) {
      if ('focus' in client) {
        await client.focus();
        break;
      }
    }

    // Always try to open the deep link (will reuse the focused tab if supported).
    await clients.openWindow(target);
  })());
});
