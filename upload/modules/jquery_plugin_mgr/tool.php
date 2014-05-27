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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         jQuery Plugin Manager
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// handle upload
if( CAT_Helper_Validate::sanitizePost('upload') && isset($_FILES['userfile']) && is_array($_FILES['userfile']) )
{
    $p = CAT_Helper_Upload::getInstance($_FILES['userfile'],CAT_PATH.'/temp');
    $p->file_overwrite = true;
    $p->process(CAT_PATH.'/temp');
    if($p->processed)
    {
        $subdir = $p->file_dst_name_body;
        $z = CAT_Helper_Zip::getInstance(CAT_PATH.'/temp/'.$p->file_dst_name)->config('Path',CAT_PATH.'/modules/lib_jquery/plugins/'.$subdir);
        $z->extract();
    }
}

// get already installed plugins
$files   = CAT_Helper_Directory::getInstance()
         -> maxRecursionDepth(0)
         -> getDirectories( CAT_PATH.'/modules/lib_jquery/plugins', CAT_PATH.'/modules/lib_jquery/plugins/');
$readmes = jqpmgr_getReadmes($files);

$parser->setPath(CAT_PATH.'/modules/jquery_plugin_mgr/templates/default');
$parser->output('tool',array('plugins'=>$files,'readmes'=>$readmes));


function jqpmgr_getReadmes($plugins)
{
    $readme_filenames = array(
        // current language
        'readme_'.strtolower( LANGUAGE ).'.html',
        // default
        'readme.html'
    );
    $dir     = CAT_PATH.'/modules/lib_jquery/plugins/';
    $readmes = array();

    foreach($plugins as $p)
    {
        foreach ( $readme_filenames as $rfile )
        {
            if ( file_exists( $dir.$p.'/'.$rfile ) )
            {
                $readmes[$p] = CAT_URL.'/modules/lib_jquery/plugins/'.$p.'/'.$rfile;
                break;
            }
            if(!isset($readmes[$p]))
                $readmes[$p]='';
        }
    }

    return $readmes;
}