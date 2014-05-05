/**
 * @package:		WordPress
 * @subpackage:		Admin Bar Button Plugin
 * @description:	JS for use in the admin area (on the Admin Bar Button settings page)
 * @since:			2.2
 */

$ = jQuery.noConflict();

$(document).ready(function(){
	
	$('input[name="delete"]', '#admin-bar-button-page').on('click', function(){
	
		var result = confirm('Are you sure you want to restore the default settings?');
		if(result !== true){
			return false;
		}
		
	});
	
});