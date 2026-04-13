const CACHE_NAME = 'ansareats-shell-v2';
const APP_SHELL = [
  '/',
  '/manifest.webmanifest',
  '/images/brand/ansareats-app-icon.svg',
  '/images/brand/ansareats-app-icon-maskable.svg',
  '/images/brand/ansareats-logo-v2.svg',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).catch(() => Promise.resolve())
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const { request } = event;
  const url = new URL(request.url);

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match('/'))
    );
    return;
  }

  if (url.origin === self.location.origin) {
    event.respondWith(
      caches.match(request).then((cached) => {
        const networkFetch = fetch(request)
          .then((response) => {
            if (response && response.status === 200) {
              const cloned = response.clone();
              caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned));
            }

            return response;
          })
          .catch(() => cached);

        return cached || networkFetch;
      })
    );
  }
});

// Handle messages from the main thread to show notifications
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SHOW_NOTIFICATION') {
    const { title, body, icon, tag, url } = event.data;

    self.registration.showNotification(title, {
      body: body,
      icon: icon || '/images/brand/ansareats-app-icon.svg',
      badge: '/images/brand/ansareats-app-icon.svg',
      tag: tag || 'order-update',
      renotify: true,
      requireInteraction: false,
      data: { url: url || '/' },
      vibrate: [200, 100, 200],
    });
  }
});

// Handle notification click — focus or open the relevant page
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      // Try to focus an existing tab
      for (const client of clients) {
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }

      // Otherwise open a new tab
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
