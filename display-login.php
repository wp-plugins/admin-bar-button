<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @description:	Customised admin area login file
 */

/**
 * Set up the custom admin login
 */
add_action('login_enqueue_scripts', 'aab_do_login');
function aab_do_login(){
	
	/** Create an instance of the AAB_Login class */
	$aab_login = new AAB_Login();

}

/**
 * AAB_Login class
 */
class AAB_Login{
	
	private $options = null;
	private $login_enabled = false;
	
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
		
		/** Check to see if the custom admin footer is enabled */
		$login_enabled = $this->options['show_custom_login'];
		
		/** Check to see if the custom admin login css should be output */
		if($login_enabled) :
			add_action('login_head', array($this, '_custom_login_css'));
		endif;
		
	}

	/**
	 * Callback for outputting the current custom admin login CSS
	 */
	public function _custom_login_css(){
	
		/** Set up the custom admin login image for display */
		if(isset($this->options['login_logo']) && $this->options['login_logo'] !== '0') :
			$logo = wp_get_attachment_image_src($this->options['login_logo'], 'full');
			$img = esc_url($logo['0']);
		else :
			$img = esc_url(get_bloginfo('siteurl').'/wp-admin/images/wordpress-logo.png?ver=20120216');
		endif;
		
		/** Work out if the display of the 'Register' and 'Lost your password' links, and the 'Back to blog' link are in the same position (as defined) */
		$displays_equal = aab_are_displays_equal($this->options, array('top', 'bottom'));
		
?>
		<style>
			/*------------------------------------------------------------------------------
			  1.0 - Login form container
			------------------------------------------------------------------------------*/
			
			#login{
				margin:		auto;
				padding:	140px 0 0
				width:		320px;
			}
			
			#login h1 a{
				background-image:		url('<?php echo $img; ?>');
				background-position:	center top;
				background-repeat:		no-repeat;
				background-size:		274px 63px;
				display:				block;
				height:					67px;
				margin-bottom:			25px;
				outline:				medium none;
				overflow:				hidden;
				padding-bottom:			15px;
				text-indent:			-9999px;
				width:					326px;
			}
			
			
			/*------------------------------------------------------------------------------
			  2.0 - Navigation
			------------------------------------------------------------------------------*/
			
			#login #nav{
				background-color:	<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? 'transparent' : $this->options['login_nav_background_colour']; ?>;
				display:			<?php echo ($this->options['login_nav_link_locatoin'] === 'none') ? 'none' : 'block'; ?>;
				margin:				<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '0 0 0 16px' : '0'; ?>;
				padding:			<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '16px 16px 0' : '10px 20px'; ?>;
				text-shadow:		<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '0 1px 0 #FFFFFF' : 'none'; ?>;
				position:			<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? 'inherit' : 'absolute'; ?>;
				color:				<?php echo $this->options['login_nav_text_colour']; ?>;
				left:				<?php echo ($displays_equal) ? 'auto' : '0'; ?>;
				right:				<?php echo ($displays_equal) ? '0' : 'auto'; ?>;
				top:				<?php echo ($this->options['login_nav_link_locatoin'] === 'top') ? '0' : 'auto'; ?>;
				bottom:				<?php echo ($this->options['login_nav_link_locatoin'] === 'bottom') ? '0' : 'auto'; ?>;
				width:				<?php echo ($displays_equal || $this->options['login_nav_link_locatoin'] === 'box') ? 'auto' : '100%'; ?>;
				z-index:			10;
				
			}
			#login #nav a{
				color:				<?php echo $this->options['login_nav_text_colour']; ?> !important;
				text-decoration:	<?php echo ($this->options['login_nav_underline_link']) ? 'underline' : 'none'; ?> !important;
			}
			#login #nav a:hover{
				color:				<?php echo $this->options['login_nav_text_colour_hover']; ?> !important;
				text-decoration:	<?php echo ($this->options['login_nav_underline_link_hover']) ? 'underline' : 'none'; ?> !important;
			}
			
			
			/*------------------------------------------------------------------------------
			  3.0 - Back to blog
			------------------------------------------------------------------------------*/
			
			#login #backtoblog{
				background-color:	<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? 'transparent' : $this->options['login_back_to_blog_background_colour']; ?>;
				display:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'none') ? 'none' : 'block'; ?>;
				margin:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '0 0 0 16px' : '0'; ?>;
				padding:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '16px 16px 0' : '10px 20px'; ?>;
				text-shadow:		<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '0 1px 0 #FFFFFF' : 'none'; ?>;
				position:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? 'inherit' : 'absolute'; ?>;
				left:				0;
				top:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'top') ? '0' : 'auto'; ?>;
				bottom:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'bottom') ? '0' : 'auto'; ?>;
				width:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] !== 'top' && $this->options['login_back_to_blog_link_locatoin'] !== 'bottom') ? 'auto' : '100%'; ?>;
				z-index:			5;
			}
			#login #backtoblog a{
				color:				<?php echo $this->options['login_back_to_blog_text_colour']; ?> !important;
				text-decoration:	<?php echo ($this->options['login_back_to_blog_underline_link']) ? 'underline' : 'none'; ?> !important;
			}
			#login #backtoblog a:hover{
				color:				<?php echo $this->options['login_back_to_blog_text_colour_hover']; ?> !important;
				text-decoration:	<?php echo ($this->options['login_back_to_blog_underline_link_hover']) ? 'underline' : 'none'; ?> !important;
			}
		</style>
<?php
	}
	
}

/**
 * Set up the custom admin login preview
 */
add_action('admin_enqueue_scripts', 'aab_do_login_preview');
function aab_do_login_preview(){
	
	/** Create an instance of the AAB_Login_Preview class */
	$aab_login_preview = new AAB_Login_Preview();

}

/**
 * AAB_Login_Preview class
 */
class AAB_Login_Preview{
	
	private $options = null;
	private $screen_id = 'admin-branding_page_djg-admin-area-branding-login';
	private $login_preview = false;

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
		
		/** Check to see if the user is on the 'Custom Admin Login' page */
		$screen = get_current_screen();
		$this->login_preview = ($screen->id === $this->screen_id) ? true : false;
		
		/** Check to see if the custom admin login preview should be shown */
		if($this->login_preview) :
			add_action('aab_login_preview', array($this, '_custom_login_preview'));
		endif;
		
	}
	
	/**
	 * Callback for displaying the custom admin header preview
	 */
	public function _custom_login_preview(){
	
		/** Output the login preview dialog */
		echo '<div id="login-preview-dialog" title="Custom Admin Login Preview">';
		echo '<span id="custom-admin-login-preview-loader">&nbsp;</span>';
		echo '<div id="login">';
		$this->custom_login_preview_body();
		echo '</div>';
		echo '</div>';
		
	}

	/**
	 * Output the inner HTML of the custom admin header
	 *
	 * @param boolean $permitted	Whether or not the current user is permitted to use the preview function (should be, but just in case)
	 */
	public function custom_login_preview_body($permitted = true){
	
		/** Output the CSS for the custom header */
		$this->custom_login_preview_css($permitted);
		
		if($permitted) :
?>
		<h1><a title="<?php bloginfo('name'); ?>" href="<?php bloginfo('siteurl'); ?>">Dyne DrewNett</a></h1>
		
		<div id="loginform">
			<p>
				<label for="user_login">Username<br>
				<input type="text" size="20" value="" class="input" id="user_login" name="log"></label>
			</p>
			<p>
				<label for="user_pass">Password<br>
				<input type="password" size="20" value="" class="input" id="user_pass" name="pwd"></label>
			</p>
			<p class="forgetmenot"><label for="rememberme"><input type="checkbox" value="forever" id="rememberme" name="rememberme"> Remember Me</label></p>
			<p class="submit">
				<input type="submit" value="Log In" class="button button-primary button-large" id="wp-submit" name="wp-submit">
				<input type="hidden" value="http://dynedrewnett/wp-admin/" name="redirect_to">
				<input type="hidden" value="1" name="testcookie">
			</p>
		</div>
		
		<p id="nav">
			<a href="#">Register</a> | <a title="Password Lost and Found" href="#">Lost your password?</a>
		</p>
		
		<p id="backtoblog"><a title="Are you lost?" href="#">&larr; Back to Dyne DrewNett</a></p>
<?php
		else :
			output_ajax_error('login');
		endif;
	
	}
	
	/**
	 * Output the CSS required for the custom admin login preview
	 *
	 * @param boolean $permitted	Whether or not the current user is permitted to use the preview function (should be, but just in case)
	 */
	function custom_login_preview_css($permitted = true){

		if($permitted) :
		
			/** Work out what additional padding needs to be displayed below the login box (to keep the dialog the same size) */
			if($this->options['login_nav_link_locatoin'] !== 'box' && $this->options['login_back_to_blog_link_locatoin'] !== 'box') :
				$padding_bottom_plus = 66;
			elseif($this->options['login_nav_link_locatoin'] !== 'box' || $this->options['login_back_to_blog_link_locatoin'] !== 'box') :
				$padding_bottom_plus = 33;
			else :
				$padding_bottom_plus = 0;
			endif;
			$padding_bottom = 50 + $padding_bottom_plus;
			
			/** Set up the custom admin login image for display */
			if(isset($this->options['login_logo']) && $this->options['login_logo'] !== '0') :
				$logo = wp_get_attachment_image_src($this->options['login_logo'], 'full');
				$img = esc_url($logo['0']);
			else :
				$img = esc_url(get_bloginfo('siteurl').'/wp-admin/images/wordpress-logo.png?ver=20120216');
			endif;
			
			/** Work out if the display of the 'Register' and 'Lost your password' links, and the 'Back to blog' link are in the same position (as defined) */
			$displays_equal = aab_are_displays_equal($this->options, array('top', 'bottom'));
			
?>
			<style>
				/*------------------------------------------------------------------------------
				  1.0 - Preview container
				------------------------------------------------------------------------------*/
				
				#login-preview-dialog{
					background:	none repeat scroll 0 0 #F1F1F1;
					min-width:	0;
					position:	relative;
				}
				
				#login-preview-dialog *{
					margin:		0;
					padding:	0;
				}
				
				
				/*------------------------------------------------------------------------------
				  2.0 - Login form container
				------------------------------------------------------------------------------*/
				
				#login{
					margin:		auto;
					opacity:	0;
					padding:	50px 0 <?php echo $padding_bottom; ?>px;
					width:		320px;
				}
				
				#login h1 a{
					background-image:		url('<?php echo $img; ?>');
					background-position:	center top;
					background-repeat:		no-repeat;
					background-size:		274px 63px;
					display:				block;
					height:					67px;
					margin-bottom:			25px;
					outline:				medium none;
					overflow:				hidden;
					padding-bottom:			15px;
					text-indent:			-9999px;
					width:					326px;
				}
				
				
				/*------------------------------------------------------------------------------
				  3.0 - Login form
				------------------------------------------------------------------------------*/
				
				#loginform{
					background:		none repeat scroll 0 0 #FFFFFF;
					box-shadow:		0 1px 3px rgba(0, 0, 0, 0.13);
					font-weight:	normal;
					margin-left:	8px;
					padding:		26px 24px 46px;
				}
				
				#loginform p{
					margin-bottom: 0;
				}
				
				#login-preview-dialog label{
					color:		#777777;
					font-size:	14px;
				}
				
				#loginform input[type="text"],
				#loginform input[type="password"]{
					background:		none repeat scroll 0 0 #FBFBFB;
					border:			1px solid #E5E5E5;
					box-shadow:		1px 1px 2px rgba(200, 200, 200, 0.2) inset;
					color:			#333333;
					font-family:	"HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
					font-size:		24px;
					font-weight:	200;
					line-height:	1;
					margin-bottom:	16px;
					margin-right:	6px;
					margin-top:		2px;
					outline:		medium none;
					padding:		3px;
					width:			100%;
				}
				#loginform input[type="text"]:focus,
				#loginform input[type="password"]:focus{
					border-color: #999999;
				}
				
				#loginform .forgetmenot{
					float:			left;
					font-weight:	normal;
					margin-bottom:	0;
				}
				#loginform .forgetmenot label{
					font-size:		12px;
					line-height:	19px;
				}
				
				#loginform p.submit{
					padding: 0;
				}
				#loginform p.submit .button-primary{
					float:		right;
					padding:	0 12px 2px;
				}
				
				
				/*------------------------------------------------------------------------------
				  4.0 - Navigation
				------------------------------------------------------------------------------*/
				
				#login #nav{
					background-color:	<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? 'transparent' : $this->options['login_nav_background_colour']; ?>;
					display:			<?php echo ($this->options['login_nav_link_locatoin'] === 'none') ? 'none' : 'block'; ?>;
					margin:				<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '0 0 0 16px' : '0'; ?>;
					padding:			<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '16px 16px 0' : '10px'; ?>;
					text-shadow:		<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? '0 1px 0 #FFFFFF' : 'none'; ?>;
					position:			<?php echo ($this->options['login_nav_link_locatoin'] === 'box') ? 'inherit' : 'absolute'; ?>;
					color:				<?php echo $this->options['login_nav_text_colour']; ?>;
					left:				<?php echo ($displays_equal) ? 'auto' : '0'; ?>;
					right:				<?php echo ($displays_equal) ? '0' : 'auto'; ?>;
					top:				<?php echo ($this->options['login_nav_link_locatoin'] === 'top') ? '0' : 'auto'; ?>;
					bottom:				<?php echo ($this->options['login_nav_link_locatoin'] === 'bottom') ? '0' : 'auto'; ?>;
					width:				<?php echo ($displays_equal || $this->options['login_nav_link_locatoin'] === 'box') ? 'auto' : '480px'; ?>;
					z-index:			10;
				}
				#login #nav a{
					color:				<?php echo $this->options['login_nav_text_colour']; ?>;
					text-decoration:	<?php echo ($this->options['login_nav_underline_link']) ? 'underline' : 'none'; ?>;
				}
				#login #nav a:hover{
					color:				<?php echo $this->options['login_nav_text_colour_hover']; ?>;
					text-decoration:	<?php echo ($this->options['login_nav_underline_link_hover']) ? 'underline' : 'none'; ?>;
				}
				
				
				/*------------------------------------------------------------------------------
				  5.0 - Back to blog
				------------------------------------------------------------------------------*/
				
				#login #backtoblog{
					background-color:	<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? 'transparent' : $this->options['login_back_to_blog_background_colour']; ?>;
					display:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'none') ? 'none' : 'block'; ?>;
					margin:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '0 0 0 16px' : '0'; ?>;
					padding:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '16px 16px 0' : '10px'; ?>;
					text-shadow:		<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? '0 1px 0 #FFFFFF' : 'none'; ?>;
					position:			<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'box') ? 'inherit' : 'absolute'; ?>;
					left:				0;
					top:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'top') ? '0' : 'auto'; ?>;
					bottom:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] === 'bottom') ? '0' : 'auto'; ?>;
					width:				<?php echo ($this->options['login_back_to_blog_link_locatoin'] !== 'top' && $this->options['login_back_to_blog_link_locatoin'] !== 'bottom') ? 'auto' : '480px'; ?>;
					z-index:			5;
				}
				#login #backtoblog a{
					color:				<?php echo $this->options['login_back_to_blog_text_colour']; ?>;
					text-decoration:	<?php echo ($this->options['login_back_to_blog_underline_link']) ? 'underline' : 'none'; ?>;
				}
				#login #backtoblog a:hover{
					color:				<?php echo $this->options['login_back_to_blog_text_colour_hover']; ?>;
					text-decoration:	<?php echo ($this->options['login_back_to_blog_underline_link_hover']) ? 'underline' : 'none'; ?>;
				}
			</style>
<?php
		else :
?>
			<style>
				/*------------------------------------------------------------------------------
				  1.0 - Login form preview error
				------------------------------------------------------------------------------*/
				#login{
					padding: 	38px 0 43px;
					width:		auto;
				}
			</style>
<?php
		endif;
		
	}
	
}

/**
 * AJAX callback for updating a preview of the custom admin login
 */
add_action('wp_ajax_preview-custom-admin-login', '_preview_custom_login');
function _preview_custom_login(){

	/** Checks user permisisons */
	$permitted = (current_user_can($_POST['branding']['role_login']) && wp_verify_nonce($_POST['security'], 'aab-action'))
				 ? true
				 : false;
	
	/** Create an instance of the AAB_Login_Preview class and update the custom admin header using the 'custom_login_preview_body' method  */	
	$custom_admin_login_preview = new AAB_Login_Preview($_POST['branding']);
	$custom_admin_login_preview->custom_login_preview_body($permitted);
	
	die();	// Required for a proper AJAX result
	
}

/**
 * Work out if, in any of the positions specified, the 'Register' and 'Lost your password' links, and the 'Back to blok link' are to be displayed in the same position
 *
 * @param required array $options	The options being used to displaying the custom admin login preview
 * @param array $positions			The positions to check
 * @return boolean					Whether or not the links are to be displayed in the same position (from the specified list)
 */
function aab_are_displays_equal($options, $positions = array()){

	$results = array();
	if(!empty($positions)) : foreach($positions as $position) :
			$results[$position] = ($options['login_back_to_blog_link_locatoin'] === $position && $options['login_nav_link_locatoin'] === $position);
		endforeach;
	endif;
	
	return in_array(true, $results);
	
}
?>