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
 *   @category        CAT_Modules
 *   @package         blackcat
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

include dirname(__FILE__).'/../data/config.inc.php';

$data        = array();
$widget_name = 'Statistics';
$number      = ( $current['last_edited_count'] > 0 && $current['last_edited_count'] < 50 )
             ? $current['last_edited_count']
             : 10;

// format installation date and time
$data['installation_time']
    = CAT_Helper_DateTime::getDateTime(INSTALLATION_TIME);

// get page statistics (count by visibility)
$pg = CAT_Helper_Page::getPagesByVisibility();
foreach( array_keys($pg) as $key )
{
    $data['visibility'][$key] = count($pg[$key]);
}

// get last edited
$data['latest'] = CAT_Helper_Page::getLastEdited($number);

global $parser;
$parser->setPath(dirname(__FILE__).'/../templates/default');
$parser->output(
    'stats.tpl',
    $data
);