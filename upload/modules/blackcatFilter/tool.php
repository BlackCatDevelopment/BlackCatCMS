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

$backend      = CAT_Backend::getInstance('admintools','blackcatFilter');
$val          = CAT_Helper_Validate::getInstance();
$showit       = false;
$errors       = array();
$upload_error = NULL;

// new filter?
if ( $val->sanitizePost('filter_add') )
{
    $data    = array();
    foreach( array( 'module_name','name','description','code','active' ) as $key )
    {
        if(!$val->sanitizePost('filter_'.$key))
        {
            if($key=='code' && isset($_FILES['filter_file']))
            {
                $data[$key] = '';
                continue;
            }
            $errors[$key] = $backend->lang()->translate('Please fill out the field: {{ name }}', array('name'=>$backend->lang()->translate($key)) );
        }
        else
        {
            $data[$key] = $val->sanitizePost('filter_'.$key);
        }
    }

    if(isset($errors['file']) && !isset($errors['code']))
        unset($errors['file']);

    if(!count($errors))
    {
        if(isset($_FILES['filter_file']))
        {
            $file = CAT_Helper_Upload::getInstance($_FILES['filter_file']);
            $file->no_script = false;
            $file->allowed   = array('application/octet-stream');
            $file->process(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/blackcatFilter/filter/'));
            if ( !$file->processed )
                $upload_error = $file->error;
            else
                $data['name'] = $file->file_dst_name_body;
                // filter must have the same name as the file
                // the file will be renamed by the upload helper if it already
                // exists, so we use the destination name here
        }
    }
    if(count($errors) || $upload_error)
    {
        $showit = true;
    }
    else
    {
        $backend->db()->query(sprintf(
            "INSERT INTO `%smod_filter` VALUES ( '%s', '%s', '%s', '%s', '%s' )",
            CAT_TABLE_PREFIX, $data['name'], $data['module_name'], $data['description'], $data['code'], $data['active']
        ));
        if($backend->db()->is_error())
            $errors[] = $backend->db()->get_error();
    }
}

// get available filters
$filters = array();
$result  = $backend->db()->query(sprintf(
    "SELECT * FROM `%smod_filter`",
    CAT_TABLE_PREFIX
));

if($result->numRows())
{
    while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
    {
        $filters[] = $row;
    }
}

$parser->setPath(dirname(__FILE__).'/templates/default');
$parser->output('tool.tpl',array(
    'filters'      => $filters,
    'showit'       => $showit,
    'missing'      => $errors,
    'modules'      => CAT_Helper_Addons::get_addons('blackcatFilter','module'),
    'upload_error' => $upload_error,
    'errors'       => implode('<br />',$errors) . '<br />' . $upload_error,
));