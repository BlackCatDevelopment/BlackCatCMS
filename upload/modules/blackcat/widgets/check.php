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
 *   @copyright       2013, 2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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

// protect
$backend = CAT_Backend::getInstance('Start','start',false,false);
if(!CAT_Users::is_authenticated()) exit; // just to be _really_ sure...

$widget_settings = array(
    'allow_global_dashboard' => true,
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Version check'),
    'preferred_column'       => 1
);

if(!function_exists('render_widget_blackcat_check'))
{
    function render_widget_blackcat_check()
    {
        $error = $version = $newer = $last = $last_version = NULL;
        $debug = false;
        $doit  = true;

        include dirname(__FILE__).'/../data/config.inc.php';
        require CAT_PATH.'/framework/CAT/ExceptionHandler.php';

        // register exception/error handlers
        set_exception_handler(array("CAT_ExceptionHandler", "exceptionHandler"));
        set_error_handler(array("CAT_ExceptionHandler", "errorHandler"));
        register_shutdown_function(array("CAT_ExceptionHandler", "shutdownHandler"));

        $widget_name  = CAT_Object::lang()->translate('Version check');

        // check if the version should be checked
        if(!CAT_Helper_Validate::sanitizeGet('blackcat_refresh'))
        {
            $file = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../data/.last');
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
            $release_info = CAT_Helper_GitHub::getRelease('BlackCatDevelopment','BlackCatCMS');
            if(!$release_info || !is_array($release_info) || !count($release_info))
            {
                $error = "Unable to load source "
                       . "(using Proxy: " . ( ( defined('GITHUB_PROXY') && GITHUB_PROXY != '' ) ? 'yes' : 'no' ) . ")<br />"
                       . "Status: " . CAT_Helper_GitHub::getError()
                       . ( ( $debug ) ? "<br />".var_dump($client->getLastRequest()) : NULL )
                       . "<br />"
                       ;
                $version = 'unknown';
            }
            else
            {
                $version = isset($release_info['tag_name']) ? $release_info['tag_name'] : 'unknown';
                if ( $version && $version != 'unknown' )
                {
                    if ( CAT_Helper_Addons::getInstance()->versionCompare($version,CAT_VERSION,'>' ) ) {
                        $newer = true;
                    }
                    $fh = @fopen(CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../data/.last'),'w');
                    if(is_resource($fh)) {
                        fputs($fh,time().'|'.$version);
                        fclose($fh);
                    }
                }
            }
        }
        else {
            $version = ( isset($last_version) && $last_version != '' )
                     ? $last_version
                     : $version;
        }

        global $parser;
        $parser->setPath(dirname(__FILE__).'/../templates/default');
        $output = $parser->get(
            'widget.tpl',
            array(
                'error'               => $error,
                'version'             => $version,
                'newer'               => $newer,
                'last'                => CAT_Helper_DateTime::getInstance()->getDate($last).' '.CAT_Helper_DateTime::getInstance()->getTime($last),
                'CAT_VERSION'         => CAT_VERSION,
                'uri'                 => $_SERVER['SCRIPT_NAME'],
                'missing_mailer_libs' => count(CAT_Helper_Addons::getLibraries('mail')),
                'missing_wysiwyg'     => count(CAT_Helper_Addons::get_addons(NULL,'module','wysiwyg')),
            )
        );
        $parser->resetPath();
        return $output;
    }
}