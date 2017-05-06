<?php

/**
 * Plugin Name: Like And Who Likes
 * Author: Anton Chernov
 * Author URI: http://pcnotes.ru
 * Version: 1.3.0
 * Description: 'Like' button and 'Who Likes' list for WordPress, BuddyPress and BBPress.
 * Text Domain: like-and-who-likes
 */
// Class autoloading
function who_likes_autoload( $class ) {
	$file = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
	require_once __DIR__ . "/includes/$file";
}

spl_autoload_register( 'who_likes_autoload' );

// Main code
new Who_Likes( new Who_Likes_Settings() );

// Uninstallation
function who_likes_uninstall() {
	Who_Likes::uninstall();
	Who_Likes_Settings::uninstall();
}

register_uninstall_hook( __FILE__, 'who_likes_uninstall' );

// Disable autoloading
spl_autoload_unregister( 'who_likes_autoload' );

