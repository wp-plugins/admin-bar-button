<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Area Branding Plugin
 */

/**
 * Include the plugin files
 */
include_once('page-header.php');
//include_once('display-footer.php');
//include_once('display-header.php');
//include_once('display-login.php');

/**
 * Output a preview image
 *
 * @param required integer $image_id	The ID of the image to show
 */
function djg_aab_do_image_preview($image_id){

	$image = wp_get_attachment_image_src(absint($image_id));
	if(!empty($image)) :
	
		$title = __('Image Preview', 'djg-admin-area-branding');
		printf(
			'<img title="%1$s" src="%2$s" width="%3$s" height="%4$s" />',
			$title,		/** %1$s - The title attribute */
			$image[0],	/** %2$s - The src attribute */
			$image[1],	/** %3$s - The width attribute */
			$image[2]	/** %4$s - The height attribute */
		);
		
	else :
		$message1 = __('Error', 'djg-admin-area-branding');
		$message2 = __('invalid image ID', 'djg-admin-area-branding');
		printf('<span id="no-image"><strong>%1$s</strong>: %2$s</span>', $message1, $message2);
	endif;
	
}

/**
 * AJAX callback to update the image preview
 */
add_action('wp_ajax_update_image_preview', '_djg_aab_update_image_preview');
function _djg_aab_update_image_preview() {
	
	djg_aab_do_image_preview($_POST['image_id']);
	die(); // this is required to return a proper result
}
?>