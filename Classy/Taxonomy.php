<?php

/**
 * Classy_Taxonomy
 * @desc	
 */

abstract class Classy_Taxonomy {
	
	protected $_taxonomy,
			  $_post_type,
			  $_data		= null,
			  $_custom		= null,
			  $_siblings	= null;

	/**
	 * __construct
	 * @desc	
	 * @param	mixed	$options
	 * @return	\Classy_Taxonomy
	 */
	public function __construct($options = array()) {
		if($options === 'initialize') {}
		elseif(is_array($options)) {
			foreach($options as $key => $value) {
				$this->$key = $value;
			}
		}
	
		return $this;
	}
	
	/**
	 * __set
	 * @desc	Magic method for setting data.
	 *			Uses method if it exists, else sets the variable on the class itself.
	 * @param	string	$key
	 * @param	string	$value
	 * @return	\Classy_Taxonomy
	 */
	public function __set($key, $value) {
		if(method_exists($this, 'set_' . $key)) {
			return $this->{'set_' . $key}($value);
		}
		else {
			$this->{$key} = $value;
		}
		return $this;
	}
	
	/**
	 * __get
	 * @desc	Magic method for geting data.
	 *			Checks three different areas;
	 *			- Method, prefixed with get_ ($this->get_forename())
	 *			- Variable, on the class ($this->forename())
	 * 			- Variable, within the default WordPress data
	 * @param	string	$key
	 * @return	mixed
	 */
	public function __get($key) {
		if(method_exists($this, 'get_' . $key)) {
			return $this->{'get_' . $key}();
		}
		elseif(isset($this->_data->{$key})) {
			return $this->_data->{$key};
		}
		elseif(isset($this->{$key})) {
			return $this->{$key};
		}
		
		return null;
	}
	
	/**
	 * __isset
	 * @desc	Magic method to check whether data is set
	 * @param	string	$key
	 * @return	boolean
	 */
	public function __isset($key) {
		if(method_exists($this, 'get_' . $key)) {
			$value = $this->{'get_' . $key}();
		}
		elseif(property_exists($this, $key)) {
			$value = $this->{$key};
		}
		
		return !empty($value) ? true : false;
	}
	
	/**
	 * set_taxonomy
	 * @desc	Sets up the default taxonomy data, including custom data.
	 * @param	object	$taxonomy
	 * @return	\Classy_Taxonomy 
	 */
	public function set_taxonomy($taxonomy) {
		$this->_data		= $taxonomy;
		$this->custom		= is_object($taxonomy) && !empty($taxonomy->term_id) ? $taxonomy->term_id : null;
		
		return $this;
	}
	
	/**
	 * get_taxonomy
	 * @desc	Retrieve the default taxonomy data.
	 * @return	object
	 */
	public function get_taxonomy() {
		return $this->_data;
	}

	/**
	 * get_post_type
	 * @desc	
	 * @return	string 
	 */
	public function get_post_type() {
		return (string) $this->_post_type;
	}
	
	/**
	 * get_ID
	 * @origin	get_the_ID()
	 * @desc	Retrieve the post ID.
	 * @return	int
	 */
	public function get_ID() {
		return !empty($this->_data->term_id) ? $this->_data->term_id : 0;
	}
	public function get_the_ID() {
		return $this->get_ID();
	}
	
	/**
	 * the_ID
	 * @origin	the_ID()
	 * @desc	Output the post ID.
	 * @output	string
	 */
	public function the_ID() {
		echo $this->get_ID();
	}
	
	/**
	 * set_custom
	 * @desc	Retrieves and sets up all of the custom data.
	 * @param	int		$id
	 * @return	\Classy
	 */
	public function set_custom($id) {
		$this->_custom = array(); // @todo
		
		return $this;
	}
	
	/**
	 * get_custom
	 * @desc	Retrieves the custom data.
	 * @return	array 
	 */
	public function get_custom() {
		return $this->_custom;
	}
	
	
	/*********************************************************
	 * =Adjacent
	 * @desc	Find the previous / next categories.
	 *********************************************************/
	
	/**
	 * next
	 * @desc	Next taxonomy ID or false if this ID is last one. False if this ID is not in the list.
	 * @return	int|bool
	 */
	public function next() {
		return $this->adjacent(false);
	}

	/**
	 * previous
	 * @desc	Previous taxonomy ID or false if this ID is last one. False if this ID is not in the list.
	 * @return	int|bool 
	 */
	public function previous() {
		return $this->adjacent(true);
	}
	
	/**
	 * adjacent
	 * @desc	
	 * @param	boolean	$previous
	 * @return	mixed
	 */
	public function adjacent($previous = true) {
		$this->_siblings = $this->_get_siblings();

		if(is_array($this->_siblings)) {
			$_sibling_ids = array_map(function ($sibling) {
				return $sibling->term_id;
			}, $this->_siblings);

			$current_index = array_search($this->term_id, $_sibling_ids);

			if($current_index !== false) {
				$index = $previous === true ? $current_index - 1 : $current_index + 1;

				if(isset($_sibling_ids[$index])) {
					return self::find_by_id($_sibling_ids[$index], $this->_taxonomy);
				}
			}
		}

		return false;
	}
	
	/**
	 * _get_siblings
	 * @desc	Get all of the IDs that have the same parent.
	 * @return	array
	 */
	protected function _get_siblings() {
		if($this->parent && is_null($this->_siblings)) {
			$this->_siblings = get_terms($this->_taxonomy, array(
				'parent'		=> $this->parent,
				'hide_empty'	=> !defined('ENVIRONMENT') || constant('ENVIRONMENT') !== 'local' ? true : false,
			));
		}
		return $this->_siblings;
	}
	
	
	/*********************************************************
	 * =General
	 * @desc	
	 *********************************************************/
	
	/**
	 * get_thte_title
	 * @desc	Wrap the category name in spans for better styling.
	 * @param	boolean		$html
	 * @return	string
	 */
	public function get_the_title($html = true) {
		$text_parts	= explode(' ', $this->name);
		
		if (count($text_parts) === 3) {
			$text = sprintf('<span class="f">%1$s</span> <span>%2$s %3$s</span>', $text_parts[0], $text_parts[1], $text_parts[2]);
		}
		elseif(count($text_parts) === 2) {
			$text = sprintf('<span class="f">%1$s</span> <span>%2$s</span>', $text_parts[0], $text_parts[1]);
		}
		else {
			$text = $this->name;
		}
		
		return $html === false ? strip_tags($text) : $text;
	}
	
	/**
	 * get_title
	 * @desc	Alias for get_the_title()
	 * @param	boolean		$html
	 * @return	string
	 */
	public function get_title($html = true) {
		return $this->get_the_title($html);
	}
	
	/**
	 * the_title
	 * @desc	Output the title
	 * @param	string	before
	 * @param	string	after
	 * @output	string
	 */
	public function the_title($before = '', $after = '') {
		echo $before . $this->get_the_title() . $after;
	}
	
	/**
	 * get_the_slug
	 * @desc	Return the post slug / post name
	 * @return	string
	 */
	public function get_the_slug() {
		return $this->slug;
	}
	
	/**
	 * the_slug
	 * @desc	Output the post slug / post name
	 * @echo	string
	 */
	public function the_slug() {
		echo $this->get_the_slug();
	}
	
	/**
	 * has_permalink
	 * @desc	
	 * @return	boolean
	 */
	public function has_permalink() {
		return true;
	}
	
	/**
	 * get_permalink
	 * @desc	
	 * @return	string
	 */
	public function get_permalink() {
		return $this->has_permalink() ? get_term_link($this->_data, $this->taxonomy) : '';
	}
	
	/**
	 * the_permalink
	 * @desc	Output the permalink and apply the filter.
	 * @output	string
	 */
	public function the_permalink() {
		echo apply_filters('the_permalink', $this->get_permalink());
	}
	
	
	/*********************************************************
	 * =Custom
	 * @desc	
	 *********************************************************/
	
	/**
	 * has_description
	 * @desc	
	 * @uses	get_tax_meta
	 * @return	boolean
	 */
	public function has_description() {
		if(function_exists('get_tax_meta')) {
			$text = get_tax_meta($this->_data, 'text');
			
			return !empty($text) ? true : false;
		}
		return false;
	}
	
	/**
	 * get_description
	 * @desc	
	 * @uses	get_tax_meta
	 * @return	string
	 */
	public function get_description() {
		$html = '';
		
		if($this->has_description()) {
			$html = apply_filters('the_content', get_tax_meta($this->_data, 'text'));
		}
		
		return $html;
	}
	
	/**
	 * the_description
	 * @desc	
	 * @uses	get_tax_meta
	 * @output	string
	 */
	public function the_description() {
		echo $this->get_description();
	}
	
	
	/*********************************************************
	 * =Images
	 * @desc	
	 *********************************************************/
	
	/**
	 * has_thumbnail
	 * @desc	
	 * @uses	get_woocommerce_term_meta
	 * @param	object	$category
	 * @return	boolean
	 */
	public function has_thumbnail() {
		if(function_exists('get_woocommerce_term_meta')) {
			$thumbnail_id = get_woocommerce_term_meta($this->term_id, 'thumbnail_id');
			
			return !empty($thumbnail_id) ? true : false;
		}
		return false;
	}
	
	/**
	 * get_thumbnail
	 * @desc	
	 * @uses	get_woocommerce_term_meta
	 * @param	string			$size
	 * @param	string|array	$attr
	 * @return	string
	 */
	public function get_thumbnail($size = 'category', $attr = '') {
		$html = '';
		
		if ($this->has_thumbnail()) {
			$thumbnail_id = get_woocommerce_term_meta($this->term_id, 'thumbnail_id');

			$html = wp_get_attachment_image($thumbnail_id, $size, false, $attr);
		}
		
		return $html;
	}
	
	/**
	 * get_thumbnail_src
	 * @desc	
	 * @uses	get_woocommerce_term_meta
	 * @param	string			$size
	 * @return	string
	 */
	public function get_thumbnail_src($size = 'category') {
		$string = '';
		
		if ($this->has_thumbnail()) {
			$thumbnail_id = get_woocommerce_term_meta($this->term_id, 'thumbnail_id');

			$string = array_shift(wp_get_attachment_image_src($thumbnail_id, $size, false));
		}
		
		return $string;
	}
	
	/**
	 * get_gallery
	 * @desc	
	 * @return	string
	 */
	public function get_gallery() {
		return $this->has_thumbnail() ? sprintf('<div class="gallery"><div class="photo">%s</div></div>', $this->get_thumbnail()) : '';
	}
	
	
	/*********************************************************
	 * =Attributes
	 * @desc	
	 *********************************************************/
	
	/**
	 * the_attr
	 * @desc	Output the attributes.
	 * @param	string	$type
	 * @param	array	$options
	 * @output	string
	 */
	public function the_attr($type, $options = array()) {
		$output = '';
		
		switch($type) {
			case 'class':
				$output = sprintf(' class="%s"', implode(' ', $this->get_attr_classes($options)));
				break;
			
			case 'data':
				$attributes	= $this->get_attr_data($options);
				$output		= ' ' . implode(' ', array_map(function ($k, $v) { return $k . '="' . $v . '"'; }, array_keys($attributes), array_values($attributes)));
				break;
		}
		
		echo $output;
	}
	
	/**
	 * get_attr_classes
	 * @origin	get_post_class
	 * @desc	Get the post class, with any optional classes passed as an option.
	 * @param	array	$classes
	 * @return	array
	 */
	public function get_attr_classes($classes = array()) {
		$classes	= array_merge(array(
			'hentry',
			'type-' . $this->_taxonomy,
			'taxonomy',
			'taxonomy-' . $this->_taxonomy,
			'taxonomy-' . $this->term_id,
			'taxonomy-' . $this->slug,
		), $classes);
				
		if($this->has_thumbnail()) {
			$classes[] = 'has-image';
		}
		
		return $classes;
	}
	
	/**
	 * get_attr_data
	 * @desc	Prefix the key/value attributes with data-
	 * @param	array	$attributes
	 * @return	array
	 */
	public function get_attr_data($attributes = array()) {
		if(count($attributes) > 0) {
			return array_combine(array_map(function ($k) { return 'data-' . $k; }, array_keys($attributes)), $attributes);
		}
		else {
			return $attributes;
		}
	}
	
	
	/*********************************************************
	 * =Finding Methods
	 * @desc	Turn the basic data in to Classy objects.
	 *********************************************************/
	
	/**
	 * forge
	 * @desc	Create an new instance of the Classy class.
	 * @param	array	$data
	 * @return	instance 
	 */
	public static function forge($data) {
		return new static($data);
	}
	
	/**
	 * find_by_id
	 * @desc	Find a taxonomy by id.
	 * @param	int		$id
	 * @param	string	$taxonomy
	 * @return	mixed 
	 */
	public static function find_by_id($id, $taxonomy) {
		$taxonomy = get_term($id, $taxonomy);
		
		if(is_object($taxonomy)) {
			return self::forge(array(
				'taxonomy'	=> $taxonomy
			));
		}
		
		return false;
	}
	
	/**
	 * get_terms
	 * @see		get_terms()
	 * @desc	Return all the terms.
	 * @param	array	$taxonomies
	 * @param	array	$options
	 * @return	array
	 */
	public static function get_terms($taxonomies, $options = array()) {
		return get_terms($taxonomies, array_merge(array(
			'orderby'		=> 'name', 
			'order'			=> 'ASC',
			'hide_empty'	=> !defined('ENVIRONMENT') || constant('ENVIRONMENT') !== 'local' ? true : false,
			'hierarchical'	=> true,
		), $options));
	}
}