<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @description:	Customised admin area header file
 */

/**
 * Set up the custom admin header
 */
add_action('admin_enqueue_scripts', 'aab_do_header');
function aab_do_header(){
	
	/** Create an instance of the AAB_Header class */
	$aab_header = new AAB_Header();

}

/**
 * AAB_Header class
 */
class AAB_Header{
	
	private $options = null;
	private $screen_id = 'admin-branding_page_djg-admin-area-branding-header';
	private $header_override = false;
	private $header_enabled = false;
	
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
		
		/** Check to see if the user is on the 'Custom Admin Header' page */
		$screen = get_current_screen();
		if($screen->id === $this->screen_id) :
			$this->header_override = true;
			aab_loading_dialog();
		endif;
		
		/** Check to see if the custom admin header is enabled */		
		$this->header_enabled = $this->options['show_custom_header'];
	
		/** Check to see if the custom admin header should be shown */
		if((bool)$this->header_enabled || $this->header_override) :
			add_action('in_admin_header', array(&$this, '_custom_header'));
		endif;
		
	}
	
	/**
	 * Callback for displaying the current custom admin header
	 */
	public function _custom_header(){
		
		/** Output the custom admin header */
		echo '<div id="custom-admin-header">';
		$this->custom_header();
		echo '</div>';
		echo '<div id="custom-admin-header-spacer"></div>';
		
	}
	
	/**
	 * Output the inner HTML of the custom admin header
	 *
	 * @param boolean $permitted	Whether or not the current user is permitted to use the preview function (should be, but just in case)
	 */
	public function custom_header($permitted = true){
		
		/** Set up the custom admin header image for display */
		if($this->options['header_logo'] !== '0') :
			$logo = wp_get_attachment_image_src($this->options['header_logo'], 'full');
			$img = '<img id="header-logo-custom" src="'.esc_url($logo['0']).'" width="'.$logo['1'].'" height="'.$logo['2'].'" />';
		else :
			$img = false;
		endif;
		
		/** Output the CSS for the custom header */
		$this->custom_header_css($permitted);
		
		if($permitted) :
?>
			<a href="<?php echo trailingslashit(get_bloginfo('url')); ?>" title="<?php esc_attr_e('Visit Site') ?>">
				<?php echo $img; ?>
			</a>
<?php
		else :
			output_ajax_error('header');
		endif;
	
	}
	
	/**
	 * Output the CSS required for the custom admin header
	 *
	 * @param boolean $permitted	Whether or not the current user is permitted to use the preview function (should be, but just in case)
	 */
	function custom_header_css($permitted = true){
		
		if($permitted) :
		
			/** Set the custom admin header logo margin string */
			$header_logo_margin = $this->options['header_logo_margin_top'].' '.$this->options['header_logo_margin_right'].' '.$this->options['header_logo_margin_bottom'].' '.$this->options['header_logo_margin_left'];
?>
			<style>
				/** For the custom admin header */
				#custom-admin-header{
					background-color:		<?php echo $this->options['header_background_colour']; ?>;
					border-bottom-color:	<?php echo $this->options['header_border_bottom_colour']; ?>;
					border-bottom-style:	<?php echo $this->options['header_border_bottom_style']; ?>;
					border-bottom-width:	<?php echo $this->options['header_border_bottom_width']; ?>;
					display:				block;
					height:					<?php echo $this->options['header_height']; ?>;
					margin-left:			-20px;
					position:				<?php echo ($this->options['header_fixed']) ? 'fixed' : 'static' ?>;
					width:					<?php echo ($this->options['header_fixed']) ? '100%' : 'auto' ?>;
					z-index:				10;
				}
				#custom-admin-header-spacer{
					display:				<?php echo ($this->options['header_fixed']) ? 'block' : 'none' ?>;
					padding-bottom:			<?php echo $this->full_header_height(); ?>px;
				}
				#header-logo-custom{
					float:					left;
					margin:					<?php echo $header_logo_margin; ?>;
					-webkit-user-select:	none;
					-moz-user-select:		none;
					-khtml-user-select:		none;
					user-select:			none;
				}
			</style>
<?php
		else :
?>
			<style>
				/** For the custom admin header */
				#custom-admin-header{
					background-color:		transparent;
					border-bottom:			none;
					height:					auto;
					margin-left:			0;
					position:				static;
					width:					auto;
				}
				#custom-admin-header-spacer{
					display:				none;
					padding-bottom:			0;
				}
				#header-logo-custom{
					margin:					0;
				}
			</style>
<?php
		endif;
		
	}
	
	/**
	 * Calculate the full height of the custom admin header (including the border)
	 */
	private function full_header_height(){
		
		return $this->options['header_height'] + $this->options['header_border_bottom_width'];
		
	}
	
}

/**
 * AJAX callback for updating a preview of the custom admin header
 */
add_action('wp_ajax_preview-custom-admin-header', '_aab_preview_header');
function _aab_preview_header(){
	
	/** Checks user permisisons */
	$permitted = (current_user_can($_POST['branding']['role_header']) && wp_verify_nonce($_POST['security'], 'aab-action'))
				 ? true
				 : false;
	
	/** Create an instance of the AAB_Header class and update the custom admin header using the 'custom_header' method  */	
	$custom_admin_header = new AAB_Header($_POST['branding']);
	$custom_admin_header->custom_header($permitted);
	
	die();	// Required for a proper AJAX result

}
?>