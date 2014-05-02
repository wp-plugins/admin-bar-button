<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Bar Button Plugin
 * @Description:	Options page for the admin bar button
 */

/**
 * Avoid direct calls to this file where WP core files are not present
 */
if(!function_exists('add_action')) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;

/** Initiate the plugin options page */
$aab_header_page = new AAB_Header_Page();

/**
 * Admin Bar Button options page class
 */
class AAB_Header_Page{
	
	/**
	 * The currently saved options
	 *
	 * @var array
	 */
	private $options = array();
	
	/**
	 * The default options (in case the user tries to submit a blank option, or if one is not set on page load)
	 *
	 * @var array
	 */
	private $defaults = array();
	
	/**
	 *	The options available for each select dropdown
	 *
	 * @var array
	 */
	private $select_options = array();
	
	/**
	 * Constructor
	 */
	public function __construct(){
	
		add_action('admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts'));	// Register the admin scripts
		add_action('admin_menu', array(&$this, 'on_admin_menu'));						// Admin menu setup
		add_action('admin_init', array(&$this, 'on_admin_init'));						// Register the admin settings
		
		$this->set_options();			// Set the currently saved options
		$this->set_defaults();			// Set the default options
		$this->set_select_options();	// Set the options available for each select
		
	}
    
    /**
	 * Add admin scripts and styles
	 */
	function on_admin_enqueue_scripts(){
	
		/** Enqueue all styles, scripts and settings for the media uploader (must be before plugin JS) */
		wp_enqueue_media();
		
		wp_enqueue_style('aab-generic', plugins_url('assets/branding.css?scope=admin-area-branding', __FILE__));
		wp_enqueue_script('aab-generic', plugins_url('assets/branding.js?scope=admin-area-branding', __FILE__ ), array('jquery-ui-dialog', 'js-color', 'aab-jquery-switchButton'));
		wp_enqueue_style('aab-jquery', plugins_url('assets/jQuery.css?scope=admin-area-branding', __FILE__));
		wp_enqueue_script('js-color', plugins_url('assets/jscolor/jscolor.js?scope=admin-area-branding', __FILE__ ));
		wp_enqueue_script('aab-jquery-switchButton', plugins_url('assets/jQueryUI-switchButton/switchButton.js?scope=admin-area-branding', __FILE__ ), array('jquery-color', 'jquery-ui-widget'));
		wp_enqueue_style('aab-jquery-switchButton', plugins_url('assets/jQueryUI-switchButton/switchButton.css?scope=admin-area-branding', __FILE__ ));
        
    }
	
	/**
	 * Add the Admin Bar Button options admin menu
	 */
	public function on_admin_menu(){
	
		add_menu_page(
			__('Admin Area Branding', 'djg-admin-area-branding'),	// Page title
			__('Admin Branding', 'djg-admin-area-branding'),		// Menu title
			'dont-show-as-page',									// Required capability
			'djg-admin-area-branding',								// Page slug
			'',														// Rendering callback
			'dashicons-art',										// Menu icon
			81														// Menu position
		);
		
        $this->pagehook	= add_submenu_page(
			'djg-admin-area-branding',								// Parent menu
			__('Header', 'djg-admin-area-branding'),				// Page title
			__('Header', 'djg-admin-area-branding'),				// Menu title
			$this->get_value('role_header'),						// Required capability
			'djg-admin-area-branding-header',						// Page slug
			array(&$this, 'on_show_page')							// Rendering callback
		);
		
	}
	
	/**
	 * Register the settings that can be saved by this plugin
	 */
	public function on_admin_init(){
	
		register_setting(
			'admin_area_branding_group',		// Group name
			'djg_admin_area_branding',			// Option name
			array(&$this, 'on_save_settings')	// Sanatize options callback
		);
		
		
		/*-----------------------------------------------
		  Header look settings
		-----------------------------------------------*/
		
		add_settings_section(
            'aab_header_look_section',										// ID
            __('How should the header look?', 'djg-admin-area-branding'),	// Title
			false,															// Callback
            'djg_admin_area_branding_header'								// Page
        );
		
		add_settings_field(
            'header_background_colour',							// ID
            __('Background Colour', 'djg-admin-area-branding'),	// Title
            array($this, '_option_header_background_colour'),	// Callback
            'djg_admin_area_branding_header',					// Page
            'aab_header_look_section',							// Section
			array(												// Args
				'label_for' => 'header_background_colour'
			) 
        );
		
		add_settings_field(
            'header_height',
            __('Height (px)', 'djg-admin-area-branding'),
            array($this, '_option_header_height'),
            'djg_admin_area_branding_header',
            'aab_header_look_section',
			array(
				'label_for' => 'header_height'
			) 
        );
		
		add_settings_field(
            'header_border_bottom_width',
            __('Border Bottom Width (px)', 'djg-admin-area-branding'),
            array($this, '_option_header_border_bottom_width'),
            'djg_admin_area_branding_header',
            'aab_header_look_section',
			array(
				'label_for' => 'header_border_bottom_width'
			) 
        );
		
		add_settings_field(
            'header_border_bottom_style',
            __('Border Bottom Style', 'djg-admin-area-branding'),
            array($this, '_option_header_border_bottom_style'),
            'djg_admin_area_branding_header',
            'aab_header_look_section',
			array(
				'label_for' => 'header_border_bottom_style'
			) 
        );
		
		add_settings_field(
            'header_border_bottom_colour',
            __('Border Bottom Colour', 'djg-admin-area-branding'),
            array($this, '_option_header_border_bottom_colour'),
            'djg_admin_area_branding_header',
            'aab_header_look_section',
			array(
				'label_for' => 'header_border_bottom_colour'
			) 
        );
		
		add_settings_field(
            'header_fixed',
            __('Set position \'fixed\'', 'djg-admin-area-branding'),
            array($this, '_option_header_fixed'),
            'djg_admin_area_branding_header',
            'aab_header_look_section',
			array(
				'label_for' => 'header_fixed'
			) 
        );
		
		
		/*-----------------------------------------------
		  Header logo settings
		-----------------------------------------------*/
		
		add_settings_section(
            'aab_header_logo_section',										// ID
            __('What logo do you want to use?', 'djg-admin-area-branding'),	// Title
            false,															// Callback
            'djg_admin_area_branding_header'								// Page
        );
		
		add_settings_field(
            'header_logo',									// ID
            __('Header Logo', 'djg-admin-area-branding'),	// Title
            array($this, '_option_header_logo'),			// Callback
            'djg_admin_area_branding_header',				// Page
            'aab_header_logo_section',						// Section
			array(											// Args
				'label_for' => 'header_logo'
			) 
        );
		
		add_settings_field(
            'header_logo_preview',
            __('Header Preview', 'djg-admin-area-branding'),
            array($this, '_option_header_logo_preview'),
            'djg_admin_area_branding_header',
            'aab_header_logo_section',
			array(
				'label_for' => 'header_logo_preview'
			) 
        );
		
		add_settings_field(
            'header_logo_margin',
            __('Header Logo Margin (px)', 'djg-admin-area-branding'),
            array($this, '_option_header_logo_margin'),
            'djg_admin_area_branding_header',
            'aab_header_logo_section',
			array(
				'label_for' => 'header_logo_margin'
			) 
        );
		
	}
	
	/**
	 * Render the page
	 */
	public function on_show_page(){
	
?>
		<div id="admin-area-branding-page" class="wrap admin-area-branding">
		
			<h2><?php _e('Admin Aread Branding - Header Settings', 'djg-admin-area-branding'); ?></h2>
			
			<form action="options.php" method="post">
			
				<?php settings_fields('admin_area_branding_group'); ?>
				<?php do_settings_sections('djg_admin_area_branding_header'); ?>
				<?php submit_button(); ?>
				
			</form>
		</div>
<?php
	}
	
	/**
	 * Sanitize the option on save
	 */
	public function on_save_settings($input){
	
		$this->set_defaults();			// Set the default options
		$this->set_select_options();	// Set the options available for each select
		$new_input = array();			// Create a new array to hold the sanitized options
		
		/** Background colour */
        if(isset($input['header_background_colour'])) :
			$colour = sanitize_text_field($input['header_background_colour']);
            $new_input['header_background_colour'] = ($colour !== '') ? $colour : $this->defaults['header_background_colour'];
		endif;
		
		/** Height */
        if(isset($input['header_height'])) :
			$value = absint($input['header_height']);
            $new_input['header_height'] = ($value !== '') ? $value : $this->defaults['header_height'];
		endif;
		
		/** Border bottom width */
        if(isset($input['header_border_bottom_width'])) :
			$value = absint($input['header_border_bottom_width']);
            $new_input['header_border_bottom_width'] = ($value !== '') ? $value : $this->defaults['header_border_bottom_width'];
		endif;
		
        /** Border bottom style */
		if(isset($input['header_border_bottom_style'])) :
            $new_input['header_border_bottom_style'] = (array_key_exists($input['header_border_bottom_style'], $this->select_options['border_style'])) ? $input['header_border_bottom_style'] : $this->defaults['header_border_bottom_style'];
		endif;
		
		/** Border bottom colour */
        if(isset($input['header_border_bottom_colour'])) :
			$colour = sanitize_text_field($input['header_border_bottom_colour']);
            $new_input['header_border_bottom_colour'] = ($colour !== '') ? $colour : $this->defaults['header_border_bottom_colour'];
		endif;
		
        return $new_input;
		
	}
	
	/**
	 * Set the $options, grabbed from the 'wp_options' DB table
	 */
	private function set_options(){
	
		$this->options = get_option('djg_admin_area_branding');
		
	}
	
	/**
	 * Set the default values, used if a value is not set when the 'on_show_page' or 'on_save_settings' methods are called
	 */
	private function set_defaults(){
	
		$this->dafaults = array(
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
			'header_logo_margin_left'		=> '0',
			'role_header'					=> 'manage_options'
		);
		
	}
	
	/**
	 * Set the options that are available for each of the <select> elements
	 *
	 * @param string $scope	The set of options to return
	 */
	private function set_select_options($scope = null){
	
		$this->select_options = array(
			'border_style'	=> array(
				'none'		=> __('None', 'djg-admin-area-branding'),
				'dashed'	=> __('Dashed', 'djg-admin-area-branding'),
				'dotted'	=> __('Dotted', 'djg-admin-area-branding'),
				'double'	=> __('Double', 'djg-admin-area-branding'),
				'groove'	=> __('Groove', 'djg-admin-area-branding'),
				'hidden'	=> __('Hidden', 'djg-admin-area-branding'),
				'inherit'	=> __('Inherit', 'djg-admin-area-branding'),
				'inset'		=> __('Inset', 'djg-admin-area-branding'),
				'outset'	=> __('Outset', 'djg-admin-area-branding'),
				'ridge'		=> __('Ridge', 'djg-admin-area-branding'),
				'solid'		=> __('Solid', 'djg-admin-area-branding')
			)
		);
		
	}
	
	/**
	 * The information to display for the Admin Area Branding header 'look' options
	 */
	public function do_info_look_section(){
	
		$para1 = __('These options relate to how the Admin Area Branding header looks.', 'djg-admin-area-branding');
		$para2 = __('You can control the background colour, height, border colour, style and width, and whether or not the header is fixed.', 'djg-admin-area-branding');
		
		printf('<p>%1$s</p>', $para1);
		printf('<p>%1$s</p>', $para2);
		
	}
	
	/**
	 * The information to display for the Admin Area Branding header 'logo' options
	 */
	public function do_info_logo_section(){
	
		$para1 = __('Here you can pick a logo (or choose to not have one) and set the margin around it.', 'djg-admin-area-branding');
		
		printf('<p>%1$s</p>', $para1);
		
	}
	
	/**
	 * Output an option of the $type specified
	 *
	 * @param required string $type	The type of option to output
	 * @parma required string $id	The ID of the option that is to be output
	 * @param array $args			The arguments to use for the option that is to be output
	 */
	private function do_option($type, $id, $args = array()){
	
		switch($type) :
		
			case 'text' :
				$this->do_option_text($id, $args);
				break;
			case 'checkbox' :
				$this->do_option_checkbox($id, $args);
				break;
			case 'hidden' :
				$this->do_option_hidden($id, $args);
				break;
			case 'select' :
				$this->do_option_select($id, $args);
				break;
		
		endswitch;
		
	}
	
	/**
	 * Output a text <input> option
	 *
	 * @parma required string $id	The ID of the option that is to be output
	 * @param array $args			The arguments to use for the option that is to be output
	 */
	private function do_option_text($id, $args = array()){
	
		$defaults = array(
			'name'		=> '',
			'value'		=> false,
			'class'		=> '',
			'description'	=> false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		
		printf(
			"\n\t".'<input type="text" id="%1$s" class="%2$s" name="%3$s" value="%4$s" />'."\n",
			$id,	/** %1$s - The ID of the input */
			$class,	/** %2$s - The class of the input */
			$name,	/** %3$s - The name of the select */
			$value	/** %4$s - The value of the option */
		);
		
		if($description) :
			printf(
				"\n\t".'<p class="description">%1$s</p>'."\n",
				$description	/** %1$s - A brief description of the option */
			);
		endif;
		
	}
	
	/**
	 * Output a text <input> option
	 *
	 * @parma required string $id	The ID of the option that is to be output
	 * @param array $args			The arguments to use for the option that is to be output
	 */
	private function do_option_checkbox($id, $args = array()){
	
		$defaults = array(
			'name'		=> '',
			'checked'	=> false,
			'value'		=> '1',
			'class'		=> '',
			'description'	=> false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		$checked = ($checked === true) ? 'checked="true"' : false;
		
		printf(
			"\n\t".'<input type="checkbox" id="%1$s" class="%2$s" name="%3$s" value="%4$s" %5$s />'."\n",
			$id,		/** %1$s - The ID of the input */
			$class,		/** %2$s - The class of the input */
			$name,		/** %3$s - The name of the input */
			$value,		/** %4$s - The value of the option */
			$checked	/** %5$s - Whether or not the checkbox should be checked */
		);
		
		if($description) :
			printf(
				"\n\t".'<p class="description">%1$s</p>'."\n",
				$description	/** %1$s - A brief description of the option */
			);
		endif;
		
	}
	
	/**
	 * Output a text <input> option
	 *
	 * @parma required string $id	The ID of the option that is to be output
	 * @param array $args			The arguments to use for the option that is to be output
	 */
	private function do_option_hidden($id, $args = array()){
	
		$$defaults = array(
			'name'		=> '',
			'value'		=> false,
			'class'		=> '',
			'description'	=> false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		$checked = ($checked === true) ? 'checked="true"' : false;
		
		printf(
			"\n\t".'<input type="hidden" id="%1$s" class="%2$s" name="%3$s" value="%4$s" />'."\n",
			$id,		/** %1$s - The ID of the input */
			$class,		/** %2$s - The class of the input */
			$name,		/** %3$s - The name of the input */
			$value		/** %4$s - The value of the option */
		);
		
	}
	
	/**
	 * Output a <select> option
	 *
	 * @parma required string $id	The ID of the option that is to be output
	 * @param array $args			The arguments to use for the option that is to be output
	 */
	private function do_option_select($id, $args = array()){
	
		$defaults = array(
			'name'			=> '',
			'options'		=> array(),
			'selected'		=> false,
			'class'			=> '',
			'optgroup'		=> 'Select an option',
			'description'	=> false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		
		if(!empty($options)) :
		
			printf(
				"\n\t".'<select id="%1$s" class="$2$s" name="%3$s">'."\n",
				$id,	/** %1$s - The ID of the select */
				$class,	/** %2$s - The class of the select */
				$name	/** %3$s - The name of the select */
			);
			
			if($optgroup) :
				printf(
					'<optgroup label="%1$s">',
					$optgroup	/** %1$s - The title of the Option Group for this set of options */
				);
			endif;
			
			foreach($options as $option => $text) :
			
				$is_selected = ($option === $selected) ? ' selected="true"' : false;
				printf(
					"\t\t".'<option value="%1$s"%2$s>%3$s</option>'."\n",
					$option,		/** %1$s - The option value */
					$is_selected,	/** %2$s - Whether or not the option is selected */
					$text			/** %3$s - The option text */
				);
				
			endforeach;
			
			if($optgroup) :
				echo '</optgroup>';
			endif;
			
			echo "\t".'</select>'."\n";
			
		endif;
		
		if($description) :
			printf(
				"\n\t".'<p class="description">%1$s</p>'."\n",
				$description	/** %1$s - A brief description of the option */
			);
		endif;
		
	}
	
	/**
	 * Callback for outputting the 'header_background_colour' field
	 */
	public function _option_header_background_colour(){
	
		$value = $this->get_value('header_background_colour');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',						// Option type
			'header_background_colour',	// ID
			array(						// Args
				'name'	=> 'djg_admin_area_branding[header_background_colour]',
				'value'	=> $value,
				'class'	=> 'regular-text colour-picker'
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_height' field
	 */
	public function _option_header_height(){
	
		$value = $this->get_value('header_height');	// Get the value currently saved for this option
		$tip1 = __('Tip', 'djg-admin-area-branding');
		$tip2 = __('Values can be between 0 and 99', 'djg-admin-area-branding');
		
		$this->do_option(
			'text',				// Option type
			'header_height',	// ID
			array(				// Args
				'name'			=> 'djg_admin_area_branding[header_height]',
				'value'			=> $value,
				'class'			=> 'small-text',
				'description'	=> sprintf('<strong>%1$s</strong>: %2$s', $tip1, $tip2)
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_border_bottom_width' field
	 */
	public function _option_header_border_bottom_width(){
	
		$value = $this->get_value('header_border_bottom_width');	// Get the value currently saved for this option
		$tip1 = __('Tip', 'djg-admin-area-branding');
		$tip2 = __('Values can be between 0 and 99', 'djg-admin-area-branding');
		
		$this->do_option(
			'text',							// Option type
			'header_border_bottom_width',	// ID
			array(							// Args
				'name'			=> 'djg_admin_area_branding[header_border_bottom_width]',
				'value'			=> $value,
				'class'			=> 'small-text',
				'description'	=> sprintf('<strong>%1$s</strong>: %2$s', $tip1, $tip2)
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_border_bottom_style' field
	 */
	public function _option_header_border_bottom_style(){
	
		$options = $this->select_options['border_style'];	// Get the valid options for this setting
		$selected = $this->get_value('header_border_bottom_style');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',						// Option type
			'header_border_bottom_style',	// ID
			array(							// Args
				'name'		=> 'djg_admin_area_branding[header_border_bottom_style]',
				'options'	=> $options,
				'selected'	=> $selected
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_border_bottom_colour' field
	 */
	public function _option_header_border_bottom_colour(){
	
		$value = $this->get_value('header_border_bottom_colour');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',							// Option type
			'header_border_bottom_colour',	// ID
			array(							// Args
				'name'	=> 'djg_admin_area_branding[header_border_bottom_colour]',
				'value'	=> $value,
				'class'	=> 'regular-text colour-picker'
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_fixed' field
	 */
	public function _option_header_fixed(){
	
		$value = $this->get_value('header_fixed');	// Get the value currently saved for this option
		$checked = ($value !== null) ? true : false;
		
		$this->do_option(
			'checkbox',		// Option type
			'header_fixed',	// ID
			array(			// Args
				'name'		=> 'djg_admin_area_branding[header_fixed]',
				'checked'	=> $checked,
				'class'		=> 'switch-yes-no'
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_logo' field
	 */
	public function _option_header_logo(){
	
		submit_button('Select Image', 'secondary', 'select-header-logo', false, array('class' => 'select-logo-button'));
?>
		<span id="remove-header-logo" class="remove-logo-button button-delete">Remove image</span>
		<span id="restore-header-logo" class="restore-logo-button button-delete">Restore Original Image</span>
<?php	
	}
	
	/**
	 * Callback for outputting the 'header_logo_preview' field
	 */
	public function _option_header_logo_preview(){
	
		$value = $this->get_value('header_logo');	// Get the value currently saved for this option
		
		echo '<div id="image-preview">';
		
		if($value === '0') :
			$message = __('No image selected', 'djg-admin-area-branding');
			printf('<span id="no-image">%1$s</span>', $message);
		else :
			djg_aab_do_image_preview($value);
		endif;
		
		echo '</div>';
		
		/** The current header logo ID */
		$this->do_option(
			'hidden',		// Option type
			'header_logo',	// ID
			array(			// Args
				'name'	=> 'djg_admin_area_branding[header_logo]',
				'value'	=> $value
			)
		);
		
		/** The ID of the header logo on page load */
		$this->do_option(
			'hidden',				// Option type
			'header_logo_on_load',	// ID
			array(					// Args
				'name'	=> 'header_logo_on_load',
				'value'	=> $value
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'header_logo_margin' field
	 */
	public function _option_header_logo_margin(){
	
		$value = $this->get_value('header_logo_margin_top');	// Get the value currently saved for this option
		$checked = ($value !== null) ? true : false;
		
		$this->do_option(
			'text',						// Option type
			'header_logo_margin_top',	// ID
			array(						// Args
				'name'	=> 'djg_admin_area_branding[header_logo_margin_top]',
				'value'	=> $value,
				'class'	=> 'small-text'
			)
		);
	
		$value = $this->get_value('header_logo_margin_right');	// Get the value currently saved for this option
		$checked = ($value !== null) ? true : false;
		
		$this->do_option(
			'text',						// Option type
			'header_logo_margin_right',	// ID
			array(						// Args
				'name'	=> 'djg_admin_area_branding[header_logo_margin_right]',
				'value'	=> $value,
				'class'	=> 'small-text'
			)
		);
	
		$value = $this->get_value('header_logo_margin_bottom');	// Get the value currently saved for this option
		$checked = ($value !== null) ? true : false;
		
		$this->do_option(
			'text',							// Option type
			'header_logo_margin_bottom',	// ID
			array(							// Args
				'name'	=> 'djg_admin_area_branding[header_logo_margin_bottom]',
				'value'	=> $value,
				'class'	=> 'small-text'
			)
		);
	
		$value = $this->get_value('header_logo_margin_left');	// Get the value currently saved for this option
		$checked = ($value !== null) ? true : false;
		$tip1 = __('Tip', 'djg-admin-area-branding');
		$tip2 = __('Top, right, bottom, left; values can be between -9 and 99', 'djg-admin-area-branding');
		
		$this->do_option(
			'text',						// Option type
			'header_logo_margin_left',	// ID
			array(						// Args
				'name'			=> 'djg_admin_area_branding[header_logo_margin_left]',
				'value'			=> $value,
				'class'			=> 'small-text',
				'description'	=> sprintf('<strong>%1$s</strong>: %2$s', $tip1, $tip2)
			)
		);
		
	}
	
	/**
	 * Dummy callback for outputtin nothing (required if the setting is to be registered but not output)
	 */
	public function _null(){
		return false;
	}
	
	/**
	 * Get the value of an option, checking first for a saved setting and then taking the default
	 *
	 * @param required string $option	The option to get a value for
	 * @return mixed					The value for the selected option
	 */
	private function get_value($option){
	
		return isset($this->options[$option]) ? $this->options[$option] : $this->dafaults[$option];
		
	}
	
}
?>