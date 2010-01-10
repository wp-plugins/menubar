<?php

function wpm_out41 ($node_id, $html, $css)
{
	if ($node_id == 0)  return array ('output' => '', 'hilite' => false);
	$item = wpm_readnode ($node_id);

	$itemdown = wpm_out41 ($item->down, $html, $css);

	$process = true;
	$active = $html['active'];
	$nourl = $html['nourl'];
	$home = get_bloginfo ('url', 'display');
	$name = $item->name? __($item->name): "";
	$attributes = $item->attributes? __($item->attributes): "";
	$selection = $item->selection? __($item->selection): "";
	$menuclass = $css? substr($css, 0, -4): $item->selection;
	$class = $item->cssclass? " class=\"$item->cssclass\"": "";
	$selected = $item->cssclass? " class=\"$item->cssclass $active\"": " class=\"$active\"";

	if ($itemdown['hilite'])  $class = $selected;
	$items = $itemdown['output'];

	if (isset ($html['items'][$item->type]))
	switch ($item->type) 
	{
	case 'Menu':
		break;

	case 'Home':
		$sof = get_option ('show_on_front');
		$pfp = get_option ('page_for_posts');

		if (is_home())  $class = $selected;
		if ($sof == 'page') 
			$url = $pfp? get_page_link ($pfp): $nourl;
		else
			$url = $home;
		if ($name == '')  $name = __('Blog');
		break;

	case 'FrontPage':
		$sof = get_option ('show_on_front');
		$pof = get_option ('page_on_front');

		if ($sof == 'page' and $pof)
		{
			if (is_page($pof))  $class = $selected;
		}
		else
		{
			if (is_home())  $class = $selected;
		}
		$url = $home;
		if ($name == '')  $name = __('Start');
		break;

	case 'Heading':
		$url = $nourl;
		break;

	case 'Category':	
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$url = get_category_link ($item->selection);
		if ($name == '')  $name = get_cat_name ($selection);
		break;

	case 'CategoryTree':
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$items = wp_list_categories ('echo=0&title_li=&child_of='.$item->selection); 
		if ($items == '<li>'. __('No categories'). '</li>')  $items = '';

		$pattern = '@\<li([^>]*)>\<a([^>]*)>(.*?)\<\/a>@i';
		$replacement = $html['items'][$item->type];
		if ($replacement)  $items = preg_replace ($pattern, $replacement, $items);
		
		$url = $item->selection? get_category_link ($item->selection): $nourl;
		$item->type = $item->selection? 'Category': 'Heading';
		if ($name == '')  $name = $selection? get_cat_name ($selection): __('Categories');
		break;

	case 'Page':
		if (is_page($item->selection))  $class = $selected;
		$url = get_page_link ($item->selection);
		if ($name == '')  $name = get_the_title ($selection);
		break;

	case 'PageTree':
		if (wpm_is_descendant($item->selection))  $class = $selected;
		
		$items = wp_list_pages ('echo=0&title_li=&child_of='.$item->selection);
		
		$pattern = '@\<li([^>]*)>\<a([^>]*)>(.*?)\<\/a>@i';
		$replacement = $html['items'][$item->type];
		if ($replacement)  $items = preg_replace ($pattern, $replacement, $items);

		$url = $item->selection? get_page_link ($item->selection): $nourl;
		$item->type = $item->selection? 'Page': 'Heading';
		if ($name == '')  $name = $selection? get_the_title ($selection): __('Pages');
		break;

	case 'Post':
		if (is_single($item->selection))  $class = $selected;
		$url = get_permalink ($item->selection);
		if ($name == '')  $name = get_the_title ($selection);
		break;

	case 'SearchBox':
		break;

	case 'External':
		$url = $item->selection;
		break;

	case 'Custom':
		$html['items'][$item->type] = $selection;
		break;

	default:
		break;
	}

	if ($process)
	{
		$pattern = array ('/%attr/', '/%class/', '/%home/', '/%id/', '/%imageurl/', '/%items/', 
			'/%menuclass/', '/%name/', '/%selection/', '/%url/',
			'/%list/', '/%submit/', '/%image/');
		$replacement = array ($attributes, $class, $home, $item->id, $item->imageurl, $items, 
			$menuclass, $name, $selection, $url);

		$list = $items? preg_replace ($pattern, $replacement, $html['list']): '';
		$submit = $selection? preg_replace ($pattern, $replacement, $html['submit']): '';
		$image = $item->imageurl? preg_replace ($pattern, $replacement, $html['image']): '';
		
		$replacement[] = $list;
		$replacement[] = $submit;
		$replacement[] = $image;
		
		$output = preg_replace ($pattern, $replacement, $html['items'][$item->type]);
	}

	$itemside = wpm_out41 ($item->side, $html, $css);
	$output .= $itemside['output'];
	$hilite = ($class == $selected) || $itemside['hilite'];

	return array ('output' => $output, 'hilite' => $hilite);
}

function wpm_output ($node_id, $html, $css)
{
	if ($node_id == 0)  return array ('output' => '', 'hilite' => false);
	$item = wpm_readnode ($node_id);

	$itemdown = wpm_output ($item->down, $html, $css);

	$name = $item->name? __($item->name): "";
	$attributes = $item->attributes? __($item->attributes): "";
	$selection = $item->selection? __($item->selection): "";
	$class = $item->cssclass? " class=\"$item->cssclass\"": "";
	$selected = $item->cssclass?
			 " class=\"$item->cssclass {$html['active']}\"": " class=\"{$html['active']}\"";

	if ($itemdown['hilite'])  $class = $selected;
	$list = $itemdown['output']? 
			preg_replace ('/%items/', $itemdown['output'], $html['list']): '';

	switch ($item->type) 
	{
	case 'Menu':
		$menuclass = $css? substr($css, 0, -4): $item->selection;

		$out = preg_replace (array ('/%id/', '/%menuclass/', '/%list/'),
					array ($item->id, $menuclass, $list), $html);

		echo $out['script'];
		echo $out['menu'];
		return;

	case 'Home':
		$sof = get_option ('show_on_front');
		$pfp = get_option ('page_for_posts');

		if (is_home()) $class = $selected;
		if ($sof == 'page') 
			$url = $pfp? get_page_link ($pfp): '';
		else
			$url = get_bloginfo ('url', 'display');
		break;

	case 'FrontPage':
		$sof = get_option ('show_on_front');
		$pof = get_option ('page_on_front');

		if ($sof == 'page' and $pof)
		{
			if (is_page($pof)) $class = $selected;
		}
		else
		{
			if (is_home()) $class = $selected;
		}
		$url = get_bloginfo ('url', 'display');
		break;

	case 'Heading':
		$url = '*';
		break;

	case 'Category':	
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$url = get_category_link ($item->selection);
		break;

	case 'CategoryTree':
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$clist = wp_list_categories ('echo=0&title_li=&child_of='.$item->selection); 
		$list = ($clist != '<li>'. __('No categories'). '</li>')?
			preg_replace ('/%items/', $clist, $html['list']): '';

		$url = $item->selection? get_category_link ($item->selection): '*';
		break;

	case 'Page':
		if (is_page($item->selection)) $class = $selected;
		$url = get_page_link ($item->selection);
		break;

	case 'PageTree':
		if (wpm_is_descendant($item->selection)) $class = $selected;
		
		$plist = wp_list_pages ('echo=0&title_li=&child_of='.$item->selection);
		$list = $plist?
			preg_replace ('/%items/', $plist, $html['list']): '';

		$url = $item->selection? get_page_link ($item->selection): '*';
		break;

	case 'Post':
		if (is_single($item->selection)) $class = $selected;
		$url = get_permalink ($item->selection);
		break;

	case 'SearchBox':
		$submit = $selection? 
			preg_replace (array ('/%id/', '/%selection/'), 
					array ($item->id, $selection), $html['submit']): '';
		
		$out = preg_replace (array ('/%id/', '/%class/', '/%home/', '/%name/', '/%submit/'),
					array ($item->id, $class, get_bloginfo ('url', 'display'), $name, $submit), $html);

		$output = $out['search'];
		$url = '';
		break;

	case 'External':
		$url = $item->selection;
		break;

	case 'Custom':
		$output = $item->selection;
		$url = '';
		break;

	default:
		$url = '';
		break;
	}

	if ($url)
	{
		$out = preg_replace (array ('/%class/', '/%url/', '/%attr/', '/%name/', '/%list/'),
					array ($class, $url, $attributes, $name, $list), $html);

		$output = ($url != '*')? $out['item']: $out['noclick'];
	}

	$itemside = wpm_output ($item->side, $html, $css);
	$output .= $itemside['output'];
	$hilite = ($class == $selected) || $itemside['hilite'];

	return array ('output' => $output, 'hilite' => $hilite);
}

function wpm_menu ($node_id, $level, $css, $ul, $li)
{
	if ($node_id == 0)  return array ('output' => '', 'hilite' => false);
	$item = wpm_readnode ($node_id);

	$itemdown = wpm_menu ($item->down, $level, $css, $ul, $li);
	
	$name = $item->name? __($item->name): "";
	$attributes = $item->attributes? __($item->attributes): "";
	$class = $item->cssclass? " class=\"$item->cssclass\"": "";
	$selected = $item->cssclass?
			 " class=\"$item->cssclass active selected\"": " class=\"active selected\"";

	if ($itemdown['hilite'])  $class = $selected;
	if ($itemdown['output'])
		$itemdown['output'] = sprintf ($ul, $itemdown['output']);
 
	switch ($item->type) 
	{
	case 'Menu':
		$mid = 'wpmenu' . $item->id;
		$mclass = $css? substr($css, 0, -4): $item->selection;

		$javascript = '
<script type="text/javascript">
// <![CDATA[
'.$mid.'Hover = function() {
	var wpmEls = document.getElementById("'.$mid.'").getElementsByTagName("li");
	for (var i=0; i<wpmEls.length; i++) {
		wpmEls[i].onmouseover=function() {
			this.className+=" wpmhover";
		}
		wpmEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" wpmhover\\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", '.$mid.'Hover);
// ]]>
</script>
';
		echo "<div class=\"$mclass-before\"></div>\n";
		echo "<div id=\"$mid\" class=\"$mclass\">";
		echo $javascript;
		echo $itemdown['output'];
		echo "</div>\n";
		echo "<div class=\"$mclass-after\"></div>\n";
		return;

	case 'Home':
		$sof = get_option ('show_on_front');
		$pfp = get_option ('page_for_posts');

		if (is_home()) $class = $selected;
		if ($sof == 'page') 
			$url = $pfp? get_page_link ($pfp): '';
		else
			$url = get_bloginfo ('url', 'display');
		break;

	case 'FrontPage':
		$sof = get_option ('show_on_front');
		$pof = get_option ('page_on_front');

		if ($sof == 'page' and $pof)
		{
			if (is_page($pof)) $class = $selected;
		}
		else
		{
			if (is_home()) $class = $selected;
		}
		$url = get_bloginfo ('url', 'display');
		break;

	case 'Heading':
		$url = '*';
		break;

	case 'Category':	
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$url = get_category_link ($item->selection);
		break;

	case 'CategoryTree':
		if (is_category($item->selection) or (is_single() and in_category($item->selection))) 
			$class = $selected; 
		else foreach ((array) get_term_children ($item->selection, 'category') as $child)
			if (is_category($child) or (is_single() and in_category($child))) 
				$class = $selected;

		$url = get_category_link ($item->selection);
		$href = $item->selection? "href=\"$url\"": "style=\"cursor:default;\"";
		
		$list = wp_list_categories ('echo=0&title_li=&child_of='.$item->selection); 
		if ($list != '<li>'. __('No categories'). '</li>')
			$ulist = sprintf ($ul, $list);
 
		$output = sprintf ($li, $class, " $href $attributes", $name, $ulist);

		$url = '';
		break;

	case 'Page':
		if (is_page($item->selection)) $class = $selected;
		$url = get_page_link ($item->selection);
		break;

	case 'PageTree':
		if (wpm_is_descendant($item->selection)) $class = $selected;
		
		$url = get_page_link ($item->selection);
		$href = $item->selection? "href=\"$url\"": "style=\"cursor:default;\"";
		
		$list = wp_list_pages ('echo=0&title_li=&child_of='.$item->selection);
		if ($list != '')
			$ulist = sprintf ($ul, $list);

		$output = sprintf ($li, $class, " $href $attributes", $name, $ulist);
 
		$url = '';
		break;

	case 'Post':
		if (is_single($item->selection)) $class = $selected;
		$url = get_permalink ($item->selection);
		break;

	case 'External':
		$url = $item->selection;
		break;

	default:
		$url = '';
		break;
	}

	if ($url)
	{
		$href = ($url != '*')? "href=\"$url\"": "style=\"cursor:default;\"";

		$output = sprintf ($li, $class, " $href $attributes", $name, $itemdown['output']);
	}

	$itemside = wpm_menu ($item->side, $level, $css, $ul, $li);
	$output .= $itemside['output'];
	$hilite = ($class == $selected) || $itemside['hilite'];

	return array ('output' => $output, 'hilite' => $hilite);
}
?>
