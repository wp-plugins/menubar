<?php

function wpm_create_child ($parent_id, $node_values)
{
	$node = _wpm_create_node ($node_values);

	if ($parent_id > 0)	
		$node = _wpm_link_child ($parent_id, $node);
	
	return $node;
}

function wpm_move_child ($parent_id, $node_id)
{
	if (_wpm_is_descendant ($parent_id, $node_id))  return false;

	$node = _wpm_unlink_node ($node_id);

	if ($parent_id > 0)
		$node = _wpm_link_child ($parent_id, $node);

	return $node;
}

function wpm_create_after ($after_id, $node_values)
{
	$node = _wpm_create_node ($node_values);
	$node = _wpm_link_after ($after_id, $node);

	return $node;
}

function wpm_move_after ($after_id, $node_id)
{
	if (_wpm_is_descendant ($after_id, $node_id))  return false;

	$node = _wpm_unlink_node ($node_id);
	$node = _wpm_link_after ($after_id, $node);

	return $node;
}

function wpm_create_before ($before_id, $node_values)
{
	$node = _wpm_create_node ($node_values);
	$node = _wpm_link_before ($before_id, $node);
	
	return $node;
}

function wpm_move_before ($before_id, $node_id)
{
	if (_wpm_is_descendant ($before_id, $node_id))  return false;

	$node = _wpm_unlink_node ($node_id);
	$node = _wpm_link_before ($before_id, $node);

	return $node;
}

function wpm_read_node ($node_id)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT * FROM $table_name WHERE id = '$node_id'";
	$node = $wpdb->get_row ($sql);

	return $node;
}

function wpm_update_node ($node)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "UPDATE $table_name SET 
		name = '$node->name', 
		type = '$node->type', 
		selection = '$node->selection',
		cssclass = '$node->cssclass',
		attributes = '$node->attributes' 
		WHERE id = '$node->id'";

	$wpdb->query ($sql);
	return true;
}

function wpm_delete_node ($node_id, $safe=true)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;
	
	$node = wpm_read_node ($node_id);	
	if ($safe && $node->down)  return false;
	
	_wpm_unlink_node ($node_id);
	
	$sql = "DELETE FROM $table_name WHERE id = '$node_id'";	
	$wpdb->query ($sql);
	
	return true;
}

function wpm_swap_node ($node_id)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$node = wpm_read_node ($node_id);
	if ($node->side == 0)  return false;
	
	$node = _wpm_unlink_node ($node_id);
	$node = _wpm_link_after ($node->side, $node);
	
	return true;
}

function _wpm_create_node ($node_values)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "INSERT INTO $table_name
		 (name, type, selection, cssclass, attributes, side, down) VALUES 
		 ('$node_values->name',
		  '$node_values->type', '$node_values->selection',
		  '$node_values->cssclass', '$node_values->attributes', '0', '0')";
		 
	$wpdb->query ($sql);
	
	$node = wpm_read_node ($wpdb->insert_id);
	return $node;
}

function _wpm_update_links ($node)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "UPDATE $table_name SET 
		side = '$node->side', 
		down = '$node->down'
		WHERE id = '$node->id'";

	$wpdb->query ($sql);
	return $node;
}

function _wpm_find_pointer ($node_id)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "SELECT * FROM $table_name WHERE (side = '$node_id' OR down = '$node_id')";
	$item = $wpdb->get_row ($sql);
	
	return $item;		
}

function _wpm_unlink_node ($node_id)
{
	$node = wpm_read_node ($node_id);
	$item = _wpm_find_pointer ($node->id);

	if ($item->side == $node->id)  $item->side = $node->side;  else
	if ($item->down == $node->id)  $item->down = $node->side;

	_wpm_update_links ($item);
	return $node;
}

function _wpm_link_child ($parent_id, $node)
{
	$parent = wpm_read_node ($parent_id);
	
	if ($parent->down == 0) 
	{
		$parent->down = $node->id;
		_wpm_update_links ($parent);
	}
	else
	{
		$item = wpm_read_node ($parent->down); 
		while ($item->side)
			$item = wpm_read_node ($item->side);

		$item->side = $node->id;
		_wpm_update_links ($item);
	}

	$node->side = 0;
	$node = _wpm_update_links ($node);
	
	return $node;
}

function _wpm_link_after ($after_id, $node)
{
	$after = wpm_read_node ($after_id);

	$node->side = $after->side;
	$node = _wpm_update_links ($node);
	
	$after->side = $node->id;
	_wpm_update_links ($after);

	return $node;
}

function _wpm_link_before ($before_id, $node)
{
	$item = _wpm_find_pointer ($before_id);

	if ($item->side == $before_id)  $item->side = $node->id;  else
	if ($item->down == $before_id)  $item->down = $node->id;
	
	_wpm_update_links ($item);

	$node->side = $before_id;
	$node = _wpm_update_links ($node);

	return $node;
}

function _wpm_is_descendant ($node_id, $parent_id, $level=0)
{
	if ($node_id == $parent_id)  return true;
	
	$item = wpm_read_node ($parent_id);
			
	if ($item->down)  
		if (_wpm_is_descendant ($node_id, $item->down, $level+1))  return true;

	if ($level && $item->side)  
		if (_wpm_is_descendant ($node_id, $item->side, $level))  return true;

	return false;
}

?>
