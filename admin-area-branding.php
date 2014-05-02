<?php
/**
 * Plugin Name:	Admin Area Branding
 * Description: Custom brand the admin area of your Wordpress install quickly and easily. Easily switch between the Wordpress defaults and your custom settings - without losing any of your custom settings.
 * Author:		David Gard
 * Version:		1.3.1
 * Text Domain: admin_area_branding
 *
 * Copyright 2014 David Gard.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Avoid direct calls to this file where WP core files are not present
 */
if(!function_exists('add_action')) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;

/** Set the required database table versions and $wpdb class variables for this plugin */
aab_set_plugin_globals();

/** Register an activation hooks so that the table is added when the plugin is activated*/
register_activation_hook(__FILE__, 'aab_set_plugin_globals');
register_activation_hook(__FILE__, 'aab_add_db_table');

/** Register an uninstall hooks so that the table is deleted when the plugin is uninstalled*/
register_uninstall_hook(__FILE__, 'aab_set_plugin_globals');
register_uninstall_hook(__FILE__, 'aab_delete_db_table');

/** Include the 'functions.php' file */
include_once('functions.php');

/** Register the custom wp_die handler for this plugin */
//add_filter('wp_die_handler', 'aab_register_die_handler');

/**
 * Set the required database table versions and $wpdb class variables for this plugin
 */
function aab_set_plugin_globals(){
	
	global $branding_db_version, $wpdb;

	$branding_db_version = '1.7';
	
	$wpdb->dd_prefix = 'dd_';
	$wpdb->admin_branding = $wpdb->dd_prefix.'admin_branding';
	
}

/**
 * Add the correct version of the 'admin_branding' database table when the plugin is activated
 */
function aab_add_db_table(){
	
	global $branding_db_version, $wpdb;
	
	$installed_version = get_option('dd_options_amdin_branding_db_table');
	
	if($installed_version !== $branding_db_version) :
		
		$query = $wpdb->prepare('
			CREATE TABLE IF NOT EXISTS `%1$s` (
				`ID` smallint(3) NOT NULL auto_increment,
				`option_name` varchar(64) character set latin1 collate latin1_general_ci NOT NULL default "",
				`option_value` longtext character set latin1 collate latin1_general_ci NOT NULL,
				PRIMARY KEY (`ID`),
				UNIQUE KEY `option_name` (`option_name`)
			)
			ENGINE=InnoDB
			DEFAULT CHARSET=utf8;
		', $wpdb->admin_branding
		);
		$result = $wpdb->query($query);
		
		/** Debug the query */
		$debug = new DD_Debug;
		$debug->debug('', '', 'add_admin_branding_table', 'Add the admin branding database table');
		
		if(!$debug->sql_error_found) :
			update_option('dd_options_amdin_branding_db_table', $branding_db_version);
		else :
			$debug->output_errors();
		endif;
		
	endif;
	
}

/**
 * Delete the 'admin_branding' database table when the plugin is uninstalled
 */
function aab_delete_db_table(){
    
	global $wpdb;

    $query = $wpdb->prepare('DROP TABLE IF EXISTS %1$s', $wpdb->admin_branding);
	$wpdb->query($query);
	
	/** Debug the query */
	$debug = new DD_Debug;
	$debug->debug('', '', 'add_admin_branding_table', 'Add the admin branding database table');
	
	if(!$debug->sql_error_found) :
		delete_option('dd_options_amdin_branding_db_table');
	else :
		$debug->output_errors();
	endif;
	
}
?>