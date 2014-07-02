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

function wrapper_getFirstImageFromContent($section_id, $exec_droplets=true)
{
    global $database;
    $settings = $database->query(sprintf(
        'SELECT `url` FROM `%smod_wrapper` WHERE section_id = "%d"',
        CAT_TABLE_PREFIX, $section_id
    ));
    if($settings->numRows())
    {
        $row = $settings->fetchRow();
        ini_set('include_path', CAT_PATH.'/modules/lib_zendlite');
        include 'Zend/Http/Client.php';
        $client = new Zend_Http_Client(
            $row['url'],
            array(
                'timeout'      => '30',
                'adapter'      => 'Zend_Http_Client_Adapter_Proxy',
/*******************************************************************************
  If your webserver uses a proxy (in local XAMPP environments, for example),
  uncomment the following two lines and set proxy_host and proxy_port to
  the appropriate values
*******************************************************************************/
//                'proxy_host'   => '',
//                'proxy_port'   => '',
            )
        );
        $client->setCookieJar();
        $client->setHeaders(
            array(
                'Pragma' => 'no-cache',
                'Cache-Control' => 'no-cache',
                'Accept-Encoding' => '',
            )
        );

        try {
            $response = $client->request( Zend_Http_Client::GET );
            if ( $response->getStatus() == '200' )
            {
                $content = $response->getBody();
                if($content != '')
                {
                    $doc = new DOMDocument();
                    libxml_use_internal_errors(true);  // avoid HTML5 errors
                    $doc->loadHTML($content);
                    libxml_clear_errors();
                    $img = $doc->getElementsByTagName('img');
                    return $img->item(0)->getAttribute('src');
                }
            }
        } catch ( Zend_HTTP_Client_Adapter_Exception $e) {}
        return NULL;
    }
}