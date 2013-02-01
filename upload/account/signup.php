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

if(!( intval(FRONTEND_SIGNUP) && (  0 == (isset($_SESSION['USER_ID']) ? intval($_SESSION['USER_ID']) : 0) )))
{
	if(INTRO_PAGE) {
		header('Location: '.CAT_URL.PAGES_DIRECTORY.'/index.php');
		exit(0);
	} else {
		header('Location: '.CAT_URL.'/index.php');
		exit(0);
	}
}

if (
       ENABLED_ASP
	&& isset( $_POST[ 'username' ] )
	&& ( // form faked? Check the honeypot-fields.
	     (
		      ! isset( $_POST[ 'submitted_when' ] )
		   || ! isset( $_SESSION[ 'submitted_when' ] )
		 )
		 ||
		 (
		      $_POST[ 'submitted_when' ] != $_SESSION[ 'submitted_when' ]
		 )
		 ||
		 (
		         ! isset( $_POST[ 'email-address' ] )
		      || $_POST[ 'email-address' ]
		 )
		 ||
		 (
		 	    ! isset( $_POST[ 'name' ] )
			  || $_POST[ 'name' ]
	 	 )
		 ||
		 (
		         ! isset( $_POST[ 'full_name' ] )
		 	  || $_POST[ 'full_name' ]
		 )
	)
) {
	exit( header( "Location: " . CAT_URL . PAGES_DIRECTORY . "" ) );
}

// Load the language file
if ( !file_exists( CAT_PATH . '/languages/' . DEFAULT_LANGUAGE . '.php' ) )
{
	exit( 'Error loading language file ' . DEFAULT_LANGUAGE . ', please check configuration' );
}
else
{
	require_once( CAT_PATH . '/languages/' . DEFAULT_LANGUAGE . '.php' );
	$load_language = false;
}


// Required page details
$page_id          = 0;
$page_description = '';
$page_keywords    = '';
define( 'PAGE_ID', 0 );
define( 'ROOT_PARENT', 0 );
define( 'PARENT', 0 );
define( 'LEVEL', 0 );
define( 'PAGE_TITLE', $TEXT[ 'SIGNUP' ] );
define( 'MENU_TITLE', $TEXT[ 'SIGNUP' ] );
define( 'MODULE', '' );
define( 'VISIBILITY', 'public' );

// Set the page content include file
if ( isset( $_POST[ 'username' ] ) )
{
	define( 'PAGE_CONTENT', CAT_PATH . '/account/signup2.php' );
}
else
{
	define( 'PAGE_CONTENT', CAT_PATH . '/account/signup_form.php' );
}

// Set auto authentication to false
$auto_auth = false;

// Include the index (wrapper) file
require( CAT_PATH . '/index.php' );

?>