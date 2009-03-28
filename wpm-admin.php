<?php

include ('wpm-tree.php');

function wpm_get_default_menu ()
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT * FROM $table_name
		WHERE type = '$wpm_options->menu_type' ORDER BY 'id' LIMIT 1";

	$menu = $wpdb->get_row ($sql);
	return $menu->id;
}

function wpm_item_list ($item_id, $list, $level)
{
	$item = wpm_read_node ($item_id);
	
	if ($level == 0)
		$list[$item->id] = __('Top Level','wpm');
	else
		$list[$item->id] = str_repeat("&#8212; ", $level) . $item->name;
		
	if ($item->down)  $list = wpm_item_list ($item->down, $list, $level+1);
	if ($item->side)  $list = wpm_item_list ($item->side, $list, $level);
		
	return $list;
}

function wpm_page_list ($parent_id, $list, $level)
{
	global $wpdb;
	
	if ($level == 0)
		$list[0] = __('All Pages','wpm');

	$sql = "SELECT ID, post_title FROM $wpdb->posts 
			WHERE post_parent = $parent_id AND post_type = 'page' ORDER BY menu_order";

	$items = $wpdb->get_results ($sql);
	
	foreach ($items as $item)
	{
		$list[$item->ID] = str_repeat("&#8212; ", $level) . $item->post_title;
		$list = wpm_page_list ($item->ID, $list, $level+1);
	}
		
	return $list;
}
	
function wpm_cat_list ($parent_id, $list, $level)
{
	global $wpdb;
	
	if ($level == 0)
		$list[0] = __('All Categories','wpm');

	$sql = "SELECT t.term_id, t.name 
			FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt 
			ON t.term_id = tt.term_id
			WHERE tt.taxonomy = 'category' AND tt.parent = $parent_id";			

	$items = $wpdb->get_results ($sql);
	
	foreach ($items as $item)
	{
		$list[$item->term_id] = str_repeat("&#8212; ", $level) . $item->name;
		$list = wpm_cat_list ($item->term_id, $list, $level+1);
	}

	return $list;
}
	
function wpm_list_menu_items ($menuid)
{
	global $wpdb, $wpm_options;
	
	$menu = wpm_read_node ($menuid);
?>

<div class="wrap">
<p>
<?php _e('To display this menu, insert the following line in your theme, e.g. at the end of <em>header.php</em>:', 'wpm'); ?>
<br />
<strong><?php echo "&lt;?php do_action('wp_menubar','$menu->name'); ?&gt;"; ?></strong>
</p>
</div>

<div class="wrap">

<h2><?php printf(__('Manage Menu: %s', 'wpm'), $menu->name); ?></h2>

<table class="widefat">

	<thead>
	<tr>
      <th colspan="2" style="text-align: center;"><?php _e('Order', 'wpm') ?></th>
	  <th scope="col"><?php _e('Name', 'wpm') ?></th>
	  <th scope="col"><?php _e('Type', 'wpm') ?></th>
      <th scope="col"><?php _e('Selection', 'wpm') ?></th>
      <th scope="col"><?php _e('CSS class', 'wpm') ?></th>
	  <th scope="col"><?php _e('Attributes', 'wpm') ?></th>
      <th colspan="2" style="text-align: center;"><?php _e('Action', 'wpm') ?></th>
	</tr>
	</thead>
	
	<tbody id="the-list">
	
<?php if ($menu->down)  wpm_print_tree ($menu->id, $menu->down, 0, 0, ''); ?>
	
	</tbody>
</table>
</div>

<?php		
}

function wpm_print_tree ($menuid, $item_id, $prev_id, $level, $class)
{
	global $wpm_options;

	$item = wpm_read_node ($item_id);
	$next_id = $item->side;
	
	$class = ($class == "") ? "alternate" : "";

	$url = WP_PLUGIN_URL . $wpm_options->menubar_dir;

	$url_up		= $wpm_options->form_action . 
		'&amp;action=swap&amp;menuid=' . $menuid . '&amp;itemid=' . $prev_id;

	$url_down 	= $wpm_options->form_action . 
		'&amp;action=swap&amp;menuid=' . $menuid . '&amp;itemid=' . $item->id;

	$url_edit	= $wpm_options->form_action . 
		'&amp;action=edit&amp;menuid=' . $menuid . '&amp;itemid=' . $item->id;
 
	$url_delete	= $wpm_options->form_action . 
		'&amp;action=delete&amp;menuid=' . $menuid . '&amp;itemid=' . $item->id; 

	$up   = $prev_id? "<a href='$url_up' class='edit' title='".__('move up','wpm')."'>
				<img src='$url/up.gif' /></a>": "<img src='$url/up.gif' />";
	$down = $next_id? "<a href='$url_down' class='edit' title='".__('move down','wpm')."'>
				<img src='$url/down.gif' /></a>": "<img src='$url/down.gif' />";

	$edit = "<a href='$url_edit' class='edit'>" . __('Edit', 'wpm') . "</a>";
	
	$delete = "<a href='" . wp_nonce_url ($url_delete, 'delete_' . $item->id) . 
		"' class='delete'>" . __('Delete', 'wpm') . "</a>";

	echo "<tr class=\"$class\">
		<td align='center'>$up</td>
		<td align='center'>$down</td>
		<td>" . str_repeat("&#8212; ", $level) . "$item->name</td>
		<td>$item->type</td>
		<td>$item->selection</td>
		<td>$item->cssclass</td>
		<td>$item->attributes</td>
		<td align='center'>$edit</td>
		<td align='center'>$delete</td>
		</tr>\n";
		
	if ($item->down)  $class = wpm_print_tree ($menuid, $item->down, 0, $level+1, $class);
	if ($item->side)  $class = wpm_print_tree ($menuid, $item->side, $item_id, $level, $class);
		
	return $class;
}

function wpm_menu_dropdown ($menuid)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT id, name FROM $table_name
		WHERE type = '$wpm_options->menu_type'";
		
	$menus = $wpdb->get_results ($sql);

	$out = "<select name='menuid' style='width: 10em;' >\n";

	foreach ($menus as $menu) :
		$selected = ($menu->id == $menuid)? 'selected' : ''; 
		$out .= "<option value='$menu->id' $selected> $menu->name </option>\n";
	endforeach;

	$out .= "</select>\n";
	echo $out;

	return true;
}

function wpm_template_dropdown ($active_template)
{
	global $wpm_options;

	$templates = array();
	$root = WP_PLUGIN_DIR . $wpm_options->menubar_dir;

	$folders = @ dir ($root);
	if ($folders)
	{
		while (($folder = $folders->read()) !== false)
		{
			if (substr ($folder, 0, 1) == '.')  continue;
			if (is_dir ("$root/$folder"))
			{
				$found = 0;
				$cfiles = array();

				$files = @ dir ("$root/$folder");
				if ($files)
				{
					while (($file = $files->read()) !== false)
					{
						if (substr ($file, 0, 1) == '.')  continue;
						elseif ($file == $wpm_options->php_file)  $found = 1;
						elseif (substr ($file, -4) == '.css')  $cfiles[] = $file;
					}
				}
				
				if ($found) 
				{
					$templates[] = wpm_2to1 ($folder, '');
					foreach ($cfiles as $cfile)
						$templates[] = wpm_2to1 ($folder, $cfile);
				}
			}
		}
	}

	sort ($templates);

	$out = "<select name='template' >\n";

	foreach ($templates as $template) :
		$selected = ($template == $active_template)? 'selected' : ''; 
		$out .= "<option value='" . $template . "' $selected> $template </option>\n";
	endforeach;

	$out .= "</select>\n";
	echo $out;

	return true;
}

function wpm_2to1 ($folder, $cfile)
{
	if ($cfile)  return "$folder " . __('with','wpm') . " $cfile";
	return "$folder " . __('without','wpm') . " CSS";
}

function wpm_1to2 ($template)
{
	$list = array();
	
	$pieces = explode (" ", $template);
	$list[0] = $pieces[0];
	$list[1] = array_pop ($pieces);
	if ($list[1] == 'CSS') $list[1] = '';

	return $list;
}

wp_reset_vars (array ('submit', 'action', 'itemid', 'parentid', 'name', 'type', 
			'Category', 'CategoryTree', 'Page', 'PageTree', 'Post', 'External',
			'cssclass', 'attributes', 'menuid', 'menuname', 'template'));
			
switch ($type)
{
case 'Home':
case 'FrontPage':
	$selection = '';  break;
case 'Category': 
	$selection = $Category;  break;
case 'CategoryTree': 
	$selection = $CategoryTree;  break;
case 'Page': 
	$selection = $Page;  break;
case 'PageTree': 
	$selection = $PageTree;  break;
case 'Post': 
	$selection = $Post;  break;
case 'External': 
	$selection = $External;  break;
}
			
switch ($submit)
{
case __('Reset Menubar', 'wpm'):  

	wpm_drop();
	wpm_create();
	$msg = 6; 

break;
case __('Select Menu', 'wpm'):  

break;
case __('Delete Menu', 'wpm'):  

	if (wpm_delete_node ($menuid))
	{
		$menuid = wpm_get_default_menu ();
		$msg = 7; 
	}
	else
		$msg = 8; 

break;
case __('Edit Menu', 'wpm'):  

	$action = 'editmenu';
	$wpm_menu = wpm_read_node ($menuid);
	include ('wpm-edit-menu.php');
	include ('admin-footer.php');
	exit;

break;
case __('Update Menu', 'wpm'):  

	check_admin_referer ('updatemenu_' . $menuid);

	$wpm_menu = wpm_read_node ($menuid);	
	$wpm_menu->name = $menuname;
	$list = wpm_1to2 ($template);
	$wpm_menu->selection = $list[0];
	$wpm_menu->cssclass = $list[1];

	if (wpm_update_node ($wpm_menu))
		$msg = 9; 
	else
		$msg = 10; 

break;
case __('Add New Menu', 'wpm'):  

	$wpm_menu = null;
	include ('wpm-edit-menu.php');
	include ('admin-footer.php');
	exit;

break;
case __('Add Menu', 'wpm'):  

	check_admin_referer ('addmenu');
	
	$wpm_menu = new stdClass;
	$wpm_menu->name = $menuname;
	$list = wpm_1to2 ($template);
	$wpm_menu->selection = $list[0];
	$wpm_menu->cssclass = $list[1];
	
	$wpm_menu->type = $wpm_options->menu_type;
	
	if ($menuid = wpm_create_node (0, $wpm_menu))
		$msg = 11; 
	else
		$msg = 12; 

break;
}

switch ($action)
{
case 'swap':

	wpm_swap_node ($itemid);

break;
case 'add':

	check_admin_referer ('add');

	$wpm_item = new stdClass;
	$wpm_item->name = $name;
	$wpm_item->type = $type;
	$wpm_item->selection = $selection;
	$wpm_item->cssclass = $cssclass;
	$wpm_item->attributes = $attributes;
	
	if (!$wpm_item->selection)
	{
		if ($wpm_item->type == 'Page')  $wpm_item->type = 'PageTree';
		if ($wpm_item->type == 'Category')  $wpm_item->type = 'CategoryTree';
	}

	if ($parentid)
		if (wpm_create_node ($parentid, $wpm_item))
			$msg = 1; 
		else
			$msg = 4; 
	else
		$msg = 15; 

break;
case 'delete':

	check_admin_referer ('delete_' . $itemid);

	if (wpm_delete_node ($itemid))
		$msg = 2; 
	else
		$msg = 14;
		
break;
case 'edit':
	
	$item = wpm_read_node ($itemid);
	include ('wpm-edit.php');
	include ('admin-footer.php');

	exit;

break;
case 'update':

	check_admin_referer ('update_' . $itemid);

	$wpm_item = wpm_read_node ($itemid);	
	$wpm_item->name = $name;
	$wpm_item->type = $type;
	$wpm_item->selection = $selection;
	$wpm_item->cssclass = $cssclass;
	$wpm_item->attributes = $attributes;
	
	if (!$wpm_item->selection)
	{
		if ($wpm_item->type == 'Page')  $wpm_item->type = 'PageTree';
		if ($wpm_item->type == 'Category')  $wpm_item->type = 'CategoryTree';
	}

	if (wpm_update_node ($wpm_item))
		$msg = 3; 
	else
		$msg = 5; 

break;
}

if (!$menuid)
	$menuid = wpm_get_default_menu ();
	
if (!$menuid)  $msg = 13;

$messages[1] = __('Menu item added.', 'wpm');
$messages[2] = __('Menu item deleted.', 'wpm');
$messages[3] = __('Menu item updated.', 'wpm');
$messages[4] = __('Menu item not added.', 'wpm');
$messages[5] = __('Menu item not updated.', 'wpm');
$messages[6] = __('Menubar cleared.', 'wpm');
$messages[7] = __('Menu deleted.', 'wpm');
$messages[8] = __('Error: menu is not empty!', 'wpm');
$messages[9] = __('Menu updated.', 'wpm');
$messages[10] = __('Menu not updated.', 'wpm');
$messages[11] = __('Menu added.', 'wpm');
$messages[12] = __('Error: duplicate or null menu name!', 'wpm');
$messages[13] = __('Please add your first menu.', 'wpm');
$messages[14] = __('Error: item has sub-items!', 'wpm');
$messages[15] = __('Error: parent not selected!', 'wpm');
?>

<?php if ($msg) : ?>
<div id="message" class="updated fade"><p><?php echo $messages[$msg]; ?></p></div>
<?php endif; ?>

<div class="wrap">

<h2><?php _e('Select Menu', 'wpm'); ?> </h2>

<form name="viewmenu" id="viewmenu" method="post" action="<?php echo $wpm_options->form_action; ?>">
	<fieldset>
	
	<?php if ($menuid) {
		wpm_menu_dropdown ($menuid); ?>
		<input type="submit" name="submit" value="<?php _e('Select Menu', 'wpm'); ?>" class="button" /> 
		<input type="submit" name="submit" value="<?php _e('Edit Menu', 'wpm'); ?>" class="button" /> 
		<input type="submit" name="submit" value="<?php _e('Delete Menu', 'wpm'); ?>" class="button delete" />
	<?php } ?>
	
	<input type="submit" name="submit" value="<?php _e('Add New Menu', 'wpm'); ?>" class="button" /> 
	</fieldset>
</form>

</div>

<?php

	if ($menuid)
	{
		wpm_list_menu_items ($menuid); 

		$item = null;
		include ('wpm-edit.php');
	}

	$heading = __('Reset Menubar', 'wpm');
	$form = '<form name="reset" id="reset" method="post" action="'. $wpm_options->form_action. '">';

?>

<hr />
<div class="wrap">
<h2><?php echo $heading; ?></h2>

<?php echo $form; ?>
<p class="submit">
<input type="submit" name="submit" value="<?php _e('Reset Menubar', 'wpm'); ?>"  />
<strong>
<?php _e('Warning: this will delete all your current menus and menu items!', 'wpm'); ?>
</strong>
</p>
</form>

</div>
<hr />
