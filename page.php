<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Bar Button Plugin
 * @description:	Options page for the admin bar button
 * @since:			2.0
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
	 * The options available for each select dropdown
	 *
	 * @var array
	 */
	private $select_options = array();
	
	/**
	 * The page hook for this plugin
	 *
	 * @var string
	 */
	private $page_hook;
	
	/**
	 * The properties of the current screen that is being displayed
	 *
	 * @var object
	 */
	private $screen;
	
	/**
	 * Constructor
	 */
	public function __construct(){
	
		add_action('after_setup_theme', array(&$this, 'after_setup_theme'));			// Add the necessary theme support
		add_action('wp_enqueue_scripts', array(&$this, 'on_wp_enqueue_scripts'));		// Enqueue the necessary front end scripts/styeles
		add_action('wp_head', array(&$this, 'on_wp_head'));								// Output the necessary CSS/JS directly into the head of the front end
		add_action('admin_menu', array(&$this, 'on_admin_menu'));						// Add the Admin Bar Button options Settings menu
		add_action('admin_init', array(&$this, 'on_admin_init'));						// Register the settings that can be saved by this plugin
		
		$this->set_options();			// Set the currently saved options
		$this->set_defaults();			// Set the default options
		$this->set_select_options();	// Set the options available for each select
		
	}
	
	/**
	 * Add the necessary theme support
	 */
	public function after_setup_theme(){
	
		/** Set the CSS to remove the space typically alocated to the admin bar */
		add_theme_support('admin-bar', array('callback' => array(&$this, 'on_admin_bar')));
		
	}
	
	/**
	 * Set the CSS to remove the space typically alocated to the admin bar
	 */
	public function on_admin_bar(){
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
	
	/**
	 * Enqueue the necessary front end scripts/styles
	 */
	public function on_wp_enqueue_scripts(){
		
		/** Enqueue the required scripts/styles */
		wp_enqueue_script('djg-admin-bar-front', plugins_url('adminBar-front.js?scope=admin-bar-button', __FILE__ ), array('jquery-ui-widget', 'jquery-effects-slide'));
		wp_enqueue_style('djg-admin-bar-front', plugins_url('adminBar-front.css?scope=admin-bar-button', __FILE__ ));
		
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
	button_activate:	'<?php echo $this->get_value('button_activate') ?>',
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
	
		$this->page_hook = add_options_page(
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
	
		add_action('load-'.$this->page_hook, array(&$this, 'on_admin_load'));							// Set information that can only be gathered once the page has loaded
		
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
            __('Admin Bar Button Settings', 'djg-admin-bar-button'),	// Title
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
            'button_activate',
            __('Button Activated On', 'djg-admin-bar-button'),
            array($this, '_option_button_activate'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'button_activate'
			) 
        );
		
		add_settings_field(
            'button_duration',
            __('Slide Duration (milliseconds)', 'djg-admin-bar-button'),
            array($this, '_option_button_duration'),
            'djg_admin_bar_button',
            'abb_button_section',
			array(
				'label_for' => 'button_duration'
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
		
		
		/*-----------------------------------------------
		  Admin Bar settings
		-----------------------------------------------*/
		
		add_settings_section(
            'abb_bar_section',											// ID
            __('WordPress Admin Bar Settings', 'djg-admin-bar-button'),	// Title
            false,														// Callback
            'djg_admin_bar_button'										// Page
        );
		
		add_settings_field(
            'bar_duration',													// ID
            __('Slide Duration (milliseconds)', 'djg-admin-bar-button'),	// Title
            array($this, '_option_bar_duration'),							// Callback
            'djg_admin_bar_button',											// Page
            'abb_bar_section',												// Section
			array(															// Args
				'label_for' => 'bar_duration'
			) 
        );
		
		add_settings_field(
            'bar_direction',
            __('Slide Direction', 'djg-admin-bar-button'),
            array($this, '_option_bar_direction'),
            'djg_admin_bar_button',
            'abb_bar_section',
			array(
				'label_for' => 'bar_direction'
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
	 * Grab the current screen and add contextual help
	 */
	public function on_admin_load(){
	
		add_action('admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts'));				// Enqueue the necessary admin scripts/styeles
		add_action('admin_print_styles-'.$this->page_hook, array(&$this, 'on_admin_print_styles'));	// Print the necessary admin styles
		
		$this->screen = get_current_screen();	// Grab the current screen
		
		$this->screen->set_help_sidebar(
			'<p>'.
				'<strong>' . __('For more information', 'djg-admin-bar-button') . ':</strong>'.
			'</p>'.
			'<p>'.
				'<a href="http://wordpress.org/plugins/admin-bar-button/" title="' .  esc_attr__('Admin Bar Button', 'djg-admin-bar-button') . '">' . __('Visit the Plugin Page', 'djg-admin-bar-button') . '</a>'. 
			'</p>'
		);
		
		$this->screen->add_help_tab(array(
			'id'		=> 'description',
			'title'		=> __('Description'),
			'content'	=>
				'<p>'.
					__('Admin Bar Button is a plugin that will create a simple button to replace the default WordPress admin bar on the front end. ', 'djg-admin-bar-button').
					__('When using this plugin, the full height of the page is used by your site, which is particularly handy if you have fixed headers.', 'djg-admin-bar-button').
				'</p>'.
				'<p>'.
					__('Please see the ', 'djg-admin-bar-button').
					'<a href="http://wordpress.org/plugins/admin-bar-button/screenshots/" title="' .  esc_attr__('Admin Bar Button &raquo; Screenshots', 'djg-admin-bar-button') . '">' . __('these screenshots', 'djg-admin-bar-button') . '</a>'. 
					__(' to see how the Admin Bar Button looks.', 'djg-admin-bar-button').
				'</p>'
		));
		
		$this->screen->add_help_tab(array(
			'id'		=> 'faq',
			'title'		=> __('FAQ'),
			'content'	=>
				'<h3>' . __('What do all of the options mean?', 'djg-admin-bar-button') . '</h3>'.
				'<p><strong><em>' . __('The Admin Bar Button, added by this plugin', 'djg-admin-bar-button') . '</em></strong></p>'.
				'<ul>'.
					'<li><strong>' . __('Button Text', 'djg-admin-bar-button') . '</strong>: ' . __('The text to display in the Admin Bar Button. You can set this to anything you want, the button will resize appropriately.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Text Direction', 'djg-admin-bar-button') . '</strong>: ' . __('The direction of the Admin Bar Button text. Default is left-to-right, but you can use right-to-left if appropriate for you language.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Position on the Screen', 'djg-admin-bar-button') . '</strong>: ' . __('Where on the screen to position the Admin Bar Button. You can place the button in any of the four corners. If you choose \'Bottom left\' or \'Bottom right\' then the WordPress Admin Bar will also be shown on the bottom of the screen.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Button Activated On', 'djg-admin-bar-button') . '</strong>: ' . __('The actions that will activate the Admin Bar. Currently you can choose between when the user clicks the button, when they hover over it, or both.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Duration', 'djg-admin-bar-button') . '</strong>: ' . __('The time (in milliseconds) that it takes for the Admin Bar Button to slide off of the screen (and back on to it when the WordPress Admin Bar is hidden again). Any positive value is acceptable, and setting it to \'0\' will disable the animation.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Direction', 'djg-admin-bar-button') . '</strong>: ' . __('The direction from which the Admin Bar Button will slide off of the screen (and back on to it when the WordPress Admin Bar is hidden again). This option is irrelevant and so ignored if \'Slide Duration\' is set to \'0\'.', 'djg-admin-bar-button') .'</li>'.
				'</ul>'.
				'<p><strong><em>' . __('The WordPress Admin Bar', 'djg-admin-bar-button') . '</em></strong></p>'.
				'<ul>'.
					'<li><strong>' . __('Slide Duration', 'djg-admin-bar-button') . '</strong>: ' . __('The time (in milliseconds) that it takes for the WordPress Admin Bar to slide on to the screen (and back off of it when the Admin Bar Button is shown again). Any positive value is acceptable, and setting it to \'0\' will disable the animation.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Direction', 'djg-admin-bar-button') . '</strong>: ' . __('The direction from which the WordPress Admin Bar will slide on to the screen (and back off of it when the Admin Bar Button is shown again). This option is irrelevant and so ignored if \'Slide Duration\' is set to \'0\'.', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Show Time', 'djg-admin-bar-button') . '</strong>: ' . __('The time (in milliseconds) that the Admin Bar will be visible for, when shown. The minimum time is 2000 (2 seconds), and setting this option to less than that will result in the default being used.', 'djg-admin-bar-button') .'</li>'.
				'</ul>'.
				
				'<h3>' . __('What do all of the options mean?', 'djg-admin-bar-button') . '</h3>'.
				'<p><strong><em>' . __('The Admin Bar Button, added by this plugin', 'djg-admin-bar-button') . '</em></strong></p>'.
				'<ul>'.
					'<li><strong>' . __('Button Text', 'djg-admin-bar-button') . '</strong>: ' . __('Admin bar', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Text Direction', 'djg-admin-bar-button') . '</strong>: ' . __('Left to right', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Position on the Screen', 'djg-admin-bar-button') . '</strong>: ' . __('Top left', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Button Activated On', 'djg-admin-bar-button') . '</strong>: ' . __('Hover and click', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Duration', 'djg-admin-bar-button') . '</strong>: ' . __('500 milliseconds (0.5 seconds)', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Direction', 'djg-admin-bar-button') . '</strong>: ' . __('Left', 'djg-admin-bar-button') .'</li>'.
				'</ul>'.
				'<p><strong><em>' . __('The WordPress Admin Bar', 'djg-admin-bar-button') . '</em></strong></p>'.
				'<ul>'.
					'<li><strong>' . __('Slide Duration', 'djg-admin-bar-button') . '</strong>: ' . __('500 milliseconds (0.5 seconds)', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Slide Direction', 'djg-admin-bar-button') . '</strong>: ' . __('Right', 'djg-admin-bar-button') .'</li>'.
					'<li><strong>' . __('Show Time', 'djg-admin-bar-button') . '</strong>: ' . __('5000 milliseconds (5 seconds)', 'djg-admin-bar-button') .'</li>'.
				'</ul>'.
				
				'<h3>' . __('Can I prevent the Admin Bar Button and/or the Admin Bar being animated when it is shown or hidden?', 'djg-admin-bar-button') . '</h3>'.
				'<p>'.
					__('Yes, you simply have to set the ', 'djg-admin-bar-button').
					'<strong>' . __('Slide Duration', 'djg-admin-bar-button') . '</strong>'.
					__(' option to ', 'djg-admin-bar-button').
					'<strong>' . __('0', 'djg-admin-bar-button') . '</strong>'.
					__('. There is a separate option for both the ', 'djg-admin-bar-button').
					'<strong>' . __('Admin Bar Button', 'djg-admin-bar-button') . '</strong>'.
					__(' and the ', 'djg-admin-bar-button').
					'<strong>' . __('WordPress Admin Bar', 'djg-admin-bar-button') . '</strong>'.
					__(', so you can animate only one or the other if you so chose.', 'djg-admin-bar-button').
				'</p>'.
				
				'<h3>' . __('Can I restore the default settings?', 'djg-admin-bar-button') . '</h3>'.
				'<p>'.
					__('Of course. Simply visit the ', 'djg-admin-bar-button').
					'<strong>' . __('Settings', 'djg-admin-bar-button') . '</strong>'.
					__(' page (', 'djg-admin-bar-button').
					'<em>' . __('Settings &raquo; Admin Bar Button', 'djg-admin-bar-button') . '</em>'.
					__('), scroll to the bottom and click Restore Defaults. ', 'djg-admin-bar-button').
					__('You\'ll be asked to confirm that you wish to do this, and then all of the defaults will be restored.', 'djg-admin-bar-button').
				'</p>'
				
		));
		
		$this->screen->add_help_tab(array(
			'id'		=> 'support',
			'title'		=> __('Support'),
			'content'	=>
				'<p>'.
					__('If you find a bug with this plugin please report it on the ', 'djg-admin-bar-button').
					'<a href="http://wordpress.org/support/plugin/admin-bar-button" title="' . esc_attr__('Admin Bar Button &raquo; Support', 'djg-admin-bar-button') . '">' . __('plugin support page', 'djg-admin-bar-button') . '</a>.'. 
				'</p>'.
				'<p>'.
					__('General comments, gripes and requests relating to this plugin are also welcome.', 'djg-admin-bar-button').
				'</p>'
		));
		
	}
	
	/**
	 * Enqueue the necessary admin scripts/styles
	 */
	public function on_admin_enqueue_scripts(){
	
		/** Enqueue the required scripts/styles */
		wp_enqueue_script('djg-admin-bar-admin', plugins_url('adminBar-admin.js?scope=admin-bar-button', __FILE__ ), array('jquery-ui-widget', 'jquery-effects-slide'));
		
	}
	
	/**
	 * Enqueue the necessary admin styles
	 */
	public function on_admin_print_styles(){
?>
<style>
.tip{
	font-style:	italic;
	margin-left: 10px;
}
</style>
<?php
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
				<p>
					<?php submit_button('Save Changes', 'primary', 'submit', false); ?>
					<?php submit_button('Restore Defaults', 'delete', 'delete', false); ?>
				</p>
				
			</form>
		</div>
<?php
	}
	
	/**
	 * Sanitize the option on save
	 */
	public function on_save_settings($input){
	
		/** Check to see if the options should be restored to default */
		if(isset($_POST['delete'])) :
			delete_option('admin_bar_button');
			return;
		endif;
		
		if(!isset($_POST['submit'])) return;	// Ensure the user is supposed to be here
		
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
		
		/** Button activate */
		if(isset($input['button_activate'])) :
            $new_input['button_activate'] = (array_key_exists($input['button_activate'], $this->select_options['button_activate'])) ? $input['button_activate'] : $this->defaults['button_activate'];
		endif;
		
		/** Button duration */
		if(isset($input['button_duration'])) :
			$time = absint($input['button_duration']);
			$new_input['button_duration'] = ($time >= 0) ? $time : $this->defaults['button_duration'];
		endif;
		
		/** Button direction */
		if(isset($input['button_direction'])) :
            $new_input['button_direction'] = (array_key_exists($input['button_direction'], $this->select_options['button_direction'])) ? $input['button_direction'] : $this->defaults['button_direction'];
		endif;
		
		/** Bar duration */
		if(isset($input['bar_duration'])) :
			$time = absint($input['bar_duration']);
			$new_input['bar_duration'] = ($time >= 0) ? $time : $this->defaults['bar_duration'];
		endif;
		
		/** Bar direction */
		if(isset($input['bar_direction'])) :
            $new_input['bar_direction'] = (array_key_exists($input['bar_direction'], $this->select_options['bar_direction'])) ? $input['bar_direction'] : $this->defaults['bar_direction'];
		endif;
		
		/** Show time */
		if(isset($input['show_time'])) :
			$time = absint($input['show_time']);
			$new_input['show_time'] = ($time > 2000) ? $time : $this->defaults['show_time'];
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
			'button_position'	=> 'top-left',
			'button_activate'	=> 'both',
			'button_duration'	=> 500,
			'button_direction'	=> 'left',
			'bar_duration'		=> 500,
			'bar_direction'		=> 'right',
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
				'top-left'		=> __('Top left', 'djg-admin-bar-button'),
				'top-right'		=> __('Top right', 'djg-admin-bar-button'),
				'bottom-left'	=> __('Bottom left', 'djg-admin-bar-button'),
				'bottom-right'	=> __('Bottom right', 'djg-admin-bar-button')
			),
			'button_activate'	=> array(
				'both'	=> __('Click and hover', 'djg-admin-bar-button'),
				'click'	=> __('Click', 'djg-admin-bar-button'),
				'hover'	=> __('Hover', 'djg-admin-bar-button')
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
		
		$this->do_tip($tip);
		$this->do_description($description);
		
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
			'description'	=> false,
			'tip'			=> false
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
		
		$this->do_tip($tip);
		$this->do_description($description);
		
	}
	
	/**
	 * Output a tip next do an option
	 *
	 * @since 2.2
	 * @param required mixed $tip	The tip to output
	 */
	private function do_tip($tip){
	
		if(is_array($tip)) :
			printf(
				"\n\t".'<span class="tip"><strong>%1$s:</strong> %2$s</span>'."\n",
				$tip[0],	/** %1$s - The tip prefix */
				$tip[1]		/** %2$s - The tip to display */
			);
		elseif(is_string($tip)) :
			printf(
				"\n\t".'<span class="tip">%1$s</span>'."\n",
				$tip	/** %1$s - The tip to display */
			);
		endif;
		
	}
	
	/**
	 * Output a description underneath an option
	 *
	 * @since 2.2
	 * @param required mixed $description	The description to output
	 */
	private function do_description($description){
	
		if(is_string($description)) :
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
	 * Callback for outputting the 'button_activate' option
	 */
	public function _option_button_activate(){
	
		$options = $this->select_options['button_activate'];	// Get the valid options for this setting
		$selected = $this->get_value('button_activate');		// Get the value currently selected for this option
		
		$this->do_option(
			'select',			// Option type
			'button_activate',	// ID
			array(				// Args
				'name'			=> 'admin_bar_button[button_activate]',
				'options'		=> $options,
				'selected'		=> $selected,
				'description'	=> __('The actions will activate the Admin Bar.', 'djg-admin-bar-button')
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
				'description'	=> __('The side of the screen from which the Admin Bar Button will exit (and enter).', 'djg-admin-bar-button'),
				'tip'			=> array(
					__('Tip', 'djg-admin-bar-button'),
					__('Ignored if Admin Bar Button Slide Duration is \'0\'', 'djg-admin-bar-button')
				)
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
				'description'	=> __('The side of the screen from which the Admin Bar will enter (and exit).', 'djg-admin-bar-button'),
				'tip'			=> array(
					__('Tip', 'djg-admin-bar-button'),
					__('Ignored if Admin Bar Slide Duration is \'0\'', 'djg-admin-bar-button')
				)
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