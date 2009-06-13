<?php

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
