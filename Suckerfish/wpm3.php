<?php
/* 
	WordPress Menubar Plugin
	PHP script for the Suckerfish template

	Credits:
	Son of Suckerfish Dropdowns
	By Patrick Griffiths and Dan Webb
	http://www.htmldog.com/articles/suckerfish/dropdowns/
*/

function wpm_display_Suckerfish ($menu, $css)
{
	wpm_menu ($menu->id, $level, $css, "\n<ul>%s</ul>", "<li%s><a%s>%s</a>%s</li>\n");
}
?>
