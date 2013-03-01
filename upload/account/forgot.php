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
 * @reformatted     2011-10-04
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

// Required page details
$page_id          = 0;
$page_description = '';
$page_keywords    = '';
define( 'PAGE_ID', 0 );
define( 'ROOT_PARENT', 0 );
define( 'PARENT', 0 );
define( 'LEVEL', 0 );
define( 'PAGE_TITLE', CAT_Helper_I18n::getInstance()->translate('Forgot') );
define( 'MENU_TITLE', CAT_Helper_I18n::getInstance()->translate('Forgot') );
define( 'VISIBILITY', 'public' );

if ( !FRONTEND_LOGIN )
{
	if ( INTRO_PAGE )
	{
		header( 'Location: ' . CAT_URL . PAGES_DIRECTORY . '/index.php' );
		exit( 0 );
	}
	else
	{
		header( 'Location: ' . CAT_URL . '/index.php' );
		exit( 0 );
	}
}

// Set the page content include file
define( 'PAGE_CONTENT', CAT_PATH . '/account/forgot_form.php' );

// Set auto authentication to false
$auto_auth = false;

// Include the index (wrapper) file
require( CAT_PATH . '/index.php' );

?>