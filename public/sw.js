// Web push service worker: sirf push dikhata hai aur click par tab focus/open karta hai (koi cache/offline nahi)
self.addEventListener('push', function (event) {
	var data = {};
	try {
		data = event.data ? event.data.json() : {};
	} catch (e) {
		data = {};
	}

	event.waitUntil(self.registration.showNotification(data.title || 'Notification', {
		body: data.body || '',
		icon: data.icon,
		image: data.image || undefined,
		data: { url: data.url || '/' }
	}));
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();

	var target = new URL(event.notification.data.url || '/', self.location.origin).href;

	event.waitUntil(
		clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
			// Isi site ka koi tab khula ho to usi ko target par le jao, warna naya tab
			for (var i = 0; i < list.length; i++) {
				if ('focus' in list[i]) {
					return list[i].focus().then(function (client) {
						return 'navigate' in client ? client.navigate(target) : client;
					});
				}
			}
			return clients.openWindow(target);
		})
	);
});
