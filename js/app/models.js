'use strict';

/*global window, document, console, Modernizr, jQuery, App:true, google, _gaq, debug */

/*jslint plusplus: true, nomen: true */

/**
 * @file	models.js
 * @desc	Models / components for the application
 */
App = (function (App, $) {

	/**
	 * =External Links
	 * @desc	Open links in a new blank window
	 */
	App.model.external = {
		enabled: true,
		$context: $('body'),
		options: {
			target: '_blank'
		},
		init: function () {
			return this;
		},
		run: function () {
			var _this = this;

			if (this.enabled) {
				this.$context.on('click.external', 'a[rel~="external"]', function (event) {
					event.preventDefault();
					event.stopPropagation();
					window.open(this.href, _this.options.target);
				});
			}
		}
	};

	return App;

}(typeof App === 'object' ? App : {}, jQuery));