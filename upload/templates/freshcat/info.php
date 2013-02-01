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
 *   @template        FreshCat - Backend-Theme for Black Cat CMS
 *   @author          Black Cat Development
 *   @author          Matthias Glienke (creativecat)
 *   @copyright       2013, Black Cat Development
 *   @copyright		  2013 Matthias Glienke (creativecat)
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
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

// OBLIGATORY LEPTON VARIABLES
$template_directory			= 'freshcat';
$template_name				= 'FreshCat Backend Theme';
$template_function			= 'theme';
$template_version			= '0.7.5';
$template_platform			= '2.x';
$template_author			= 'Matthias Glienke, creativecat';
$template_license			= '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_license_terms		= '-';
$template_description		= 'Introduced with Black Cat CMS in 2013.<br/><br/>Done by Matthias Glienke, <a class="icon-creativecat" href="http://creativecat.de"> creativecat</a>';
$template_engine			= 'dwoo';
$template_guid				= 'AD6296ED-31BD-49EB-AE23-4DD76B7ED776';


?>