<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          Dwoo Template Engine
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.lepton-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
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

require_once LEPTON_PATH.'/framework/functions.php';

require LEPTON_PATH.'/framework/LEPTON/Helper/I18n.php';
global $__dwoo_plugin_lang;
$__dwoo_plugin_lang = new LEPTON_Helper_I18n();

function Dwoo_Plugin_translate( Dwoo $dwoo, $msg, $args = array() ) {
	global $__dwoo_plugin_lang;
	// just to be sure
	if ( ! is_object($__dwoo_plugin_lang) ) {
        if ( ! class_exists('LEPTON_Helper_I18n',false) ) {
            require dirname(__FILE__).'/../../../../../framework/LEPTON/Helper/I18n.php';
        }
        $__dwoo_plugin_lang = new LEPTON_Helper_I18n();
    }
	return $__dwoo_plugin_lang->translate($msg, $args);
}

?>