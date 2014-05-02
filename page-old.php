<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @description:	Page enabling users to manage custom admin branding settings
 */

/**
 * Avoid direct calls to this file where wp core files are not present
 */
if(!function_exists('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

$aab_page = new AAB_Page();

/**
 * Custom Branding Options
 */
class AAB_Page{
	
	/**
	 * Whether or not to enable admin debugging
	 *
	 * @var boolean
	 */
	private $enable_debugging;
	
	/**
	 * The title of the page to display
	 *
	 * @var string
	 */
	private $page_title = '';
	
	/**
	 * The metabox that should be displayed to the user
	 *
	 * @var string
	 */
	private $metabox = '';
	
	/**
	 * The scope of the page that will be displayed to the user
	 *
	 * @var string
	 */
	private $scope = '';
	
	/**
	 * The current plugin options
	 *
	 * @var array
	 */
	private $defaults = '';
	
	/**
	 * The current plugin options
	 *
	 * @var array
	 */
	private $current_options = '';
	
	/**
	 * The options which are to be updated (along with defaults for any missing options)
	 *
	 * @var array
	 */
	private $updated_options = '';
	
	/**
	 * The capabilities available for the user to choose from (so that admins can change who can manage different areas of the plugin)
	 *
	 * @var string
	 */
	private $roles = array();
	
	/**
	 * The status of an action that the user carries out
	 *
	 * @var integer
	 */
	private $status = 0;
	
	/**
	 * Constructor
	 */
	function __construct(){
		
		/** Register the callback for registering admin styles and scripts */
		add_action('admin_enqueue_scripts', array(&$this, 'on_register_admin_scripts'));
		
		/** Register the callback for the admin menu setup */
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		
		/** Register the callback for custom branding options to be validated and then saved. */
		add_action('admin_post_save_custom-branding-options', array(&$this, 'on_save_changes'));
		
		/** Add scrips and styles to the page header */
		add_action('admin_head', array(&$this, 'on_admin_header'));
		
	}
    
    /**
	 * Add admin scripts and styles
	 */
	function on_register_admin_scripts(){
	
        /** Register the generic script/style for the plugin */
		wp_register_style('aab-generic', plugins_url('css/style.css?scope=admin-area-branding', __FILE__));
		wp_register_script('aab-generic', plugins_url('js/branding.js?scope=admin-area-branding', __FILE__ ), array('aab-jQuery-spinner', 'aab-jQuery-switchButton'));
		
		/** Register the style for the jQuery UI widget's used by the plugin */
		wp_register_style('aab-jquery', plugins_url('css/jquery.css?scope=admin-area-branding', __FILE__));	
		
		/** Register the script for the JSColor colour picker */
		wp_register_script('js-color', plugins_url('js/jscolor.js?scope=admin-area-branding', __FILE__ ));
		
		/** Enqueue the script for the custom jQuery UI inlineSpinner and blockSpinner widget */
		wp_register_script('aab-jQuery-spinner', plugins_url('js/jQueryUI-spinner/spinner.js?scope=admin-area-branding', __FILE__ ), array('jquery-ui-widget'));
		
		/** Enqueue the script/style for the custom jQuery UI switchButton widget */
		wp_register_script('aab-jQuery-switchButton', plugins_url('js/jQueryUI-switchButton/switchButton.js?scope=admin-area-branding', __FILE__ ), array('jquery-ui-widget'));
		wp_register_style('aab-jQuery-switchButton', plugins_url('js/jQueryUI-switchButton/switchButton.css?scope=admin-area-branding', __FILE__ ));
        
    }
	
	/**
	 * Add the Custom Admin Branding options admin menu
	 */
	function on_admin_menu(){
		
		/** Set the default options (required for showing the user something if no setting is defined) */
		$this->set_defaults();
		
		/** Set the current options (required for showing the user the current settings) */
		$this->set_current_options($this->defaults);
		
		/** Add the page to the menu */
		add_menu_page(__('Admin Area Branding'), __('Admin Branding'), 'dont-show-as-page', 'djg-admin-area-branding', '', 'dashicons-art', 81);
        $this->pagehook['header']	= add_submenu_page('djg-admin-area-branding', __('Header'), __('Header'), $this->current_options['role_header'], 'djg-admin-area-branding-header', array(&$this, 'on_show_page'));
		$this->pagehook['footer']	= add_submenu_page('djg-admin-area-branding', __('Footer'), __('Footer'), $this->current_options['role_footer'], 'djg-admin-area-branding-footer', array(&$this, 'on_show_page'));
		$this->pagehook['login']	= add_submenu_page('djg-admin-area-branding', __('Login'),  __('Login'),  $this->current_options['role_login'],  'djg-admin-area-branding-login', 	array(&$this, 'on_show_page'));
		$this->pagehook['manage']	= add_submenu_page('djg-admin-area-branding', __('Manage'), __('Manage'), 'manage_options', 'djg-admin-area-branding-manage', array(&$this, 'on_show_page'));
        
        /** Add the admin styles and scripts */
		foreach($this->pagehook as $pagehook) :
			add_action('load-'.$pagehook, array(&$this, 'on_load_page'));
			add_action('admin_print_styles-'.$pagehook, array(&$this, 'on_admin_header'));
		endforeach;
		
	}
	
	/**
	 * Setup the initial property values when the page loads
	 */
	function on_load_page(){
		
		/** Set the screen object */
		$this->screen = get_current_screen();
		
		/** Set the page variables (required for displaying the page to users) */
		$this->set_page_vars();
		
		/** Register the callback to use for displaying the relevant metabox to users */
		add_action('admin_area_branding_metabox', array($this, $this->metabox));
		
	}
    
    /**
	 * Add styles and scripts to the header of the page
	 */
	function on_admin_header(){
		
		/** Check that we are on the on the correct page and add the admin scripts */
		if(!in_array($this->screen->id, $this->pagehook)) :
			return false;
		endif;
		
		/** Enqueue all styles, scripts and settings for the media uploader (must be before plugin JS) */
		wp_enqueue_media();
		
        wp_enqueue_style('aab-generic');				// Generic plugin CSS
		wp_enqueue_script('aab-generic');				// Generic plugin JS
		wp_enqueue_script('js-color');					// JSColor plugin
		wp_enqueue_style('aab-jquery');					// CSS for jQuery UI widgets
		wp_enqueue_script('jquery-ui-dialog');			// jQuery UI dialog
		wp_enqueue_script('jquery-ui-spinner');			// jQuery UI spinner
		wp_enqueue_script('jquery-effects-slide');		// jQuery slide effect
		wp_enqueue_style('aab-jQuery-switchButton');	// CSS for custom jQuery UI switchButton 
		
    }
	
	/**
	 * Save any changes
	 */
	function on_save_changes(){
		
		/** Register the custom wp_die handler for this plugin */
		add_filter('wp_die_handler', 'register_admin_branding_die_handler');
		
		/** Checks user permisisons */
		if(!current_user_can('manage_options')) :
			$warning = new AAB_Invalid_Action('permission');
			wp_die($warning, 'Cheatin&#8217; uh?', array('back_link' => true));
		endif;
			
		/** Checks the admin referrer is from the correct page. */
		check_admin_action('aab-action');
		
		/** Set up a debug object (required when carrying out database operations) */
		global $debug;
		$debug = new AAB_Debug;
		
		/** Set the option defaults which should be shown to the user */
		$this->set_defaults();
		
		/** Set the current options (required for checking if a setting has changed) */
		$this->set_current_options();
		
		/** Set whether or not to enable admin debugging (i.e. full error messages shown) */
		$this->enable_debugging = $this->current_options['debug'];
		
		/** Set the updated options (including a default for any that are not specified) */
		$this->set_updated_options($_POST['page']);
		
		/** Ensure that the user actually wants to be here by checking that 'action' exists in $_POST */
		if(isset($_POST['action'])) :
		
			/** The options on the 'Manage' page are being updated */
			if($_POST['submit'] === 'Update') :
			
				$successful = $this->do_update_options();
				
				if($successful) : // Adding the poll was successful, so destroy the current $_SESSION
					$this->status = 1;
				else :
					$this->status = 2;
				endif;
				
			/** The user is here in error (or by desire, but wihtout permission) */
			else:
			
				$warning = new AAB_Invalid_Action('submit');
				wp_die($warning, 'I don\'t think so, cheeky!', array('back_link' => true));
				
			endif;
		
		endif;		
		
		/** Set the correct status */
		$status = ($this->status !== 0) ? $this->status : 99;
		$_POST['_wp_http_referer'] = add_query_arg('status', $status, $_POST['_wp_http_referer']);
		
		/** Checks to see if there are errors which need to be debuged */
		$this->debug();
		
		/** Finally, redirect the user back to where they can from */
		wp_redirect($_POST['_wp_http_referer']);
		
	}
	
	/**
	 * Render the page
	 */
	function on_show_page(){
?>
		<div id="admin-area-branding-page" class="wrap admin-area-branding">
		
			<h2><?php echo $this->page_title; ?></h2>
			
			<?php $this->splash_message() ?>
			
			<form action="admin-post.php" method="post">
				<?php wp_nonce_field('aab-action'); ?>
				<input type="hidden" name="action" value="save_custom-branding-options" />
                
				<div id="admin-area-branding-metabox" class="metabox-holder">
					<?php do_action('admin_area_branding_metabox'); ?>
				</div>
				
			</form>
		</div>
<?php
	}
	
	/**
	 * Set the properties required for page display
	 */
	private function set_page_vars(){
	
		switch($this->screen->id) :
		
			case $this->pagehook['manage'] :
				$title = __('Manage Custom Branding', 'admin_area_branding');
				$metabox = 'metabox_manage';
				$scope = 'manage';
				break;
			case $this->pagehook['header'] :
				$title = __('Custom Admin Header', 'admin_area_branding');
				$metabox = 'metabox_header';
				$scope = 'header';
				break;
			case $this->pagehook['footer'] :
				$title = __('Custom Admin Footer', 'admin_area_branding');
				$metabox = 'metabox_footer';
				$scope = 'footer';
				break;
			case $this->pagehook['login'] :
				$title = __('Custom Admin Login', 'admin_area_branding');
				$metabox = 'metabox_login';
				$scope = 'login';
				break;
				
		endswitch;
		
		$this->page_title = $title;
		$this->metabox = $metabox;
		$this->scope = $scope;
		
	}
	
	/**
	 * Get a set of default values (in case a value is not defined)
	 *
	 * @return array	The default values for the options in this plugin
	 */
	private function set_defaults(){

		$this->defaults = aab_get_default_options();
		
	}
	
	/**
	 * Set all of the current options for this plugin as a associative array (using the $current_options property)
	 *
	 * @param boolean|array $defaults	Whether or not to parse the default options when getting the current options (can also pass an array of $defaults)
	 */
	private function set_current_options($defaults = false){
	
		$this->current_options = aab_get_current_options($defaults);
		
	}
	
	/**
	 * Set the users updated options using the $updated_options property (by parsing the $_POST['branding'] array with the $defaults class variable array for the page being updated)
	 *
	 * @param required string $page	The page that the users is currently on
	 */
	private function set_updated_options($page){
		
		$valid_options = array_filter($_POST['branding'], array($this, '_remove_empty_callback'));
		$this->updated_options = wp_parse_args($valid_options, $this->defaults[$page]);
		
	}
	
	/**
	 * Render the 'Manage' metabox on the page
	 */
	function metabox_manage(){
?>
		<h3 class="first"><?php _e('Turn options on or off', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['show_custom_header'] === '1') ? 'checked="true"' : false; ?>
			<label for="show_custom_header" class="for-manage"><?php _e('Custom admin header:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[show_custom_header]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="show_custom_header" class="switch" name="branding[show_custom_header]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['show_custom_footer'] === '1') ? 'checked="true"' : false; ?>
			<label for="show_custom_footer" class="for-manage"><?php _e('Custom admin footer:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[show_custom_footer]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="show_custom_footer" class="switch" name="branding[show_custom_footer]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['show_custom_login'] === '1') ? 'checked="true"' : false; ?>
			<label for="show_custom_login" class="for-manage"><?php _e('Custom admin login screen:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[show_custom_login]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="show_custom_login" class="switch" name="branding[show_custom_login]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<h3 class="first"><?php _e('Plugin settings', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['debug'] === '1') ? 'checked="true"' : false; ?>
			<label for="debug" class="for-manage"><?php _e('Enable debugging:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[debug]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="debug" class="switch-yes-no" name="branding[debug]" value="1" <?php echo $checked; ?>></input>
			<div class="tips">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Enabling debugging will show you details of any error that may occur with this plugin. Hopefully you\'ll never need it!', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<h3 class="first"><?php _e('Plugin permissions', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<label for="role_header" class="for-manage"><?php _e('Role required to edit Header:', 'admin_area_branding') ?></label>
			<select id="role_header" name="branding[role_header]">
				<?php echo $this->get_role_options($this->current_options['role_header']); ?>
			</select>
		</div>
		
		<div class="single-option">
			<label for="role_footer" class="for-manage"><?php _e('Role required to edit Footer:', 'admin_area_branding') ?></label>
			<select id="role_footer" name="branding[role_footer]">
				<?php echo $this->get_role_options($this->current_options['role_footer']); ?>
			</select>
		</div>
		
		<div class="single-option">
			<label for="role_login" class="for-manage"><?php _e('Role required to edit Login:', 'admin_area_branding') ?></label>
			<select id="role_login" name="branding[role_login]">
				<?php echo $this->get_role_options($this->current_options['role_login']); ?>
			</select>
		</div>
		
		<input type="hidden" id="page" name="page" value="manage"></input>
		
		<?php submit_button(__('Update')); ?>
<?php
	}
	
	/**
	 * Render the 'Header' metabox on the page
	 */
	function metabox_header(){
?>
		<h3 class="first"><?php _e('Set colour, height and border', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<label for="header_background_colour" class="for-header"><?php _e('Background colour:', 'admin_area_branding') ?></label>
			<input type="text" id="header_background_colour" class="colour-picker" name="branding[header_background_colour]" value="<?php echo $this->current_options['header_background_colour']; ?>"></input>
		</div>
		
		<div class="single-option">
			<label for="header_height" class="for-header"><?php _e('Height:', 'admin_area_branding') ?></label>
			<input type="text" id="header_height" class="small inline-spinner" name="branding[header_height]" value="<?php echo $this->current_options['header_height']; ?>"></input>
			<div class="tips no-margin">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Values can be between 0px and 99px', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<div class="single-option">
			<label for="header_border_bottom_width" class="for-header"><?php _e('Border bottom width:', 'admin_area_branding') ?></label>
			<input type="text" id="header_border_bottom_width" class="small inline-spinner" name="branding[header_border_bottom_width]" value="<?php echo $this->current_options['header_border_bottom_width']; ?>"></input>
			<div class="tips no-margin">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Values can be between 0px and 99px', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<div class="single-option">
			<label for="header_border_bottom_style" class="for-header"><?php _e('Border bottom style:', 'admin_area_branding') ?></label>
			<select id="header_border_bottom_style" name="branding[header_border_bottom_style]">
				<?php echo $this->get_border_style_options(); ?>
			</select>
		</div>
		
		<div class="single-option">
			<label for="header_border_bottom_colour" class="for-header"><?php _e('Border bottom colour:', 'admin_area_branding') ?></label>
			<input type="text" id="header_border_bottom_colour" class="colour-picker" name="branding[header_border_bottom_colour]" value="<?php echo $this->current_options['header_border_bottom_colour']; ?>"></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['header_fixed'] === '1') ? 'checked="true"' : false; ?>
			<label for="header_fixed" class="for-header"><?php _e('Set position \'fixed\':', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[header_fixed]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="header_fixed" class="switch-yes-no" name="branding[header_fixed]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<h3>Choose a logo</h3>
		
		<div class="single-option">
			<?php $logo_id = (isset($this->current_options['header_logo'])) ? $this->current_options['header_logo'] : 0; ?>
			<label for="header_logo" class="for-header"><?php _e('Logo:', 'admin_area_branding') ?></label>
			<input type="hidden" id="header_logo" name="branding[header_logo]" value="<?php echo $logo_id; ?>"/>
			<input type="hidden" id="original_header_logo" name="branding[original_header_logo]" value="<?php echo $logo_id; ?>"/>
			<input type="button" id="select-header-logo" class="select-logo-button button-secondary" value="<?php _e('Select Image', 'admin_area_branding'); ?>" />
			<a id="remove-header-logo" class="remove-logo-button button-delete"><?php _e('Remove Image', 'admin_area_branding') ?></a>
			<a id="restore-header-logo" class="restore-logo-button button-delete"><?php _e('Restore Original Image', 'admin_area_branding') ?></a>
		</div>
<?php
		/** Set up the custom admin header image for display */
		if(isset($this->current_options['header_logo']) && $this->current_options['header_logo'] !== '0') :
			$logo = wp_get_attachment_image_src($this->current_options['header_logo'], 'full');
			$img = '<img id="logo-preview" src="'.esc_url($logo['0']).'" width="'.$logo['1'].'" height="'.$logo['2'].'" />';
		else :
			$img = '<div class="tips no-margin"><span class="tip">' . __('No logo selected', 'admin_area_branding') . '</span></div>';
		endif;
?>
		<div class="single-option">
			<label class="for-header"><?php _e('Logo preview:', 'admin_area_branding') ?></label>
			<span class="logo-preview-span"><?php echo $img; ?></span>
			<span class="custom-admin-logo-preview-loader">&nbsp;</span>
		</div>
		
		<div class="single-option">
			<label for="header_logo_margin_top" class="for-header"><?php _e('Logo margin:', 'admin_area_branding') ?></label>
			<input type="text" id="header_logo_margin_top" class="tiny block-spinner" name="branding[header_logo_margin_top]" value="<?php echo $this->current_options['header_logo_margin_top']; ?>"></input>
			<input type="text" id="header_logo_margin_right" class="tiny block-spinner" name="branding[header_logo_margin_right]" value="<?php echo $this->current_options['header_logo_margin_right']; ?>"></input>
			<input type="text" id="header_logo_margin_bottom" class="tiny block-spinner" name="branding[header_logo_margin_bottom]" value="<?php echo $this->current_options['header_logo_margin_bottom']; ?>"></input>
			<input type="text" id="header_logo_margin_left" class="tiny block-spinner" name="branding[header_logo_margin_left]" value="<?php echo $this->current_options['header_logo_margin_left']; ?>"></input>
			<div class="tips no-margin">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Top, right, bottom, left', 'admin_area_branding') ?></span>
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Values can be between -9px and 99px', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<div class="manage-form">
			<input type="hidden" id="page" name="page" value="header"></input>
			<input type="hidden" id="role_header" name="role_header" value="<?php echo $this->current_options['role_header'] ?>"></input>
			<?php submit_button(__('Update'), 'primary', 'submit', false); ?>
			<?php submit_button(__('Preview Changes'), 'secondary', 'preview-changes', false); ?>
			<a id="restore-original-header" class="restore-original-button button-delete"><?php _e('Restore Header', 'admin_area_branding') ?></a>
		</div>
<?php
	}
	
	/**
	 * Render the 'Footer' metabox on the page
	 */
	function metabox_footer(){
?>
		<h3 class="first">Configure the custom admin footer</h3>
		
		<div class="single-option">
			<label for="footer_text" class="for-textarea"><?php _e('Footer Text:', 'admin_area_branding') ?></label>
			<textarea id="footer_text" name="branding[footer_text]"><?php echo $this->current_options['footer_text'] ?></textarea>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['footer_show_version'] === '1') ? 'checked="true"' : false; ?>
			<label for="footer_show_version" class="for-footer"><?php _e('Show the installed version of Wordpress:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[footer_show_version]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="footer_show_version" class="switch-yes-no" name="branding[footer_show_version]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="manage-form">
			<input type="hidden" id="page" name="page" value="footer"></input>
			<input type="hidden" id="role_footer" name="role_footer" value="<?php echo $this->current_options['role_footer'] ?>"></input>
			<?php submit_button(__('Update'), 'primary', 'submit', false); ?>
			<?php submit_button(__('Preview Changes'), 'secondary', 'preview-changes', false); ?>
			<a id="restore-original-footer" class="restore-original-button button-delete"><?php _e('Restore Footer', 'admin_area_branding') ?></a>
		</div>
<?php
	}
	
	/**
	 * Render the 'Login' metabox on the page
	 */
	function metabox_login(){
?>		
		<h3 class="first"><?php _e('Choose a logo', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<?php $logo_id = (isset($this->current_options['login_logo'])) ? $this->current_options['login_logo'] : 0; ?>
			<label for="login_logo" class="for-login"><?php _e('Logo:', 'admin_area_branding') ?></label>
			<input type="hidden" id="login_logo" name="branding[login_logo]" value="<?php echo $logo_id; ?>"/>
			<input type="hidden" id="original_login_logo" name="branding[original_login_logo]" value="<?php echo $logo_id; ?>"/>
			<input type="button" id="select-login_logo" class="select-logo-button button-secondary" value="<?php _e('Select Image', 'admin_area_branding'); ?>" />
			<a id="remove-login_logo" class="remove-logo-button button-delete"><?php _e('Remove Image', 'admin_area_branding') ?></a>
			<a id="restore-login_logo" class="restore-logo-button button-delete"><?php _e('Restore Original Image', 'admin_area_branding') ?></a>
		</div>
<?php
		/** Set up the custom admin header image for display */
		if(isset($this->current_options['login_logo']) && $this->current_options['login_logo'] !== '0') :
			$logo = wp_get_attachment_image_src($this->current_options['login_logo'], 'full');
			$img = '<img id="logo-preview" src="'.esc_url($logo['0']).'" width="'.$logo['1'].'" height="'.$logo['2'].'" />';
		else :
			$img = '<div class="tips no-margin"><span class="tip">' . __('No logo selected', 'admin_area_branding') . '</span></div>';
		endif;
?>
		<div class="single-option">
			<label class="for-login"><?php _e('Logo preview:', 'admin_area_branding') ?></label>
			<span class="logo-preview-span"><?php echo $img; ?></span>
			<span class="custom-admin-logo-preview-loader">&nbsp;</span>
		</div>
		
		<h3><?php _e('Configure the \'Register\' and \'Lost your password?\' links', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<label for="login_nav_link_locatoin" class="for-login"><?php _e('Link position:', 'admin_area_branding') ?></label>
			<select id="login_nav_link_locatoin" name="branding[login_nav_link_locatoin]">
				<?php echo $this->get_link_placement_options($this->current_options['login_nav_link_locatoin']); ?>
			</select>
		</div>
		
		<div class="single-option">
			<label for="login_nav_background_colour" class="for-login"><?php _e('Background colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_nav_background_colour" class="colour-picker" name="branding[login_nav_background_colour]" value="<?php echo $this->current_options['login_nav_background_colour']; ?>"></input>
			<div class="tips">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> Only used if \'Register\' and \'Lost your password?\' links are displayed in position \'Top of page\' or \'Bottom of page\'', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<div class="single-option">
			<label for="login_nav_text_colour" class="for-login"><?php _e('Text colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_nav_text_colour" class="colour-picker" name="branding[login_nav_text_colour]" value="<?php echo $this->current_options['login_nav_text_colour']; ?>"></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['login_nav_underline_link'] === '1') ? 'checked="true"' : false; ?>
			<label for="login_nav_underline_link" class="for-login"><?php _e('Underline the link:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[login_nav_underline_link]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="login_nav_underline_link" class="switch-yes-no" name="branding[login_nav_underline_link]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="single-option">
			<label for="login_nav_text_colour_hover" class="for-login"><?php _e('Text colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_nav_text_colour_hover" class="colour-picker" name="branding[login_nav_text_colour_hover]" value="<?php echo $this->current_options['login_nav_text_colour_hover']; ?>"></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['login_nav_underline_link_hover'] === '1') ? 'checked="true"' : false; ?>
			<label for="login_nav_underline_link_hover" class="for-login"><?php _e('Underline the link on hover:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[login_nav_underline_link_hover]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="login_nav_underline_link_hover" class="switch-yes-no" name="branding[login_nav_underline_link_hover]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<h3><?php _e('Configure the \'Back to blog\' link', 'admin_area_branding') ?></h3>
		
		<div class="single-option">
			<label for="login_back_to_blog_link_locatoin" class="for-login"><?php _e('Link position:', 'admin_area_branding') ?></label>
			<select id="login_back_to_blog_link_locatoin" name="branding[login_back_to_blog_link_locatoin]">
				<?php echo $this->get_link_placement_options($this->current_options['login_back_to_blog_link_locatoin']); ?>
			</select>
		</div>
		
		<div class="single-option">
			<label for="login_back_to_blog_background_colour" class="for-login"><?php _e('Background colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_back_to_blog_background_colour" class="colour-picker" name="branding[login_back_to_blog_background_colour]" value="<?php echo $this->current_options['login_back_to_blog_background_colour']; ?>"></input>
			<div class="tips">
				<span class="tip"><span class="title"><?php _e('Tip:', 'admin_area_branding') ?></span> <?php _e('Only used if \'Back to blog\' link is displayed in position \'Top of page\' or \'Bottom of page\'', 'admin_area_branding') ?></span>
			</div>
		</div>
		
		<div class="single-option">
			<label for="login_back_to_blog_text_colour" class="for-login"><?php _e('Text colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_back_to_blog_text_colour" class="colour-picker" name="branding[login_back_to_blog_text_colour]" value="<?php echo $this->current_options['login_back_to_blog_text_colour']; ?>"></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['login_back_to_blog_underline_link'] === '1') ? 'checked="true"' : false; ?>
			<label for="login_back_to_blog_underline_link" class="for-login"><?php _e('Underline the link:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[login_back_to_blog_underline_link]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="login_back_to_blog_underline_link" class="switch-yes-no" name="branding[login_back_to_blog_underline_link]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="single-option">
			<label for="login_back_to_blog_text_colour_hover" class="for-login"><?php _e('Text colour:', 'admin_area_branding') ?></label>
			<input type="text" id="login_back_to_blog_text_colour_hover" class="colour-picker" name="branding[login_back_to_blog_text_colour_hover]" value="<?php echo $this->current_options['login_back_to_blog_text_colour_hover']; ?>"></input>
		</div>
		
		<div class="single-option">
			<?php $checked = ($this->current_options['login_back_to_blog_underline_link_hover'] === '1') ? 'checked="true"' : false; ?>
			<label for="login_back_to_blog_underline_link_hover" class="for-login"><?php _e('Underline the link on hover:', 'admin_area_branding') ?></label>
			<input type="hidden" name="branding[login_back_to_blog_underline_link_hover]" value="0"> <!-- Dummy input to ensure a value of 0 is passed if the checkbox is not checked -->
			<input type="checkbox" id="login_back_to_blog_underline_link_hover" class="switch-yes-no" name="branding[login_back_to_blog_underline_link_hover]" value="1" <?php echo $checked; ?>></input>
		</div>
		
		<div class="manage-form">
			<input type="hidden" id="page" name="page" value="login"></input>
			<input type="hidden" id="role_login" name="role_login" value="<?php echo $this->current_options['role_login'] ?>"></input>
			<?php submit_button(__('Update', 'admin_area_branding'), 'primary', 'submit', false); ?>
			<?php submit_button(__('Preview Changes', 'admin_area_branding'), 'secondary', 'preview-login', false); ?>
		</div>
		
		<?php do_action('aab_login_preview'); ?>
<?php
	}
	
	/**
	 * Get all of the available border styles and create a <select> <option> for each
	 *
	 * @return string	The formatted <option> elements ready for output
	 */
	function get_border_style_options(){
		
		$border_styles = array(
			'none'		=> __('None', 'admin_area_branding'),
			'dashed'	=> __('Dashed', 'admin_area_branding'),
			'dotted'	=> __('Dotted', 'admin_area_branding'),
			'double'	=> __('Double', 'admin_area_branding'),
			'groove'	=> __('Groove', 'admin_area_branding'),
			'hidden'	=> __('Hidden', 'admin_area_branding'),
			'inherit'	=> __('Inherit', 'admin_area_branding'),
			'inset'		=> __('Inset', 'admin_area_branding'),
			'outset'	=> __('Outset', 'admin_area_branding'),
			'ridge'		=> __('Ridge', 'admin_area_branding'),
			'solid'		=> __('Solid', 'admin_area_branding')
		);
		if(!empty($border_styles)) : foreach($border_styles as $key => $style) :
				$selected = ($key === $this->current_options['header_border_bottom_style']) ? ' selected="true"' : false;
				$options.= "\t\t\t\t".'<option value="'.$key.'"'.$selected.'>'.$style.'</option>'."\n";
			endforeach;
		endif;
		
		return $options;
		
	}
	
	/**
	 * Get all of the available placements for links and create a <select> <option> for each
	 *
	 * @param string $selected_option	The selected option to check against
	 * @return string					The formatted <option> elements ready for output
	 */
	function get_link_placement_options($selected_option = ''){
		
		$placements = array(
			'none'		=> __('Hidden', 'admin_area_branding'),
			'box'		=> __('Under login box', 'admin_area_branding'),
			'top'		=> __('Top of page', 'admin_area_branding'),
			'bottom'	=> __('Bottom of page', 'admin_area_branding')
		);
		if(!empty($placements)) : foreach($placements as $key => $style) :
				$selected = ($key === $selected_option) ? ' selected="true"' : false;
				$options.= "\t\t\t\t".'<option value="'.$key.'"'.$selected.'>'.$style.'</option>'."\n";
			endforeach;
		endif;
		
		return $options;
		
	}
	
	/**
	 * Get all of the available roles for the site and create a <select> <option> for each
	 *
	 * @param string $selected_option	The selected option to check against
	 * @return string					The formatted <option> elements ready for output
	 */
	private function get_role_options($selected_option = ''){
		
		if(empty($this->roles)) :
			add_filter('editable_roles', array($this, '_sort_editable_roles'));
			$this->roles = get_editable_roles();
			remove_filter('editable_roles', array($this, '_sort_editable_roles'));
		endif;
		
		if(!empty($this->roles)) : foreach($this->roles as $key => $role) :
				$selected = ($key === $selected_option) ? ' selected="true"' : false;
				$options.= "\t\t\t\t".'<option value="'.$key.'"'.$selected.'>'.$role['name'].'</option>'."\n";
			endforeach;
		endif;
		
		return $options;
	
	}
	
	/**
	 * Filter the roles grabbed by 'get_editable_roles', sorting them alphabetically by name
	 *
	 * @param required array $roles	The roles grabbed by 'get_editable_roles'
	 * @return array				The filtered roles
	 */
	public function _sort_editable_roles($roles){
		
		uasort($roles, function($a, $b){
			return strcasecmp($a['name'], $b['name']);
		});
		return $roles;
		
	}
	
	/**
	 * Update the database options
	 */
	private function do_update_options(){
	
		global $debug, $wpdb;
		
		$queries = array(); // Initialise to avoid errors
		if(!empty($this->updated_options)): foreach($this->updated_options as $option => $value) :				
				if($this->current_options[$option] !== $value) :
					$queries[] = aab_update_option($option, $value, false);
				endif;
			endforeach;
		endif;
		
		/** Run all of the generated queries for updating the poll options, and check that there were no errors before committing */
		if(!empty($queries)) :
		
			$wpdb->query('TRANSACTION START');
			
			/** Loop through each query and run it (if there is no previous error) */
			foreach($queries as $query) :
				if(!$debug->sql_error_found) :
					$wpdb->query($query);
					$debug->debug('', '', 'update_admin_branding_options', 'Update admin branding options in the database');
				endif;
			endforeach;
			
			if(!$debug->sql_error_found) : // No errors were found, commit the transaction
				$wpdb->query('COMMIT;');
			else : // An error occured somewhere, so rollback the transaction
				$wpdb->query('ROLLBACK;');
			endif;
			
		endif;
		
		return (!$debug->sql_error_found) ? true : false;
		
	}
	
	/**
	 * If debugging of this plugin is allowed, check to see if there is any debug errors to show
	 */
	private function debug(){
	
		global $debug;
		
		if($this->enable_debugging) :
			$debug->output_errors();
		endif;
		
	}
	
	/**
	 * Writes a custom message at the top of an admin options page
	 */
	private function splash_message(){
	
		/** Check that there is a status for a splash message to be displayed */
		if(!$_REQUEST['status']) :
			return false;
		endif;
		
		/** Work out the class of the splash message */
		$message_classes[1] = 'updated';
		$message_classes[2] = 'error';
		$message_classes[99] = 'error';
		$message_class = $message_classes[$_REQUEST['status']];
		
		$this->set_splash_messages();
		$message = $this->messages_splash[$_REQUEST['status']];
			
		/** Display the message splash */
		echo '<div id="message" class="'.$message_class.' below-h2">';
		echo '<p>'.$message.'</p>';
		echo '</div>';
		
	}
	
	/**
	 * Set the splash messages available for this plugin
	 */
	private function set_splash_messages(){
	
		$this->messages_splash = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __('Your admin branding options have been successfully saved.', 'admin_area_branding'),
			2  => __('An error has occured. Please try again, and if still unsuccessful, contact an admin.', 'admin_area_branding'),
			99 => __('An unknown error occured, please try again.', 'admin_area_branding')
		);
		
	}
	
	/**
	 * Checks all keys in an array so that empty keys are removed
	 *
	 * @param required string $value The value to check
	 */
	function _remove_empty_callback($value){
		
		return ($value !== '' && $value !== false);
		
	}
	
}
?>