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
 *   @copyright       2015, Black Cat Development
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

define('CURRENT_URL',$_SERVER['SCRIPT_NAME']);
define('GITHUB_URL','https://raw.githubusercontent.com/BlackCatDevelopment/bcExtensionsCatalog/master/catalog.json');
define('PROXYHOST','proxy.materna.de');
define('PROXYPORT','8080');

function get_catalog()
{
    $string    = file_get_contents(CAT_PATH."/temp/catalog.json");
    $catalog   = json_decode($string,true);
    if(is_array($catalog))
        return $catalog;
    else
        return array();
}

function update_catalog()
{
    $ch   = init_client(GITHUB_URL);
    $data = curl_exec($ch);
    if(curl_error($ch))
    {
        print json_encode(array('success'=>false,'message'=>trim(curl_error($ch))));
        exit();
    }
    $fh = fopen( CAT_PATH.'/temp/catalog.json', 'w' );
    fwrite($fh,$data);
    fclose($fh);
}

function init_client($url=NULL)
{
    $headers = array(
        'User-Agent: php-curl'
    );
    $connection = curl_init();
    curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($connection, CURLOPT_MAXREDIRS, 2);
    curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
    if(defined('PROXYHOST'))
        curl_setopt($connection, CURLOPT_PROXY, PROXYHOST);
    if(defined('PROXYPORT'))
        curl_setopt($connection, CURLOPT_PROXYPORT, PROXYPORT);
    if($url)
        curl_setopt($connection, CURLOPT_URL, $url);
    return $connection;
}