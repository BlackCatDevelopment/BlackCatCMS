<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id: tool.php 1903 2012-04-19 09:15:27Z webbird $
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
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

include dirname(__FILE__).'/library_info.php';

?>

<div style="border:2px solid #ccc;margin-top:25px;padding:15px;">
  <table style="width:100%">
	<tr>
	  <td>Module:</td>
	  <td><?php echo $lib_info['library_path']; ?> (<?php echo $lib_info['library_name']; ?>)</td>
	</tr>
	<tr>
	  <td>Version:</td>
	  <td><?php echo $lib_info['library_version']; ?></td>
	</tr>
    <tr>
	  <td>Info:</td>
	  <td><?php echo $lib_info['library_info']; ?></td>
	</tr>
  </table><br /><br />
  <p>Please note: This is a library module which has no Admin Tool functionality.
  If you need an Admin Tool to manage your jQuery Plugins, please install
  <a href="http://www.websitebakers.com/pages/libs/libraryadmin.php">LibraryAdmin</a>.</p>
  <p>Hinweis: Dies ist ein Bibliotheksmodul ohne Admin Tool Funktionalität.
  Wenn Sie ein Admin Tool benötigen, um Ihre jQuery Plugins zu verwalten, installieren
  Sie bitte <a href="http://www.websitebakers.com/pages/libs/libraryadmin.php">LibraryAdmin</a>.</p>
</div>
