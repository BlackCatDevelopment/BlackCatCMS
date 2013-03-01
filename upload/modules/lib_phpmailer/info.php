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
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
 *   @package         lib_phpmailer
 *
 */

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

$module_directory    = 'lib_phpmailer';
$module_name         = 'PHPMailer Library';
$module_function     = 'library';
$library_function    = 'mail';
$module_version      = '5.2.3';
$module_platform     = '1.0.x';
$module_requirements = 'PHP 5.2 or higher';
$module_author 		 = 'Andy Prevost, Marcus Bointon, Brent R. Matzelle (see class.pop3.php for more details); Black Cat Development (Module)';
$module_home		 = 'https://code.google.com/a/apache-extras.org/p/phpmailer/';
$module_license 	 = 'GNU General Public License (module); LGPLv2 (PHPMailer)';
$module_description  = 'PHPMailer Library for use with Black Cat CMS';
$module_guid         = '4F10A434-35A5-45C3-9B2C-F718F423E0FD';

?>