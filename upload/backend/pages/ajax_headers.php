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

include 'functions.php';

header('Content-type: application/json');

// ===============
// ! check perms
// ===============
$users = CAT_Users::getInstance();
if ( ! $users->checkPermission('pages', 'pages_settings', false) == true )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You do not have the permission to do this.'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// ===============
// ! Get page id
// ===============
$val = CAT_Helper_Validate::getInstance();
$page_id = $val->get('_REQUEST', 'page_id', 'numeric');

if ($page_id=='')
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('Invalid data!'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// Add JS
if($val->sanitizePost('add_js_file')!='')
{
    $result = CAT_Helper_Page::adminAddHeaderComponent('js',$val->sanitizePost('add_js_file'),$page_id);
    print json_encode( $result );
    exit();
}
elseif($val->sanitizePost('add_css_file')!='')
{
    $result = CAT_Helper_Page::adminAddHeaderComponent('css',$val->sanitizePost('add_css_file'),$page_id);
    print json_encode( $result );
    exit();
}
elseif($val->sanitizePost('del_js_file')!='')
{
    $result = CAT_Helper_Page::adminDelHeaderComponent('js',$val->sanitizePost('del_js_file'),$page_id);
    print json_encode( $result );
    exit();
}
elseif($val->sanitizePost('del_css_file')!='')
{
    $result = CAT_Helper_Page::adminDelHeaderComponent('css',$val->sanitizePost('del_css_file'),$page_id);
    print json_encode( $result );
    exit();
}
elseif($val->sanitizePost('add_plugin')!='')
{
    $plugin = $val->sanitizePost('add_plugin');
    $success = true;
    // find JS files
    $js  = CAT_Helper_Directory::getInstance()
           ->maxRecursionDepth(5)
           ->setSuffixFilter(array('js'))
           ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin,true,true,CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin);
    // find CSS files
    $css = CAT_Helper_Directory::getInstance()
           ->maxRecursionDepth(5)
           ->setSuffixFilter(array('css'))
           ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin,true,true,CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin);
    foreach($js as $file)
        CAT_Helper_Page::adminAddHeaderComponent('js',$plugin.'/'.$file,$page_id);
    foreach($css as $file)
        CAT_Helper_Page::adminAddHeaderComponent('css',$plugin.'/'.$file,$page_id);
    $ajax    = array(
        'message'    => $success ? 'ok' : 'error',
        'success'    => $success
    );
    print json_encode( $ajax );
    exit();
}
elseif($val->sanitizePost('del_plugin')!='')
{
    $plugin = $val->sanitizePost('del_plugin');
    // find JS files
    $js  = CAT_Helper_Directory::getInstance()
           ->maxRecursionDepth(5)
           ->setSuffixFilter(array('js'))
           ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin,true,true,CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin);
    // find CSS files
    $css = CAT_Helper_Directory::getInstance()
           ->maxRecursionDepth(5)
           ->setSuffixFilter(array('css'))
           ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin,true,true,CAT_PATH.'/modules/lib_jquery/plugins/'.$plugin);
    foreach($js as $file)
        CAT_Helper_Page::adminDelHeaderComponent('js','/modules/lib_jquery/plugins/'.$plugin.$file,$page_id);
    foreach($css as $file)
        CAT_Helper_Page::adminDelHeaderComponent('css','/modules/lib_jquery/plugins/'.$plugin.$file,$page_id);
    print json_encode(array('success'=>true,'message'=>'ok'));
    exit();
}
elseif($val->sanitizePost('order')!='')
{
    if(is_array($val->sanitizePost('order')))
    {
        $type = $val->sanitizePost('type');
        $q    = sprintf(
            'UPDATE `%spages_headers` SET `page_%s_files` = \'%s\' WHERE `page_id`="%d"',
            CAT_TABLE_PREFIX, $type, serialize($val->sanitizePost('order')), $page_id
        );
        $database->query($q);
        print json_encode(array(
            'success' => $database->is_error() ? false : true,
            'message' => $database->is_error() ? $database->get_error() : 'Success'
        ));
        exit();
    }
}
elseif($val->sanitizePost('save')!='')
{
    $data = CAT_Helper_Page::getExtraHeaderFiles($page_id);
    if(count($data))
        $q = 'UPDATE `:prefix:pages_headers` SET `use_core`=:use_core, `use_ui`=:use_ui WHERE `page_id`=:page_id';
    else
        $q = 'INSERT INTO `:prefix:pages_headers` ( `page_id`, `use_core`, `use_ui` ) VALUES ( :page_id, :use_core, :use_ui )';
    $database->query(
        $q,
        array(
            'use_core' => ($val->sanitizePost('use_core')=='true'?'Y':'N'),
            'use_ui'   => ($val->sanitizePost('use_ui')=='true'?'Y':'N'),
            'page_id'  => $page_id
        )
    );
    print json_encode(array(
        'success' => $database->is_error() ? false : true,
        'message' => $database->is_error() ? $database->get_error() : 'Success'
    ));
    exit();
}


