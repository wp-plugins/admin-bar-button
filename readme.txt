=== Admin Bar Button ===
Contributors: duck__boy
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3DPCXL86N299A
Tags: admin bar, admin, bar, jquery ui, jquery ui, widget factory, widget, factory, duck__boy
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Replace the default WordPress admin bar on the front end with a simple button.

== Description ==

Admin Bar Button is a plugin that will create a simple button to replace the default WordPress admin bar on the front end.
When using this plugin, the full height of the page is used by your site, which is particularly handy if you have fixed headers.
Please see the [Screenshots tab](http://wordpress.org/plugins/admin-bar-button/screenshots/ "Admin Bar Button &raquo; Screenshots") to see how the Admin Bar Button looks.

After activating the plugin, if you wish you can change how the Admin Bar Button looks and works by visiting the **Settings** page (*Settings &raquo; Admin Bar Button*).
However, **no user interaction is required** by the plugin; if you wish, you can simply install and activate Admin Bar Button and it'll work right away.

== Installation ==

= If you install the plugin via your WordPress blog =
1. Click 'Install Now' underneath the plugin name
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Job done!

= If you download from http://wordpress.org/plugins/ =

1. Upload the folder `admin-bar-button` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it!

== Frequently Asked Questions ==

= Can I change how the Admin Bar Button looks and works? =

Yes, there are several settings that you can alter if you so wish. To do this, simply
visit the plugin settings page at '**Settings -> Admin Bar Button**' and set the options
as you wish them to be.

= What do all of the options mean? =

***The Admin Bar Button***

* **Button Text**		> The text to display in the Admin Bar Button
* **Text Direction**		> The direction of the Admin Bar Button text
* **Position on the Screen**	> Where on the screen to position the Admin Bar Button
* **Slide Direction**		> The side of the screen from which the Admin Bar Button will exit (and enter)
* **Slide Duration**		> The time (in milliseconds) that it takes for the Admin Bar Button to slide off of (and on to) the screen

***The Admin Bar***

* **Slide Direction**		> The side of the screen from which the Admin Bar will enter (and exit)
* **Slide Duration**		> The time (in milliseconds) that it takes for the Admin Bar to slide on to (and off of) the screen
* **Show Time**			> The time (in milliseconds) that the Admin Bar will be visible for, when shown

== Screenshots ==

1. The minimised Admin Bar Button, shown when the Admin Bar is not active.
2. The regular Admin Bar, as shown here, is still available when the Admin Bar Button is hovered over.
3. The plugin settings page.

== Changelog ==

= 2.2 =
* New option to choose which action upon the Admin Bar Button shows the Admin Bar; Click and hover, click, or hover

= 2.1.1 =
* Fix error where sometimes the space originally ocupied by the admin bar was still being added to the page

= 2.1 =
* **Critical Fix** - Fix a possible JS error when a visitor to the site is not logged in
* Creation a text domain for future foreign language support
* Updates to the FAQ's

= 2.0 =
* New admin menu available for setting Admin Bar Button options; now there is no need to edit any JS or PHP to get the button the way you want it.
* Minor bug fix to the adminBar jQuery UI widget

= 1.1 =
* Minor changes to function names to avoid possible clashes
* Minor changes to the adminBar jQuery UI widget
* Addition of screen shots
* Updates to the FAQ's
* Important update to the installation instrustions

= 1.0 =
* First release on the WordPress repository

== Upgrade Notice ==

The latest version fixes a possible JS error when non-logged in users are visiting your website.