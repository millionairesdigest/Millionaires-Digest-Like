=== Like And Who Likes ===
Contributors: Atonyk
Tags: like, vote, rate, social, wordpress, buddypress, bbpress
Requires at least: 4.6
Tested up to: 4.7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds the 'Like' button and 'Who Likes' list for WordPress, BuddyPress and BBPress.

== Description ==

This plugin adds the 'Like' button and 'Who Likes' list to:

* WordPress posts and comments
* BuddyPress activities and comments
* BBPress posts

It allows for registered users to like the items. And it shows for both registered and unregistered users the existing likes.

The components to show the likes to can be configured on the settings page. For example, the likes can be disabled for Wordpress comments.

Likes are saved in the internal Wordpress and BuddyPress meta tables. No separate tables are created. 

The plugin cleans all its data on uninstallation (but not on deactivation).

You can contribute on - [https://github.com/ansnap/like-and-who-likes](https://github.com/ansnap/like-and-who-likes)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/like-and-who-likes` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to 'Settings' -> 'Like And Who Likes' to configure the plugin

== Frequently Asked Questions ==

= Can I style the 'Like' button and 'Who Likes' list? =

Yes, there are the CSS classes:

* for the button: '.wl-like' and '.wl-unlike' added when the user liked the post
* for the list: '.wl-list'

== Screenshots ==

1. Wordpress post
2. BuddyPress activity

== Changelog ==

= 1.3.1 =
* Fixed a bug caused uninstallation problems when BuddyPress was not installed
* Fixed a bug that was preventing the like button from working in Wordpress blogs if BuddyPress was not installed

= 1.3.0 =
* Added the settings page to be able to enable/disable the likes in Wordpress, BuddyPress and BBPress

= 1.2.0 =
* BuddyPress - the 'Like' button now is only shown when the activity is available for commenting. Because if the activity is not commentable it means that it refers to a blog post or forum post, which in turn is available for liking on its own page.
* BBPress - the 'Like' button moved to its logical place - to the post links panel.
* The 'Who Likes' block is wrapped in the paragraph instead of the div to inherit content's margins.

= 1.1.0 =
* Added support for Wordpress blog posts and comments.

= 1.0.0 =
* Initial version.
