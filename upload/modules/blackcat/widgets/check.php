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
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));        $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
            if (empty($sub)) continue; $dir .= '/'.$sub;
            if (file_exists($dir.'/framework/class.secure.php')) {
                    include($dir.'/framework/class.secure.php'); $inc = true;        break;
            }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

include dirname(__FILE__).'/../data/config.inc.php';

$widget_name = 'Version check';
$error = $version = $newer = $last = $last_version = NULL;
$debug = false;
$doit  = true;

if(!CAT_Helper_Validate::sanitizeGet('blackcat_refresh'))
{
    $file = sanitize_path(dirname(__FILE__).'/../data/.last');
    if ( file_exists($file) )
    {
        $fh = @fopen($file,'r');
        if ( is_resource($fh) )
        {
            $last = fgets($fh);
            fclose($fh);
        }
    }
    if ( $last )
    {
        list( $last, $last_version ) = explode('|',$last);
        if ( $last > ( time() - 60 * 60 * 24 ) ) {
            $doit = false;
        }
    }
}

if ( $doit ) {

    ini_set('include_path', CAT_PATH.'/modules/lib_zendlite');
    include 'Zend/Http/Client.php';
    $client = new Zend_Http_Client(
        $current['source'],
        array(
            'timeout'      => $current['timeout'],
            'adapter'      => 'Zend_Http_Client_Adapter_Proxy',
            'proxy_host'   => $current['proxy_host'],
            'proxy_port'   => $current['proxy_port'],
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
        if ( $response->getStatus() != '200' ) {
            $error = "Unable to load source "
                   . "(using Proxy: " . ( ( isset($current['proxy_host']) && $current['proxy_host'] != '' ) ? 'yes' : 'no' ) . ")<br />"
                   . "Status: " . $response->getStatus() . " - " . $response->getMessage()
                   . ( ( $debug ) ? "<br />".var_dump($client->getLastRequest()) : NULL )
                   . "<br />"
                   ;
            $version = 'unknown';
        }
        else
        {
            $version = $response->getRawBody();
        }
    } catch ( Zend_HTTP_Client_Adapter_Exception $e) {
        $error = "Unable to load source "
               . "(using Proxy: " . ( ( isset($current['proxy_host']) && $current['proxy_host'] != '' ) ? 'yes' : 'no' ) . ")<br />"
           . $e->getMessage()
           . "<br />"
           ;
        $version = 'unknown';
    }

    if ( $version && $version != 'unknown' )
    {
        if ( CAT_Helper_Addons::getInstance()->versionCompare($version,CAT_VERSION,'>' ) ) {
            $newer = true;
        }
    }

    $fh   = @fopen(sanitize_path(dirname(__FILE__).'/../data/.last'),'w');
    if ( is_resource($fh) ) {
    fputs($fh,time().'|'.$version);
    fclose($fh);
    }

}
else {
    $version = ( isset($last_version) && $last_version != '' )
             ? $last_version
             : $version;
}

global $parser;
$parser->setPath(dirname(__FILE__).'/../templates/default');
$parser->output(
    'widget.tpl',
    array(
        'error' => $error,
        'version' => $version,
        'newer' => $newer,
        'last' => CAT_Helper_DateTime::getInstance()->getDate($last).' '.CAT_Helper_DateTime::getInstance()->getTime($last),
        'CAT_VERSION' => CAT_VERSION,
        'uri' => $_SERVER['SCRIPT_NAME'],
        'missing_mailer_libs' => count(CAT_Helper_Addons::getLibraries('mail')),
        'missing_wysiwyg' => count(CAT_Helper_Addons::get_addons(NULL,'module','wysiwyg')),
    )
);
