<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author        WebsiteBaker Project        
 * @author        LEPTON Project
 * @author        Ralf Hertsch <rh@lepton-cms.org>
 * @copyright     2004 - 2010 WebsiteBaker Project
 * @copyright     since 2011 LEPTON Project
 * @link          http://blackcat-cms.org
 * @license       http://www.gnu.org/licenses/gpl.html
 * @version       $Id$
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

require_once CAT_PATH.'/modules/'.basename(dirname(__FILE__)).'/library.php';

// init & execute the LEPTON Search
$search = new CATSearch();
$search->exec();