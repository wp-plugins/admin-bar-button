<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @description:	Include global plugin functions
 */
 
/** Include the plugin files */
include_once('page-header.php');
include_once('display-footer.php');
include_once('display-header.php');
include_once('display-login.php');
include_once('utilities/utilities.php');
	
/**
 * Get a set of default values (in case a value is not defined)
 *
 * @return array	The default values for the options in this plugin
 */
function aab_get_default_options(){

	$defaults['manage'] = array(
		'show_custom_header'	=> '0',
		'show_custom_footer'	=> '0',
		'show_custom_login'		=> '0',
		'debug'					=> '0',
		'role_header'			=> 'administrator',
		'role_footer'			=> 'administrator',
		'role_login'			=> 'administrator'
	);
	
	$defaults['header'] = array(
		'header_background_colour'		=> 'transparent',
		'header_height'					=> '0',
		'header_border_bottom_colour'	=> 'transparent',
		'header_border_bottom_width'	=> '0',
		'header_border_bottom_style'	=> 'none',
		'header_fixed'					=> '1',
		'header_logo'					=> '0',
		'header_logo_margin_top'		=> '0',
		'header_logo_margin_right'		=> '0',
		'header_logo_margin_bottom'		=> '0',
		'header_logo_margin_left'		=> '0'
	);
	
	$defaults['footer'] = array(
		'footer_text'			=> '<span id="footer-thankyou">Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.</span>',
		'footer_show_version'	=> '1'
	);
	
	$defaults['login'] = array(
		'login_logo'								=> '0',
		'login_nav_link_locatoin'					=> 'box',
		'login_nav_background_colour'				=> 'transparent',
		'login_nav_text_colour'						=> '#21759B',
		'login_nav_underline_link'					=> '1',
		'login_nav_text_colour_hover'				=> '#D54E21',
		'login_nav_underline_link_hover'			=> '1',
		'login_back_to_blog_link_locatoin'			=> 'box',
		'login_back_to_blog_background_colour'		=> 'transparent',
		'login_back_to_blog_text_colour'			=> '#21759B',
		'login_back_to_blog_underline_link'			=> '1',
		'login_back_to_blog_text_colour_hover'		=> '#D54E21',
		'login_back_to_blog_underline_link_hover'	=> '1'
	);
	
	return $defaults;
	
}

/**
 * Get the current values for this plugin (can be parsed with the defaults if desired)
 *
 * @param boolean|array $defaults	Whether or not to parse the default options when getting the current options (can also pass an array of $defaults)
 * @return array					The current values for the options in this plugin (parsed with the defaults if a defaults array was passed)
 */
function aab_get_current_options($defaults = false){
	
	global $wpdb;
	
	/** Check to see if the defaults should be parsed with the current options */
	if($defaults === true) :
		$defaults = get_default_branding_options();
	endif;
	
	/** Get the current options */
	$query = $wpdb->prepare('SELECT `%1$s`.* FROM `%1$s`', $wpdb->admin_branding);
	$results = $wpdb->get_results($query);
	
	/** Create an array of the current options */
	$options = array(); // Initialise to avoid errors
	if(!empty($results)) : foreach($results as $result) :
			$options[$result->option_name] = $result->option_value;
		endforeach;
	endif;
	
	/** If the defaults are declared, parse them with the $options */
	if(is_array($defaults) && !empty($defaults)): foreach($defaults as $type) :
			$options = wp_parse_args($options, $type);
		endforeach;
	endif;
	
	return $options;
	
}

/**
 * Create a custom branding option in the database
 *
 * @param required string $option	The name of the custom branding option to create
 * @param required mixed $value		The value to create the custom branding option with
 * @param boolean $run_query		Whether or not the run the create query
 * @return mixed					Either boolean false for failure, integer for the ID of the created custom branding option (if $run === true), or the generated query (if $run === false)
 */
function aab_create_option($option, $value, $run_query = true){

	$result = aab_update_option($option, $value, $run_query);
	
	return $result;
	
}

/**
 * Update a custom branding option in the database
 *
 * @param required string $option	The name of the custom branding option to update
 * @param required mixed $value		The value to update the custom branding option with
 * @param boolean $run_query		Whether or not the run the update query
 * @return mixed					Either boolean false for failure, integer for the ID of the updated custom branding option (if $run === true), or the generated query (if $run === false)
 */
function aab_update_option($option, $value, $run_query = true){

	global $wpdb;
	
	$query = $wpdb->prepare('REPLACE INTO %1$s SET `option_name` = "%3$s", `option_value` = "%2$s";', $wpdb->admin_branding, $value, $option);
	
	if($run) :
		$result = $wpdb->query($query);
	else :
		$result = stripslashes($query);
	endif;
	
	return $result;
	
}

/**
 * Gets a custom branding option value from the database
 *
 * @param required string $option	The custom branding option to return the value of
 * @return mixed					The value of the requested custom branding option (false if it does not exist)
 */
function aab_get_option($option){

	global $wpdb;
	
	$query = $wpdb->prepare('SELECT `%1$s`.`option_value` FROM `%1$s` WHERE `%1$s`.`option_name` IN ("%2$s");', $wpdb->admin_branding, $option);
	$value = $wpdb->get_var($query);
	
	return $value;
	
}
	
/**
 * Output a 'Loading' dialog, which will be displayed in the centre of the screen
 */
function aab_loading_dialog(){

	/** Output the loading dialog */
	echo '<div id="loading-dialog">';
	echo '<div id="loading-dialog-inside">';
	echo '<span id="custom-admin-preview-spinner">&nbsp;</span>';
	echo '<h2>Generating preview...</h2>';
	echo '</div>';
	echo '</div>';
	
}

/**
 * Output an error message is the user is unable to preview a custom admin area
 *
 * @param mixed $scope	The scope of the custom admin area that the user is trying to preview
 */
function aab_output_ajax_error($scope = false){
	
	$classes = array();
	if($scope) :
		$classes = explode(' ', $scope);
	endif;
	array_unshift($classes, 'error', 'custom-admin-message');
	$classes = join(' ', $classes);
	
	$error_part = ($scope) ? 'preview the Custom Admin '.$scope : 'do this';
	$url = wp_get_referer();
	$link = '<a href="'.$url.'" title="Relad this page">Reload this page</a> to try again.';
	echo '<div id="message" class="'.$classes.'">';
	echo '<p>Sorry, you don\'t have permission to '.$error_part.'. '.$link.'</p>';
	echo '</div>';
	
}

/**
 * AJAX callback for updating a preview of a custom admin logo image
 */
add_action('wp_ajax_preview_custom_branding_image_logo', '_aab_preview_logo');
function _aab_preview_logo(){
	
	/** Register the custom wp_die handler for this plugin */
	add_filter('wp_die_handler', 'aab_register_die_handler');
	
	/** Checks user permisisons */
	if(!current_user_can('manage_options')) :
		$warning = new Invalid_Action('permission');
		wp_die($warning, 'Cheatin&#8217; uh?', array('back_link' => true));
	endif;
	
	/** Checks the admin referrer is from the correct page. */
	aab_check_admin_action($_POST['security']);
	
	/** Set up the custom admin logo image for display */
	if(!empty($_POST['logo']) && $_POST['logo'] !== '0') :
		$logo = wp_get_attachment_image_src($_POST['logo'], 'full');
		$img = '<img id="logo-preview" src="'.esc_url($logo['0']).'" width="'.$logo['1'].'" height="'.$logo['2'].'" />';
	else :
		$img = '<div class="tips no-margin"><span class="tip">' . __('No logo selected', 'admin_area_branding') . '</span></div>';
	endif;
	
	echo $img;
	
	die();	// Required for a proper AJAX result
	
}
?>