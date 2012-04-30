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
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

function Dwoo_Plugin_show_wysiwyg_editor( Dwoo $dwoo, $name, $id, $content, $width = '100%', $height = '350px' ) {
	if ( !function_exists( 'show_wysiwyg_editor' ) )
	{
		@require_once( LEPTON_PATH.'/modules/' . WYSIWYG_EDITOR . '/include.php' );
		$wysiwyg_editor_loaded	= true;
	}
	ob_start();
	show_wysiwyg_editor( $name, $id, $content, $width, $height );
	$content = ob_get_clean();
	echo $content;
}

?>