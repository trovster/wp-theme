<?php require_once(dirname(__FILE__) . '/Classy.php');

/**
 * Classy_Featured
 * @desc	
 */

class Classy_Featured extends Classy {
	
	protected $_post_type	= 'featured';

	/**
	 * __construct
	 * @desc	
	 * @param	array	$options
	 * @return	\Classy_Featured
	 */
	public function __construct($options = array()) {
		parent::__construct($options);

		return $this;
	}
	
	/**
	 * init_register_post_type
	 * @desc	Register the post type, for custom post types.
	 */
	public function init_register_post_type() {
		register_post_type($this->get_post_type(), array(
			'labels' => array(
				'name'					=> 'Featured',
				'singular_name'			=> 'Featured',
				'add_new'				=> 'Add New Featured Box',
				'add_new_item'			=> 'Add New Featured Box',
				'edit'					=> 'Edit Featured Box',
				'edit_item'				=> 'Edit Featured Box',
				'new_item'				=> 'New Featured Box',
				'view'					=> 'View Featured Box',
				'view_item'				=> 'View Featured Box',
				'search_items'			=> 'Search Featured Boxes',
				'not_found'				=> 'No Featured Boxes found',
				'not_found_in_trash'	=> 'No Featured Boxes found in Trash',
				'parent'				=> 'Parent Featured Box',
			),
			'description'			=> 'Featured box',
			'capability_type'		=> 'post',
			'public'				=> true,
			'exclude_from_search'	=> true,
			'show_ui'				=> true,
			'rewrite'				=> false,
			'hierarchical'			=> false,
			'register_meta_box_cb'	=> false,
			'taxonomies'			=> array(),
			'supports'				=> array(
				'title',
//				'editor',
//				'author',
				'thumbnail',
				'excerpt',
//				'trackbacks',
//				'custom-fields',
//				'comments',
//				'revisions',
				'page-attributes',
//				'post-formats',
			),
		));
	}
	
	/**
	 * init_register_taxonomies
	 * @desc	Register any taxonomies.
	 */
	public function init_register_taxonomies() {}
	
	/**
	 * init_register_images
	 * @desc	Register any image sizes.
	 *			Can also be used to setup multiple images.
	 */
	public function init_register_images() {
		add_image_size($this->get_post_type(), 500, 250, true);
	}
	
	/**
	 * get_options
	 * @desc	Options for WP_Query.
	 * @param	array	$options
	 * @return	array
	 */
	public static function get_options($options = array()) {
		return array_merge(array(
			'post_type'			=> 'featured',
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'title_li'			=> '',
			'echo'				=> 0,
			'depth'				=> 1,
			'posts_per_page'	=> 6,
			'meta_query'		=> array(),
		), $options);
	}
	
	/**
	* pre_get_posts
	* @desc	Restrict posts
	*/
	public function pre_get_posts(&$query) {
		parent::pre_get_posts($query);
		
		if(!empty($query->query_vars) && !empty($query->query_vars['post_type']) && $query->query_vars['post_type'] === $this->get_post_type() && !is_admin()) {
			$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(
				array(
					'key'		=> '_thumbnail_id',
					'compare'	=> '!=',
					'value'		=> ''
				),
			));
		}
	}
	
	
	/*********************************************************
	 * =WordPress Methods
	 * @desc	General WordPress methods.
	 *********************************************************/
	
	/**
	 * get_permalink
	 * @desc	Checks whether a permalink is set for this post type.
	 * @return	boolean
	 */
	public function has_permalink() {
		return $this->has_custom_value('link');
	}

	/**
	 * get_permalink
	 * @desc	
	 * @param	boolean	$leavename
	 * @return	string
	 */
	public function get_permalink($leavename = false) {
		return $this->has_permalink() ? $this->get_custom_value('link') : '#';
	}
	
	/**
	 * has_permalink_text
	 * @desc	Checks whether a permalink text is set for this post type.
	 * @return	boolean
	 */
	public function has_permalink_text() {
		return $this->has_custom_value('link_text');
	}

	/**
	 * get_permalink_text
	 * @desc	
	 * @return	string
	 */
	public function get_permalink_text() {
		return $this->has_permalink_text() ? $this->get_custom_value('link_text') : 'View ' . $this->get_the_title();
	}
	
	/**
	 * has_colour
	 * @desc	
	 * @return	boolean
	 */
	public function has_colour() {
		return $this->has_custom_value('colour');
	}

	/**
	 * get_colour
	 * @desc	
	 * @return	string
	 */
	public function get_colour() {
		return $this->has_colour() ? $this->get_custom_value('colour') : '#000000';
	}
	
	
	/**
	 * has_youtube_url
	 * @desc	Checks whether a YouTube URL is set for this post type.
	 * @return	boolean
	 */
	public function has_youtube_url() {
		return strpos($this->get_permalink(), 'youtube') > 0 ? true : false;
	}

	/**
	 * get_youtube_url
	 * @desc	Get the YouTube URL
	 * @return	string
	 */
	public function get_youtube_url() {
		return $this->has_youtube_url() ? $this->get_permalink() : '';
	}
	
	/**
	 * get_youtube_id
	 * @desc	Return the YouTube ID (v=)
	 * @return	string
	 */
	public function get_youtube_id() {
		$url	= $this->get_youtube_url();
		$domain	= parse_url($url, PHP_URL_HOST);
		$url	= esc_url($url);
		
		if(in_array($domain, array('www.youtube.com', 'youtube.com'))) {
			parse_str(parse_url($url, PHP_URL_QUERY));
			$id = $v;
		}
		elseif($domain === 'youtu.be') {
			$id = trim(parse_url($url, PHP_URL_PATH), '/');
		}
		else {
			$id = '';
		}
		
		return $id;
	}
	
	
	/*********************************************************
	 * =Common Methods
	 * @desc	Useful common methods.
	 *********************************************************/
	
	/**
	 * get_attr_classes
	 * @origin	get_post_class
	 * @desc	Get the post class, with any optional classes passed as an option.
	 * @param	array	$classes
	 * @return	array
	 */
	public function get_attr_classes($classes = array()) {
		$classes = parent::get_attr_classes($classes);
		
		if($this->has_permalink()) {
			$classes[] = 'has-link';
		}
		else {
			$classes[] = 'has-no-link';
		}
		
		if($this->has_youtube_url()) {
			$classes[] = 'has-youtube_url';
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
		if($this->has_youtube_url()) {
			$attributes['youtube-id']	= $this->get_youtube_id();
			$attributes['youtube-url']	= $this->get_youtube_url();
		}
		
		return parent::get_attr_data($attributes);
	}
	
	
	/*********************************************************
	 * =Admin Listing
	 * @desc	Default actions and filters called for
	 *			listing of columns on the admin area.
	 *********************************************************/
	
	/**
	 * filter_manage_column_listing
	 * @desc	Add extra columns to the admin listing screen.
	 * @param	array	$columns
	 * @return	array
	 */
	public function filter_manage_column_listing($columns) {
		$columns = array_merge(array(
			'cb'					=> $columns['cb'],
			'title'					=> $columns['title'],
			'featured_thumbnail'	=> 'Image',
			'featured_link'			=> 'Link',
			'featured_colour'		=> 'Colour',
		), $columns);
		
		return $columns;
	}
	
	/**
	 * filter_manage_column_sorting
	 * @desc	Sort any columns on the admin listing screen.
	 * @param	array	$columns
	 * @return	array
	 */
	public function filter_manage_column_sorting($columns) {
		return $columns;
	}
	
	/**
	 * action_manage_column_value
	 * @desc	Output the values for the extra columns.
	 * @param	string	$column
	 * @param	int		$post_id
	 */
	public function action_manage_column_value($column, $post_id) {
		$classy_post	= self::find_by_id($post_id);
		
		switch($column) {
			case 'featured_thumbnail':
				echo $classy_post->has_thumbnail() ? $classy_post->get_thumbnail('thumb') : '-';
				break;
			
			case 'featured_link':
				echo $classy_post->has_permalink() ? $classy_post->get_permalink() : '-';
				break;
			
			case 'featured_colour':
				echo sprintf('<div style="height: 25px; width: 100px; overflow: hidden; display: block; text-indent: -9999px; text-align: left; font-size: 0; background-color: %1$s">%1$s</div>', $classy_post->get_colour());
				break;
		}
	}
	
	
	/*********************************************************
	 * =Admin Boxes
	 * @desc	Default actions and filters called for adding
	 *			extra content / boxes in the admin area.
	 *********************************************************/

	/**
	* action_admin_init_meta_box
	* @desc		Assign the meta box.
	*/
	public function action_admin_init_meta_box()  {
		add_meta_box($this->get_post_type() . '_link', 'Link', array($this, 'meta_box_page_link'), $this->get_post_type(), 'advanced', 'high');
		add_meta_box($this->get_post_type() . '_colour', 'Colour', array($this, 'meta_box_page_colour'), $this->get_post_type(), 'advanced', 'high');
	}
	
	/**
	 * meta_box_page_link
	 * @desc	
	 * @param	object	$post
	 */
	public function meta_box_page_link($post) {
		$classy_page = self::find_by_id($post->ID);
		
		echo self::_meta_field_html($classy_page->get_custom_value('link'), 'link', 'URL', true, 'text');
		echo self::_meta_field_html($classy_page->get_custom_value('link_text'), 'link_text', 'Link Text', true, 'text');
	}
	
	/**
	 * meta_box_page_colour
	 * @desc	Colour picker
	 * @param	object	$post
	 */
	public function meta_box_page_colour($post) {
		$classy_page = self::find_by_id($post->ID);
		
		echo self::_meta_field_html($classy_page->get_colour(), 'colour', 'Colour', true, 'text');
	}
	
	
	/*********************************************************
	 * =Finding Methods
	 * @desc	Turn the basic data in to Classy objects.
	 *********************************************************/
	
	/**
	 * find_by_slug
	 * @desc	Find a post by 'slug'.
	 * @param	string	$slug
	 * @return	mixed 
	 */
	public static function find_by_slug($slug, $post_type = 'featured') {
		return parent::find_by_slug($slug, $post_type);
	}

}

/**
 * Hook in to WordPress
 */
if(class_exists('Classy_Featured')) {
	$classy_featured = new Classy_Featured('initialize');
}