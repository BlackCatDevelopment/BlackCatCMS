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

$mod_headers = array();

if ( file_exists( WB_PATH.'/modules/libraryadmin/plugins/lytebox' ) ) {
    $mod_headers = array(
		'backend' => array(
		    'css' => array(
				array(
					'media'		=> 'screen',
					'file'		=> '/modules/libraryadmin/plugins/lytebox/lytebox.css',
				)
			),
			'js' => array(
                '/modules/libraryadmin/plugins/lytebox/lytebox.js'
			),
		),
	);
}
elseif ( file_exists( WB_PATH.'/modules/lib_jquery/plugins/SlimBox2' ) ) {
    $mod_headers = array(
		'backend' => array(
		    'css' => array(
				array(
					'media'		=> 'screen',
					'file'		=> '/modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.css',
				)
			),
			'js' => array(
                '/modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.pack.js',
			),
		),
	);
}

?>