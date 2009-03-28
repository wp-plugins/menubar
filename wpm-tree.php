<?php

function wpm_create_node ($parent_id, $node)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "INSERT INTO $table_name
		 (name, type, selection, cssclass, attributes, side, down) VALUES 
		 ('$node->name', '$node->type', '$node->selection',
		 	'$node->cssclass', '$node->attributes', '0', '0')";
		 
	$wpdb->query ($sql);
	
	if ($parent_id == 0)  return $wpdb->insert_id;
	
	$parent = wpm_read_node ($parent_id);
	if ($parent->down == 0) 
	{
		$parent->down = $wpdb->insert_id;
		wpm_update_links ($parent);
	}
	else
	{
		$item = wpm_read_node ($parent->down); 
		while ($item->side)
			$item = wpm_read_node ($item->side);

		$item->side = $wpdb->insert_id;
		wpm_update_links ($item);
	}

	return $wpdb->insert_id;
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

function wpm_update_links ($node)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;

	$sql = "UPDATE $table_name SET 
		side = '$node->side', 
		down = '$node->down'
		WHERE id = '$node->id'";

	$wpdb->query ($sql);
	return true;
}

function wpm_delete_node ($node_id, $safe=true)
{
	global $wpdb, $wpm_options;
	$table_name = $wpdb->prefix . $wpm_options->table_name;
	
	$node = wpm_read_node ($node_id);	
	if ($safe and $node->down)  return false;

	$sql = "SELECT * FROM $table_name WHERE side = '$node_id'";
	$item = $wpdb->get_row ($sql);
	if ($item->side == $node_id)
	{
		$item->side = $node->side;
		wpm_update_links ($item);
	}
	else
	{
		$sql = "SELECT * FROM $table_name WHERE down = '$node_id'";
		$item = $wpdb->get_row ($sql);
		if ($item->down == $node_id)
		{
			$item->down = $node->side;
			wpm_update_links ($item);
		}
	}
	
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
	
	$sql = "SELECT * FROM $table_name WHERE side = '$node_id'";
	$item = $wpdb->get_row ($sql);
	if ($item->side == $node_id)
	{
		$item->side = $node->side;
		wpm_update_links ($item);
	}
	else
	{
		$sql = "SELECT * FROM $table_name WHERE down = '$node_id'";
		$item = $wpdb->get_row ($sql);
		if ($item->down == $node_id)
		{
			$item->down = $node->side;
			wpm_update_links ($item);
		}
	}

	$item = wpm_read_node ($node->side);
	
	$node->side = $item->side;
	$item->side = $node_id;
	wpm_update_links ($item);
	wpm_update_links ($node);
	
	return true;
}

?>
