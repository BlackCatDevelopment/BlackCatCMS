<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

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