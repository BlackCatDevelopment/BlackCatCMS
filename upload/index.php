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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */


$starttime = array_sum(explode(" ",microtime()));

// Include config file
$config_file = dirname(__FILE__).'/config.php';
if(file_exists($config_file))
{
	require_once($config_file);
}
else
{
	/**
	 *	File isn't there, so we try to run the installer
	 */
	$host       = $_SERVER['HTTP_HOST'];
	$uri        = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
	$file       = 'install/index.php';
	$target_url = 'http://'.$host.$uri.'/'.$file;
	header('Location: '.$target_url);
	exit(); // make sure that the code below will not be executed
}

global $wb, $admin;

// -----------------------------------------------------------------------------
// Create new frontend object; this is for backward compatibility only!
include CAT_PATH.'/framework/class.frontend.php';
$wb = new frontend();
// keep SM2 quiet
$wb->extra_where_sql = "visibility != 'none' AND visibility != 'hidden' AND visibility != 'deleted'";
include CAT_PATH.'/framework/frontend.functions.php';
// -----------------------------------------------------------------------------

// get page to show
$page_id = CAT_Helper_Page::selectPage() or die();

// this will show the Intro- or Default-Page if no PAGE_ID is available
$page    = CAT_Page::getInstance($page_id);

// -----------------------------------------------------------------------------
// keep SM2 happy
$wb->page = CAT_Helper_Page::properties($page_id);
$wb->default_link = CAT_Helper_Page::properties($page_id,'link');
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// needed at least for droplets
$admin =& $wb;
// -----------------------------------------------------------------------------

// hand over to page handler
$page->show();

exit();
