const CACHE_NAME = 'ansareats-shell-v5';
const APP_SHELL = [
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
      fetch(request, { cache: 'no-store' }).catch(() => Response.error())
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
              caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned)).catch(() => {});
            }

            return response;
          })
          .catch(() => cached || Response.error());

        return cached || networkFetch;
      })
    );
  }
});

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

self.addEventListener('push', (event) => {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  let data = {};

  if (event.data) {
    try {
      data = event.data.json();
    } catch (error) {
      data = {};
    }
  }

  event.waitUntil(showPushNotification(data));
});

async function showPushNotification(data) {
  if (data.audience === 'owner') {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    const hasVisibleOrdersPage = clients.some((client) => {
      const url = new URL(client.url);

      return url.origin === self.location.origin
        && url.pathname === '/owner/orders'
        && client.visibilityState === 'visible';
    });

    if (hasVisibleOrdersPage) {
      return;
    }
  }

  const title = data.title || 'AnsarEats Update';
  const body = data.body || 'You have a new update.';
  const url = data.url || '/orders';
  const tag = data.tag || 'general-update';

  return self.registration.showNotification(title, {
    body: body,
    icon: '/images/brand/ansareats-app-icon.svg',
    badge: '/images/brand/ansareats-app-icon.svg',
    tag: tag,
    renotify: true,
    requireInteraction: false,
    data: { url: url },
    vibrate: [200, 100, 200],
  });
}

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }

      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
