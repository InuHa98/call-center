(function($) {
	const _sectionHeader = $('#section-header');

	const _sectionMainSideNav = $('.side-nav-main');
	const _btnSideNav = $('#btn_sidenav-menu');
	const _btnSideNavGroup = $('.side-nav-menu__items-group__title');
	const class_show_sidenav = 'show-side-nav';
	const class_show_sidenav_group = 'show__group';


	const _btnShowNotification = $('#btn_notification');
	const _btnShowAuth = $('#btn_auth');
	const class_show_notification = 'show__notification';
	const class_show_auth = 'show__auth';

	const _backToTop = $('#back-to-top');

	const _tabmenuHorizontal = $('.tabmenu-horizontal');
	const class_tabmenu_active = 'active';

		
	(function() {


		_btnShowNotification.on('click', function() {
			_sectionHeader.removeClass(class_show_auth);
			_sectionHeader.toggleClass(class_show_notification);
		});

		_btnShowAuth.on('click', function() {
			_sectionHeader.removeClass(class_show_notification);
			_sectionHeader.toggleClass(class_show_auth);
		});

		_btnSideNav.on('click', function(e) {
			e.stopPropagation();
			_sectionHeader.removeClass(class_show_notification);
			_sectionHeader.removeClass(class_show_auth);
			$('body').toggleClass(class_show_sidenav);
		});

		_sectionMainSideNav.after().on('click', function() {
			$('body').removeClass(class_show_sidenav);
		});

		_btnSideNavGroup.on('click', function() {
			$(this).parent().toggleClass(class_show_sidenav_group);
		});
	})();


	(function() {
		if (_backToTop.length > 0)
		{
			_backToTop.on('click', function()
			{
				$("html, body").animate({
					scrollTop: 0
				}, "slow", function(){
					_backToTop.css('display', 'none');
				});
			});
		}
	})();

	(function(){
		var container = _tabmenuHorizontal,
			scrollTo = container.find('.' + class_tabmenu_active);
		if(scrollTo.length > 0)
		{
			container.scrollLeft(
			    scrollTo.offset().left - container.offset().left + container.scrollLeft()
			);			
		}
	})();

})(jQuery);


function modeView(cookie_name) {

	const _changeViewMode = $('[role=change-view-mode]');
	const _listView = $('.list-view');
	const class_viewMode_active = 'active';
	const class_mode_table = 'mode--table';
	const mode_table = 'table';
	
	_changeViewMode.on("click", function() {
		let viewMode = $(this).data('mode');
		if(viewMode)
		{
			
			_changeViewMode.removeClass(class_viewMode_active);
			$(this).addClass(class_viewMode_active);
			if(viewMode === mode_table)
			{
				_listView.removeClass(class_mode_table).addClass(class_mode_table);
			}
			else
			{
				_listView.removeClass(class_mode_table);
			}
			var time = new Date();
			time.setFullYear(time.getFullYear() + 1);
			document.cookie = cookie_name +'='+viewMode+'; expires=' + time.toGMTString() + '; path=/';
		}
	});
};


function tooltip(options = {}) {

	var target = options.target || '.tooltip-target',
		exclude_element = options.exclude_element || [],
		data_target = options.data_target || '.tooltip-data', 
		class_tooltip = 'tooltip__body';

	var body_tooltip = null;
	var data = {
		image: null,
		title: null,
		desc: null
	};
	var isHover = false,
		cursorX = 0,
		cursorY = 0;

	var updatePosition = function() {
		var tooltip_width = body_tooltip.outerWidth(true),
			tooltip_height = body_tooltip.outerHeight(true);

		if(cursorX + tooltip_width >= $(window).width())
		{
			cursorX = cursorX - (cursorX + tooltip_width - $(window).width());
		}


		if(cursorY + tooltip_height >= $(window).height())
		{
			cursorY = cursorY - tooltip_height;
		}


		if(tooltip_width >= $(window).width())
		{
			body_tooltip.css({
				width: 'auto'
			});
		}

		if(cursorX < 0)
		{
			cursorX = 0;
		}

		if(cursorY < 0)
		{
			cursorY = 0;
		}
	};

	var showTooltip = function() {

		body_tooltip = $('\
			<div class="'+class_tooltip+'">\
				<div class="tooltip-image">\
					<img src="'+data.image+'" />\
				</div>\
				<div class="tooltip-info">\
					<div class="tooltip-name">'+data.title+'</div>\
					<div class="tooltip-text">'+data.desc+'</div>\
				</div>\
			</div>\
		');

		body_tooltip.css({
			top: cursorY + 'px',
			left: cursorX + 'px'
		});

		$('body').append(body_tooltip);

		body_tooltip.animate({
			opacity: 1
		}, {
			queue: false
		});

		isHover = true;
	};

	var hideTooltip = function() {
		$('body').find('.'+class_tooltip).stop().remove();
		body_tooltip = null;
	};


	$(document)
	.on("mouseenter", target, function(e) {
		cursorX = e.clientX;
		cursorY = e.clientY;
		
		var data_element = $(this).parents(data_target);
		data.image = data_element.find("[data-tooltip=image]").attr("src"),
		data.title = data_element.find("[data-tooltip=title]").text(),
		data.desc = data_element.find("[data-tooltip=desc]").text();

		showTooltip();
	})
	.on("mouseleave", target, function() {
		if(isHover) {
			$(this).stop();
			hideTooltip();			
		}
		isHover = false;
		cursorX = null;
		cursorY = null;
	})
	.on("mousemove", target, function(e) {

		if(!isHover) {
			return;
		}

		if(exclude_element.includes(e.target.classList[0])) {
			return body_tooltip.hide();
		}

		body_tooltip.show();
		

		cursorX = e.clientX;
		cursorY = e.clientY;

		updatePosition();
		body_tooltip.css({
			top: cursorY,
			left: cursorX
		});
	});

}

function comfirm_dialog(title, text)
{
	return new Promise(function(resolve, reject) {
		$.dialogShow({
			title: title,
			content: '<div class="dialog-message">'+text+'</div>',
			button: {
				confirm: 'Continue',
				cancel: 'Cancel'
			},
			bgHide: false,
			onConfirm: function(){
				resolve(true);
			},
			onCancel: function(){
				resolve(false);
			}
		});
	});
}

function comfirm_dialog(title, text)
{
	return new Promise(function(resolve, reject) {
		$.dialogShow({
			title: title,
			content: '<div class="dialog-message">'+text+'</div>',
			button: {
				confirm: 'Continue',
				cancel: 'Cancel'
			},
			isCenter: true,
			bgHide: false,
			onConfirm: function(){
				resolve(true);
			},
			onCancel: function(){
				resolve(false);
			}
		});
	});
}

function role_event(event, name, callback)
{
	$(document).on(event, '[role="'+name+'"]', function(event) {
		callback.bind(this)(event);
	});
}

function role_click(name, callback)
{
	$(document).on('click', '[role="'+name+'"]', function(event) {
		callback($(this), event);
	});
}

