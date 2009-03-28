
<script type='text/javascript'>
// <![CDATA[
	function toggleType(form) {
		for (index = 3; index <= 8; index++)
			document.getElementById('select'+index).style.display = 'none';
		index = form.type.selectedIndex;
		if (index >= 3 && index <= 8)
			document.getElementById('select'+index).style.display = '';
	}
// ]]>
</script>  

<?php

if ($action == 'edit') {
	$heading = __('Edit Menu Item', 'wpm');
	$submit_text = __('Edit Menu Item', 'wpm');
	$form = '<form name="update" id="update" method="post" action="'. $wpm_options->form_action. '">';
	$action = 'update';
	$nonce_action = 'update_' . $item->id;
} else {
	$heading = __('Add Menu Item', 'wpm');
	$submit_text = __('Add Menu Item', 'wpm');
	$form = '<form name="add" id="add" method="post" action="'. $wpm_options->form_action. '">';
	$action = 'add';
	$nonce_action = 'add';
}

$typelist = array (
/* 0 */	"Home" =>		'Home'		. __(': the main page of your blog', 'wpm'),
/* 1 */	"FrontPage" =>	'FrontPage'		. __(': the front page of your site', 'wpm'),
/* 2 */	"Heading" =>	'Heading'		. __(': a non clickable item', 'wpm'), 
/* 3 */	"Category" =>	'Category'		. __(': a category page', 'wpm'), 
/* 4 */	"CategoryTree" =>	'CategoryTree'	. __(': a category page, with subcategories', 'wpm'), 
/* 5 */	"Page" =>		'Page'		. __(': a static page', 'wpm'),
/* 6 */	"PageTree" =>	'PageTree'		. __(': a static page, with subpages', 'wpm'),
/* 7 */	"Post" =>		'Post'		. __(': a single post', 'wpm'),
/* 8 */	"External" =>	'External'		. __(': any other URL', 'wpm')
);		

?>

<div class="wrap">
<h2><?php echo $heading ?></h2>
<div id="ajax-response"></div>
<?php echo $form ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="itemid" value="<?php echo $item->id; ?>" />
<input type="hidden" name="menuid" value="<?php echo $menuid; ?>" />
<?php wp_nonce_field($nonce_action); ?>

<table class="editform" width="100%" cellspacing="2" cellpadding="5">

	<?php $item_list = wpm_item_list ($menuid, $item_list, 0); ?>
	<?php $page_list = wpm_page_list (0, $page_list, 0); ?>
	<?php $cat_list = wpm_cat_list (0, $cat_list, 0); ?>

	<?php if ($action == 'add') 
		  wpm_select (__('Parent:', 'wpm'), 'parentid', '', $item_list, $menuid); ?>

	<?php wpm_input  (__('Name:', 'wpm'), 'name', $item->name, '20', __('(e.g. Home, News)', 'wpm')); ?>

	<?php wpm_select (__('Type:', 'wpm'), 'type', 'onchange="toggleType(this.form)"', $typelist, $item->type); ?>

	<?php wpm_optselect (3, $item->type, __('Category:', 'wpm'), 'Category', '', $cat_list, $item->selection); ?>
	<?php wpm_optselect (4, $item->type, __('Category:', 'wpm'), 'CategoryTree', '', $cat_list, $item->selection); ?>
	<?php wpm_optselect (5, $item->type, __('Page:', 'wpm'), 'Page', '', $page_list, $item->selection); ?>
	<?php wpm_optselect (6, $item->type, __('Page:', 'wpm'), 'PageTree', '', $page_list, $item->selection); ?>
	<?php wpm_optinput  (7, $item->type, __('Post ID:', 'wpm'), 'Post', $item->selection, '20', ''); ?>
	<?php wpm_optinput  (8, $item->type, __('URL:', 'wpm'), 'External', $item->selection, '20', ''); ?>

	<?php wpm_input  (__('CSS class:', 'wpm'), 'cssclass', $item->cssclass, '20', __('(optional CSS class of this menu item)', 'wpm')); ?>
	<?php wpm_input  (__('Attributes:', 'wpm'), 'attributes', $item->attributes, '20', __('(e.g. target="_blank", title="click me!")', 'wpm')); ?>
	
</table>

<p class="submit"> <input type="submit" name="submit" value="<?php echo $submit_text ?>" /> </p>

</form>
</div>

<?php

function wpm_select ($label, $name, $attr, $list, $selected)
{
	echo "\n<tr>\n";
		echo "<th width=\"20%\" scope=\"row\" valign=\"top\">\n";
			echo "<label for=\"$name\"> $label </label>\n";
		echo "</th>\n";
		echo "<td>\n";		
			echo "<select name=\"$name\" $attr >\n";
			foreach ($list as $value => $caption)
			{
				echo "<option value=\"$value\"";
				if ($value == $selected) echo " selected=\"selected\"";
 				echo "> $caption &nbsp; </option>\n";
			}
			echo "</select>\n";
		echo "</td>\n";
	echo "</tr>\n";

	return true;
}

function wpm_optselect ($id, $type, $label, $name, $attr, $list, $selected)
{
	$style = ($type != $name)? ' style="display: none;"': '';
	 
	echo "\n<tr id=\"select$id\"$style>\n";
		echo "<th width=\"20%\" scope=\"row\" valign=\"top\">\n";
			echo "<label for=\"$name\"> $label </label>\n";
		echo "</th>\n";
		echo "<td>\n";		
			echo "<select name=\"$name\" $attr >\n";
			foreach ($list as $value => $caption)
			{
				echo "<option value=\"$value\"";
				if ($value == $selected) echo " selected=\"selected\"";
 				echo "> $caption &nbsp; </option>\n";
			}
			echo "</select>\n";
		echo "</td>\n";
	echo "</tr>\n";

	return true;
}

function wpm_input ($label, $name, $value, $size, $comment)
{
	$value = attribute_escape ($value);

	echo "\n<tr>\n";
		echo "<th width=\"20%\" scope=\"row\" valign=\"top\">\n";
			echo "<label for=\"$name\"> $label </label>\n";
		echo "</th>\n";
		echo "<td>\n";
			echo "<input name=\"$name\" id=\"$name\" type=\"text\" value=\"$value\" size=\"$size\" />\n";
			echo "$comment\n";
		echo "</td>\n";
	echo "</tr>\n";
		
	return true;
}

function wpm_optinput ($id, $type, $label, $name, $value, $size, $comment)
{
	$style = ($type != $name)? ' style="display: none;"': '';
	$value = attribute_escape ($value);
	 
	echo "\n<tr id=\"select$id\"$style>\n";
		echo "<th width=\"20%\" scope=\"row\" valign=\"top\">\n";
			echo "<label for=\"$name\"> $label </label>\n";
		echo "</th>\n";
		echo "<td>\n";
			echo "<input name=\"$name\" id=\"$name\" type=\"text\" value=\"$value\" size=\"$size\" />\n";
		echo "</td>\n";
	echo "</tr>\n";
	
	return true;
}
?>
