'use strict';

/*global window, document, console, jQuery */

jQuery.noConflict();

/**
 * @file	app.js
 * @desc	Create the main application which sets up the fire events
 */
var App = (function (App, $, undefined) {

	App	= {
//		is_modern:	(querySelector in document && localStorage in window && addEventListener in window),
		util:	{
			route:		{},
			debug:		{
				log: function () {
					window.log.history = window.log.history || [];
					window.log.history.push(arguments);
					if (typeof console !== 'undefined') {
						console.log(Array.prototype.slice.call(arguments));
					}
				}
			}
		},
		options:	{
			autoload: {}
		},
		fragments:	{},
		routes:		{},
		model:		{
			loaded: {},
			_init: function (model, $context, options, run) {
				var instance;
				if (typeof this[model] === 'object' && typeof this[model].init === 'function') {
					instance = this[model].init($context, options);

					if (run === true && typeof instance.run === 'function') {
						instance.run('autoload');
					}
				}
				return instance;
			},
			_autoload: function (autoload) {
				var i = 0;
				if (typeof autoload === 'object') {
					for (i in autoload) {
						if (autoload.hasOwnProperty(i)) {
							if (typeof this[autoload[i].model] === 'object') {
								this.loaded[autoload[i].model] = App.model._init(autoload[i].model, autoload[i].$context, autoload[i].options, autoload[i].run);
							} else {
								this.loaded[autoload[i]] = App.model._init(autoload[i]);
							}
						}
					}
				}
			},
			getLoaded: function () {
				return this.loaded;
			}
		}
	};

	App.util.array = {};

	App.util.object = {};

	App.util.string = {
		trim: function (string) {
			return string.replace(/^\s+|\s+$/g, "");
		},
		toCamelCase: function (string) {
			var s = App.util.string.trim(string);
			return (/\S[A-Z]/.test(s)) ?
				s.replace(/(.)([A-Z])/g, function (t, a, b) {
					return a + ' ' + b.toLowerCase();
				}) :
				s.replace(/([\ \-])([a-z0-9])/g, function (t, a, b) {
					return b.toUpperCase();
				});
		},
		hash: function (string) {
			return string.substring(string.indexOf('#') + 1);
		},
		getCssStyle: function (ruleSelector, cssprop) {
			for (var c = 0, lenC = document.styleSheets.length; c < lenC; c++) {
				var rules = document.styleSheets[c].cssRules;
				for (var r = 0, lenR = rules.length; r < lenR; r++) {
					var rule = rules[r];
					if (rule.selectorText == ruleSelector && rule.style) {
						return rule.style[cssprop]; // rule.cssText;
					}
				}
			}
			return null;
		}
	};

	App.util.validate = {
		regex: function (string, regex) {
			return regex.test(string);
		},
		email: function (string) {
			return App.util.validate.regex(string, "/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/");
		}
	};

	App.util.route.fire = function (routes, func, funcname, args) {
		funcname = (funcname === undefined) ? 'initialize' : App.util.string.toCamelCase(funcname);

		if (func !== '' && routes[func] && typeof routes[func][funcname] === 'function') {
			routes[func][funcname](args);
		}
	};

	App.util.route.start = function (routes) {
		App.util.route.fire(routes, 'common');
		App.util.route.array(routes, document.body.className.split(/\s+/));
		App.util.route.fire(routes, 'common', 'finalize');
	};
	
	App.util.route.array = function (routes, array) {
		var unique = [];
		$.each(array, function (i, route) {
			var routeParts = route.match(/([a-z0-9]+)-(.*)/i);
			if (routeParts !== null && routeParts.length === 3) {
				if ($.inArray(routeParts[1] + routeParts[2], unique) === -1) {
					App.util.route.fire(routes, routeParts[1], routeParts[2]);
					unique.push(routeParts[1] + routeParts[2]);
				}
			} else if ($.inArray(route, unique) === -1) {
				App.util.route.fire(routes, route);
				unique.push(route);
			}
		});
	};

	return App;

}(typeof App === 'object' ? App : {}, jQuery)),

_gaq = _gaq || [];

// set log to shorthand
window.log = window.debug = App.util.debug.log;