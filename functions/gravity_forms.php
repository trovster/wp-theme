<?php

/*   
Component: Gravity Forms
Description: Gravity Forms tweaks
Author: Surface / Trevor Morris
Author URI: http://www.madebysurface.co.uk
Version: 0.0.1
*/

/**
 * _site_gform_tabindex
 * @desc	Disable tab index.
 * @return	boolean
 */
function _site_gform_tabindex() {
	return false;
}
add_filter('gform_tabindex', '_site_gform_tabindex');

/**
 * _site_filter_gform_field_css_class
 * @desc	Add extra classes to the container of certain types of inputs.
 * @param	string		$css_class
 * @param	array		$field
 * @param	array		$form
 * @return	string
 */
function _site_filter_gform_field_css_class($css_class, $field, $form) {
	switch($field['type']) {
		case 'checkbox':
			$css_class .= ' gfield_checkbox';
			break;
		
		case 'radio':
			$css_class .= ' gfield_radio';
			break;
		
		case 'select':
			$css_class .= ' gfield_select';
			break;
		
		case 'hidden':
			$css_class .= ' gfield_hidden';
			break;
		
		case 'file':
		case 'fileupload':
			$css_class .= ' gfield_file';
			break;
	}
	
	if(!empty($field['inputName']) && $field['inputName'] === 'enrol_cost') {
		 $css_class .= ' enrol_cost';
	}
	
	return $css_class;
}
add_filter('gform_field_css_class', '_site_filter_gform_field_css_class', 10, 3);
	
/**
 * filter_gform_address_state
 * @desc	Change the Gravity Form Address label for 'State / Province / Region'.
 * @param	string	$label
 * @param	int		$form_id
 * @return	string
 */
function _site_gform_address_state($label, $form_id) {
	return 'Region';
}
add_filter('gform_address_state', '_site_gform_address_state', 10, 2);

/**
 * gform_address_zip
 * @desc	Change the Gravity Form Address label for 'Zip / Postal Code'.
 * @param	string	$label
 * @param	int		$form_id
 * @return	string
 */
function _site_gform_address_zip($label, $form_id) {
	return 'Postcode';
}
add_filter('gform_address_zip', '_site_gform_address_zip', 10, 2);

/**
 * _site_gform_field_input_placeholder
 * @desc	Add placeholder for name and email on the newsletter form.
 * @param	string	$input
 * @param	string	$field
 * @param	string	$value
 * @param	int		$lead_id
 * @param	int		$form_id
 * @return	string
 */
function _site_gform_field_input_placeholder($input, $field, $value, $lead_id, $form_id) {
	if($form_id === 1 && $field['id'] === 1) {
		$input = sprintf('<input type="%s" placeholder="Enter your full name" id="%s" name="%s" value="%s" />', $field['type'], $field['id'], 'input_' . $field['id'], $value);
	}
	if($form_id === 1 && $field['id'] === 2) {
		$input = sprintf('<input type="%s" placeholder="Enter your email address" id="%s" name="%s" value="%s" />', $field['type'], $field['id'], 'input_' . $field['id'], $value);
	}

	return $input;
}
add_filter('gform_field_input', '_site_gform_field_input_placeholder', 10, 5);