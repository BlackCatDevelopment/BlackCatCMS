<?php
/**
 *  @template			FreshCat - Backend-Theme for Lepton
 *  @version			see info.php of this template
 *  @author				Matthias Glienke (creativecat)
 *  @copyright			2012 Matthias Glienke (creativecat)
 *  @license			GNU General Public License
 *  @license terms		see info.php of this template
 *  @platform			LEPTON, see info.php of this template
 *  @requirements		PHP 5.2.x and higher
  * @version			$Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

// OBLIGATORY WEBSITE BAKER VARIABLES
$template_directory			= 'freshcat';
$template_name				= 'FreshCat Backend Theme';
$template_function			= 'theme';
$template_version			= '0.7.2';
$template_platform			= '2.x';
$template_author			= 'Matthias Glienke, creativecat';
$template_license			= '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_license_terms		= '-';
$template_description		= 'First backend theme for Lepton using the template engine Dwoo.<br/>Introduced with Lepton 2.0 in 2012.<br/><br/>Done by Matthias Glienke, <a href="http://creativecat.de">creativecat</a>';
$template_engine			= 'dwoo';
$template_guid				= 'AD6296ED-31BD-49EB-AE23-4DD76B7ED776';

?>