<?php
/**
 * Plugin Name: Admin Bar Button
 * Description: Hide the front end admin bar and replace it with an 'Show admin bar' button. When you hover over the button, the bar appears (for 5 seconds, unless you hover over it, then it'll stay).
 * Author: David Gard
 * Version: 1.0
 */

/**
 * Avoid direct calls to this file where WP core files are not present
 */
if(!function_exists('add_action')) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;

/**
 * Enqueue any necessary admin scripts/styeles
 */
add_action('wp_enqueue_scripts', '_enqueue_admin_bar_button_scripts');
function _enqueue_admin_bar_button_scripts(){
	
	global $wp_styles;
	
	/** Enqueue the JS required scripts */
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-effects-slide');
	wp_enqueue_script('dd-admin-bar', plugins_url('js/adminBar.js?scope=admin-bar-button', __FILE__ ), array('jquery-ui-widget', 'jquery-effects-slide'));
	
	/** Enqueue the required CSS */
	wp_enqueue_style('dd-admin-bar', plugins_url('css/adminBar.css?scope=admin-bar-button', __FILE__ ));
	
	/** Ensure the 'Show admin bar' button displays in IE8 and below */
	$wp_styles->add_data('dd-admin-bar-ie8', 'conditional', 'lt IE 9');
	
}

/**
 * Make sure that the admin bar does not add any margin to the top of the <body>
 */
add_theme_support('admin-bar', array('callback' => 'admin_bar_display'));
function admin_bar_display(){
?>
	<style>
	body{
		margin-top: 0;
	}
	#wpadminbar{
		display: none;
	}
	</style>
<?php
}