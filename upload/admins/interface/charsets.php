<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
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



// Create array
$CHARSETS = array();
$CHARSETS['utf-8'] = 'Unicode (utf-8)';
$CHARSETS['iso-8859-1'] = 'Latin-1 Western European (iso-8859-1)';
$CHARSETS['iso-8859-2'] = 'Latin-2 Central European (iso-8859-2)';
$CHARSETS['iso-8859-3'] = 'Latin-3 Southern European (iso-8859-3)';
$CHARSETS['iso-8859-4'] = 'Latin-4 Baltic (iso-8859-4)';
$CHARSETS['iso-8859-5'] = 'Cyrillic (iso-8859-5)';
$CHARSETS['iso-8859-6'] = 'Arabic (iso-8859-6)';
$CHARSETS['iso-8859-7'] = 'Greek (iso-8859-7)';
$CHARSETS['iso-8859-8'] = 'Hebrew (iso-8859-8)';
$CHARSETS['iso-8859-9'] = 'Latin-5 Turkish (iso-8859-9)';
$CHARSETS['iso-8859-10'] = 'Latin-6 Nordic (iso-8859-10)';
$CHARSETS['iso-8859-11'] = 'Thai (iso-8859-11)';
$CHARSETS['gb2312'] = 'Chinese Simplified (gb2312)';
$CHARSETS['big5'] = 'Chinese Traditional (big5)';
$CHARSETS['iso-2022-jp'] = 'Japanese (iso-2022-jp)';
$CHARSETS['iso-2022-kr'] = 'Korean (iso-2022-kr)';

?>