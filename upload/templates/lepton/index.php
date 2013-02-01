<?php

/**
 *  @template       Lepton-Start
 *  @version        see info.php of this template
 *  @author         cms-lab
 *  @copyright      2010-2011 CMS-LAB
 *  @license        http://creativecommons.org/licenses/by/3.0/
 *  @license terms  see info.php of this template
 *  @platform       see info.php of this template
 *  @requirements   PHP 5.2.x and higher
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

$defs = get_defined_constants(true);
foreach($defs['user'] as $const => $value ) {
    if(preg_match('~^SHOW_~',$const)) { // SHOW_SEARCH etc.
        $parser->setGlobals($const,$value);
        continue;
    }
    if(preg_match('~^FRONTEND_~',$const)) { // FRONTEND_LOGIN etc.
        $parser->setGlobals($const,$value);
        continue;
    }
}

$parser->setPath(dirname(__FILE__).'/templates');
$parser->output('index.lte');