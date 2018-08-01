<?php

/*
Plugin Name: Millionaire's Digest Like
Description: Give users the ability to like posts types, comments, etc. set by the Founder & CEO.
Version: 1.0.0
Author: K&L (Founder of the Millionaire's Digest)
Author URI: https://millionairedigest.com/
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

