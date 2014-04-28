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
$abb_page = new ABB_Page();

/**
 * Admin Bar Button options page class
 */
class ABB_Page{
	
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
	
		add_action('wp_enqueue_scripts', array(&$this, 'on_wp_enqueue_scripts'), 5);	// Enqueue the necessary front end scripts/styeles
		add_action('wp_head', array(&$this, 'on_wp_head'));								// Output the necessary CSS/JS directly into the head of the front end
		add_action('admin_menu', array(&$this, 'on_admin_menu'));						// Add the Admin Bar Button options Settings menu
		add_action('admin_init', array(&$this, 'on_admin_init'));						// Register the settings that can be saved by this plugin
		
		$this->set_options();			// Set the currently saved options
		$this->set_defaults();			// Set the default options
		$this->set_select_options();	// Set the options available for each select
		
	}
	
	/**
	 * Enqueue the necessary front end scripts/styeles
	 */
	function on_wp_enqueue_scripts(){
		
		/** Enqueue the required scripts/styles */
		wp_enqueue_script('djg-admin-bar', plugins_url('adminBar.js?scope=admin-bar-button', __FILE__ ), array('jquery-ui-widget', 'jquery-effects-slide'));
		wp_enqueue_style('djg-admin-bar', plugins_url('adminBar.css?scope=admin-bar-button', __FILE__ ));
		
	}
	
	/**
	 * Output the necessary CSS/JS directly into the head of the front end
	 */
	public function on_wp_head(){
	
		if(is_user_logged_in()) :
?>
<script type="text/javascript">
/** The options to use for displaying the Admin Bar Button */
var djg_admin_bar_button = {
	text:				'<?php echo $this->get_value('text') ?>',
	text_direction:		'<?php echo $this->get_value('text_direction') ?>',
	button_position:	'<?php echo $this->get_value('button_position') ?>',
	button_direction:	'<?php echo $this->get_value('button_direction') ?>',
	button_duration:	<?php echo $this->get_value('button_duration') ?>,
	bar_direction:		'<?php echo $this->get_value('bar_direction') ?>',
	bar_duration:		<?php echo $this->get_value('bar_duration') ?>,
	show_time:			<?php echo $this->get_value('show_time') ?>
}
</script>
<?php
		else :
?>
<script type="text/javascript">
/** Don't display the Admin Bar Button (as no user is logged in) */
var djg_admin_bar_button = false
</script>
<?php
		endif;

	}
	
	/**
	 * Add the Admin Bar Button options Settings menu
	 */
	public function on_admin_menu(){
	
		add_options_page(
			__('Admin Bar Button Settings', 'djg-admin-bar-button'),	// Page title
			__('Admin Bar Button', 'djg-admin-bar-button'),				// Menu title
			'manage_options',											// Required capability
			'djg-admin-bar-button',										// Page slug
			array(&$this, 'on_show_page')								// Rendering callback
		);
		
	}
	
	/**
	 * Register the settings that can be saved by this plugin
	 */
	public function on_admin_init(){
	
		register_setting(
			'admin_bar_button_group',			// Group name
			'admin_bar_button',					// Option name
			array(&$this, 'on_save_settings')	// Sanatize options callback
		);
		
		
		/*-----------------------------------------------
		  Admin Bar Button settings
		-----------------------------------------------*/
		
		add_settings_section(
            'abb_button_section',										// ID
            __('How should the button work?', 'djg-admin-bar-button'),	// Title
            false,														// Callback
            'djg_admin_bar_button'										// Page
        );
		
		add_settings_field(
            'text',										// ID
            __('Button Text', 'djg-admin-bar-button'),	// Title
            array($this, '_option_button_text'),		// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'text'
			) 
        );
		
		add_settings_field(
            'text_direction',
            __('Text Direction', 'djg-admin-bar-button'),
            array($this, '_option_text_direction'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'text_direction'
			) 
        );
		
		add_settings_field(
            'button_position',
            __('Position on the Screen', 'djg-admin-bar-button'),
            array($this, '_option_button_position'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'button_position'
			) 
        );
		
		add_settings_field(
            'button_direction',
            __('Slide Direction', 'djg-admin-bar-button'),
            array($this, '_option_button_direction'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'button_direction'
			) 
        );
		
		add_settings_field(
            'button_duration (milliseconds)',
            __('Slide Duration', 'djg-admin-bar-button'),
            array($this, '_option_button_duration'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'button_duration'
			) 
        );
		
		
		/*-----------------------------------------------
		  Admin Bar settings
		-----------------------------------------------*/
		
		add_settings_section(
            'abb_bar_section',												// ID
            __('What about the Amdin Bar itself?', 'djg-admin-bar-button'),	// Title
            false,															// Callback
            'djg_admin_bar_button'											// Page
        );
		
		add_settings_field(
            'bar_direction',								// ID
            __('Slide Direction', 'djg-admin-bar-button'),	// Title
            array($this, '_option_bar_direction'),			// Callback
            'djg_admin_bar_button',							// Page
            'abb_bar_section',								// Section
			array(											// Args
				'label_for' => 'bar_direction'
			) 
        );
		
		add_settings_field(
            'bar_duration',
            __('Slide Duration (milliseconds)', 'djg-admin-bar-button'),
            array($this, '_option_bar_duration'),
            'djg_admin_bar_button',
            'abb_bar_section',
			array(
				'label_for' => 'bar_duration'
			) 
        );
		
		add_settings_field(
            'show_time',
            __('Show Time (milliseconds)', 'djg-admin-bar-button'),
            array($this, '_option_show_time'),
            'djg_admin_bar_button',
            'abb_bar_section',
			array(
				'label_for' => 'show_time'
			) 
        );
		
	}
	
	/**
	 * Render the plugin page
	 */
	public function on_show_page(){
	
?>
		<div id="admin-bar-button-page" class="wrap admin-bar-button">
		
			<h2><?php _e('Admin Bar Button Settings', 'djg-admin-bar-button'); ?></h2>
			
			<form action="options.php" method="post">
			
				<?php settings_fields('admin_bar_button_group'); ?>
				<?php do_settings_sections('djg_admin_bar_button'); ?>
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
		
		/** Button text */
        if(isset($input['text'])) :
			$text = sanitize_text_field($input['text']);
            $new_input['text'] = ($text !== '') ? $text : $this->defaults['text'];
		endif;
		
        /** Text direction */
		if(isset($input['text_direction'])) :
            $new_input['text_direction'] = (array_key_exists($input['text_direction'], $this->select_options['text_direction'])) ? $input['text_direction'] : $this->defaults['text_direction'];
		endif;
		
		/** Button position */
		if(isset($input['button_position'])) :
            $new_input['button_position'] = (array_key_exists($input['button_position'], $this->select_options['button_position'])) ? $input['button_position'] : $this->defaults['button_position'];
		endif;
		
		/** Button direction */
		if(isset($input['button_direction'])) :
            $new_input['button_direction'] = (array_key_exists($input['button_direction'], $this->select_options['button_direction'])) ? $input['button_direction'] : $this->defaults['button_direction'];
		endif;
		
		/** Button duration */
		if(isset($input['button_duration'])) :
			$time = absint($input['button_duration']);
			$new_input['button_duration'] = ($time > 0) ? $time : $this->defaults['button_duration'];
		endif;
		
		/** Bar direction */
		if(isset($input['bar_direction'])) :
            $new_input['bar_direction'] = (array_key_exists($input['bar_direction'], $this->select_options['bar_direction'])) ? $input['bar_direction'] : $this->defaults['bar_direction'];
		endif;
		
		/** Bar duration */
		if(isset($input['bar_duration'])) :
			$time = absint($input['bar_duration']);
			$new_input['bar_duration'] = ($time > 0) ? $time : $this->defaults['bar_duration'];
		endif;
		
		/** Show time */
		if(isset($input['show_time'])) :
			$time = absint($input['show_time']);
			$new_input['show_time'] = ($time > 0) ? $time : $this->defaults['show_time'];
		endif;
		
        return $new_input;
		
	}
	
	/**
	 * Set the $options, grabbed from the 'wp_options' DB table
	 */
	private function set_options(){
	
		$this->options = get_option('admin_bar_button');
		
	}
	
	/**
	 * Set the default values, used if a value is not set when the 'on_show_page' or 'on_save_settings' methods are called
	 */
	private function set_defaults(){
	
		$this->dafaults = array(
			'text'				=> __('Admin bar', 'djg-admin-bar-button'),
			'text_direction'	=> 'ltr',
			'button_position'	=> 'left',
			'button_direction'	=> 'left',
			'button_duration'	=> 500,
			'bar_direction'		=> 'right',
			'bar_duration'		=> 500,
			'show_time'			=> 5000
		);
		
	}
	
	/**
	 * Set the options that are available for each of the <select> elements
	 *
	 * @param string $scope	The set of options to return
	 */
	private function set_select_options($scope = null){
	
		$this->select_options = array(
			'text_direction'	=> array(
				'ltr'	=> __('Left to right', 'djg-admin-bar-button'),
				'rtl'	=> __('Right to left', 'djg-admin-bar-button')
			),
			'button_position'	=> array(
				'left'	=> __('Top left', 'djg-admin-bar-button'),
				'right'	=> __('Top right', 'djg-admin-bar-button')
			),
			'button_direction'	=> array(
				'up'	=> __('Slide up', 'djg-admin-bar-button'),
				'down'	=> __('Slide down', 'djg-admin-bar-button'),
				'left'	=> __('Slide left', 'djg-admin-bar-button'),
				'right'	=> __('Slide right', 'djg-admin-bar-button')
			),
			'bar_direction'	=> array(
				'up'	=> __('Slide up', 'djg-admin-bar-button'),
				'down'	=> __('Slide down', 'djg-admin-bar-button'),
				'left'	=> __('Slide left', 'djg-admin-bar-button'),
				'right'	=> __('Slide right', 'djg-admin-bar-button')
			)
		);
		
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
			'class'		=> ''
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
	 * Callback for outputting the 'text' option
	 */
	public function _option_button_text(){
	
		$value = $this->get_value('text');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'text',				// ID
			array(				// Args
				'name'			=> 'admin_bar_button[text]',
				'value'			=> $value,
				'class'			=> 'regular-text'
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'text_direction' option
	 */
	public function _option_text_direction(){
	
		$options = $this->select_options['text_direction'];	// Get the valid options for this setting
		$selected = $this->get_value('text_direction');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'text_direction',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[text_direction]',
				'options'		=> $options,
				'selected'		=> $selected
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'button_position' option
	 */
	public function _option_button_position(){
	
		$options = $this->select_options['button_position'];	// Get the valid options for this setting
		$selected = $this->get_value('button_position');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'button_position',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_position]',
				'options'		=> $options,
				'selected'		=> $selected
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'button_direction' option
	 */
	public function _option_button_direction(){
	
		$options = $this->select_options['button_direction'];	// Get the valid options for this setting
		$selected = $this->get_value('button_direction');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'button_direction',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_direction]',
				'options'		=> $options,
				'selected'		=> $selected,
				'description'	=> __('The side of the screen from which the Admin Bar Button will exit (and enter).', 'djg-admin-bar-button')
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'button_duration' option
	 */
	public function _option_button_duration(){
	
		$value = $this->get_value('button_duration');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'button_duration',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_duration]',
				'value'			=> $value,
				'class'			=> 'regular-text',
				'description'	=> __('The time that it takes for the Admin Bar Button to slide off of (and on to) the screen.', 'djg-admin-bar-button')
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'bar_direction' option
	 */
	public function _option_bar_direction(){
	
		$options = $this->select_options['bar_direction'];	// Get the valid options for this setting
		$selected = $this->get_value('bar_direction');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'bar_direction',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[bar_direction]',
				'options'		=> $options,
				'selected'		=> $selected,
				'description'	=> __('The side of the screen from which the Admin Bar will enter (and exit).', 'djg-admin-bar-button')
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'bar_duration' option
	 */
	public function _option_bar_duration(){
	
		$value = $this->get_value('bar_duration');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'bar_duration',		// ID
			array(				// Args
				'name'			=> 'admin_bar_button[bar_duration]',
				'value'			=> $value,
				'class'			=> 'regular-text',
				'description'	=> __('The time that it takes for the Admin Bar to slide on to (and off of) the screen.', 'djg-admin-bar-button')
			)
		);
		
	}
	
	/**
	 * Callback for outputting the 'show_time' option
	 */
	public function _option_show_time(){
	
		$value = $this->get_value('show_time');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'show_time',		// ID
			array(				// Args
				'name'			=> 'admin_bar_button[show_time]',
				'value'			=> $value,
				'class'			=> 'regular-text',
				'description'	=> __('The time that the Admin Bar will be visible for, when shown.', 'djg-admin-bar-button')
			)
		);
		
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