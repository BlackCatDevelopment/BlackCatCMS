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

require_once( CAT_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_intro');

// Get page content
$filename		= CAT_PATH . PAGES_DIRECTORY . '/intro' . PAGE_EXTENSION;

if ( file_exists( $filename ) )
{
	$handle		= fopen($filename, "r");
	$content	= fread($handle, filesize($filename));
	fclose( $handle );
}
else
{
	$content = '';
}

if ( $admin->get_get('wysiwyg') != 'no' )
{
	if ( !defined( 'WYSIWYG_EDITOR' ) || WYSIWYG_EDITOR == 'none' || !file_exists( CAT_PATH . '/modules/' . WYSIWYG_EDITOR . '/include.php' ) )
	{
		function show_wysiwyg_editor( $name, $id, $content, $width, $height )
		{
			echo '<textarea name="' . $name . '" id="' . $id . '" style="width: ' . $width . '; height: ' . $height . ';">' . $content . '</textarea>';
		}
	}
	else
	{
		$id_list		= array('content');
		require( CAT_PATH . '/modules/' . WYSIWYG_EDITOR . '/include.php' );
	}
}
// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;
$data_dwoo = array();

ob_start();
	show_wysiwyg_editor('content','content',$content,'100%','500px');
	$data_dwoo['intro_page_content']	= ob_get_contents();
ob_end_clean();

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_pages_intro.lte', $data_dwoo);

// Print admin footer
$admin->print_footer();

?>