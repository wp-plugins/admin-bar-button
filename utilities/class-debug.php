<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @Description: 	Allows debugging of database actions and calls a 'AAB_Debug_Error' if required
 */

/**
 * Custom class to extend the WP_Error class and allow sufficent debugging of actions carried out by this plugin
 */
class AAB_Debug{
	
	/**
	 * The WP_Error object containing error details
	 *
	 * @var WP_Error object
	 */
	private $debug_error;
	
	/**
	 * Whether or not an error was found when adding/updating a poll
	 *
	 * @var boolean
	 */
	public $sql_error_found;

	/**
	 * Degugs the $wpbd->insert query that has just been run
	 *
	 * @param required mixed $data		Any data relevant to the error
	 * @param string $data_explaination	A basic description of what the data shown in the error is
	 * @param string $code				The code to store the error under
	 * @param string $description		A friendly description of what was happening to display to users
	 */
	function debug($data = array(), $data_explaination = '', $code = 'general_error', $description = ''){
	
		global $wpdb;		
		
		$this->sql_error_found = ($wpdb->last_error !== '');
		
		if($this->sql_error_found) :
			
			/** Make sure the AAB_Debug_Error object exists */
			if(!is_a($this->debug_error, 'AAB_Debug_Error')) :
				$this->debug_error = new AAB_Debug_Error();
			endif;
			
			/** Add the message to the AAB_Debug_Error object */
			$this->debug_error->add_message($data, $data_explaination, $code, $description);
			
		endif;
		
	}
	
	/**
	 * Dies and shows a debug message (if there is one or more)
	 */
	function output_errors($echo = true){
	
		if($this->sql_error_found) :
			if($echo) :
				wp_die($this->debug_error, 'Don\'t panic, Mr. Mannering!', array('back_link' => true));
			else :
				return $this->debug_error;
			endif;
		endif;
		
	}
	
}
?>