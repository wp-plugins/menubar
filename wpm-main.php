<?php

/*
Plugin Name: Menubar
Plugin URI: http://www.dontdream.it/wp-menubar-3-documentation
Description: Configurable menus with your choice of menu templates.
Version: 4.0
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

global $wpm_options;
$wpm_options = new stdClass;
$wpm_options->admin_name	= 'Menubar';
$wpm_options->menubar_dir	= '/menubar';
$wpm_options->templates_dir	= '/menubar-templates';
$wpm_options->admin_file	= 'menubar/wpm-admin.php';
$wpm_options->form_action	= get_option ('siteurl'). '/wp-admin/themes.php?page='. $wpm_options->admin_file;
$wpm_options->php_file    	= 'wpm3.php';
$wpm_options->table_name  	= 'menubar3';
$wpm_options->function_name	= 'wpm_display_';
$wpm_options->menu_type   	= 'Menu';
$wpm_options->wpm_version 	= '4.0';

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

	$sql = "CREATE TABLE $table_name (
  		`id`        	bigint(20)   NOT NULL auto_increment,
  		`name`      	varchar(255) NOT NULL default '',
  		`type`      	varchar(255) NOT NULL default '',
  		`selection` 	varchar(255) NOT NULL default '',
  		`cssclass`		varchar(255) NOT NULL default '',
  		`attributes`	varchar(255) NOT NULL default '',
  		`options`		longtext     NOT NULL default '',
		`side`      	bigint(20)   NOT NULL default '0',
  		`down`      	bigint(20)   NOT NULL default '0',
  		PRIMARY KEY (`id`)
		) $charset_collate; ";

	require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
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

	$page = add_submenu_page ('themes.php', __('Manage Menubar', 'wpm'),
		$wpm_options->admin_name, 8, $wpm_options->admin_file);
		
	add_action ("admin_print_scripts-$page", 'wpm_scripts');

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

	$url = WP_PLUGIN_URL . $wpm_options->templates_dir;
	$root = WP_PLUGIN_DIR . $wpm_options->templates_dir;

	if (!file_exists ("$root"))
		echo "<br /><b>WP Menubar error</b>:  Folder wp-content/plugins$wpm_options->templates_dir not found!<br />\n<br />Please create that folder and install at least one Menubar template.<br />\n";
	elseif (!file_exists ("$root/$template"))
		echo "<br /><b>WP Menubar error</b>:  Folder wp-content/plugins$wpm_options->templates_dir/$template not found!<br />\n";
	elseif ($template && !file_exists ("$root/$template/$wpm_options->php_file"))
		echo "<br /><b>WP Menubar error</b>:  File $template/$wpm_options->php_file not found in wp-content/plugins$wpm_options->templates_dir!<br />\n";
	elseif ($css && !file_exists ("$root/$template/$css"))
		echo "<br /><b>WP Menubar error</b>:  File $template/$css not found in wp-content/plugins$wpm_options->templates_dir!<br />\n";
	else
	{
		if ($template)
			include_once ("$root/$template/$wpm_options->php_file");
		if ($css)
			echo '<link rel="stylesheet" href="'. "$url/$template/$css". '" type="text/css" media="screen" />' . "\n";
		return true;
	}

	return false;
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
	$root = WP_PLUGIN_DIR . $wpm_options->templates_dir;
	$function = $wpm_options->function_name . $template; 

	if (!file_exists ("$root"))
		echo "<br /><b>WP Menubar error</b>:  Folder wp-content/plugins$wpm_options->templates_dir not found!<br />\n<br />Please create that folder and install at least one Menubar template.<br />\n";
	elseif (!file_exists ("$root/$template"))
		echo "<br /><b>WP Menubar error</b>:  Folder wp-content/plugins$wpm_options->templates_dir/$template not found!<br />\n";
	elseif ($menu == '')
		echo "<br /><b>WP Menubar error</b>:  Menu $menuname not found in database!<br />\n";
	elseif ($template == '') 
		echo "<br /><b>WP Menubar error</b>:  No template selected for menu $menuname!<br />\n";
	elseif (!function_exists ($function))
		echo "<br /><b>WP Menubar error</b>:  Function $function() not found!<br />\n";
	else
	{
		echo "<!-- WP Menubar $version: start menu $menuname, template $template, CSS $css -->\n";
		$function ($menu, $css);
		echo "<!-- WP Menubar $version: end menu $menuname, template $template, CSS $css -->\n";
		return true;
	}

	return false;
}

include ('wpm-menu.php');

function wpm_is_descendant ($ancestor)
{
	global $wpdb;
	global $wp_query;

	if (!$wp_query->is_page)  return false;
	if (!$ancestor)  return true;
	
	$page_obj = $wp_query->get_queried_object ();
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

if (class_exists ('WP_Widget'))
{
class WP_Widget_Menubar extends WP_Widget
{
	function WP_Widget_Menubar ()
	{
		$widget_ops = array ('description' => __('Select a menu to display', 'wpm'));
		$this->WP_Widget ('Menubar', 'Menubar', $widget_ops);
	}

	function widget ($args, $instance)
	{
		extract ($args);
		$title = apply_filters ('widget_title', esc_attr ($instance['title']));
	
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		do_action ('wp_menubar', $instance['menu']);
		echo $after_widget;
	}

	function update ($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args ((array) $new_instance, array ('title' => '', 'menu' => ''));
		$instance['title'] = strip_tags ($new_instance['title']);
		$instance['menu'] = strip_tags ($new_instance['menu']);
		return $instance;
	}

	function form ($instance)
	{
		$instance = wp_parse_args ((array) $instance, array ('title' => '', 'menu' => ''));
		$title = strip_tags ($instance['title']);
		$menu = strip_tags ($instance['menu']);
	?>
		<p>
		<label for="<?php echo $this->get_field_id ('title'); ?>"><?php _e ('Title:', 'wpm'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id ('title'); ?>" name="<?php echo $this->get_field_name ('title'); ?>" type="text" value="<?php echo esc_attr ($title); ?>" />
		<label for="<?php echo $this->get_field_id ('menu'); ?>"><?php _e ('Menu name:', 'wpm'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id ('menu'); ?>" name="<?php echo $this->get_field_name ('menu'); ?>" type="text" value="<?php echo esc_attr ($menu); ?>" />
		</p>
	<?php
	}
}

add_action ('widgets_init', 'wpm_widget_init');
function wpm_widget_init ()
{
	register_widget ('WP_Widget_Menubar');
}
}

function wpm_ajax ()
{
	$command = $_POST['command'];
	$type = $_POST['type'];
	
	switch ($command)
	{
	case 'typeargs':
		include_once ('wpm-edit.php');
		wpm_typeargs ($type);
		break;
		
	default:
		echo "bad ajax command received: $command\n";
		break;
	}
	
	exit;
}

function wpm_scripts ()
{
}

register_activation_hook (__FILE__, 'wpm_create');
add_action ('admin_menu', 'wpm_add_pages');
add_action ('wp_ajax_menubar', 'wpm_ajax');

add_action ('wp_head', 'wpm_css', 10, 2);
add_action ('wp_menubar', 'wpm_display', 10, 3);

load_plugin_textdomain ('wpm', 'wp-content/plugins/menubar');
?>
