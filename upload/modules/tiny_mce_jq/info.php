<?php

/**
 *  @module         TinyMCE-jQ
 *  @version        see info.php of this module
 *  @authors        erpe, Dietrich Roland Pehlke (Aldus)
 *  @copyright      2010-2012 erpe, Dietrich Roland Pehlke (Aldus)
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 *  Please note: TINYMCE is distibuted under the <a href="http://tinymce.moxiecode.com/license.php">(LGPL) License</a> 
 *  Ajax Filemanager is distributed under the <a href="http://www.gnu.org/licenses/gpl.html)">GPL </a> and <a href="http://www.mozilla.org/MPL/MPL-1.1.html">MPL</a> open source licenses 
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php


$module_directory     = 'tiny_mce_jq';
$module_name          = 'TinyMCE-jQ';
$module_function      = 'WYSIWYG';
$module_version       = '3.4.9.0';
$module_platform      = '1.x';
$module_author        = 'erpe, Aldus';
$module_home          = 'http://lepton-cms.org';
$module_guid          = '16643d7b-b7e4-4dc4-9ff5-10b9c26114cd';
$module_license       = 'GNU General Public License, TINYMCE is LGPL, Ajax Filemanager is also open source license.';
$module_license_terms  = '-';
$module_description   = 'TinyMCE 3.4.9. build date:(2012-02-23)<br>with Ajax Image File Manager and image editor<BR> allows you to edit the content of a page and see media image folder.<BR>To link your template css file to the styles in tinymce you need to edit the <b>include.php</b> file inside this module.';

/**
 *	3.4.9.0	2012-02-23	- Update to tiny_mce 3.4.9.
 *
 *	3.4.8.0	2012-02-02	- Update to tiny_mce 3.4.8.
 * 
 *	3.4.7.2	2012-01-18	- Bugfix inside ajaxfilemanager for allowed upload-filetypes, e.g. 'jpeg'.
 *
 *	3.4.7.1	2011-12-06	- Bugfix in dropleps plugin 
 *
 *	3.4.7.0	2011-11-03	- Update to tiny_mce 3.4.7.  
 *
 *	3.4.6.0	2011-09-29	- Update to tiny_mce 3.4.6. 
 * 
 *	3.4.5.0	2011-09-06	- Update to tiny_mce 3.4.5.  
 *
 *	3.4.4.1	2011-08-25	- Bugfix for getting the correct path to an existing editor.css file.
 *
 *	3.4.4.0	2011-07-12	- Some patches and codechanges inside include.php to avoid conflicts if
 *						  this module is called from another modul without declaring $database and
 *						  id_list as globals. E.g. within currend modul 'contactlist'.
 *
 *
 */
?>