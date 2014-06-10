<?php // add_filter('show_admin_bar', '__return_false');

/*   
Component: Site
Description: Site specific functions.
Author: Surface / Trevor Morris
Author URI: http://www.madebysurface.co.uk
Version: 0.0.1
*/

/**
 * =include
 * @desc	Include all the function components.
 */
$functions = glob(dirname(__FILE__) . '/functions/*.php');
foreach($functions as $function) {
	if(!in_array(basename($function), array('function.php'))) {
		require_once $function;
	}
}

/**
 * =include
 * @desc	Include Classy.
 */
// include all the custom post types
$cpts = glob(dirname(__FILE__) . '/Classy/*.php');
foreach($cpts as $cpt) {
	if(!in_array(basename($cpt), array('_cpt.php'))) {
		require_once $cpt;
	}
}

/**
 * Theme Support Options
 */
add_theme_support('post-thumbnails');
add_theme_support('html5');
add_theme_support('woocommerce');
add_theme_support('event-organiser');

/**
 * _site_action_wp_enqueue_scripts
 * @desc	Queue stylesheets and JavaScript used on the project.
 *			Dequeue commonly added scripts.
 */
function _site_action_wp_enqueue_scripts() {
	if(!is_admin()) {
		// de-register unwanted scripts
		wp_deregister_script('jquery');

		// register javascript
		wp_register_script('jquery', get_stylesheet_directory_uri() . '/js/jquery/1.11.1.js', false, '1.11.1', false);
		
		// app
		$app = array('app', 'app.options', 'app.models', 'app.models.menu', 'app.run');
		wp_register_script('app', get_stylesheet_directory_uri() . '/js/app/app.js', array('jquery'), '1.0', true);
		wp_register_script('app.options', get_stylesheet_directory_uri() . '/js/app/options.js', array('jquery', 'app'), '1.0', true);
		wp_register_script('app.models', get_stylesheet_directory_uri() . '/js/app/models.js', array('jquery', 'app'), '1.0', true);
		wp_register_script('app.models.menu', get_stylesheet_directory_uri() . '/js/app/models.menu.js', array('jquery', 'app'), '1.0', true);
		wp_register_script('app.run', get_stylesheet_directory_uri() . '/js/app/run.js', array('jquery', 'app'), '1.0', true);
		
		// plugins
		$plugin = array('plugin.isotope');
//		wp_register_script('plugin.isotope', get_stylesheet_directory_uri() . '/js/jquery/plugin/isotope-1.5.25.js', array('jquery'), '1.5.25', true);
		
		// enqueue javascript
		wp_enqueue_script('jquery');
		if(_site_is_section('homepage')) {}

		wp_enqueue_script('app');
		wp_enqueue_script('app.options');
		wp_enqueue_script('app.models');
		wp_enqueue_script('app.models.menu');
		wp_enqueue_script('app.run');
		
		// CSS
		$css = 'thedesignfrontier';
		wp_register_style($css, get_template_directory_uri() . '/css/' . $css . '.css', array(), filemtime(get_template_directory() . '/css/' . $css . '.css'));
		wp_enqueue_style($css);
		
		// production loading the minified/concat JavaScript and CSS
		$production_js	= '/js/_production.min.js';
		$production_css	= '/css/' . $css . '.min.css';
		if(!defined('ENVIRONMENT') || constant('ENVIRONMENT') === 'live') {
			if(file_exists(get_template_directory() . $production_js)) {
				wp_register_script('_production', get_stylesheet_directory_uri() . $production_js, array(), filemtime(get_template_directory() . $production_js), true);
				wp_enqueue_script('_production');

				foreach(array_merge($app, $plugin) as $script) {
					wp_dequeue_script($script);
				}
			}
			if(file_exists(get_template_directory() . $production_css)) {
				wp_deregister_style($css);
				wp_register_style($css, get_template_directory_uri() . $production_css, array(), filemtime(get_template_directory() . $production_css));
				wp_enqueue_style($css);
			}
		}
	}
}
add_action('wp_enqueue_scripts', '_site_action_wp_enqueue_scripts');

/**
 * _site_action_remove_nextgen_css_js
 * @desc	Remove all the CSS and JS added by NextGen Gallery
 */
function _site_action_remove_nextgen_css_js() {
	if(!is_admin()) {
		// remove NextGen Gallery CSS and JS
		wp_deregister_style('nggallery');
		wp_deregister_style('nextgen_widgets_style');
		wp_deregister_style('nextgen_basic_thumbnails_style');
		wp_deregister_style('nextgen_pagination_style');

		wp_deregister_script('ngg_common');
		wp_deregister_script('piclens');
		wp_deregister_script('nextgen-basic-thumbnails-ajax-pagination');
		wp_deregister_script('photocrati-nextgen_basic_thumbnails');
	}
}
add_action('wp_footer', '_site_action_remove_nextgen_css_js');

/**
 * _site_get_navigation
 * @desc	The primary navigation.
 * @param	string		$type
 * @return	array
 */
function _site_get_navigation($type = 'main') {
	$navigation = array(
		'home' => array(
			'text'			=> 'Home',
			'href'			=> '/',
			'class'			=> array('home'),
			'page_id'		=> 2,
		),
		'about' => array(
			'text'			=> 'About',
			'href'			=> '/about/',
			'class'			=> array('about'),
			'page_id'		=> 4,
		),
	);
	
	if ($type === 'main') {}
	if ($type === 'sitemap') {}
	
	return $navigation;
}


/**
 * _site_is_section
 * @desc	Check what page is currently active.
 * @global	object	$post
 * @param	string	$page
 * @return	boolean 
 */
function _site_is_section($page) {
	global $post;
	
	switch(strtolower($page)) {
		case 2:
		case 'homepage':
		case 'home':
			return is_front_page();
			break;
		
		case 4:
		case 'about':
			return (is_object($post) && $post->ID === 4) || (!empty($post->ancestors) && is_array($post->ancestors) && in_array(4, $post->ancestors)) || is_page('about');
			break;
	}
	
	return false;
}

/**
 * _site_filter_body_class
 * @desc	Add extra information to the body class.
 * @hook	add_filter('body_class');
 * @param	array	$classes
 * @return	array
 */
function _site_filter_body_class($classes) {

	if(!is_array($classes)) {
		$classes = (array) $classes;
	}
	
	if(is_404()) {
		 $classes[] = 'page';
	}
	if(_site_is_section('homepage')) {
		$classes[] = 'homepage';
	}
	
	return $classes;
}
add_filter('body_class', '_site_filter_body_class');