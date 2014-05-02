<?php
/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 * @Description:	Registers a custom die handler for this plugin
 */

/**
 * Set the custom wp_die handler for this plugin
 */
function aab_register_die_handler($handler){
	
	return '_aab_die_handler';
	
}

/**
 * Kill WordPress execution and display an HTML error message
 *
 * @param required string $message	The error message to display
 * @param string $title 			The error page title
 * @param string|array $args		Optional arguments to control behavior
 */
function _aab_die_handler($message, $title = '', $args = array()){
	
	$defaults = array(
		'back_link_before'		=> '&laquo; ',				// The text to show before the back link
		'back_link_after'		=> '',						// The text to show after the back link
		'back_text'				=> 'Go back and try again',	// The text to show for the the back link
		'show_back_link_before'	=> false,					// Whether or not to display the back link before the error message
		'show_back_link_after'	=> true,					// Whether or not to display the back link after the error message
		'response'				=> 500,						// The HTML response to show the user
	);
	$r = wp_parse_args($args, $defaults);

	$have_gettext = function_exists('__');

	/** Check to see if the $message is in fact a WP_Error object */
	if(function_exists('is_wp_error') && is_wp_error($message)) :
	
		/** If no title is implicitly declared, have a look in the WP_Error that was passed */
		if(empty($title)) :
			
			$error_data = $message->get_error_data();
			if(is_array($error_data) && isset($error_data['title'])) :
				$title = $error_data['title'];
			endif;
				
		endif;
		
		/** Grab the error messages from the WP_Error object */
		$errors = $message->get_error_messages();
		
		switch(count($errors)) :
		
			case 0 : // There are no errors defined, set the message to an empty string
				$message = '';
				break;
				
			case 1 : // There is just one error definded, so set the message to that one
				$message = $errors[0];
				break;
				
			default : // There are multiple errors defined, so joing them and make a list
				$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
				break;
				
		endswitch;
		
	endif;
	
	/** Check for a back link to add to the message */
	if(isset($r['back_link']) && $r['back_link']) :
		$back_text = ($have_gettext) ? __(sprintf('%1$s%2$s%3$s', $r['back_link_before'], $r['back_text'], $r['back_link_after'])) : sprintf('%1$s%2$s%3$s', $r['back_link_before'], $r['back_text'], $r['back_link_after']);
		$back_text = sprintf("\n".'<p><a href="javascript:history.back()">%1$s</a></p>', $back_text);
	endif;

	/** Check to see if the action 'admin_head' has already happened, and if not, outplut the header information for this error */
	if(!did_action('admin_head')) :
		
		/** Check to see if the HTML headers have been sent, and if not, send them wiht the 'response' code from the function */
		if(!headers_sent()) :
			status_header($r['response']);
			nocache_headers();
			header('Content-Type: text/html; charset=utf-8');
		endif;
		
		/** Check for a title and add a default one if one is not set */
		if(empty($title)) :
			$title = $have_gettext ? __('WordPress &rsaquo; Error') : 'WordPress &rsaquo; Error';
		endif;
?>
<!DOCTYPE html>
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php if(function_exists('language_attributes') && function_exists('is_rtl')) : language_attributes(); else : echo "dir='ltr'"; endif; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<style type="text/css">
		body{
			background: #F9F9F9;
		}
		#error-container{
			background: #FFFFFF;
			border: 1px solid #DFDFDF;
			color: #333333;
			font-family: sans-serif;
			margin: 50px;
			padding: 30px;
			border-radius: 3px;
			-webkit-border-radius: 3px;
		}
		#error-container h1,
		#error-container h2{
			border-bottom: 1px solid #DADADA;
			clear: both;
			color: #666666;
			font: 24px Georgia, "Times New Roman", Times, serif;
			margin: 10px 0 0;
			padding: 0 0 5px;
		}
		#error-container h2{
			font-size: 20px;
			margin: 30px 0 0;
		}
		#error-container p{
			font-size: 14px;
			line-height: 1.5;
			margin: 0 0 10px;
		}
		#error-container #error-text p:nth-of-type(1){
			margin: 20px 0 10px;
		}
		#error-container .error-output p{
			margin: 20px 0 0;
		}
		#error-container code{
			font-family: Consolas, Monaco, monospace;
		}
		#error-container a{
			color: #21759B;
			text-decoration: none;
		}
		#error-container a:hover{
			color: #D54E21;
		}
		#error-container ul{
			list-style-type: none;
			margin: 0;
			padding: 0;
		}
		#error-container pre{
			margin: 0px;
		}
		#error-container .message{
			margin-bottom: 20px;
		}
	</style>
</head>
<body id="error-page">
	<?php endif; // !did_action('admin_head') ?>
	<div id="error-container">
		<?php if($r['show_back_link_before']) : echo $back_text; endif; ?>
		<div class="message"><?php echo $message; ?></div>
		<?php if($r['show_back_link_after']) : echo $back_text; endif; ?>
	</div>
</body>
</html>
<?php
	die(); // Finally, kill the output
	
}
?>