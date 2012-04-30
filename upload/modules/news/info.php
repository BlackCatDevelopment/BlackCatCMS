<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2012, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 * @version         $Id: info.php 1462 2011-12-12 16:31:23Z frankh $
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include class.secure.php
 
$module_directory = 'news';
$module_name      = 'News';
$module_function  = 'page';
$module_version   = '3.6.11';
$module_platform  = '1.0.x';
$module_author    = 'Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan, Jurgen Nijhuis';
$module_license   = 'GNU General Public License';
$module_license_terms  = '-';
$module_guid      = '200a3816-e0f6-4fb9-aea8-8e7749896a34';
$module_description = 'This page type is designed for making a news page (including patch with backend pagination and image upload).';
$module_home      = 'http://lepton-cms.org';

/**
Image Upload:
	to use this feature, you will need the following placeholders
	in the modules settings: [PIC_URL] or [PICTURE].
	You may apply this placeholders in "Post Loop" (preview) layout field  and/or "Post Header" layout field (single post view).
	PLEASE NOTE: if the above placeholders are not in use there, no upload mechanism will be shown in modify_post.php
	PLEASE NOTE: the images will be resized automaticly to max dimensions as set in settings

	[PIC_URL] = returns the url to the image
	[PICTURE] = returns a whole img tag 
	
*/  
?>