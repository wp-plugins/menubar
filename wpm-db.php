<?php

function wpm_default_name ($item)
{
	$id = $item->id;
	$type = $item->type;
	$selection = $item->selection;
	
	switch ($type)
	{
	case 'Home':			return __('Blog');
	case 'FrontPage':		return __('Start');
	case 'Tag':				return wpm_tag_name ($selection);
	case 'TagList':			return __('Tags');
	case 'Category':		return get_cat_name ($selection);
	case 'CategoryTree':	return $selection? get_cat_name ($selection): __('Categories');
	case 'Page':			return get_the_title ($selection);
	case 'PageTree':		return $selection? get_the_title ($selection): __('Pages');
	case 'Post':			return get_the_title ($selection);
	default:				return '';
	}
}

function wpm_display_selection ($type, $selection)
{
	switch ($type)
	{
	case 'Tag':
		return wpm_tag_name ($selection);

	case 'Category':	
		return get_cat_name ($selection);

	case 'CategoryTree':
		return ($name = $selection)? get_cat_name ($selection): __('All');

	case 'Page':
		return get_the_title ($selection);

	case 'PageTree':
		return ($name = $selection)? get_the_title ($selection): __('All');

	case 'Post':
		return get_the_title ($selection);

	case 'Custom':
		return htmlspecialchars ($selection);
		
	default:
		return $selection;
	}
}

function wpm_get_tags ()
{
	global $wpdb, $wpm_tags;
	if ($wpm_tags === null)
	{
		$sql = "SELECT t.term_id, t.name, tt.count
			FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt
			ON t.term_id = tt.term_id
			WHERE tt.taxonomy = 'post_tag' ORDER BY t.name ASC";		

		$wpm_tags = $wpdb->get_results ($sql);
	}
	return $wpm_tags;
}

function wpm_tag_name ($id)
{
	$tags = wpm_get_tags ();
	foreach ($tags as $tag)
		if ($tag->term_id == $id)  return $tag->name;
	return null;	
}

function wpm_get_cats ()
{
	global $wpdb, $wpm_cats;
	if ($wpm_cats === null)
	{
		$sql = "SELECT t.term_id, t.name 
			FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt 
			ON t.term_id = tt.term_id
			WHERE tt.taxonomy = 'category'";			

		$wpm_cats = $wpdb->get_results ($sql);
	}
	return $wpm_cats;
}

?>
