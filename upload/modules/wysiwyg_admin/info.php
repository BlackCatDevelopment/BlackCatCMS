<?php

/**
 *	@module			wysiwyg Admin
 *	@version		see info.php of this module
 *	@authors		Dietrich Roland Pehlke
 *	@copyright		2010-2011 Dietrich Roland Pehlke
 *	@license		GNU General Public License
 *	@license terms	see info.php of this module
 *	@platform		see info.php of this module
 *	@requirements	PHP 5.2.x and higher
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

 
$module_directory	= 'wysiwyg_admin';
$module_name		= 'wysiwyg Admin';
$module_function	= 'tool';
$module_version		= '0.2.4';
$module_platform	= '1.x';
$module_author		= 'Dietrich Roland Pehlke (Aldus)';
$module_license		= '<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">lgpl</a>';
$module_license_terms	= '-';
$module_description	= 'This module allows to manage some basic settings of the choosen wysiwyg-editor.';
$module_guid		= '895FD071-DA62-4E90-87C8-F3E11BC1F9AB';

/**
 *
 *	0.2.4	2011-11-12	- Minor cosemetic codechanges.
 *	0.2.3	2011-11-07	- Additional buttons for TinyMCE for new pagelinks and dorpleps.
 *	0.2.2	2011-08-17	- Bugfix for missing leptoken inside the backend-interface.
 *	0.2.1	2011-07-14	- Change names of toolbar inside CK-Editor driver.
 *	0.2.0	2011-07-13	- Codecleanings for LEPTON.
 *	0.1.9	2011-02-12	- Bugfix inside uninstall.php - wrong table-name.
 *	0.1.8	2010-10-20	- Bugfix inside "tool.php" for IE8; removing the '#' to the correct valid http(-s) link.
 *	0.1.7	2010-10-18	- Add driver vor the "edit area"-module.
 *	0.1.6	2010-10-14	- Remove typos inside the english language file. (thanks to Klaus Weitzel)
 *	0.1.3	2010-05-17	- First private run.
 *
 */
?>