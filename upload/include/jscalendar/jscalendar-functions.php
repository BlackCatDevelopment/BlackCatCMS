<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
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
 *
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

// convert string from jscalendar to timestamp.
// converts dd.mm.yyyy and mm/dd/yyyy, with or without time.
// strtotime() may fails with e.g. "dd.mm.yyyy" and PHP4
function jscalendar_to_timestamp($str, $offset='') {
	$str = trim($str);
	if($str == '0' || $str == '')
		return('0');
	if($offset == '0')
		$offset = '';
	// convert to yyyy-mm-dd
	// "dd.mm.yyyy"?
	if(preg_match('/^\d{1,2}\.\d{1,2}\.\d{2}(\d{2})?/', $str)) {
		$str = preg_replace('/^(\d{1,2})\.(\d{1,2})\.(\d{2}(\d{2})?)/', '$3-$2-$1', $str);
	}
	// "mm/dd/yyyy"?
	if(preg_match('#^\d{1,2}/\d{1,2}/(\d{2}(\d{2})?)#', $str)) {
		$str = preg_replace('#^(\d{1,2})/(\d{1,2})/(\d{2}(\d{2})?)#', '$3-$1-$2', $str);
	}
	// use strtotime()
	if($offset!='')
		return(strtotime($str, $offset));
	else
	return(strtotime($str));
}

?>