'use strict';

/*global window, document, console, Modernizr, jQuery, App:true, google, _gaq, debug */

/*jslint plusplus: true, nomen: true */

/**
 * @file	models-menu.js
 * @desc	Menu model for the application
 */
App = (function (App, $) {
	
	/**
	 * =Menu
	 * @desc	
	 */
	App.model.menu = {
		enabled: true,
		$context: $('#nav'),
		$body: $('body'),
		$content: $('#wrapper, #header'),
		$window: $(window),
		$nav: null,
		home: true,
		$toggle: $('<a />', {
			id:		'toggle-nav',
			href:	'#toggle-nav',
			text:	'☰'
		}),
		options: {
			distance:		260,
			speed:			500,
			easing:			'swing',
			className:		'nav-shown'
		},
		init: function ($context, options) {
			if (typeof $context === 'object' && $context !== null) {
				this.$context = $context;
			}
			if (typeof options === 'object') {
				this.options = $.extend({}, this.options, options);
			}

			this.options.distance = this.$context.data('mobile-width') || this.options.distance;

			return this;
		},
		run: function (options) {
			var _this = this;

			if (typeof options === 'object') {
				this.options = $.extend({}, this.options, options);
			}

			if (this.enabled) {
				this.menu().resize();
			}

			return this;
		},

		/*
		 * resize
		 * @desc	Event handler for resizing.
		 *			Sets the width of the document on mobile layout.
		 *			Remove the open menu class on default layout.
		 * @return	object
		 */
		resize: function () {
			var _this	= this,
				size	= null;

			this.$window.on('resize.menu', function (event) {
				var the_size	= _this._get_size(),
					width		= _this._get_width(),
					height		= _this._get_height(),
					change		= false;

				if (the_size !== size) {
					size	= the_size;
					change	= true;
				}

				if (change === true) {
					switch (size) {
					case 'tablet':
					case 'mobile':
					case 'small':
						break;

					default:
						_this._menu_reset();
						break;
					}
					
					_this.$body.data('size', size);
				}
				
				if (size === 'mobile' || size === 'tablet') {
					if (_this.$body.is('.' + _this.options.className)) {
						_this._set_width(width);
						if (App.options.has.transitions) {
							$('li.page_item_has_children > ul').height(height);
						}
					} else if (change === true) {
						_this._set_width(false);
					}
				}
			}).trigger('resize.menu').on('orientationchange.menu', function (event) {
				_this.$window.trigger('resize.menu');
			});

			return this;
		},

		/*
		 * menu
		 * @desc	Adds the menu toggle button.
		 *			Event handler for toggling the menu.
		 *			Which adds a class to the body, handled with CSS.
		 * @return	object
		 */
		menu: function () {
			var _this	= this;

			this.$nav = this.$context.children().clone().appendTo(this.$body).attr('id', 'nav-mobile').addClass('nav').width(this.options.distance);
			this.$content.filter('#header').find('.inner').append(this.$toggle);

			if (this.home === true) {
				this.$nav.children('ul').prepend($('<li></li>', {
					'class':	'home',
					'html':		'<a href="/">Home</a>'
				}));
			}

			this.$body.on('click.menu.toggle', '#toggle-nav', function (event, swipe, type) {
				var $a			= $(event.target).closest('a'),
					toggle		= 'hide',
					width		= _this._get_width(),
					speed		= _this.options.speed;

				$a.blur();
				event.preventDefault();

				if ($.type(swipe) === 'boolean' && swipe === true && $.type(type) === 'string') {
					toggle		= type;
					speed		= 0;
				} else {
					if (_this.$body.is('.' + _this.options.className)) {
						toggle = 'hide';
					} else {
						toggle = 'show';
					}
				}

				switch (toggle.toLowerCase()) {
				case 'show':
					_this._menu_show(width, speed);
					break;

				case 'hide':
					_this._menu_hide(speed);
					break;
				}
			});

			this.enable_swipe().enable_sub_nav();

			return this;
		},

		/*
		 * enable_swipe
		 * @desc	Enable swiping the content to show/hide the navigation on mobile.
		 * @return	object
		 */
		enable_swipe: function () {
			var _this = this;

			this.$body.on('swipeleft.menu.toggle swiperight.menu.toggle', function (event) {
				switch(event.type) {
				case 'swipeleft':
					_this.$toggle.trigger('click', [true, 'hide']);
					break;
					
				case 'swiperight':
//					_this.$toggle.trigger('click', [true, 'show']);
					break;
				}
			});

			return this;
		},

		/*
		 * enable_sub_nav
		 * @desc	Enable the event handling to show sub navigation.
		 * @return	object
		 */
		enable_sub_nav: function () {
			var _this = this;

			this.$nav.on('click.menu.subnav', 'li.has-children > a, li.page_item_has_children > a', function (event) {
				var $a			= $(event.target).closest('a'),
					$li			= $a.closest('li'),
					$ul			= $li.children('ul'),
					level		= $li.is('.page_item_has_children') ? 2 : 1,
					speed		= 500,
					$siblings	= $li.siblings();
					
				if ($li.is('.' + App.options.className.selected)) {
					if (level === 2 && App.options.has.transitions) {
						_this.$nav.removeClass(App.options.className.selected);
					} else {
						$ul.slideUp(speed);
					}
					$li.removeClass(App.options.className.selected);
				} else {
					if (level === 2 && App.options.has.transitions) {
						_this.$nav.addClass(App.options.className.selected);
					} else {
						$ul.slideDown(speed);
						$siblings.children('ul').slideUp(speed);
					}
					$li.addClass(App.options.className.selected);
					$siblings.removeClass(App.options.className.selected);
				}
				event.stopPropagation();
				event.preventDefault();
			}).on('click', function (event) {
				var $$		= $(event.target),
					$a		= $$.closest('a'),
					$ul		= $$.closest('ul'),
					$open	= _this.$nav.find('.page_item_has_children.selected .children'),
					$a		= $open.closest('li.selected').children('a');
			
				if (!$ul.is('.children') && $a.length) {
					$a.trigger('click'); // close this sub-nav
					event.preventDefault();
				}
			});
			
			return this;
		},

		/*
		 * _get_size
		 * @desc	Find which defined layout we're using, based on css :after value.
		 * @return	string
		 */
		_get_size: function () {
			var size	= '',
				value	= null;

			if (typeof window.getComputedStyle === 'function') {
				size = window.getComputedStyle(document.body, ':after').getPropertyValue('content');
			}

			if (typeof size === 'string' && size.indexOf('small') !== -1) {
				value = 'small';
			} else if (typeof size === 'string' && size.indexOf('mobile') !== -1) {
				value = 'mobile';
			} else if (typeof size === 'string' && size.indexOf('tablet') !== -1) {
				value = 'tablet';
			} else if (typeof size === 'string' && size.indexOf('desktop') !== -1) {
				value = 'desktop';
			}

			return value;
		},

		/*
		 * _get_width
		 * @desc	Return the width of the container.
		 * @return	int
		 */
		_get_width: function () {
			return parseInt(this.$body.width(), 10);
		},

		/*
		 * _get_height
		 * @desc	Return the height of the container.
		 * @return	int
		 */
		_get_height: function () {
			return parseInt(this.$body.height(), 10);
		},

		/*
		 * _set_width
		 * @desc	
		 * @return	object
		 */
		_set_width: function (width) {
			if ($.type(width) === 'number') {
				this.$content.css('width', width);
			} else {
				this.$content.css('width', 'auto');
			}

			return this;
		},

		/*
		 * _menu_show
		 * @desc	
		 * @param	width	int
		 * @param	speed	int
		 * @return	object
		 */
		_menu_show: function (width, speed) {
			var _this = this;

			speed = speed !== undefined && $.type(speed) === 'number' ? speed : this.options.speed;

			this._set_width(width);

			if (speed === 0) {
				this.$content.css('left', this.options.distance);
				_this.$body.addClass(_this.options.className);
			} else {
				this.$content.animate({
					left: this.options.distance
				}, $.extend({}, this.options, {
					speed: speed
				})).promise().done(function () {
					_this.$body.addClass(_this.options.className);
				});
			}

			return this;
		},

		/*
		 * _menu_hide
		 * @desc	
		 * @param	speed	int
		 * @return	object
		 */
		_menu_hide: function (speed) {
			var _this = this;

			speed = speed !== undefined && $.type(speed) === 'number' ? speed : this.options.speed;

			if (speed === 0) {
				this.$content.css('left', 0);
				this._set_width(false);
				this.$body.removeClass(_this.options.className);
			} else {
				this.$content.animate({
					left: 0
				}, $.extend({}, this.options, {
					speed: speed
				})).promise().done(function () {
					_this._set_width(false);
					_this.$body.removeClass(_this.options.className);
				});
			}

			return this;
		},

		/*
		 * _menu_reset
		 * @desc	Reset the menu, by removing all active classes
		 *			and hiding all the sub navs.
		 * @return	object
		 */
		_menu_reset: function () {
			var $selected = this.$nav.find('.' + App.options.className.selected);

			this._menu_hide(0);

			$selected.filter('.has-children').children('ul').hide();

			if (!App.options.has.transitions) {
				$selected.filter('.page_item_has_children').children('ul').hide();
			}

			$selected.removeClass(App.options.className.selected);

			this.$nav.removeClass(App.options.className.selected);

			return this;
		}
	};

	return App;

}(typeof App === 'object' ? App : {}, jQuery));