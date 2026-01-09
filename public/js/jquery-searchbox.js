(function ($) {
	/*
	* 検索機能付き セレクトボックス
	*
	* Copyright (c) 2020 iseyoshitaka
	*/
	$.fn.searchBox = function (opts) {

		// 引数に値が存在する場合、デフォルト値を上書きする
		var settings = $.extend({}, $.fn.searchBox.defaults, opts);
		// alert(settings)
		// console.log(settings,$.fn.searchBox)
		var init = function (obj) {
			var self = $(obj),
				parent = self.closest('div,tr'),
				searchWord = '',                           // 絞り込み文字列
				// true if ANY ancestor <table data-repeater-list="accounts"> exists
				isAccounts = self.parents('table[data-repeater-list="accounts"]').length > 0;

			// 1) add the fake text input
			self.before('<input type="text" class="refineText formTextbox" style="border-color: var(--primary);" />');
			var refineText = parent.find('.refineText');
			if (settings.mode === MODE.NORMAL) {
				refineText.attr('readonly', 'readonly');
			}

			// 2) show selected option in the input
			var selectedOption = self.find('option:selected');
			if (selectedOption.length) {
				if (!(settings.mode === MODE.TAG && selectedOption.val() === '')) {
					refineText.val(selectedOption.text());
				}
			}

			// 3) build the UL with all <li> placeholders
			var visibleTarget = self.find('option').map(function (i, e) {
				return '<li data-selected="off" data-searchval="' + $(e).val() + '">'
					+ '<span>' + $(e).text() + '</span></li>';
			}).get();
			self.after($('<ul class="searchBoxElement"></ul>').hide());

			// 4) ONLY set widths if NOT inside the accounts‐repeater table
			if (!isAccounts) {
				var w = settings.elementWidth || self.width();
				refineText.css('width', w);
				parent.find('.searchBoxElement').css('width', w);
			}

			// 5) delayed reflow (e.g. if container resized later)
			setTimeout(function () {
				document.querySelectorAll('.refineText').forEach(function (el) {
					// skip any refineText inside the accounts‐repeater table
					if (el.closest('table[data-repeater-list="accounts"]')) return;
					var pw = el.parentNode?.offsetWidth;
					if (pw) el.style.width = pw + 'px';
				});
				document.querySelectorAll('.searchBoxElement').forEach(function (el) {
					// assume the UL is immediately after its <select>
					var sel = el.previousElementSibling;					
					if (sel && sel.closest('table[data-repeater-list="accounts"]')) return;
					var pw = el.parentNode?.offsetWidth;
					if (pw) el.style.width = pw + 'px';
				});
			}, 500);

			// 6) hide the original select
			self.hide();

			// 7) search‐and‐click logic (unchanged except we always update the UL in parent)
			var changeSearchBoxElement = function () {
				var $box = parent.find('.searchBoxElement');
				$box.empty();

				if (searchWord) {
					var matcher = new RegExp(searchWord.replace(/\\/g, '\\\\'), 'i');
					var filtered = $(visibleTarget.join(''))
						.filter(function () { return $(this).text().match(matcher); });
					$box.html(filtered);
				} else {
					$box.html(visibleTarget.slice(0, settings.optionMaxSize).join(''));
				}
				$box.show();

				// highlight current
				$box.find('li').removeClass('selected')
					.filter('[data-searchval="' + self.val() + '"]')
					.addClass('selected')
					.end()
					.click(function (e) {
						e.preventDefault();
						var li = $(this), val = li.data('searchval');
						self.val(val).change();
						$box.find('li').attr('data-selected', 'off');
						li.attr('data-selected', 'on');
					});
			};

			refineText.on('keyup', function () {
				searchWord = $(this).val();
				changeSearchBoxElement();
			});

			self.on('change', function () {
				var so = $(this).find('option:selected');
				searchWord = so.text();
				refineText.val(searchWord);
				if (settings.selectCallback) {
					settings.selectCallback({
						selectVal: so.val(),
						selectLabel: so.text()
					});
				}
			});

			refineText.on('click', function (e) {
				e.preventDefault();
				if (settings.mode === MODE.NORMAL) {
					searchWord = '';
				} else if (settings.mode === MODE.INPUT) {
					$(this).val(''); searchWord = '';
				} else if (settings.mode === MODE.TAG && self.val() === '') {
					$(this).val(''); searchWord = '';
				}
				parent.find('.searchBoxElement').hide();
				changeSearchBoxElement();
			});

			$(document).on('click', function (e) {
				if ($(e.target).hasClass('refineText')) return;
				parent.find('.searchBoxElement').hide();
				if (settings.mode !== MODE.TAG) {
					var so = self.find('option:selected');
					searchWord = so.text();
					refineText.val(searchWord);
				}
			});
		};


		$(this).each(function () {
			init(this);
		});

		return this;
	}

	var MODE = {
		NORMAL: 0, // 通常のセレクトボックス
		INPUT: 1, // 入力式セレクトボックス
		TAG: 2 // タグ追加式セレクトボックス
	};

	$.fn.searchBox.defaults = {
		selectCallback: null, // 選択後に呼ばれるコールバック
		elementWidth: null, // セレクトボックスの表示幅
		optionMaxSize: 100, // セレクトボックス内に表示する最大数
		mode: MODE.INPUT // 表示モード
	};

})(jQuery);
