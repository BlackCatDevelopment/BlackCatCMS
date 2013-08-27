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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
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

// Check if user uploaded a file
if (!isset($_FILES['userfile']) || $_FILES['userfile']['size'] == 0)
{
	header("Location: index.php");
	exit(0);
}

$backend = CAT_Backend::getInstance('Addons', 'addons');

// Check if module dir is writable (doesn't make sense to go on if not)
if ( !(is_writable( CAT_PATH .  '/modules/') && is_writable( CAT_PATH . '/templates/') && is_writable( CAT_PATH . '/languages/') ) )
{
    $backend->print_error( 'Unable to write to the target directory' );
}

// keep old modules happy
require_once CAT_PATH.'/framework/class.admin.php';
$admin = new admin('Addons','addons');

if(CAT_Helper_Addons::installUploaded($_FILES['userfile']['tmp_name'],$_FILES['userfile']['name']))
{
    $backend->print_success( 'Installed successfully' );
}
else
{
    // error is already printed by the helper
    $backend->print_footer( 'Unable to install the module' );
}
