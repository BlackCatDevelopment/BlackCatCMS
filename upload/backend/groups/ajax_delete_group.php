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
 *   @review          21.07.2014 18:24:04
 *
 */

if (defined('CAT_PATH')) {
    include(CAT_PATH.'/framework/class.secure.php');
} else {
    $root = "../";
    $level = 1;
    while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root.'framework/class.secure.php')) {
        include($root.'framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}

$backend = CAT_Backend::getInstance('Access', 'groups', false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Access','groups_delete') )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You do not have the permission to delete a group.' ),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

$group_id = trim( $val->sanitizePost('id','numeric') );

if( ! $group_id || $group_id == '' )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You sent an invalid value'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

$members = $users->getMembers($group_id);
if(count($members))
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('The group cannot be deleted as it has members'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// Delete the group
$backend->db()->query(
    "DELETE FROM `:prefix:groups` WHERE `group_id` = :id LIMIT 1",
    array('id'=>$group_id)
);
if ( $backend->db()->isError() )
{
    $ajax    = array(
        'message'    => $backend->db()->getError(),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}
else
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('Group deleted successfully'),
        'success'    => true
    );
    print json_encode( $ajax );
    exit();
}
exit();
