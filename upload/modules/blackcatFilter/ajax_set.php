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
 *   @package         blackcatFilter
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

header('Content-type: application/json');

$backend = CAT_Backend::getInstance('admintools','blackcatFilter',false,false);
$val     = CAT_Helper_Validate::getInstance();
$error   = NULL;

if ( !CAT_Users::getInstance()->checkPermission('admintools','blackcatFilter') )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You do not have permissions to modify this page'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

$filter = $val->get('_REQUEST','filter');
$action = $val->get('_REQUEST','action');

// filter to activate/deactivate?
if($action!='delete')
{
    $value = ( $action == 'activate' )
           ? 'Y'
           : 'N'
           ;
    $backend->db()->query(sprintf(
        "UPDATE `%smod_filter` SET filter_active='%s' WHERE filter_name='%s'",
        CAT_TABLE_PREFIX, $value, $filter
    ));
    if($backend->db()->is_error())
        $error = $backend->db()->get_error();
}
// filter to delete?
else
{

    $res = $backend->db()->query(sprintf(
        "SELECT * FROM `%smod_filter` WHERE filter_name='%s'",
        CAT_TABLE_PREFIX, $filter
    ));
    if($res && $res->numRows())
    {
        $data = $res->fetchRow(MYSQL_ASSOC);
        if(!isset($data['code']) || $data['code']=='')
        {
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$data['module_name'].'/filter/'.$data['filter_name'].'.php');
            if(file_exists($file))
                unlink($file);
        }
        $backend->db()->query(sprintf(
            "DELETE FROM `%smod_filter` WHERE filter_name='%s'",
            CAT_TABLE_PREFIX, $filter
        ));
        if($backend->db()->is_error())
            $error = $backend->db()->get_error();
    }
    // just do nothing if the entry is not there (should never happen)
}

$ajax	= array(
	'message'	=> ( $error ? $error : $backend->lang()->translate('Details saved successfully') ),
	'success'	=> ( $error ? false  : true )
);
print json_encode( $ajax );
exit();