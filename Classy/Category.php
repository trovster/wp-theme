<?php require_once(dirname(__FILE__) . '/Taxonomy.php');

/**
 * Classy_Category
 * @desc	
 */

class Classy_Category extends Classy_Taxonomy {
	
	protected $_taxonomy	= 'category',
			  $_post_type	= 'post';

	/**
	 * __construct
	 * @desc	
	 * @param	array	$options
	 * @return	\Classy_Category
	 */
	public function __construct($options = array()) {
		parent::__construct($options);

		return $this;
	}
	
	/**
	 * get_attr_data
	 * @desc	Prefix the key/value attributes with data-
	 * @param	array	$attributes
	 * @return	array
	 */
	public function get_attr_data($attributes = array()) {
		$attributes['filter'] = $this->slug;
		return parent::get_attr_data($attributes);
	}
	
	/**
	 * find_by_id
	 * @desc	Find a taxonomy by id.
	 * @param	int		$id
	 * @param	string	$taxonomy
	 * @return	mixed 
	 */
	public static function find_by_id($id, $taxonomy = 'category') {
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
	public static function get_terms($taxonomies = 'category', $options = array()) {
		return get_terms($taxonomies, array_merge(array(
			'orderby'		=> 'name', 
			'order'			=> 'ASC',
			'hide_empty'	=> !defined('ENVIRONMENT') || constant('ENVIRONMENT') !== 'local' ? true : false,
			'hierarchical'	=> true,
		), $options));
	}
}

/**
 * Hook in to WordPress
 */
if(class_exists('Classy_Category')) {
	$classy_category = new Classy_Category('initialize');
}