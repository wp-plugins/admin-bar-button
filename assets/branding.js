/**
 * @package:		WordPress
 * @subpackage:		Admin Admin Branding
 */

/*------------------------------------------------------------------------------

TABLE OF CONTENTS:
------------------
 1.0 - Invoke the JSColor plugin
 2.0 - Invoke the switchButton plugin
 3.0 - Preview changes
 4.0 - Manage media on the selected page

------------------------------------------------------------------------------*/

$ = jQuery.noConflict();

/*------------------------------------------------------------------------------
  1.0 - Invoke the JSColor plugin
------------------------------------------------------------------------------*/

$(document).ready(function(){

	var JSColor_picker = {}; // Create an object to hold all of the colour pickers (so that can be individual manipulated, if required)
	
	/** Loop through each element with the 'colour-picker' class... */
	$('.colour-picker', '#admin-area-branding-page').each(function(index, element){
		
		var id = $(element).attr('id'); // The ID of the currently element
		
		/** Add an instance of the 'jscolour.color' object to the 'JSColor_picker' object */
		JSColor_picker[id] = new jscolor.color(element, {
			hash: true,
			required: false
		});
		
	});
	
});


/*------------------------------------------------------------------------------
  2.0 - Invoke the switchButton plugin 
------------------------------------------------------------------------------*/

$(document).ready(function(){

	$('.switch', '#admin-area-branding-page').switchButton();
	$('.switch-yes-no', '#admin-area-branding-page').switchButton({
		label_on:	'Yes',
		label_off:	'No'
	});
	
});


/*------------------------------------------------------------------------------
  3.0 - Preview changes
------------------------------------------------------------------------------*/

$(function(){
	
	/**
	 * Set up the 'customAdminPreview' function to allow users to preview areas of custom admin branding
	 */
	$(document).ready(function(){
		customAdminPreview.init();
	});
	customAdminPreview = {
		
		/**
		 * Constructor
		 */
		init : function(){
		
			var t = this;	// This object
			
			t.scope = $('input[name="page"]', '#admin-area-branding-page').val();	// The scope of the page (e.g. 'header' or 'footer')
			t.scope_ok = t.set_object_vars();											// Whether or not the scope for this page is valid
			t.make_safe_copy();															// Make a safe copy of the custom admin area that can be edited on the page being displayed
			t.matabox_holder = $('#admin-area-branding-page');						// The parent metabox that holds the custom admin header
			t.setup_preview_dialog();													// Set up the loading dialog so that it is ready to show
			t.setup_preview_login_dialog();												// Set up the loading dialog so that it is ready to show
			
			/** Show the preview of the custom admin header/footer when the user clicks the 'Preview' button */
			t.matabox_holder.on('click', '.manage-form input#preview-changes', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.show_preview();
				}
				
			});
			
			/** Hide the preview of the custom admin header/footer when the user clicks the 'Restore' button */
			t.matabox_holder.on('click', '.manage-form .restore-original-button', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.restore_original();
				}
				
			});
			
			/** Show the preview of the custom admin login in a dialog when the user clicks the 'Preview Login' button */
			t.matabox_holder.on('click', '.manage-form input#preview-login', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.show_preview_login();
				}
				
			});
			
		}, // init
		
		/**
		 * Set the object variables necessary to complete the 'preview' action
		 */
		set_object_vars : function(){
		
			var t = this;	// This object
			
			switch(t.scope){
				case 'header' :
					t.ajax_loader		= $('#custom-admin-preview-loader');	// The page's AJAX loader
					t.live_container	= $('#custom-admin-header');			// The container for the live header
					t.restore_button	= $('#restore-original-header');		// The 'Restore Header' button
					t.preview_action	= 'preview-custom-admin-header';		// The AJAX action to carry out
					t.slide_direction	= {										// The direction in which the footer should slide off/on the screen
						start:	'left',
						done:	'right'
					}
					break;
				case 'footer' :
					t.ajax_loader		= $('#custom-admin-preview-loader');	// The page's AJAX loader
					t.live_container	= $('#wpfooter');						// The container for the live footer
					t.restore_button	= $('#restore-original-footer');		// The 'Restore Footer' button
					t.preview_action	= 'preview-custom-admin-footer';		// The AJAX action to carry out
					t.slide_direction	= {										// The direction in which the footer should slide off/on the screen
						start:	'up',
						done:	'down'
					}
					break;
				case 'login' :
					t.ajax_loader		= $('#custom-admin-login-preview-loader');	// The page's AJAX loader
					t.live_container	= $('#login-preview-dialog');				// The container for the live footer
					t.inner_container	= t.live_container.find('#login');			// The inner container of the dialog used to show the preview
					t.restore_button	= false;									// The 'Restore Footer' button
					t.preview_action	= 'preview-custom-admin-login';				// The AJAX action to carry out
					t.slide_direction	= {											// The direction in which the footer should slide off/on the screen
						start:	'left',
						done:	'left'
					}
					break;
				default:
					return false;
			}
			
			return true;
			
		}, // set_object_vars
		
		/**
		 * Set the branding options that are to be passed to 'preview' action
		 */
		set_branding_options : function(){
		
			var t = this,		// This object
				branding = {}; 	// An empty object to hold the branding options (initilise so that an empty array is passed if no options are set)
			
			switch(t.scope){
				case 'header' :
					branding = {
						header_background_colour:		$('input#header_background_colour', '#admin-area-branding-page').val(),
						header_height:					$('input#header_height', '#admin-area-branding-page').val(),
						header_border_bottom_width:		$('input#header_border_bottom_width', '#admin-area-branding-page').val(),
						header_border_bottom_style:		$('select#header_border_bottom_style', '#admin-area-branding-page').val(),
						header_border_bottom_colour:	$('input#header_border_bottom_colour', '#admin-area-branding-page').val(),
						header_fixed:					($('input#header_fixed', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						header_logo:					$('input#header_logo', '#admin-area-branding-page').val(),
						header_logo_margin_top:			$('input#header_logo_margin_top', '#admin-area-branding-page').val(),
						header_logo_margin_right:		$('input#header_logo_margin_right', '#admin-area-branding-page').val(),
						header_logo_margin_bottom:		$('input#header_logo_margin_bottom', '#admin-area-branding-page').val(),
						header_logo_margin_left:		$('input#header_logo_margin_left', '#admin-area-branding-page').val(),
						role_header:					$('input#role_header', '#admin-area-branding-page').val()
					};
					break;
				case 'footer' :
					branding = {
						footer_text:			$('textarea#footer_text', '#admin-area-branding-page').val(),
						footer_show_version:	($('input#footer_show_version', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						role_footer:			$('input#role_footer', '#admin-area-branding-page').val()
					};
					break;
				case 'login' :
					branding = {
						login_logo:									$('input#login_logo', '#admin-area-branding-page').val(),
						login_nav_link_locatoin:					$('select#login_nav_link_locatoin', '#admin-area-branding-page').val(),
						login_nav_background_colour:				$('input#login_nav_background_colour', '#admin-area-branding-page').val(),
						login_nav_text_colour:						$('input#login_nav_text_colour', '#admin-area-branding-page').val(),
						login_nav_underline_link:					($('input#login_nav_underline_link', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						login_nav_text_colour_hover:				$('input#login_nav_text_colour_hover', '#admin-area-branding-page').val(),
						login_nav_underline_link_hover:				($('input#login_nav_underline_link_hover', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						login_back_to_blog_link_locatoin:			$('select#login_back_to_blog_link_locatoin', '#admin-area-branding-page').val(),
						login_back_to_blog_background_colour:		$('input#login_back_to_blog_background_colour', '#admin-area-branding-page').val(),
						login_back_to_blog_text_colour:				$('input#login_back_to_blog_text_colour', '#admin-area-branding-page').val(),
						login_back_to_blog_underline_link:			($('input#login_back_to_blog_underline_link', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						login_back_to_blog_text_colour_hover:		$('input#login_back_to_blog_text_colour_hover', '#admin-area-branding-page').val(),
						login_back_to_blog_underline_link_hover:	($('input#login_back_to_blog_underline_link_hover', '#admin-area-branding-page').prop('checked')) ? '1' : '0',
						role_login:									$('input#role_login', '#admin-area-branding-page').val()
					};
					break;
			}
			
			return branding;
			
		}, // set_branding_options
		
		/**
		 * Make a safe copy of the custom admin area that can be edited by the page being displayed
		 */
		make_safe_copy : function(){
		
			var t = this;	// This object
			
			/** Ensure that the scope is ok, and thus a safe copy should be made */
			if(!t.scope_ok || t.scope === 'login'){
				return false;
			}
			
			var live_id = t.live_container.attr('id'),	// The ID of the live container that a safe copy of is being cloned from
				safe_id = live_id+'-safe';				// The ID of the safe container that is to be created
			
			/** Clone the live container, change the ID and the insert the new safe container before the live container */
			t.live_container.clone().attr('id', safe_id).insertBefore(t.live_container);
			
			t.safe_container = $('#'+safe_id);	// The container for a safe copy of the live custom admin area that can be edited
			
		}, // make_safe_copy
		
		/**
		 * Set up the 'Loading' dialog (which actually says 'Generating preview...')
		 */
		setup_preview_dialog : function(){
			
			var t = this;	// This object
			
			$('#loading-dialog').dialog({
				autoOpen:		false,
				closeOnEscape:	false,
				draggable:		false,
				height:			94,
				hide:			400,
				maxHeight:		94,
				minHeight:		94,
				modal:			true,
				position:		{
					my:			'center',
					at:			'center',
					of:			window,
					collision:	'none',
					using:		t.dialog_position
				},
				resizable:		false,
				show:			400,
				width:			350,
				open:			function(event, ui){
					
					/** Style the dialog box */
					$(this).closest('.ui-dialog').css({ // Remove the borders and padding
						'background-image':	'none',
						'border':			'none',
						'padding':			'0',
						'position':			'fixed'
					});
					$(this).closest('.ui-dialog').children('.ui-dialog-content').css('height', '80px');
					$(this).closest('.ui-dialog').children('.ui-dialog-titlebar').css('display', 'none');
					$(this).closest('.ui-dialog').children('.ui-dialog-buttonpane').css('display', 'none');
					
					/** Style the overlay */
					$('.ui-widget-overlay').css('background-image', 'none');
					
				}
			});
			
		}, // setup_preview_dialog
		
		/**
		 * Show a preview of the custom admin area type for the page that is being viewed, using the current page settings
		 */
		show_preview : function(){
		
			var t = this;	// This object
			
			/** Set the attributes to update for the preview */
			data = {
				security:	$('input[name="_wpnonce"]', '#admin-area-branding-page').val(),
				action:		t.preview_action,
				branding:	t.set_branding_options()
			};
			
			/** Show the AJAX loader */
			$('#loading-dialog').dialog('open');
				
			/** Run the AJAX request to create the preview of the custom branding area */
			var request = $.post(ajaxurl, data, function(response){
			
				/** Hide the original custom admin area */
				t.live_container.hide('slide', { direction: t.slide_direction.start } ,1000, function(){
				
					/** Insert the preview of the updated custom admin area */
					t.live_container.html(response);
					
				});
				
			});
			
			/** Once the AJAX request is fully complete, show the preview custom branding area */
			request.done(function(){
			
				/** Show the preview of the updated custom admin area */
				t.live_container.show('slide', { direction: t.slide_direction.done } ,1000, function(){
				
					/** Show the 'Restore' button */
					t.restore_button.show().animate({ opacity: 1 }, 1000);
					
					/** Hide the AJAX loader */
					$('#loading-dialog').dialog('close');
					
				});
				
			});
			
		}, // show_preview
		
		/**
		 * Set up the 'Custom Admin Login Preview' dialog
		 */
		setup_preview_login_dialog : function(){
			
			var t = this;	// This object
			
			$('#login-preview-dialog').dialog({
				autoOpen:		false,
				draggable:		false,
				hide:			400,
				maxWidth:		500,
				minWidth:		500,
				modal:			true,
				position:		{
					my:			'center',
					at:			'center',
					of:			window,
					collision:	'none',
					using:		t.dialog_position
				},
				resizable:		false,
				show:			400,
				width:			500,
				buttons: 		{			
					'Close': function(){
						$(this).dialog('close');
					}
				},
				open: function(event, ui){
					
					t.ajax_loader.css('opacity', '1');
					
					/** Style the dialog box */
					$(this).closest('.ui-dialog').css({ // Format the background
						'background-image':	'none',
						'border':			'none',
						'padding':			'0',
						'position':			'fixed'
					});
					$(this).closest('.ui-dialog').children('.ui-dialog-titlebar').css('margin', '5px');					// Remove the margin under the titlebar
					$(this).closest('.ui-dialog').children().children('.ui-dialog-titlebar-close').hide();				// Hide the 'x' close button
					$(this).closest('.ui-dialog').children('.ui-dialog-buttonpane').css({	// Remove the margin above the button pane and colour the background
						'background-color':	'#CCCCCC',
						'border-color':		'#BBBBBB',
						'margin':			'0'
					});
					
					/** Style the overlay */
					$('.ui-widget-overlay').css('background-image', 'none');
					
				},
				close: function(event, ui){
					
					/** Hide the AJAX loader (just in case the dialog is closed while it is still being shown */
					t.ajax_loader.css('opacity', '0');
					
				}
			}); 
			
		}, // setup_preview_login_dialog
		
		/**
		 * Show a preview of the custom admin login
		 */
		show_preview_login : function(){
			
			var t = this;	// This object
			
			/** Hide the current custom login preview and show the AJAX loader */
			t.inner_container.css('opacity', '0');
			
			/** Show the AJAX loader */
			$('#login-preview-dialog').dialog('open');
			
			/** Set the attributes to update for the preview */
			var data = {
				security:	$('input[name="_wpnonce"]', '#admin-area-branding-page').val(),
				action:		t.preview_action,
				branding:	t.set_branding_options()
			};
			
			/** Run the AJAX request to create the preview of the custom branding header */
			var request = $.post(ajaxurl, data, function(response){
			
				/** Insert the preview of the updated custom admin login */
				t.inner_container.html(response);
				
			});
			
			/** Once the AJAX request is fully complete, show the preview custom branding header */
			request.done(function(){
			
				/** Show the updated custom login preview and hide the AJAX loader */
				t.inner_container.animate({ opacity: 1 }, 2000);
				t.ajax_loader.animate({ opacity: 0 }, 1000);
				
			});
			
		}, // show_preview_login
		
		/**
		 * Set the position of the dialog so that it is centred (as using position 'fixed' for the dialog messes this up) 
		 */
		dialog_position : function(target, element){
			
			/** Set the 'left' and 'top' values correctly so that the dialog is displayed in the centre of the screen */
			target.left = Math.ceil(($(window.top).width() / 2) - (element.element.width / 2));
			target.top = Math.ceil(($(window.top).height() / 2) - (element.element.height / 2));
			
			/** Update the CSS of the target element */
			$(this).closest('.ui-dialog').css(target);
			
		}, // dialog_position
		
		/**
		 * Restore the original custom admin area type for the page that is being viewed
		 */
		restore_original : function(){
		
			var t = this;	// This object
			
			/** Show the AJAX loader */
			t.ajax_loader.animate({ opacity: 1 }, 1000);
			
			/** Show the 'Restore Header' button */
			t.restore_button.animate({ opacity: 0 }, 300);
			
			/** Remove the preview that is currently being shown */			
			t.live_container.hide('slide', { direction: t.slide_direction.start }, 1000, function(){
			
				t.live_container.html(t.safe_container.html());
				
				/** Show the original header */
				t.live_container.show('slide', { direction: t.slide_direction.done }, 1000, function(){
					
					/** Hide the AJAX loader */
					t.ajax_loader.animate({ opacity: 0 }, 300);
					
				});
				
			});
			
		} // restore_original
		
	};

});


/*------------------------------------------------------------------------------
  4.0 - Manage media on the selected page
------------------------------------------------------------------------------*/

$(function(){
	
	/**
	 * Set up the 'customAdminMedia' function to allow users to change images used by custom admin branding
	 */
	$(document).ready(function(){
		adminAreaBrandingMedia.init();
	});
	adminAreaBrandingMedia = {
	
		mediaFrame: false,
		
		/**
		 * Constructor
		 */
		init : function(){
		
			this._createEvents();
			
		}, // init
		
		_createEvents : function(){
		
			var t = this;	// This object
			
			$('#select-header-logo').on('click', function(e){
			
				e.preventDefault();
				t._openMediaFrame();
				
			});
			
		}, // _create_events
		
		_openMediaFrame : function(){
		
			if(this.mediaFrame === false){
				this.mediaFrame = getMediaFrame('Select an Admin Area Branding header image');
			}
			
			/** Finally, open the media manager */
			this.mediaFrame.open();
			
		} // _openMediaManager
		
	}
	
	/**
	 * Create a media manager instance
	 */
	function getMediaFrame(title){
	
		mediaFrame = wp.media.frames.aab_media_frame = wp.media({
		
			/** Set the parameters for the media uploader */
			button:		{ text: 'Select image' },						// Set the text of the button.
			className:	'media-frame admin-area-branding-media-frame',	// The class to use for this instance of the media manager
			library:	{ type: 'image' },								// Ensure only images are allowed
			multiple:	false,											// Disable multiple selections
			title:		title											// The tile to show in the media manager
			
		});
		
		mediaFrame.on('select', function(){
		
			var attachment = mediaFrame.state().get('selection').first().toJSON();	// Grab the attachment selection
			$('#header-logo').val(attachment.id);									// Update the hidden logo ID field
			
			var data = {
				action:		'update_image_preview',
				image_id:	attachment.id
			};
			
			/** Carry out an AJAX request to update the image preview */
			$.post(ajaxurl, data, function(response){
				$('#image-preview').html(response);
			});
			
		});
		
		return mediaFrame;
		
	}
	
});




$(function(){
	
	/**
	 * Set up the 'customAdminMedia' function to allow users to change images used by custom admin branding
	 */
	$(document).ready(function(){
		//customAdminMedia.init();
	});
	customAdminMedia2 = {
		
		/**
		 * Constructor
		 */
		init : function(){
		
			var t = this;	// This object
			
			t.aab_media_frame,														// The custom admin area branding media frame
			t.scope = $('input[name="page"]', '#admin-area-branding-page').val(); 	// The scope of the page (e.g. 'header' or 'footer')
			t.scope_ok = t.set_object_vars();										// Whether or not the scope of the page is acceptable
			t.logo_preview_loader = $('.custom-admin-logo-preview-loader', '#admin-area-branding-page');	// The AJAX loader for changing the image preview
			
			/** Check to see if the 'Remove Image' button should be shown (only on relevant pages) */
			if(t.scope === 'header' || t.scope === 'login'){
				t.check_button('remove');
			}
			
			/** Open the media manager when the any button with the class 'select-logo-button' is clicked*/
			$('#admin-area-branding-page').on('click', 'input.select-logo-button', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.open_media_manager(this);
				}
				
			});
			
			$('#admin-area-branding-page').on('click', 'a.remove-logo-button', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.remove_image_preview();
				}
				
			});
			
			$('#admin-area-branding-page').on('click', 'a.restore-logo-button', function(e){
			
				e.preventDefault();
				
				if(t.scope_ok){
					t.restore_image_preview();
				}
				
			});
				
		}, // init
		
		/**
		 * Set the object variables necessary to complete the 'image preview' action
		 */
		set_object_vars : function(){
		
			var t = this;	// This object
			
			switch(t.scope){
				case 'header' :
					t.media_manager_title = 'Select a custom admin header image';
					t.logo_id_field = $('input[name="branding[header_logo]"]', '#admin-area-branding-page');
					t.original_logo_id = $('input[name="branding[original_header_logo]"]', '#admin-area-branding-page').val(),
					t.preview_action = 'preview_custom_branding_image_logo';
					t.preview_container = $('.logo-preview-span', '#admin-area-branding-page');
					break;
				case 'login' :
					t.media_manager_title = 'Select a custom admin login image';
					t.logo_id_field = $('input[name="branding[login_logo]"]', '#admin-area-branding-page');
					t.original_logo_id = $('input[name="branding[original_login_logo]"]', '#admin-area-branding-page').val(),
					t.preview_action = 'preview_custom_branding_image_logo';
					t.preview_container = $('.logo-preview-span', '#admin-area-branding-page');
					break;
				default:
					return false;
			}
			
			return true;
			
		}, // set_object_vars
		
		/**
		 * Setup an instance of the media manager and display it to the user
		 */
		open_media_manager : function(){
		
			var t = this;	// This object
			
			/** Ensure the 'aab_media_frame' media manager instance already exists (i.e. if it's already been used since the page was loaded) */
			if(!t.aab_media_frame){
				
				t.aab_media_frame = wp.media.frames.aab_media_frame = wp.media({
				
					/** Set the parameters for the media uploader */
					button: { text: 'Select image' },							// Set the text of the button.
					className: 'media-frame custom-admin-branding-media-frame',	// The class to use for this instance of the media manager
					library: { type: 'image' },									// Ensure only images are allowed
					multiple: false,											// Disable multiple selections
					title: t.media_manager_title								// The tile to show in the media manager
					
				});
				
				t.aab_media_frame.on('select', function(){
					
					/** Grab the attachment selection and construct a JSON representation of the model */
					var media_attachment = t.aab_media_frame.state().get('selection').first().toJSON();
					
					/** Update the hidden 'branding[header_logo]' field with the ID of the selected logo */
					t.logo_id_field.val(media_attachment.id);
					
					/** Update the preview of the image that is being used */
					t.update_image_preview();
					
				});
				
			}
			
			/** Finally, open the media manager */
			t.aab_media_frame.open();
			
		}, // load
		
		/**
		 * Update a logo image preview
		 */
		update_image_preview : function(){
		
			var t = this;	// This object
			
			/** Set the attributes to update for the preview */
			var data = {
				security:	$('input[name="_wpnonce"]', '#admin-area-branding-page').val(),
				action:		t.preview_action,
				logo:		t.logo_id_field.val(),
			};
			
			/** Hide the 'Logo preview' row */
			t.preview_container.css('opacity', '0').hide();
			
			/** Show the AJAX loader */
			show_loader = t.logo_preview_loader.animate({ opacity: 1 }, function(){
				
				/** Run the AJAX request to grab the preview of the logo that has been changed */
				var request = $.post(ajaxurl, data, function(response){
				
					/** Add the preview logo to the preview logo container */
					t.preview_container.html(response);
					
				});
				
				/** Once the AJAX request is fully complete, show the preview of the logo that has been changed */
				request.done(function(){
				
					/** Hide the AJAX loader */
					t.logo_preview_loader.animate({ opacity: 0 }, 0);
					
					/** Show the 'Logo preview' row (just in case it was previously hidden) */
					t.preview_container.show().animate({ opacity: 1 }, 1000);
					
					/** Check to see if the 'Remove Image' button should be shown */
					t.check_button('remove');
					
					/** Check to see if the 'Restore Original Image' button should be shown */
					t.check_button('restore');
					
				});
				
			});
			
		}, // update_image_preview
		
		/**
		 * Remove a logo image preview
		 */
		remove_image_preview : function(){
		
			var t = this;	// This object
			
			/** Update the imgae ID hidden field value to '0' */
			t.logo_id_field.val('0');
			
			/** Hide the 'Logo preview' row */
			t.preview_container.html('<div class="tips no-margin"><span class="tip">No logo selected</span></div>');			
			
			/** Check to see if the 'Remove Image' button should be shown */
			t.check_button('remove');
			
			/** Check to see if the 'Restore Original Image' button should be shown */
			t.check_button('restore');
			
		},
		
		/**
		 * Restore a logo image preview to what it was when the page was loaded
		 */
		restore_image_preview : function(){
		
			var t = this;	// This object
			
			/** Update the logo ID in the relevant logo ID field */
			t.logo_id_field.val(t.original_logo_id)
			
			/** Restort the original image preview */
			t.update_image_preview();
			
		}, // restore_image_preview
		
		/**
		 * Cheack to see if a button (of the passed type) should be shown or hidden
		 *
		 * @param required string button_type	The type of button to check
		 */
		check_button : function(button_type){
		
			var t = this,	// This object
				button,		// The button that is to be shown/hidden
				criteria,	// The criteria to chack against
				preview_logo_id = t.logo_id_field.val();	// The ID of the preview logo that is currently being shown
				
			/** Set the vars for the relevant typ of button */
			switch(button_type){
				case 'remove' :
					button = $('.remove-logo-button', '#admin-area-branding-page');
					criteria = '0';
					break;
				case 'restore' :
					button = $('.restore-logo-button', '#admin-area-branding-page');
					criteria = t.original_logo_id;
					break;
				default:
					return false;
			}
			
			/** Check to see if the button should be shown or hidden */
			if(preview_logo_id !== criteria){
				button.show().animate({ opacity: 1 }, 1000);
			} else {
				button.animate({ opacity: 0 }, 0).hide();
			}
			
		}, // check_button
		
	};

});