<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @Description: 	Include the relevant utilities files
 */

/**
 * Avoid direct calls to this file where WP core files are not present
 */
if(!function_exists('add_action')) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;

/** Include the plugin files */
include_once('class-debug.php');
include_once('class-debug-error.php');
include_once('class-invalid-action.php');
include_once('die.php');

/**
 * Check that an admin action is from a valid source
 * Replicates most of the functionality of the WP function `check_admin_referer()`, but calls a custom error
 *
 * @param string $action	The action to check
 * @param string $query_arg	The $_POST key that holds the passed nonce value
 */
function aab_check_admin_action($action = -1, $query_arg = '_wpnonce'){
	
	if($action === -1) :
		$warning = new DJG_Invalid_Action('nonce');
		wp_die($warning, 'Alert, alert - unauthorised access!', array('response' => 403));
	endif;
	
	/** Grab the admin URL, the referer and varify the nonce */
	$adminurl = strtolower(admin_url());
	$referer = strtolower(wp_get_referer());
	$result = isset($_REQUEST[$query_arg]) ? wp_verify_nonce($_REQUEST[$query_arg], $action) : false;
	
	/** If - $result is false AND ($action is not set AND the $adminurl is not present in the $referer) is false */
	if(!$result && !($action === -1 && strpos($referer, $adminurl) === 0)) :
		
		/** Grab the relevant warning (WP has a slightly different one for logging out, so we'll just stick with it) */
		if($action === 'log-out') :
			$warning = new DJG_Invalid_Action('log-out');
			$title = 'No, please don\'t go :(';
		else :
			$warning = new DJG_Invalid_Action('nonce');
			$title = 'Alert, alert - unauthorised access!';
		endif;
		
		/** Show the warning to the user (or add it to $_POST if this is an ajax request) */
		if(defined('DOING_AJAX') && DOING_AJAX) :
		
			$errors = $warning->get_error_messages('error');
			$_POST['debug_string'] = $errors[0];
			$_POST['not_authorised'] = true;
			
		else :
			wp_die($warning, $title, array('response' => 403));
		endif;
		
	endif;
	
	do_action('check_admin_referer', $action, $result);
	
	return $result;
	
}
?>