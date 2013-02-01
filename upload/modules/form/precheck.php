<?php

/**
 *  @author         Ryan Djurovich
 *  @author         Rudolph Lartey
 *  @author         John Maats
 *  @author         Dietrich Roland Pehlke
 *  @copyright      2004-2011 Ryan Djurovich, Rudolph Lartey, John Maats, Dietrich Roland Pehlke
 *  @license        see info.php of this module
 *  @todo           separate HTML from code, in addition the used HTML is no longer 
 *                  valid and uses deprecated attributes i.e. cellpadding a.s.o.
 *  @version        $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {    
    if (defined('LEPTON_VERSION')) include(CAT_PATH.'/framework/class.secure.php'); 
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
$status = (defined('LEPTON_VERSION') === true) ? true : 'false';
$PRECHECK['CUSTOM_CHECKS'] = array(
        'LEPTON CMS' => array(
                'REQUIRED' => 1,
                'ACTUAL' => (defined('LEPTON_VERSION')) ? 1 : 0,
                'STATUS' => (int) defined('LEPTON_VERSION')
        )
);
