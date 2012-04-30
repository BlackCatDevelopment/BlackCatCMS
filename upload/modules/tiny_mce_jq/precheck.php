<?php

/**
 *  @module         TinyMCE-jQ
 *  @version        see info.php of this module
 *  @authors        erpe, Dietrich Roland Pehlke (Aldus)
 *  @copyright      2010-2011 erpe, Dietrich Roland Pehlke (Aldus)
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH'))
{
    include(WB_PATH . '/framework/class.secure.php');
}
else
{
    $oneback = "../";
    $root    = $oneback;
    $level   = 1;
    while (($level < 10) && (!file_exists($root . '/framework/class.secure.php')))
    {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root . '/framework/class.secure.php'))
    {
        include($root . '/framework/class.secure.php');
    }
    else
    {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php 

// Checking Requirements

$PRECHECK['VERSION']        = array(
    'VERSION' => '1.0',
    'OPERATOR' => '>='
);

$PRECHECK['WB_ADDONS']      = array(
    'wysiwyg_admin' => array(
        'VERSION' => '0.2.3',
        'OPERATOR' => '>='
    ),
);

?>