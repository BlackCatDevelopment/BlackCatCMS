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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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

function _loadURL($url) {
    include dirname(__FILE__).'/data/config.inc.php';
    ini_set('include_path', CAT_PATH.'/modules/lib_zendlite');
    include CAT_PATH.'/modules/lib_zendlite/library.php';
    $client = new Zend\Http\Client(
        $url,
        array(
            'timeout'      => $current['timeout'],
            'adapter'      => 'Zend\Http\Client\Adapter\Proxy',
            'proxy_host'   => $current['proxy_host'],
            'proxy_port'   => $current['proxy_port'],
            'sslverifypeer' => false
        )
    );
    $client->setHeaders(
        array(
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache',
        )
    );

    try {
        $response = $client->send();
        if ( $response->getStatusCode() != '200' ) {
            $error = "Unable to load source "
                   . "(using Proxy: " . ( ( isset($current['proxy_host']) && $current['proxy_host'] != '' ) ? 'yes' : 'no' ) . ")<br />"
                   . "Status: " . $response->getStatus() . " - " . $response->getMessage()
                   . ( ( $debug ) ? "<br />".var_dump($client->getLastRequest()) : NULL )
                   . "<br />"
                   ;
        }
        else
        {
            return $response->getBody();
        }
    } catch ( Exception $e ) {
        $error = "Unable to load source "
               . "(using Proxy: " . ( ( isset($current['proxy_host']) && $current['proxy_host'] != '' ) ? 'yes' : 'no' ) . ")<br />"
           . $e->getMessage()
           . "<br />"
           ;
    }
    echo $error;
}