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

$abb_page = new ABB_Page();

/**
 * Admin Bar Button options
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
	
		add_action('wp_head', array(&$this, 'on_wp_head'));			// Output the option values into the header
		add_action('admin_menu', array(&$this, 'on_admin_menu'));	// Admin menu setup
		add_action('admin_init', array(&$this, 'on_admin_init'));	// Register the admin settings
		
		$this->set_options();			// Set the currently saved options
		$this->set_defaults();			// Set the default options
		$this->set_select_options();	// Set the options available for each select
		
	}
	
	public function on_wp_head(){
?>
<script type="text/javascript">
/** The options to use for displaying the Admin Bar Button */
var djg_admin_bar_button_options = {
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
	}
	
	/**
	 * Add the Admin Bar Button options admin menu
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
			'admin_bar_button_group',	// Group name
			'admin_bar_button',			// Option name
			array(&$this, 'on_save_settings')	// Sanatize options callback
		);
		
		
		/**
		 * Admin Bar Button settings
		 */
		add_settings_section(
            'abb_button_section',										// ID
            __('How should the button work?', 'djg-admin-bar-button'),	// Title
            array($this, 'do_button_section_info'),						// Callback
            'djg_admin_bar_button'										// Page
        );
		
		add_settings_field(
            'text',										// ID
            'Button Text',								// Title
            array($this, '_option_button_text'),		// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'text'
			) 
        );
		
		add_settings_field(
            'text_direction',							// ID
            'Text Direction',					// Title
            array($this, '_option_text_direction'),		// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'text_direction'
			) 
        );
		
		add_settings_field(
            'button_position',							// ID
            'Position on the Screen',					// Title
            array($this, '_option_button_position'),	// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'button_position'
			) 
        );
		
		add_settings_field(
            'button_direction',							// ID
            'Direction',								// Title
            array($this, '_option_button_direction'),	// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'button_direction'
			) 
        );
		
		add_settings_field(
            'button_duration',							// ID
            'Duration',									// Title
            array($this, '_option_button_duration'),	// Callback
            'djg_admin_bar_button',						// Page
            'abb_button_section',						// Section
			array(										// Args
				'label_for' => 'button_duration'
			) 
        );
		
		
		/**
		 * Admin Bar settings
		 */
		add_settings_section(
            'abb_bar_section',												// ID
            __('What about the Amdin Bar itself?', 'djg-admin-bar-button'),	// Title
            array($this, 'do_bar_section_info'),							// Callback
            'djg_admin_bar_button'											// Page
        );
		
		add_settings_field(
            'bar_direction',							// ID
            'Direction',								// Title
            array($this, '_option_bar_direction'),		// Callback
            'djg_admin_bar_button',						// Page
            'abb_bar_section',							// Section
			array(										// Args
				'label_for' => 'bar_direction'
			) 
        );
		
		add_settings_field(
            'bar_duration',								// ID
            'Duration',									// Title
            array($this, '_option_bar_duration'),		// Callback
            'djg_admin_bar_button',						// Page
            'abb_bar_section',							// Section
			array(										// Args
				'label_for' => 'bar_duration'
			) 
        );
		
		
		/**
		 * General settings
		 */
		add_settings_section(
            'abb_general_section',														// ID
            __('And finnally just one general settings...', 'djg-admin-bar-button'),	// Title
            array($this, 'do_general_section_info'),									// Callback
            'djg_admin_bar_button'														// Page
        );
		
		add_settings_field(
            'show_time',								// ID
            'Show Time',								// Title
            array($this, '_option_show_time'),			// Callback
            'djg_admin_bar_button',						// Page
            'abb_general_section',						// Section
			array(										// Args
				'label_for' => 'show_time'
			) 
        );
		
	}
	
	/**
	 * Render the page
	 */
	public function on_show_page(){
	
?>
		<div id="admin-bar-button-page" class="wrap admin-bar-button">
		
			<h2><?php _e('Admin Bar Button Settings', 'djg-admin-bar-button'); ?></h2>
			
			<?php //$this->splash_message() ?>
			
			<form action="options.php" method="post">
			
				<?php settings_fields('admin_bar_button_group'); ?>
				<?php do_settings_sections('djg_admin_bar_button'); ?>
				<?php submit_button(); ?>
				
			</form>
		</div>
<?php
	}
	
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
	
	private function set_options(){
	
		$this->options = get_option('admin_bar_button');
		
	}
	
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
	
	public function do_button_section_info(){
	
		echo '<p>These options relate to the Admin Bar Button that is shown in place of the Admin Bar.</p>';
		echo '<p>You can control what the button text says and the text direction, as well as where the button is positioned how it\'s hidden and how long it takes to hide.</p>';
		
	}
	
	public function do_bar_section_info(){
	
		echo '<p>These options relate to how the Admin Bar is shown and how long it takes to show.</p>';
		
	}
	
	public function do_general_section_info(){
	
		echo '<p>Here you can set some general options, such as how long the Admin Bar remains visible for when shown.</p>';
		
	}
	
	private function do_option($type, $id, $args = array()){
	
		switch($type) :
		
			case 'text' :
				$this->do_text_input($id, $args);
				break;
			case 'select' :
				$this->do_select($id, $args);
				break;
		
		endswitch;
		
	}
	
	private function do_text_input($id, $args = array()){
	
		$defaults = array(
			'name'		=> '',
			'value'		=> false
		);
		$args = wp_parse_args($args, $default);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		
		printf(
			"\n\t".'<input type="text" id="%1$s" class="regular-text" name="%2$s" value="%3$s" />'."\n",
			$id,	/** %1$s - The ID of the select */
			$name,	/** %2$s - The name of the select */
			$value	/** %3$s - The value of the option */
		);
		
		if(isset($description)) :
			printf(
				"\n\t".'<p class="description">%1$s</p>'."\n",
				$description	/** %1$s - A brief description of the option */
			);
		endif;
		
	}
	
	private function do_select($id, $args = array()){
	
		$defaults = array(
			'name'		=> '',
			'options'	=> array(),
			'selected'	=> false,
		);
		$args = wp_parse_args($args, $default);
		extract($args, EXTR_OVERWRITE);
		
		$name = ($name !== '') ? $name : $id;
		
		if(!empty($options)) :
		
			printf(
				"\n\t".'<select id="%1$s" class="regular-text" name="%2$s">'."\n",
				$id,	/** %1$s - The ID of the select */
				$name	/** %2$s - The name of the select */
			);
			
			foreach($options as $option => $text) :
			
				$is_selected = ($option === $selected) ? ' selected="true"' : false;
				printf(
					"\t\t".'<option value="%1$s"%2$s>%3$s</option>'."\n",
					$option,		/** %1$s - The option value */
					$is_selected,	/** %2$s - Whether or not the option is selected */
					$text			/** %3$s - The option text */
				);
				
			endforeach;
			
			echo "\t".'</select>'."\n";
			
		endif;
		
		if(isset($description)) :
			printf(
				"\n\t".'<p class="description">%1$s</p>'."\n",
				$description	/** %1$s - A brief description of the option */
			);
		endif;
		
	}
	
	public function _option_button_text(){
	
		$value = $this->get_value('text');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'text',				// ID
			array(				// Args
				'name'			=> 'admin_bar_button[text]',
				'value'			=> $value
			)
		);
		
	}
	
	public function _option_text_direction(){
	
		$options = $this->select_options['text_direction'];	// Get the valid options for this setting
		$selected = $this->get_value('text_direction');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'text_direction',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[text_direction]',
				'options'		=> $options,
				'selected'		=> $selected,
				'description'	=> 'The direction of the Admin Bar Button text.'
			)
		);
		
	}
	
	public function _option_button_position(){
	
		$options = $this->select_options['button_position'];	// Get the valid options for this setting
		$selected = $this->get_value('button_position');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'button_position',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_position]',
				'options'		=> $options,
				'selected'		=> $selected,
				'description'	=> 'The position on the screen of the Admin Bar Button.'
			)
		);
		
	}
	
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
				'description'	=> 'The side of the screen from which the Admin Bar Button will exit (and enter).'
			)
		);
		
	}
	
	public function _option_button_duration(){
	
		$value = $this->get_value('button_duration');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'button_duration',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_duration]',
				'value'			=> $value,
				'description'	=> 'The time (in miliseconds) that it taks for the Admin Bar Button to slide off of (and on to) the screen.'
			)
		);
		
	}
	
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
				'description'	=> 'The side of the screen from which the Admin Bar will enter (and exit).'
			)
		);
		
	}
	
	public function _option_bar_duration(){
	
		$value = $this->get_value('bar_duration');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'bar_duration',		// ID
			array(				// Args
				'name'			=> 'admin_bar_button[bar_duration]',
				'value'			=> $value,
				'description'	=> 'The time (in miliseconds) that it taks for the Admin Bar to slide on to (and off of) the screen.'
			)
		);
		
	}
	
	public function _option_show_time(){
	
		$value = $this->get_value('show_time');	// Get the value currently saved for this option
		
		$this->do_option(
			'text',				// Option type
			'show_time',		// ID
			array(				// Args
				'name'			=> 'admin_bar_button[show_time]',
				'value'			=> $value,
				'description'	=> 'The time (in miliseconds) that the Admin Bar will be visible for, when shown.'
			)
		);
		
	}
	
	private function get_value($field){
	
		return isset($this->options[$field]) ? $this->options[$field] : $this->dafaults[$field];
		
	}
	
}


?>