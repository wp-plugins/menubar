<?php

/*
Plugin Name: Menubar
Plugin URI: http://www.dontdream.it/wp-menubar-30
Description: Configurable menus with your choice of menu templates.
Version: 3.0
Author: andrea@dontdream.it
Author URI: http://www.dontdream.it/
*/

/*  Copyright 2007-2009  www.dontdream.it  (email : andrea@dontdream.it)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined ('WP_PLUGIN_URL'))
	define ('WP_PLUGIN_URL', get_option ('siteurl'). '/wp-content/plugins');
if (!defined ('WP_PLUGIN_DIR'))
	define ('WP_PLUGIN_DIR', ABSPATH. 'wp-content/plugins' );

$wpm_options = new stdClass;
$wpm_options->admin_name	= 'Menubar';
$wpm_options->menubar_dir	= '/menubar';
$wpm_options->admin_file	= 'menubar/wpm-admin.php';
$wpm_options->form_action	= get_option ('siteurl'). '/wp-admin/edit.php?page='. $wpm_options->admin_file;
$wpm_options->php_file    	= 'wpm3.php';
$wpm_options->table_name  	= 'menubar3';
$wpm_options->function_name	= 'wpm_display_';
$wpm_options->menu_type   	= 'Menu';
$wpm_options->wpm_version 	= '3.0';

function wpm_readnode ($node_id)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT * FROM $table_name WHERE id = '$node_id'";
	$node = $wpdb->get_row ($sql);

	return $node;
}

function wpm_create ()
{
	global $wpdb, $wpm_options;

	$charset_collate = '';

	if (version_compare (mysql_get_server_info (), '4.1.0', '>='))
	{
		if (!empty ($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if (!empty ($wpdb->collate))
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	$table_name = $wpdb->prefix . $wpm_options->table_name;
	$table_name = $wpdb->prefix . 'menubar3';

	$sql = "CREATE TABLE $table_name (
  		`id`        	bigint(20)   NOT NULL auto_increment,
  		`name`      	varchar(255) NOT NULL default '',
  		`type`      	varchar(255) NOT NULL default '',
  		`selection` 	varchar(255) NOT NULL default '',
  		`cssclass`		varchar(255) NOT NULL default '',
  		`attributes`	varchar(255) NOT NULL default '',
  		`side`      	bigint(20)   NOT NULL default '0',
  		`down`      	bigint(20)   NOT NULL default '0',
  		PRIMARY KEY (`id`)
		) $charset_collate; ";

	require_once (ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta ($sql);

	return true;
}

function wpm_drop ()
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "DROP TABLE $table_name";

	$wpdb->query ($sql);
	return true;
}

function wpm_add_pages ()
{
	global $wpm_options;

	add_management_page ($wpm_options->admin_name,
		$wpm_options->admin_name, 8, $wpm_options->admin_file);

	return true;
}

function wpm_css ($template='', $css='')
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT DISTINCT selection, cssclass FROM $table_name 
			WHERE type = '$wpm_options->menu_type'";
 
	$rows = $wpdb->get_results ($sql);
	
	echo "\n<!-- WP Menubar $wpm_options->wpm_version: start CSS -->\n"; 
	
	if ($template) 
		wpm_include ($template, $css);
		
	foreach ($rows as $row)
		wpm_include ($row->selection, $row->cssclass);
		
	echo "<!-- WP Menubar $wpm_options->wpm_version: end CSS -->\n"; 

	return true;
}

function wpm_include ($template, $css)
{
	global $wpm_options;

	$url = WP_PLUGIN_URL . $wpm_options->menubar_dir;
	$root = WP_PLUGIN_DIR . $wpm_options->menubar_dir;

	if ($css)
		if (!file_exists ("$root/$template/$css"))
			echo "<br /><b>WP Menubar error</b>:  File $template/$css not found!<br />\n";
		else
			echo '<link rel="stylesheet" href="' . "$url/$template/$css" .  
				 '" type="text/css" media="screen" />' . "\n";
	
	if (!file_exists ("$root/$template/$wpm_options->php_file"))
		echo "<br /><b>WP Menubar error</b>:  File $template/$wpm_options->php_file not found!<br />\n";
	else
		include_once ("$root/$template/$wpm_options->php_file");

	return true;
}

function wpm_display ($menuname, $template='', $css='')
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT * FROM $table_name
		WHERE name = '$menuname' AND type = '$wpm_options->menu_type'";
		
	$menu = $wpdb->get_row ($sql);

	if ($template == '') $template = $menu->selection;
	if ($css == '') $css = $menu->cssclass;

	$version = $wpm_options->wpm_version;
	$root = WP_PLUGIN_DIR . $wpm_options->menubar_dir;
	$function = $wpm_options->function_name . $template; 

	if ($menu == '')
		echo "<br /><b>WP Menubar error</b>:  Menu $menuname not found!<br />\n";
	elseif (!file_exists ("$root/$template"))
		echo "<br /><b>WP Menubar error</b>:  Template $template not found!<br />\n";
	else
	{
		echo "<!-- WP Menubar $version: start menu $menuname, template $template, CSS $css -->\n";
		if (function_exists ($function))
			$function ($menu, $css);
		else
			echo "<br /><b>WP Menubar error</b>:  Function $function() not found!<br />\n";
		echo "<!-- WP Menubar $version: end menu $menuname, template $template, CSS $css -->\n";
	}

	return true;
}

include ('wpm-menu.php');

function wpm_is_descendant ($ancestor)
{
	global $wpdb;
	global $wp_query;

	if (!$wp_query->is_page)  return false;
	if (!$ancestor)  return true;
	
	$page_obj = $wp_query->get_queried_object();
	$page = $page_obj->ID;
	if ($page == $ancestor)  return true;

	while (1)
	{
		$sql = "SELECT * FROM $wpdb->posts WHERE ID = $page";
		$post = $wpdb->get_row ($sql);

		$parent = $post->post_parent;
		if ($parent == 0)  return false;
		if ($parent == $ancestor)  return true;

		$page = $parent;
	}
}

add_action ('activate_menubar/wpm-main.php', 'wpm_create');
add_action ('admin_menu', 'wpm_add_pages');
add_action ('wp_head', 'wpm_css', 10, 2);
add_action ('wp_menubar', 'wpm_display', 10, 3);

load_plugin_textdomain ('wpm', 'wp-content/plugins/menubar');

?>
