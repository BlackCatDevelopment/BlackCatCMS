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

$settings = array (
  array (
    'value' => 'http://blackcat-cms.org/media/_internal_/version.txt',
    'name' => 'source',
    'label' => 'Version check source file',
    'type' => 'text',
    'disabled' => 'disabled',
  ),
  array (
    'value' => '30',
    'name' => 'timeout',
    'label' => 'Timeout',
    'type' => 'text',
  ),
  array (
    'value' => '',
    'name' => 'proxy_host',
    'label' => 'Proxy host (leave empty if you don\'t have one)',
    'type' => 'text',
  ),
  array (
    'value' => '',
    'name' => 'proxy_port',
    'label' => 'Proxy port (leave empty if you don\'t need a proxy)',
    'type' => 'text',
  ),
  array (
    'value' => '5',
    'name' => 'last_edited_count',
    'label' => 'Number of last edited pages to show',
    'type' => 'text',
  ),
);

// --- do not change this manually, use the Admin Tool! ---
$current = array(
    'source' => 'http://blackcat-cms.org/media/_internal_/version.txt',
    'timeout' => '30',
    'proxy_host' => '',
    'proxy_port' => '',
    'last_edited_count' => '10',
);