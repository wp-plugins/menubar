=== Menubar ===
Tags: menu, menubar, navigation, dropdown, suckerfish, superfish
Requires at least: 2.6
Tested up to: 2.9.2
Stable tag: 4.6

Single and multi-level menus for your WordPress site, styled with customizable menu templates.

== Description ==

With Menubar you can easily build and manage single and multi-level menus for your WordPress site.

* Build your menus choosing menu items among Home, FrontPage, Category, CategoryTree, Page, PageTree, Heading, Post, SearchBox, External, Custom, in any combination
* Display a menu inserting in your theme, e.g. at the end of *header.php*, the line `<?php do_action ('wp_menubar', 'your_menu_name'); ?>`
* Use the *Menubar* widget to display your menus in a widgetized area / sidebar (only with WordPress 2.8 or higher)
* Style your menus using Menubar templates - a few easily customizable templates are already available, and you can add new templates without changing the plugin code
* Upgrade automatically to a newer Menubar version without losing your template customizations

A Menubar live demo is available on the [WP Menubar demo site](http://www.dontdream.it/demo/).

== Installation ==

See the [First installation procedure](http://www.blogsweek.com/wp-menubar-3-documentation#First%20installation%20procedure) or the [Upgrading procedure](http://www.blogsweek.com/wp-menubar-3-documentation#Upgrading%20procedure).

== Frequently Asked Questions ==

See the [WP Menubar documentation](http://www.blogsweek.com/wp-menubar-3-documentation).

== Screenshots ==

Visit the [WP Menubar demo site](http://www.dontdream.it/demo/) or the [WordPress Menubar](http://www.blogsweek.com/category/wordpress-menubar) category archive. 

== Changelog ==

= 4.6 =
* Added the Tag and TagList types (only with new template versions)
* Added the Depth field for the PageTree and CategoryTree types 
= 4.5 =
* Added support for icons in menu items (only with new templates)
* Fixed a bug affecting single and double quote characters that were incorrectly escaped 
= 4.4 =
* Improved performance moving the Menubar data from a DB table to a serialized option, so just a single DB call per page is needed
= 4.3 =
* Fixed a bug affecting the *Add Menu Item* and *Edit Menu Item* forms in IE6 and IE7
= 4.2 =
* Added support for the wp-config.php FORCE\_SSL\_ADMIN definition
* Added support for the new Superfish template
= 4.1 =
* Improvements to the new template structure - older templates are supported as well
* Added the Custom type to insert custom HTML in your menu (only with new templates)
* The name of a menu item is automatically generated when the *Name* field is left blank (only with new templates)
= 4.0 =
* New template structure for better customization - old templates are supported as well
* Added the SearchBox type to integrate a search box in your menubar (only with new templates)
* Moved the *Menubar* admin page under the *Appearance* admin menu
= 3.6 =
* Improved the first installation procedure with more user-friendly messages
= 3.5 =
* Added the *Menubar* widget to display menus in a widgetized area (only with WordPress 2.8 or higher)
= 3.4 =
* Improved compatibility with the qTranslate plugin
= 3.3 =
* Fixed a bug affecting menu names containing a space, and a bug in a special menu reordering case
= 3.2 =
* Added more options to easily rearrange the order of your menu items
= 3.1 =
* Menubar templates are now stored in the *menubar-templates* folder, so future automatic upgrades won't overwrite your customizations
= 3.0 =
* First version hosted in the WordPress Plugin Directory
