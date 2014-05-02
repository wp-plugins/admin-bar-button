/**
 * @package:		WordPress
 * @subpackage:		Admin Area Branding
 * @Description:	Custom jQuery UI 'switchButton' widget for implementing a toggle switch button in place of a checkbox
 * @Note:			This widget will work only on checkboxes, and nothing else
 */

$ = jQuery.noConflict();

$(function(){
	
	$.widget('DJGUI.switchButton', {
	
		options : {
			
			checked:			undefined,		// Whether or not to show the switch button as 'checked' (will take the current value if 'undefined')
			display:			'inline-block',	// How to display the widget
			
			width:				60,				// The overall width of the widget
			height:				18,				// The overall height of the widget
			widget_css:			{},				// Optional CSS for the widget
			widget_on_css:		{				// Optional CSS for the widget when in the 'on' state
				'background-color':	'#EAF1DD'
			},
			widget_off_css:		{				// Optional CSS for the widget when in the 'off' state
				'background-color':	'#F2DBDB'
			},
			
			label_on:			'On',			// The label to use for the 'on' state
			label_off:			'Off',			// The label to use for the 'off' state
			label_css:			{},				// Optional CSS for the labels (note that 'line-height' and 'width' will always be overwritten by this plugin)
			label_on_css:		{				// Optional CSS for the 'on' state label
				'color': '#333333'
			},
			label_off_css:		{				// Optional CSS for the 'off' state label
				'color': '#333333'
			},
			
			button_css:			{},				// Optional CSS for the switch button			
			button_on_css:		{				// Optional CSS for the switch button when in the 'on' state
				'background-color':		'#DFDFDF',
				'border-right-width':	'0',
				'border-left-width':	'1px'
			},
			button_off_css:		{				// Optional CSS for the switch button when in the 'off' state
				'background-color':		'#DFDFDF',
				'border-right-width':	'1px',
				'border-left-width':	'0'
			}
			
        }, // options
		
		/**
		 * Constructor
		 */
		_create : function(){
		
			/** Ensure that the 'checked' state is valid and is set */
			this._validate_element();
			if(!this.valid){
				return false;
			}
			
			/** Ensure that the 'checked' state is valid and is set */
			this._validate_checked_option();
			
			/** Initialise the layout of the widget */
			this._create_layout();
			
			/** Initialise the events which can be triggered by this widget */			
			this._create_events();
			
			/** Finally show the widget to the user */
			this._show();
			
		}, // _create
		
		/**
		 * Validate the selector that this instance of 'switchButton' was called upon and ensure it is a checkbox
		 */
		_validate_element : function(){
		
			this.valid = (this.element.is('input:checkbox')) ? true : false;
			
		}, // _validate_element
		
		/**
		 * Validate the 'checked' option to ensure that it is valid
		 */
		_validate_checked_option : function(){
			
			/** If the user chose not to set the state of the switch, grab it from the receiver checkbox */
			if(this.options.checked === undefined || typeof this.options.checked !== 'boolean'){
                this.options.checked = this.element.prop('checked');
            }
			
		}, // _validate_checked_option
		
		/**
		 * Create the layout of the widget
		 */
		_create_layout : function(){
		
			/** Hide the receiver checkbox for this widget */
			this.element.hide();
			
			/** Create the relevant DOM objects for the switch button */
            this.button_container = $('<div>').addClass('dd-switch-button-container');
            this.button = $('<div>').addClass('dd-switch-button');
			this.label_on = $('<span>').addClass('dd-switch-button-label');
			this.label_off = $('<span>').addClass('dd-switch-button-label');
			
            /** Insert the switch button in to the DOM */
            this.button_container.insertAfter(this.element);
			this.button_container.append(this.label_on);
			this.button_container.append(this.label_off);
            this.button_container.append(this.button);
			
            // Call refresh to update labels text and visibility
            this._format_layout();
			
		}, // _create_layout
		
		/**
		 * Format the layout of the widget (using the options, either default or supplied by the user)
		 */
		_format_layout : function(){
		
			this._format_container();
			this._format_labels();
			this._format_button();
			
		}, // _format_layout
		
		/**
		 * Format the container of the widget
		 */
		_format_container : function(){
		
			/** Set the height and width of the button container */
			this.button_container.width(this.options.width);
			this.button_container.height(this.options.height);
			
			/** Set the correct 'on' or 'off' state class for the button container */
			this._set_state_class(this.button_container);
			
			/** Set the button container CSS */
			this.button_container.css(this.options.widget_css);
			
		}, // _format_container
		
		/**
		 * Format the 'on' and 'off' labels created within the widget (or whatever possible user values they have)
		 */
		_format_labels : function(){	
			
			/** Set the label width */
			this.options.label_width = this.options.width / 2;
			
			/** Merge the required defaults for the label CSS with any options supplied by the user */
			this.label_css = $.extend(this.options.label_css, {
				'line-height': this.options.height+'px',
				'width': this.options.label_width+'px'
			});
			
			/** Set the CSS to give to the 'on' and 'off' labels */			
			var label_css_on = new this._get_css_object(this.label_css, this.options.label_on_css);
			var label_css_off = new this._get_css_object(this.label_css, this.options.label_off_css);
			
			/** Set the CSS of the 'on' and 'off' labels */
			this.label_on.css(label_css_on);
			this.label_off.css(label_css_off);
			
			/** Set the 'on' and 'off' label text */
			this.label_on.html(this.options.label_on);
			this.label_off.html(this.options.label_off);
			
		}, // _format_labels
		
		/**
		 * Format the switch button created by the widget
		 */
		_format_button : function(){
		
			/** Set the height and width of the switch button */
			this.button.width(this.options.width / 2 - 1); // /2 as only one label should be covered, -1 for the border on the outside edge of the switch button
			this.button.height(this.options.height);
			
			/** Set the switch button CSS */
			this.button.css(this.options.button_css);
			
			/** Set the correct 'on' or 'off' state class for the button */
			this._set_state_class(this.button);
			
		}, // _format_button
		
		/**
		 * Create events triggered by actions on this widget
		 */
		_create_events : function() {
		
			var t = this;	// This object
			
			this.button_container.on('click', function(e){
			
				/** Ensure default actions are prevented and that only the 'button_container' div can capture the click */
				e.preventDefault();
                e.stopPropagation();
				
				/** Toggle the switch button */
				t._toggle();
				
			});

        }, // _create_events
		
		/**
		 * Show the switch button (Applies the correct CSS but does not animate any changes)
		 */
		_show : function(){
		
			/** Set the CSS which is to be used for the current state of the switch button */
			this._set_state_css();
			
			/** Update the CSS for the widget container and the switch button */
			this.button.css(this.button_css);
			this.button_container.css(this.button_container_css).css('display', this.options.display);
			
		}, // show
		
		/**
		 * Toggle the switch button, changing it's state from 'on' to 'off', or 'off' to 'on'
		 */
		_toggle : function(){
			
			/** Set the switch state (whether or not it is checked) */
			this._set_state();
		
			/** Set the CSS which is to be used for the switch */
			this._set_state_css();
			
			/** Set the correct 'on' or 'off' state class for the button container and the button */
			this._set_state_class(this.button_container);
			this._set_state_class(this.button);
			
			/** Animate the button toggle */
			this.button_container.animate(this.button_container_css, 300);
			this.button.animate(this.button_css, 300);
			
		}, // _toggle
		
		/**
		 * Set the correct 'on' or 'off' state class for the passed element (depending on the state of 'this.options.checked')
		 *
		 * @param required object element	The element to add the correct state calss to
		 */
		_set_state_class : function(element){
		
			/** Ensure that a jQuery element was passed */
			if(element instanceof jQuery === false){
				return false;
			}
			
			if(this.options.checked){
				element.removeClass('off').addClass('on');
			} else {
				element.removeClass('on').addClass('off');
			}
			
		}, // _set_button_class
		
		/**
		 * Set the correct CSS properties for the widget container and the switch button (depending on the state of 'this.options.checked')
		 */
		_set_state_css : function(){
			
			switch(this.options.checked){
			
				case true:
					var button_container_state_css = this.options.widget_on_css
					var button_left = this.options.label_width;
					var button_state_css = this.options.button_on_css;
					break;
				case false:
					var button_container_state_css = this.options.widget_off_css
					var button_left = '0';
					var button_state_css = this.options.button_off_css;
					break;
			}
			
			this.button_container_css = new this._get_css_object(button_container_state_css);
			this.button_css = new this._get_css_object(button_state_css, { 'left': button_left });
			
		}, // _set_state_css
		
		/**
		 * Set the correct state of the switch button, and update the state of 'this.options.checked'
		 */
		_set_state : function(){
		
			if(this.options.checked){
				this.element.removeAttr('checked');
			} else {
				this.element.prop('checked', 'true');
			}
			
			this.options.checked = this.element.prop('checked');
			
		}, // _set_state
		
		/**
		 * Creates an instance of an object for setting supplied CSS properties
		 *
		 * @param properties object			The preset properties (probably from this.options.something) to use for this 'objCss' instance
		 * @param additional object			Any additional properties (created on-the-fly) to add to this 'objCss' instance
		 */
		_get_css_object : function(properties, additional){
			
			var t = this;	// The _label_css object
			
			if(typeof properties === 'object'){
				$.each(properties, function(key, value){
					t[key] = value;
				});
			}
			
			if(typeof additional === 'object'){
				$.each(additional, function(key, value){
					t[key] = value;
				});
			}
			
		} // _get_css_object
		
	});
	
});