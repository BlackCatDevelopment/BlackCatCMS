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
 *   @copyright       2016, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

$starttime = array_sum(explode(" ",microtime()));

// error configuration for production environment
ini_set('display_startup_errors', 'off');
ini_set('display_errors', 'off');
ini_set('html_errors', 'off');
ini_set('docref_root', 0);
ini_set('docref_ext', 0);
ini_set('error_reporting', '-1');
ini_set('log_errors_max_len', 0);

// Include config file
$config_file = dirname(__FILE__).'/config.php';
if(file_exists($config_file))
{
	require_once($config_file);
    if(defined('CAT_ENVIRONMENT') && CAT_ENVIRONMENT == 'development')
    {
        ini_set('display_startup_errors', 'on');
        ini_set('display_errors', 'on');
        ini_set('html_errors', 'on');
        ini_set('error_reporting', E_ALL & E_STRICT);
        ini_set('log_errors_max_len', 1024);
    }
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

require dirname(__FILE__).'/framework/CAT/ExceptionHandler.php';

// register exception/error handlers
set_exception_handler(array("CAT_ExceptionHandler", "exceptionHandler"));
set_error_handler(array("CAT_ExceptionHandler", "errorHandler"));
register_shutdown_function(array("CAT_ExceptionHandler", "shutdownHandler"));

global $wb, $admin;

// -----------------------------------------------------------------------------
// Create new frontend object; this is for backward compatibility only!
include CAT_PATH.'/framework/class.frontend.php';
$wb = new frontend();
// keep SM2 quiet
$wb->extra_where_sql = "visibility != 'none' AND visibility != 'hidden' AND visibility != 'deleted'";
// some modules may use $wb->page_id
if(isset($page_id))
    $wb->page_id=$page_id;
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

// -----------------------------------------------------------------------------
// clean up log files (older than 24 hours and size 0)
$files = CAT_Helper_Directory::findFiles('log_\d{4}-\d{2}-\d{2}\.txt',CAT_PATH.'/temp');
if(count($files))
    foreach($files as $f)
        if(filemtime($f)<(time()-24*60*60)&&filesize($f)==0)
            unlink($f);
$files = CAT_Helper_Directory::findFiles('log_\d{4}-\d{2}-\d{2}\.txt',CAT_PATH.'/temp/logs');
if(count($files))
    foreach($files as $f)
        if(filemtime($f)<(time()-24*60*60)&&filesize($f)==0)
            unlink($f);
// -----------------------------------------------------------------------------

// hand over to page handler
$page->show();

exit();
