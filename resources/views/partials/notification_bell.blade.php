{{-- Header notification bell (8 portal headers share this). Data: NotificationController, AJAX --}}
@can('access-notifications')
<li class="nav-item dropdown dropdown-large" id="notificationBell">
	<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
		data-bs-toggle="dropdown" data-bs-auto-close="outside">
		<span class="alert-count d-none" id="notificationBellCount">0</span>
		<i class='bx bx-bell'></i>
	</a>
	<div class="dropdown-menu dropdown-menu-end">
		<div class="msg-header">
			<p class="msg-header-title">Notifications</p>
			<a href="javascript:;" class="msg-header-clear d-none" id="notificationBellReadAll">Mark all read</a>
		</div>
		<div class="header-notifications-list notification-bell-list" id="notificationBellList">
			<p class="msg-info text-center py-3 mb-0">No notifications</p>
		</div>
		<a href="javascript:;" class="d-none" id="notificationBellPush">
			<div class="text-center msg-footer" id="notificationBellPushText">Enable push notifications</div>
		</a>
	</div>
</li>

@push('scripts')
<script>
	var notificationBellUrls = {
		list: @json(route('notifications.index')),
		readAll: @json(route('notifications.read_all')),
		pushSubscribe: @json(route('notifications.push_subscribe')),
		pushUnsubscribe: @json(route('notifications.push_unsubscribe')),
		sw: @json(asset('sw.js')),
		vapidKey: @json(config('webpush.public_key'))
	};

	// Push ke liye secure context (https / localhost / browser flag) aur VAPID key chahiye, warna link hidden rehta hai
	function notificationBellPushSupported() {
		return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window
			&& window.isSecureContext && !!notificationBellUrls.vapidKey;
	}

	function notificationBellKeyToBytes(key) {
		var padding = '='.repeat((4 - key.length % 4) % 4);
		var raw = atob((key + padding).replace(/-/g, '+').replace(/_/g, '/'));
		var bytes = new Uint8Array(raw.length);
		for (var i = 0; i < raw.length; i++) {
			bytes[i] = raw.charCodeAt(i);
		}
		return bytes;
	}

	function notificationBellPushSave(sub) {
		var payload = sub.toJSON();
		payload.content_encoding = (window.PushManager.supportedContentEncodings || ['aesgcm'])[0];
		return $.post(notificationBellUrls.pushSubscribe, payload);
	}

	function notificationBellPushLabel(subscribed) {
		$('#notificationBellPushText').text(subscribed ? 'Disable push notifications' : 'Enable push notifications');
		$('#notificationBellPush').removeClass('d-none').data('subscribed', subscribed);
	}

	// sw.js register, maujooda subscription ho to dobara save (isi browser par dusre user ke login ke baad row is user par aa jaye)
	function notificationBellPushInit() {
		if (!notificationBellPushSupported()) {
			return;
		}

		navigator.serviceWorker.register(notificationBellUrls.sw)
			.then(function () { return navigator.serviceWorker.ready; })
			.then(function (reg) { return reg.pushManager.getSubscription(); })
			.then(function (sub) {
				notificationBellPushLabel(!!sub);
				if (sub) {
					notificationBellPushSave(sub);
				}
			});
	}

	function notificationBellPushEnable() {
		Notification.requestPermission().then(function (permission) {
			if (permission !== 'granted') {
				toastr.warning('Notifications are blocked in this browser.');
				return;
			}

			navigator.serviceWorker.ready
				.then(function (reg) {
					return reg.pushManager.subscribe({
						userVisibleOnly: true,
						applicationServerKey: notificationBellKeyToBytes(notificationBellUrls.vapidKey)
					});
				})
				.then(function (sub) {
					return notificationBellPushSave(sub).done(function () {
						notificationBellPushLabel(true);
						toastr.success('Push notifications enabled.');
					});
				})
				.catch(function () { toastr.error('Could not enable push notifications.'); });
		});
	}

	function notificationBellPushDisable() {
		navigator.serviceWorker.ready
			.then(function (reg) { return reg.pushManager.getSubscription(); })
			.then(function (sub) {
				if (!sub) {
					notificationBellPushLabel(false);
					return;
				}
				return $.post(notificationBellUrls.pushUnsubscribe, { endpoint: sub.endpoint })
					.then(function () { return sub.unsubscribe(); })
					.then(function () {
						notificationBellPushLabel(false);
						toastr.success('Push notifications disabled.');
					});
			})
			.catch(function () { toastr.error('Could not disable push notifications.'); });
	}

	// Badge: 0 = hidden, 9 se zyada = "9+"
	function notificationBellSetCount(count) {
		var $badge = $('#notificationBellCount');
		$badge.text(count > 9 ? '9+' : count).toggleClass('d-none', count < 1);
		$('#notificationBellReadAll').toggleClass('d-none', count < 1);
	}

	// List rows jQuery .text() se bante hain (title/body mein HTML inject nahi hota)
	function notificationBellRender(data) {
		var $list = $('#notificationBellList').empty();

		notificationBellSetCount(data.unread);

		if (!data.items.length) {
			$list.append($('<p class="msg-info text-center py-3 mb-0"></p>').text('No notifications'));
			return;
		}

		$.each(data.items, function (i, item) {
			var $title = $('<h6 class="msg-name"></h6>').text(item.title);
			var $time = $('<span class="msg-time float-end"></span>').text(item.time);

			if (item.unread) {
				$time.prepend(
					$('<button type="button" class="notification-bell-read" title="Mark as read"><i class="bx bx-check"></i></button>')
						.attr('data-read-url', item.read_url)
				);
			}
			$title.append($time);

			var $row = $('<a class="dropdown-item notification-bell-item"></a>')
				.attr('href', item.open_url)
				.toggleClass('notification-bell-unread', item.unread)
				.append($('<div class="d-flex align-items-center"></div>')
					.append(item.image
						? $('<img class="msg-avatar" alt="">').attr('src', item.image)
						: $('<div class="notify bg-light-primary text-primary"><i class="bx bx-calendar-check"></i></div>'))
					.append($('<div class="flex-grow-1"></div>')
						.append($title)
						.append($('<p class="msg-info"></p>').text(item.body))));

			$list.append($row);
		});
	}

	function notificationBellLoad() {
		$.getJSON(notificationBellUrls.list, notificationBellRender)
			.fail(function () { toastr.error('Could not load notifications.'); });
	}

	$(function () {
		notificationBellLoad();
		notificationBellPushInit();

		$('#notificationBellPush').on('click', function (e) {
			e.preventDefault();
			if ($(this).data('subscribed')) {
				notificationBellPushDisable();
			} else {
				notificationBellPushEnable();
			}
		});

		// Dropdown khulte hi taza list
		$('#notificationBell').on('show.bs.dropdown', notificationBellLoad);

		// Ek notification read (row link na khule)
		$('#notificationBellList').on('click', '.notification-bell-read', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $btn = $(this);
			$.post($btn.attr('data-read-url'))
				.done(function (res) {
					$btn.closest('.notification-bell-item').remove();
					notificationBellSetCount(res.unread);
					// List khali ho gayi (ya 10 se zyada unread thay) to dobara load: empty-state ya agli unread row
					if (!$('#notificationBellList .notification-bell-item').length) {
						notificationBellLoad();
					}
				})
				.fail(function () { toastr.error('Could not mark as read.'); });
		});

		// Saari read
		$('#notificationBellReadAll').on('click', function (e) {
			e.preventDefault();

			$.post(notificationBellUrls.readAll)
				.done(notificationBellLoad)
				.fail(function () { toastr.error('Could not mark all as read.'); });
		});
	});
</script>
@endpush
@endcan
