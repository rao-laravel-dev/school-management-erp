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
	</div>
</li>

@push('scripts')
<script>
	var notificationBellUrls = {
		list: @json(route('notifications.index')),
		readAll: @json(route('notifications.read_all'))
	};

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
