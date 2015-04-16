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

$max         = 5;

$pg          = CAT_Helper_Page::getInstance();
$widget_name = $pg->lang()->translate('Forum News');
$tpl_data    = array();

$dom = new DOMDocument();
$dom->loadXML(_loadURL('http://forum.blackcat-cms.org/feed.php?f=2'));

$items = $dom->getElementsByTagName('entry');
$cnt   = 0;

foreach($items as $item)
{
    if($item->childNodes->length)
    {
        if(substr(str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),0,3) == 'Re:') continue;
        $tpl_data[] = array(
            'published' => $item->getElementsByTagName('published')->item(0)->textContent,
            'link'      => $item->getElementsByTagName('link')->item(0)->getAttribute('href'),
            'title'     => str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),
        );
        $cnt++;
        if($cnt == $max) break;
    }
}

global $parser;
$parser->setPath(dirname(__FILE__).'/../templates/default');
$parser->output('news.tpl',array('news'=>$tpl_data));

function _loadURL($url) {
    include dirname(__FILE__).'/../data/config.inc.php';
    ini_set('include_path', CAT_PATH.'/modules/lib_zendlite');
    include CAT_PATH.'/modules/lib_zendlite/library.php';
    $client = new Zend\Http\Client(
        $url,
        array(
            'timeout'      => $current['timeout'],
            'adapter'      => 'Zend\Http\Client\Adapter\Proxy',
            'proxy_host'   => $current['proxy_host'],
            'proxy_port'   => $current['proxy_port'],
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