<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @Description: 	Debugger extension of the WP_Error class to output more relevant details about an error
 */

/**
 * Custom class to extend the WP_Error class and allow sufficent debugging of actions carried out by this plugin
 */
class AAB_Debug_Error extends WP_Error{
	
	/**
	 * Constructor
	 */
	function __construct(){
		
		/** Check to see if the $debug_error WP_Error object exists, and create it if not */
		if(empty($this->errors)) :
			$message_header = $this->get_debug_header();
			$this->add('header', $message_header);
		endif;
		
	}
	
	/**
	 * Add a custom error message
	 *
	 * @param required mixed $data		Any data relevant to the error
	 * @param string $data_explaination	A basic description of what the data shown in the error is
	 * @param string $code				The code to store the error under
	 * @param string $description		A friendly description of what was happening to display to users
	 */
	function add_message($data, $data_explaination, $code = 'general_errror', $description = ''){
		
		$message = $this->get_debug_message($data, $data_explaination, $description);
		if($message) :
			$this->add($code, $message);
		endif;
		
	}
	
	/**
	 * Grab the header to show to users when they encounter a fatal error
	 *
	 * @return string $message_header	The message header 
	 */
	private function get_debug_header(){
		
		/** Start buffering the output */
		ob_start();
		
		/** Declare the possible titles and grab a random key */
		$titles = array(
			'Something is very, very wrong...',
			'Grab the life jackets, this boat is gonna sink!',
			'WE\'RE ALL GONNA DIE!!!!',
			'It had teeth as big as your arm, and... OMG, IT\'S BEHIND YOU!!!',
			'STOP!!! The bridge is out!',
			'ALIENS!!!!!!!!',
			'Shhh... Don\'t make a sound...',
			'Why did the chicken cross the road, you ask? Maybe it didn\'t, maybe the chicken stayed put and the road moved...'
		);
		$random_title_key = array_rand($titles, 1);
		
?>		
		<div id="error-text">
			<h1><?php echo $titles[$random_title_key]; ?></h1>
			<p>Have I got your attention? Good. I'm sorry about this and all that rubbish, but something has gone wrong.</p>
			<p>You can click the link below this message to go back and try again (but you'll probably need to re-enter values), because this may only be a temporary glitch.</p>
			<p>If you are still having trouble after that, please contanct a puny human admin who will be happy to take a look. Because that's face it, they will be sad and understand what the gibberish below means.</p>
		</div>
<?php	
		/** Grab the $message_header from output buffer and clear it */
		$message_header = ob_get_clean();
		
		return $message_header;
		
	}
	
	/**
	 * Grab the message to show for this particular error
	 *
	 * @param required mixed $data		Any data relevant to the error
	 * @param string $data_explaination	A basic description of what the data shown in the error is
	 * @param string $description		A friendly description of what was happening to display to users
	 * @return string $message_data		The body of the error message
	 */
	private function get_debug_message($data, $data_explaination = '', $description = ''){
		
		global $wpdb;
		$error = array(); // Initilise to avoid errors when checking
		
		$possible_info = array(
			'last-error'	=> array('name' => '$wpdb->last_error', 'value' => $wpdb->last_error),
			'last-query'	=> array('name' => '$wpdb->last_query', 'value' => $wpdb->last_query),
			'_post'			=> array('name' => '$_POST', 'value' => print_r($_POST, true)),
			'data'			=> array('name' => '$data', 'value' => print_r($data, true), 'description' => $data_explaination)
		);
		
		foreach($possible_info as $key => $info) :
		
			if(!empty($info['value'])) :
			
				$error = sprintf("\t\t\t".'<div id="%1$s">'."\n", $key);
				$error.= sprintf("\t\t\t\t".'<p><strong>%1$s -</strong> %2$s</p>'."\n", $info['name'], $info['description']);
				$error.= sprintf("\t\t\t\t".'<pre>%1$s</pre>'."\n", $info['value']);
				$error.= "\t\t\t".'</div>'."\n\n";
				$errors[] = $error;
			
			endif;
			
		endforeach;
		
		if(empty($errors)) :
			return false;
		endif;
		
		/** Construct the error number ordinal */
		$count = count($this->get_error_messages());
		$ordinal = $this->convert_to_ordinal($count);
		$msg_number = sprintf('<strong>%1$s</strong>', $ordinal);
		
		/** See if the description is populated */
		$description = (!empty($description)) ? $description : 'Unfortunately my human master did not tell me what I was doing when this error occured, so you\'ll just have to guess. Good luck with that!';
		
		/** Start buffering the output */
		ob_start();
?>
		<div class="error-output">
		
			<h2>Here is what the puny human admin should know for the <?php echo $msg_number; ?> error...</h2>
			
			<div id="description">
				<p>
					<strong>Description -</strong>
					<?php echo $description; ?>
				</p>
			</div>
<?php			
			foreach($errors as $error) :
				echo $error;
			endforeach;
?>
			
		</div>
<?php		
		/** Grab the $message_data from output buffer and clear it */
		$message_data = ob_get_clean();
		
		return $message_data;
		
	}
	
	/**
	 * Convert a real number to an ordinal (i.e. 1 becomes 'first')
	 *
	 * @param required integer $num		The number to transform
	 * @return string					The ordinal to display
	 */
	private function convert_to_ordinal($num){
	
		if(!is_int($num)) :
			return false;
		endif;
		
		if($num < 1 || $num > 99) :
			return WP_Error('convert_to_ordinal', 'Sorry, the \'convert_to_ordinal()\' function can only handle number between 1-99 (inclusive) at the moment');
		endif;
		
		$hyphen = '-';
		$numbers = array(
			1 => 'first',
			2 => 'second',
			3 => 'third',
			4 => 'fourth',
			5 => 'fifth',
			6 => 'sixth',
			7 => 'seventh',
			8 => 'eighth',
			9 => 'ninth',
			10 => 'tenth',
			11 => 'eleventh',
			12 => 'twelth',
			13 => 'thirteenth',
			14 => 'fourteenth',
			15 => 'fifteenth',
			16 => 'sixteenth',
			17 => 'seventeenth',
			18 => 'eighteenth',
			19 => 'nineteenth',
			20 	=> array('twenty', 'twentieth'),
			30 	=> array('thirty', 'thirtieth'),
			40 	=> array('fourty', 'fortieth'),
			50 	=> array('fifty', 'fiftieth'),
			60 	=> array('sixty', 'sixtieth'),
			70 	=> array('seventy', 'seventieth'),
			80 	=> array('eighty', 'eightieth'),
			90 	=> array('ninety', 'ninetieth')
		);
		
		/** If the number is less than 20, just return the ordinal */
		if($num < 20) :
		
			return $numbers[$num];
			
		/** Else if it's a 2 digit number... */
		elseif(strlen($num) === 2) :
			
			/** Work out the last number */
			$last = substr($num, strlen($num)-1);
			
			/** If the last is '0', return the ordinal in position [1] for that number */
			if($last === '0') :
			
				return $numbers[$num][1];
				
			/** Else workout the parent part, and return the ordinal for position [0] for that number followed by the single digit ordinal */
			else :
			
				/** Work out the parent for this ordinal (i.e. 21 has the parent 20) */
				$first = substr($num, 0, 1);
				$parent = intval($first.'0');
				return sprintf('%1$s%2$s%3$s', $numbers[$parent][0], $hyphen, $numbers[$last]);
				
			endif;
			
		endif;
		
	}
	
}
?>