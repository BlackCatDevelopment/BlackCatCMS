<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
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

// Get posted content
if ( !isset($_POST['content']) )
{
	header("Location: intro" . PAGE_EXTENSION . "");
	exit(0);
}

require_once( CAT_PATH . '/framework/class.admin.php' );
// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php' );

$admin			= new admin('Pages', 'pages_intro');
$content		= $admin->strip_slashes( $admin->get_post('content') );

if (strlen($content) == 0)
{
	$admin->print_error( 'Please insert content, an empty intro page cannot be saved.', 'intro.php');
}
else
{
	// Write new content
	$filename	= CAT_PATH . PAGES_DIRECTORY . '/intro' . PAGE_EXTENSION;
	$handle		= fopen($filename, 'w');
	if ( is_writable( $filename ) )
	{
		if ( fwrite( $handle, $content ) )
		{
			fclose( $handle );
			change_mode( $filename, 'file' );
			$admin->print_success( 'Intro page saved successfully' );
		}
		else
		{
			fclose($handle);
			$admin->print_error( 'Cannot write to file page-directory/intro.php, (insufficient privileges)' );
		}
	}
	else
	{
		$admin->print_error( 'Cannot write to file page-directory/intro.php, (insufficient privileges)' );
	}
}

// Print admin footer
$backend->print_footer();

?>