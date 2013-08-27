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
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         mojito
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


// OBLIGATORY VARIABLES
$template_directory		= 'mojito';
$template_name			= 'Mojito (Standard Frontend Template)';
$template_function		= 'template';
$template_version		= '0.1';
$template_platform		= '1.x';
$template_license		= '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_license_terms	= '-';
$template_author		= 'Matthias Glienke, creativecat';
$template_description	= 'Introduced with Black Cat CMS in 2013.<br/><br/>Done by Matthias Glienke, <a class="icon-creativecat" href="http://creativecat.de"> creativecat</a>';
$template_engine		= 'dwoo';
$template_guid			= '29c34310-02d0-4609-b00e-6461669b052e';
$template_variants		= array( 'default', 'custom' );

// OPTIONAL VARIABLES FOR ADDITIONAL MENUES AND BLOCKS
$menu[1]				= 'Hauptnavigation';
$menu[2]				= 'Metanavigation';

$block[1]				= 'Main Content';
$block[2]				= 'Header Content';
$block[3]				= 'Sidebar';


?>