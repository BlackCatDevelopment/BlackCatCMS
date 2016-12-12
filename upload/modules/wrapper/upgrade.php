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
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
 *   @package         wrapper
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

if(!isset($module_version))
{
    $details = CAT_Helper_Addons::getAddonDetails('wrapper');
    $module_version = $details['version'];
}
if ( ! CAT_Helper_Addons::versionCompare( $module_version, '2.7.2', '>=' ) ) {
	$database->query('ALTER TABLE `'.CAT_TABLE_PREFIX.'mod_wrapper` CHANGE COLUMN `type` `wtype` VARCHAR(50) NOT NULL DEFAULT \'iframe\' AFTER `width`;');
}
if ( ! CAT_Helper_Addons::versionCompare( $module_version, '2.7.5', '>=' ) ) {
    $database->query('ALTER TABLE `'.CAT_TABLE_PREFIX.'mod_wrapper`
	CHANGE COLUMN `height` `height` VARCHAR(50) NOT NULL DEFAULT \'400\' AFTER `url`,
	CHANGE COLUMN `width` `width` VARCHAR(50) NOT NULL DEFAULT \'100%\' AFTER `height`;');
}
if ( ! CAT_Helper_Addons::versionCompare( $module_version, '2.7.5', '>=' ) ) {
    $database->query('ALTER TABLE `'.CAT_TABLE_PREFIX.'mod_wrapper`
	CHANGE COLUMN `url` `url` TEXT NULL;');
}


// remove old template files
$ltes = CAT_Helper_Directory::getInstance()->findFiles( '.*\.lte', dirname(__FILE__).'/htt' );
if(count($ltes))
    foreach($ltes as $file)
        @unlink($file);

// add files to class_secure
$addons_helper = new CAT_Helper_Addons();
foreach(
    array( 'save.php' )
    as $file
) {
    if ( false === $addons_helper->sec_register_file( 'wrapper', $file ) )
    {
         error_log( "Unable to register file -$file-!" );
    }
}