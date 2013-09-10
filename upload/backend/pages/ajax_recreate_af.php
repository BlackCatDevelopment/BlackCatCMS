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

$backend = CAT_Backend::getInstance('Pages','pages',false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Pages','pages') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to proceed this action'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$page_id = $val->sanitizePost('page_id','numeric');

// Get page id
if (!$page_id)
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// load page settings
$page     = CAT_Helper_Page::getPage($page_id);
// get file name
$filename = CAT_PATH . PAGES_DIRECTORY . $page['link'] . PAGE_EXTENSION;
// create access file
if(CAT_Helper_Page::createAccessFile($filename, $page_id))
{
    $ajax = array(
		'message'	=> $backend->lang()->translate('Access file created successfully'),
		'success'	=> true
	);
}
else
{
    $ajax = array(
		'message'	=> $backend->lang()->translate('Unable to re-create the access file!'),
		'success'	=> false
	);
}

print json_encode( $ajax );
exit();