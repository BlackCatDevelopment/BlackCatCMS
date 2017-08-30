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

global $backend, $val, $users;

$backend = CAT_Backend::getInstance('start','start',false,false);
$val     = CAT_Helper_Validate::getInstance();
$users   = CAT_Users::getInstance();

function backend_pages_prechecks($need_permission)
{
    global $backend, $users;
    if ( ! $users->checkPermission('pages',$need_permission,false,false) )
    {
        CAT_Object::json_error($backend->lang()->translate('You don not have the permission to add a page.'));
        exit();
    }

    // check if pages folder is writable
    if ( !is_writable(CAT_PATH.PAGES_DIRECTORY.'/') )
    {
    	CAT_Object::json_error($backend->lang()->translate('The pages directory is not writable!'));
    	exit();
    }
}   // end function backend_pages_prechecks()

function backend_pages_getoptions()
{
    global $val, $users;
    $options = array(
        'admin_groups'   => ( ($val->sanitizePost('admin_groups',NULL,true) != '') ? $val->sanitizePost('admin_groups',NULL,true) : array('1') ),
        'description'    => htmlspecialchars($val->sanitizePost('description',NULL,true), ENT_QUOTES, "UTF-8", false),
        'keywords'       => htmlspecialchars($val->sanitizePost('keywords',NULL,true), ENT_QUOTES, "UTF-8", false),
        'language'       => $val->sanitizePost('language',NULL,true),
        'level'          => 0, // just a default here
        'link'           => '/'.( ( $val->sanitizePost('page_link',NULL,true) != '' ) ? htmlspecialchars($val->sanitizePost('page_link',NULL,true)) : '' ),
        'menu'           => ( ( $val->sanitizePost('menu',NULL,true) != '') ? $val->sanitizePost('menu',NULL,true) : 1 ),
        'menu_title'     => htmlspecialchars($val->sanitizePost('menu_title',NULL,true), ENT_QUOTES, "UTF-8", false ),
        'modified_by'    => $users->get_user_id(),
        'modified_when'  => time(),
        'page_title'     => htmlspecialchars($val->sanitizePost('page_title',NULL,true), ENT_QUOTES, "UTF-8", false),
        'parent'         => ( $val->sanitizePost('parent','numeric',true) ? $val->sanitizePost('parent','numeric',true) : 0 ),
        'position'       => 1, // just a default here
        'searching'      => $val->sanitizePost('searching',NULL,true) ? '1' : '0',
        'target'         => $val->sanitizePost('target',NULL,true),
        'template'       => $val->sanitizePost('template',NULL,true),
        'variant'        => $val->sanitizePost('variant',NULL,true),
        'viewing_groups' => ( ( $val->sanitizePost('viewing_groups',NULL,true) != '' ) ? $val->sanitizePost('viewing_groups',NULL,true) : array('1') ),
        'visibility'     => $val->sanitizePost('visibility',NULL,true),
    );
    return $options;
}