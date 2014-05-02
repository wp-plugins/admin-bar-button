<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @Description: 	Debugger extension of the WP_Error class, specifically for ouputting errors for invalid 'actions' (before any database action occurs)
 */

/**
 * Custom class to extend the WP_Error class to display warning to users who are where they should not be
 */
class AAB_Invalid_Action extends WP_Error{
	
	/**
	 * Constructor
	 *
	 * @param required string $type	The type of invalid action that the user is attempting
	 */
	function __construct($type){
		
		/** Add the invalid action message */
		$message = $this->get_invalid_action_message($type);
		
		if(!empty($message)) :
			$this->add('error', $message);
		endif;
		
	}
	
	/**
	 * Return the message to display to a user who is trying to run an invalid action
	 *
	 * @param required string $type	The type of invalid action that the user is attempting
	 * @return string				The message to display to the user
	 */
	function get_invalid_action_message($type){
		
		/** Start buffering the output */
		ob_start();
		
		switch($type) :
		
			case 'permission' :
?>
				<div id="error-text">
					<h1>Not a chance, my friend!</h1>
					<p>Nice try, but you don't have permission to be here. Off you go now...</p>
				</div>
<?php
				break;
				
			case 'nonce' :
?>
				<div id="error-text">
					<h1>You are not authorised to do this!</h1>
					<p>Either somthing has gone wrong with your request, or you are not supposed to be here.</p>
					<p>Personnaly, I think it's the latter, as you look pretty dodgy to me, but I'll give you the benifit of the doubt just this once...</p>
					<?php if(!(defined('DOING_AJAX') && DOING_AJAX)) : ?>
						<p><a href="<?php echo esc_url(remove_query_arg('updated', wp_get_referer())); ?>">Click here to go back and try again.</a></p>
					<?php endif; ?>
				</div>
<?php			
				break;
		
			case 'submit' :
?>
				<div id="error-text">
					<h1>And just what the heck do you think you are doing here?!</h1>
					<p>I'm afraid you've not given me a valid 'Submit', so I'm going to have to ask you to kindly sling your hook!</p>
					<p>Make it snappy, and don't come back without speaking to the admin, else I'll tell your mum what you've been up to!</p>
				</div>
<?php
				break;
		
			case 'log-out' :
?>
				<div id="error-text">
					<h1>Aww, it looks like you are trying to leave me :(</h1>
					<p>I still love you, and really want you to stay, but if you must go, then click the link below...</p>
					<p>"I don't love you back, and I really do want to <a href="<?php echo wp_logout_url(); ?>">log out</a>".</p>
				</div>
<?php
				break;
				
		endswitch;
		
		/** Grab the $message_header from output buffer and clear it */
		return ob_get_clean();	
		
	}
	
}
?>