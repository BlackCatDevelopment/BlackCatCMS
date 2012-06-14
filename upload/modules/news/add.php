<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2011, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

global $database, $page_id, $section_id;

$header = '<table cellpadding=\"0\" cellspacing=\"0\" class=\"loop_header\">'."\n";
$post_loop = '<tr class=\"post_top\">
<td class=\"post_title\"><a href=\"[LINK]\">[TITLE]</a></td>
<td class=\"post_date\">[PUBLISHED_DATE], [PUBLISHED_TIME]</td>
</tr>
<tr>
<td class=\"post_short\" colspan=\"2\">
[PICTURE][SHORT]
<span style=\"visibility:[SHOW_READ_MORE];\"><a href=\"[LINK]\">[TEXT_READ_MORE]</a></span>
</td>
</tr>';
$footer = '</table>
<table cellpadding="0" cellspacing="0" class="page_header" style="display: [DISPLAY_PREVIOUS_NEXT_LINKS]">
<tr>
<td class="page_left">[PREVIOUS_PAGE_LINK]</td>
<td class="page_center">[OF]</td>
<td class="page_right">[NEXT_PAGE_LINK]</td>
</tr>
</table>';
$post_header = addslashes('<table cellpadding="0" cellspacing="0" class="post_header">
<tr>
<td><h1>[TITLE]</h1></td>
<td rowspan="3" style="display: [DISPLAY_IMAGE]">[GROUP_IMAGE]</td>
</tr>
<tr>
<td class="public_info"><b>[TEXT_POSTED_BY] [DISPLAY_NAME] ([USERNAME]) [TEXT_ON] [PUBLISHED_DATE]</b></td>
</tr>
<tr style="display: [DISPLAY_GROUP]">
<td class="group_page"><a href="[BACK]">[PAGE_TITLE]</a> &gt;&gt; <a href="[BACK]?g=[GROUP_ID]">[GROUP_TITLE]</a></td>
</tr>
</table>');
$post_footer = '<p>[TEXT_LAST_CHANGED]: [MODI_DATE] [TEXT_AT] [MODI_TIME]</p>
<a href=\"[BACK]\">[TEXT_BACK]</a>';
$comments_header = addslashes('<br /><br />
<h2>[TEXT_COMMENTS]</h2>
<table cellpadding="2" cellspacing="0" class="comment_header">');
$comments_loop = addslashes('<tr>
<td class="comment_title">[TITLE]</td>
<td class="comment_info">[TEXT_BY] [DISPLAY_NAME] [TEXT_ON] [DATE] [TEXT_AT] [TIME]</td>
</tr>
<tr>
<td colspan="2" class="comment_text">[COMMENT]</td>
</tr>');
$comments_footer = '</table>
<br /><a href=\"[ADD_COMMENT_URL]\">[TEXT_ADD_COMMENT]</a>';
$comments_page = '<h1>[TEXT_COMMENT]</h1>
<h2>[POST_TITLE]</h2>
<br />';
$commenting = 'none';
$use_captcha = true;

$database->query("INSERT INTO ".TABLE_PREFIX."mod_news_settings (section_id,page_id,header,post_loop,footer,post_header,post_footer,comments_header,comments_loop,comments_footer,comments_page,commenting,use_captcha) VALUES ('$section_id','$page_id','$header','$post_loop','$footer','$post_header','$post_footer','$comments_header','$comments_loop','$comments_footer','$comments_page','$commenting','$use_captcha')");

?>