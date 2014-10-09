<?php
/**
 * @package TwiST
 */
/*
Plugin Name: Twilio SMS Tool
Version: 0.0.2
Author: Chandu
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'TWIST__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TWIST__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( TWIST__PLUGIN_DIR . 'class.twist-installer.php' );

register_activation_hook( __FILE__, array( "Twist_Installer", "install"));
register_uninstall_hook(__FILE__, array( "Twist_Installer","uninstall"));
register_deactivation_hook(__FILE__, array( "Twist_Installer","uninstall"));

require_once( TWIST__PLUGIN_DIR . 'class.twist-settings-manager.php' );
require_once( TWIST__PLUGIN_DIR . 'class.twist-tool-manager.php' );

Twist_Settings_Manager::init();
Twist_Tool_Manager::init();

