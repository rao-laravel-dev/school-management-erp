$(function() {
	"use strict";
	// PerfectScrollbar error fix
    try {
        new PerfectScrollbar(".header-message-list");
        new PerfectScrollbar(".header-notifications-list");
    } catch (e) {
        console.log("Scrollbar elements not found, skipping...");
    }

	    $(".mobile-search-icon").on("click", function() {
			$(".search-bar").addClass("full-search-bar")
		}),

		$(".search-close").on("click", function() {
			$(".search-bar").removeClass("full-search-bar")
		}),

		$(".mobile-toggle-menu").on("click", function() {
			$(".wrapper").addClass("toggled")
		}),
		



		// Saved dark theme (head script ne html par lagayi) ke saath icon sync
		$("html").hasClass("dark-theme") && $(".dark-mode-icon i").attr("class", "bx bx-sun"),

		$(".dark-mode").on("click", function() {

			if($(".dark-mode-icon i").attr("class") == 'bx bx-sun') {
				$(".dark-mode-icon i").attr("class", "bx bx-moon");
				$("html").attr("class", "light-theme");
				try { localStorage.setItem('smartschool-theme', 'light-theme'); } catch (e) {}
			} else {
				$(".dark-mode-icon i").attr("class", "bx bx-sun");
				$("html").attr("class", "dark-theme");
				try { localStorage.setItem('smartschool-theme', 'dark-theme'); } catch (e) {}
			}

		}), 

		
		$(".toggle-icon").click(function() {
			$(".wrapper").hasClass("toggled") ? ($(".wrapper").removeClass("toggled"), $(".sidebar-wrapper").unbind("hover")) : ($(".wrapper").addClass("toggled"), $(".sidebar-wrapper").hover(function() {
				$(".wrapper").addClass("sidebar-hovered")
			}, function() {
				$(".wrapper").removeClass("sidebar-hovered")
			}))
		}),
		$(document).ready(function() {
			$(window).on("scroll", function() {
				$(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut()
			}), $(".back-to-top").on("click", function() {
				return $("html, body").animate({
					scrollTop: 0
				}, 600), !1
			})
		}),
		
		$(function() {
			for (var e = window.location, o = $(".metismenu li a").filter(function() {
					return this.href == e
				}).addClass("").parent().addClass("mm-active"); o.is("li");) o = o.parent("").addClass("mm-show").parent("").addClass("mm-active")
		}),
		
		
		$(function() {
			$("#menu").metisMenu()
		}), 
		
		$(".chat-toggle-btn").on("click", function() {
			$(".chat-wrapper").toggleClass("chat-toggled")
		}), $(".chat-toggle-btn-mobile").on("click", function() {
			$(".chat-wrapper").removeClass("chat-toggled")
		}),


		$(".email-toggle-btn").on("click", function() {
			$(".email-wrapper").toggleClass("email-toggled")
		}), $(".email-toggle-btn-mobile").on("click", function() {
			$(".email-wrapper").removeClass("email-toggled")
		}), $(".compose-mail-btn").on("click", function() {
			$(".compose-mail-popup").show()
		}), $(".compose-mail-close").on("click", function() {
			$(".compose-mail-popup").hide()
		}), 
		
		
		$(".switcher-btn").on("click", function() {
			$(".switcher-wrapper").toggleClass("switcher-toggled")
		}), $(".close-switcher").on("click", function() {
			$(".switcher-wrapper").removeClass("switcher-toggled")
		}), $("#lightmode").on("click", function() {
			$("html").attr("class", "light-theme")
		}), $("#darkmode").on("click", function() {
			$("html").attr("class", "dark-theme")
		}), $("#semidark").on("click", function() {
			$("html").attr("class", "semi-dark")
		}), $("#minimaltheme").on("click", function() {
			$("html").attr("class", "minimal-theme")
		}), $("#headercolor1").on("click", function() {
			$("html").addClass("color-header headercolor1"), $("html").removeClass("headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor2").on("click", function() {
			$("html").addClass("color-header headercolor2"), $("html").removeClass("headercolor1 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor3").on("click", function() {
			$("html").addClass("color-header headercolor3"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor4").on("click", function() {
			$("html").addClass("color-header headercolor4"), $("html").removeClass("headercolor1 headercolor2 headercolor3 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor5").on("click", function() {
			$("html").addClass("color-header headercolor5"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor3 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor6").on("click", function() {
			$("html").addClass("color-header headercolor6"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor3 headercolor7 headercolor8")
		}), $("#headercolor7").on("click", function() {
			$("html").addClass("color-header headercolor7"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor3 headercolor8")
		}), $("#headercolor8").on("click", function() {
			$("html").addClass("color-header headercolor8"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor3")
		})
		
	// sidebar colors 
	$('#sidebarcolor1').click(theme1);
	$('#sidebarcolor2').click(theme2);
	$('#sidebarcolor3').click(theme3);
	$('#sidebarcolor4').click(theme4);
	$('#sidebarcolor5').click(theme5);
	$('#sidebarcolor6').click(theme6);
	$('#sidebarcolor7').click(theme7);
	$('#sidebarcolor8').click(theme8);

	function theme1() {
		$('html').attr('class', 'color-sidebar sidebarcolor1');
	}

	function theme2() {
		$('html').attr('class', 'color-sidebar sidebarcolor2');
	}

	function theme3() {
		$('html').attr('class', 'color-sidebar sidebarcolor3');
	}

	function theme4() {
		$('html').attr('class', 'color-sidebar sidebarcolor4');
	}

	function theme5() {
		$('html').attr('class', 'color-sidebar sidebarcolor5');
	}

	function theme6() {
		$('html').attr('class', 'color-sidebar sidebarcolor6');
	}

	function theme7() {
		$('html').attr('class', 'color-sidebar sidebarcolor7');
	}

	function theme8() {
		$('html').attr('class', 'color-sidebar sidebarcolor8');
	}
	
	
});

// Header search: filters the current user's own sidebar links (already permission-filtered)
(function ($) {
	$(function () {
		"use strict";
		var $modal = $('#SearchModal');
		if (!$modal.length) return;

		var $input = $modal.find('input[type=search]');
		var $list = $modal.find('.search-list');
		var items = null;
		var results = [];
		var peopleResults = []; // students/staff hits from /search/people (items carry a `section` field)
		var peopleTimer = null;
		var peopleXhr = null;

		// compact look: small input and smaller result rows (scoped to #SearchModal)
		$input.removeClass('form-control-lg').addClass('form-control-sm');
		$modal.find('.search-show').removeClass('fs-4').addClass('fs-5');
		$('<style></style>').text('#SearchModal .search-list .list-group-item { padding: .35rem .75rem; font-size: .875rem; } #SearchModal .search-list small { font-size: .75rem; } #SearchModal input.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 .2rem rgba(220,53,69,.25); background-image: none; }').appendTo('head');
		// placeholder tells the user what the box searches (people only when the layout exposes data-people-url)
		$input.attr('placeholder', $modal.data('people-url') ? 'Search pages, or people by name, ID or roll no...' : 'Search pages...');
		var active = -1;

		function buildIndex() {
			var seen = {};
			items = [];
			$('#menu a[href]').each(function () {
				var $a = $(this);
				var href = ($a.attr('href') || '').trim();
				var low = href.toLowerCase();
				if (!href || href === '#' || low.indexOf('javascript:') === 0 || low.indexOf('logout') !== -1) return;
				if ($a.closest('form').length || seen[href]) return;
				var label = $.trim($a.find('.menu-title').text() || $a.text());
				if (!label) return;
				var group = $.trim($a.closest('ul').siblings('a.has-arrow').find('.menu-title').text());
				seen[href] = true;
				items.push({
					href: href,
					label: label,
					group: group,
					icon: $a.find('i').first().attr('class') || 'bx bx-link',
					key: (label + ' ' + group).toLowerCase()
				});
			});
		}

		function message(text) {
			$list.empty().append($('<div class="text-muted"></div>').text(text));
		}

		function setActive(i) {
			var $els = $list.find('.list-group-item');
			if (!$els.length) return;
			active = (i + $els.length) % $els.length;
			// group text inherits the row colour (white on .active); only its opacity changes
			$els.removeClass('active').find('small').css('opacity', '.65');
			var $cur = $els.eq(active).addClass('active');
			$cur.find('small').css('opacity', '.9');
			$cur[0].scrollIntoView({ block: 'nearest' });
		}

		// danger state: query typed but nothing found anywhere (pages and, later, people)
		function updateDanger() {
			var q = $.trim($input.val());
			$input.toggleClass('is-invalid', !!q && !results.length && !peopleResults.length);
		}

		function render() {
			render_();
			updateDanger();
		}

		function render_() {
			var q = $.trim($input.val()).toLowerCase();
			active = -1;
			if (!q) { results = []; message('Type to search pages...'); return; }
			if (!items) buildIndex();
			results = $.grep(items, function (it) { return it.key.indexOf(q) !== -1; }).slice(0, 15);
			if (!results.length && !peopleResults.length) { message('No matching pages'); return; }
			$list.empty();
			if (results.length) {
				var $group = $('<div class="list-group"></div>');
				$.each(results, function (_, it) {
					var $row = $('<a class="list-group-item list-group-item-action d-flex align-items-center gap-2"></a>').attr('href', it.href);
					$row.append($('<i></i>').addClass(it.icon));
					$row.append($('<span></span>').text(it.label));
					if (it.group) $row.append($('<small class="ms-auto"></small>').css('opacity', '.65').text(it.group));
					$group.append($row);
				});
				$list.append($group);
			}
			renderPeople();
			setActive(0);
		}

		// Students / Staff sections below Pages (rows share the Pages row class, so keyboard nav spans all sections)
		function renderPeople() {
			$.each([['students', 'Students'], ['staff', 'Staff']], function (_, sec) {
				var rows = $.grep(peopleResults, function (p) { return p.section === sec[0]; });
				if (!rows.length) return;
				$list.append($('<div class="text-muted fw-semibold small mt-2 mb-1"></div>').text(sec[1]));
				var $group = $('<div class="list-group"></div>');
				$.each(rows, function (_, p) {
					var $row = $('<a class="list-group-item list-group-item-action d-flex align-items-center gap-2"></a>').attr('href', p.url);
					$row.append($('<img class="rounded-circle" width="28" height="28" alt="">').attr('src', p.photo).css('object-fit', 'cover'));
					$row.append($('<span></span>').text(p.name + ' (' + p.code + ')'));
					if (p.meta) $row.append($('<small class="ms-auto"></small>').css('opacity', '.65').text(p.meta));
					$group.append($row);
				});
				$list.append($group);
			});
		}

		// debounced people lookup (only when the layout exposes data-people-url and query has 2+ chars)
		function schedulePeople() {
			clearTimeout(peopleTimer);
			if (peopleXhr) { peopleXhr.abort(); peopleXhr = null; }
			var q = $.trim($input.val());
			var url = $modal.data('people-url');
			if (!url || q.length < 2) {
				if (peopleResults.length) { peopleResults = []; render(); }
				return;
			}
			peopleTimer = setTimeout(function () {
				peopleXhr = $.getJSON(url, { q: q }).done(function (res) {
					if (q !== $.trim($input.val())) return; // stale response
					peopleResults = [];
					$.each(res.students || [], function (_, p) { p.section = 'students'; peopleResults.push(p); });
					$.each(res.staff || [], function (_, p) { p.section = 'staff'; peopleResults.push(p); });
					render();
				}).fail(function (xhr, status) {
					if (status === 'abort') return;
					peopleResults = [];
					render();
				});
			}, 300);
		}

		$modal.on('show.bs.modal', function () {
			if (!items) buildIndex();
			render();
		});
		// replace the template's static "Search content..." text right away
		render();
		// Bootstrap's focus trap moves focus to the modal itself, so focus the input after it settles
		function focusInput() {
			setTimeout(function () {
				$input[0].focus();
				if (document.activeElement !== $input[0]) setTimeout(function () { $input[0].focus(); }, 150);
			}, 0);
		}
		$modal.on('shown.bs.modal', focusInput);
		$modal.on('hidden.bs.modal', function () {
			clearTimeout(peopleTimer);
			if (peopleXhr) { peopleXhr.abort(); peopleXhr = null; }
			peopleResults = [];
			$input.val('');
			render();
		});

		$input.on('input', function () { render(); schedulePeople(); });
		$input.on('keydown', function (e) {
			if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
			else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
			else if (e.key === 'Enter') {
				e.preventDefault();
				var href = $list.find('.list-group-item').eq(active > -1 ? active : 0).attr('href');
				if (href) window.location.href = href;
			}
		});
	});
})(jQuery);
