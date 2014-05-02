<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @description:	Customised admin area footer file
 */

/**
 * Set up the custom admin header
 */
add_action('admin_enqueue_scripts', 'aab_do_footer');
function aab_do_footer(){
	
	/** Create an instance of the AAB_Footer class */
	$aab_footer = new AAB_Footer();

}

/**
 * AAB_Footer class
 */
class AAB_Footer{
	
	private $options = null;
	private $screen_id = 'admin-branding_page_djg-admin-area-branding-footer';
	private $footer_override = false;
	private $footer_enabled = false;
	
	/**
	 * Constructor
	 *
	 * @param required array $options	The options to use for displaying the custom admin header
	 */
	public function __construct($options = null){
		
		/** Set the custom admin header header options */
		if($options === null) :
			$this->options = aab_get_current_options();
		elseif(is_array($options)) :
			$this->options = $options;
		else :
			return;
		endif; 
		
		/** Check to see if we are doing AJAX, and if see exit now */
		if(defined('DOING_AJAX') && DOING_AJAX) :
			return;
		endif;
		
		/** Check to see if the user is on the 'Custom Admin Footer' page */
		$screen = get_current_screen();
		if($screen->id === $this->screen_id) :
			$this->footer_override = true;
			aab_loading_dialog();
		endif;
		
		/** Check to see if the custom admin footer is enabled */
		$this->footer_enabled = $this->options['show_custom_footer'];
		
		/** Check to see if the custom admin footer should be shown */
		if((bool)$this->footer_enabled || $this->footer_override) :
			add_action('in_admin_footer', array(&$this, 'aab_footer_display'));
		endif;
		
	}
	
	/**
	 * Replaces the default admin footer text with the custom footer text
	 *
	 * @param required string $footer_text The default admin footer text
	 * @return string $footer_text The updated, custom admin footer text
	 */
	public function aab_footer_display(){
		
		add_filter('admin_footer_text', array(&$this, '_custom_footer_text'), $footer_text);
		add_filter('update_footer', array(&$this, '_custom_footer_version'));
		
	}
	
	/**
	 * Callback for displaying custom text in the admin footer area
	 *
	 * @param required srting $footer_text	The original footer text
	 * @return string						The footer text text to display
	 */
	public function _custom_footer_text($footer_text){
		
		$footer_text = aab_get_option('footer_text');
		
		return $footer_text;
		
	}
	
	/**
	 * Callback for displaing the Wordpress version in the admin footer area
	 *
	 * @return string	The footer version text to display
	 */
	public function _custom_footer_version(){
		
		$show = aab_get_option('footer_show_version');
		$footer_version = $this->get_custom_footer_version($show);
		
		return $footer_version;
		
	}
	
	/**
	 * Get the version of Wordpress to display in the footer
	 *
	 * @param boolean $show	Whether or not to show the current Wordpress version
	 * @return string		The footer version text to display
	 */
	private function get_custom_footer_version($show = false){

		$footer_version = ($show) ? 'Version '.get_bloginfo('version') : false;
		
		return $footer_version;
		
	}
	
	/**
	 * Show a preview of the custom admin footer to the user
	 *
	 * @param boolean $permitted	Whether or not the current user is permitted to use the preview function (should be, but just in case)
	 */
	public function AJAX_update($permitted = true){
		
		if($permitted) :
		
			/** Grab the Wordpress version to display (or 'false' if it should not be displayed) */
			$version = $this->get_custom_footer_version($this->options['footer_show_version']);
			
			/** Output the updated custom admin header */
			echo '<p id="footer-left" class="alignleft">';
			echo $this->options['footer_text'];
			echo '</p>';
			echo '<p id="footer-upgrade" class="alignright">'.$version.'</p>';
			echo '<div class="clear"></div>';
			
		else :
			output_ajax_error('footer');
		endif;
		
	}
	
}

/**
 * AJAX callback for updating a preview of the custom admin footer
 */
add_action('wp_ajax_preview-custom-admin-footer', '_aab_preview_footer');
function _aab_preview_footer(){
	
	/** Checks user permisisons */
	$permitted = (current_user_can($_POST['branding']['role_footer']) && wp_verify_nonce($_POST['security'], 'aab-action'))
				 ? true
				 : false;
	
	/** Create an instance of the AAB_Footer class and update the custom admin footer using the 'AJAX_update' method  */
	$custom_admin_footer = new AAB_Footer($_POST['branding']);
	$custom_admin_footer->AJAX_update($permitted);
	
	die();	// Required for a proper AJAX result
	
}
?>