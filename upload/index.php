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

// add framework subdir to include path
set_include_path (
    implode(
        PATH_SEPARATOR,
        array(
            realpath(dirname(__FILE__).'/framework'),
            get_include_path(),
        )
    )
);
// register autoloader
function catcms_autoload($class) {
	@include str_replace( '_', '/', $class ) . '.php';
}
spl_autoload_register('catcms_autoload',false,true);

define('DEBUG', false);

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
	 *
	 *	Anmerkung:  HTTP/1.1 verlangt einen absoluten URI inklusive dem Schema,
	 *	Hostnamen und absoluten Pfad als Argument von Location:, manche, aber nicht alle
	 *	Clients akzeptieren jedoch auch relative URIs.
	 */
	$host       = $_SERVER['HTTP_HOST'];
	$uri        = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
	$file       = 'install/index.php';
	$target_url = 'http://'.$host.$uri.'/'.$file;
	header('Location: '.$target_url);
	die();	// make sure that the code below will not be executed
}

require_once(CAT_PATH.'/framework/class.frontend.php');
// Create new frontend object
$wb = new frontend();

// Figure out which page to display
// Stop processing if intro page was shown
$wb->page_select() or die();

// Collect info about the currently viewed page
// and check permissions
$wb->get_page_details();

// Collect general website settings
$wb->get_website_settings();

// Load functions available to templates, modules and code sections
// also, set some aliases for backward compatibility
require(CAT_PATH.'/framework/frontend.functions.php');

global $database;


// redirect menu-link
$this_page_id = PAGE_ID;

$sql  = 'SELECT `module`, `block` FROM `'.CAT_TABLE_PREFIX.'sections` ';
$sql .= 'WHERE `page_id` = '.(int)$this_page_id.' AND `module` = "menu_link"';
$query_this_module = $database->query($sql);
if($query_this_module->numRows() == 1)  // This is a menu_link. Get link of target-page and redirect
{
	// get target_page_id
	$sql  = 'SELECT * FROM `'.CAT_TABLE_PREFIX.'mod_menu_link` WHERE `page_id` = '.(int)$this_page_id;
	$query_tpid = $database->query($sql);
	if($query_tpid->numRows() == 1)
	{
		$res = $query_tpid->fetchRow();
		$target_page_id = $res['target_page_id'];
		$redirect_type = $res['redirect_type'];
		$anchor = ($res['anchor'] != '0' ? '#'.(string)$res['anchor'] : '');
		$extern = $res['extern'];
		// set redirect-type
		if($redirect_type == 301) {
			@header('HTTP/1.1 301 Moved Permanently', TRUE, 301);
		}
		if($target_page_id == -1)
		{
			if($extern != '')
			{
				$target_url = $extern.$anchor;
				header('Location: '.$target_url);
				exit;
			}
		}
		else
		{
			// get link of target-page
			$sql  = 'SELECT `link` FROM `'.CAT_TABLE_PREFIX.'pages` WHERE `page_id` = '.$target_page_id;
			$target_page_link = $database->get_one($sql);
			if($target_page_link != null)
			{
				$target_url = CAT_URL.PAGES_DIRECTORY.$target_page_link.PAGE_EXTENSION.$anchor;
				header('Location: '.$target_url);
				exit;
			}
		}
	}
}

//Get pagecontent in buffer for Droplets and/or Filter operations
ob_start();
require(CAT_PATH.'/templates/'.TEMPLATE.'/index.php');
$output = ob_get_contents();
if(ob_get_length() > 0) { ob_end_clean(); }

// wb->preprocess() -- replace all [wblink123] with real, internal links
$wb->preprocess($output);
// Load Droplet engine and process
if(file_exists(CAT_PATH .'/modules/dropleps/droplets.php'))
{
    include_once(CAT_PATH .'/modules/dropleps/droplets.php');
    if(function_exists('evalDroplets'))
    {
		$output = evalDroplets($output);
    }
}
// Output interface for Addons
if(file_exists(CAT_PATH .'/modules/output_interface/output_interface.php')) {
	include_once(CAT_PATH .'/modules/output_interface/output_interface.php');
	if(function_exists('output_interface')) {
		$output = output_interface($output);
	}
}

// CSRF protection - add tokens to internal links
if ($wb->is_authenticated()) {
	if (file_exists(CAT_PATH .'/framework/tokens.php')) {
		include_once(CAT_PATH .'/framework/tokens.php');
		if (function_exists('addTokens')) addTokens($output, $wb);
	}
}

echo $output;
exit;
?>