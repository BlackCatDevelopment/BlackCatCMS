<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
 
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

$backend  = CAT_Backend::getInstance('Pages', 'pages_intro');
$val      = CAT_Helper_Validate::getInstance();

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

if ( $val->sanitizeGet('wysiwyg') != 'no' )
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
$tpl_data = array();

ob_start();
	show_wysiwyg_editor('content','content',$content,'100%','500px');
	$tpl_data['intro_page_content']	= ob_get_contents();
//ob_end_clean();
ob_clean(); // allow multiple buffering for csrf-magic

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_pages_intro', $tpl_data);

// Print admin footer
$backend->print_footer();

?>