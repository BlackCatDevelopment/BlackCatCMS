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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         wysiwyg
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

/**
 *	Get content
 */
$result = CAT_Helper_Page::getInstance()->db()->query(sprintf(
    "SELECT `content` FROM `%smod_wysiwyg` WHERE `section_id`= '%d'",
    CAT_TABLE_PREFIX, $section_id
));
if( $result && $result->numRows() > 0 )
{
    $data    = $result->fetchRow(MYSQL_ASSOC);
    $content = htmlspecialchars($data['content']);
}
else
{
    $content = '';
}

if(!isset($wysiwyg_editor_loaded))
{
	$wysiwyg_editor_loaded = true;
    $config = array('width'=>'100%','height'=>'250px');
    // get settings
    $result = CAT_Helper_Page::getInstance()->db()->query(sprintf(
        "SELECT * from `%smod_wysiwyg_admin_v2` where `editor`='%s' AND (`set_name`='width' OR `set_name`='height')",
        CAT_TABLE_PREFIX, WYSIWYG_EDITOR
    ));
    if($result->numRows())
    {
        while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
        {
            $config[$row['set_name']] = $row['set_value'];
        }
    }
	if (!defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR=="none" || !file_exists(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php'))
    {
		function show_wysiwyg_editor( $name, $id, $content, $width = '100%', $height = '250px', $print = true)
        {
			$editor = '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
            if($print)
                echo $editor;
            else
                return $editor;
		}
	}
    else
    {
		$id_list       = array();
		$result  = CAT_Helper_Page::getInstance()->db()->query(sprintf(
              "SELECT `section_id` FROM `%ssections` "
            . "WHERE `page_id`= '%d' AND `module`= 'wysiwyg' "
            . "ORDER BY position",
            CAT_TABLE_PREFIX, $page_id
        ));
		if ( $result->numRows() > 0)
        {
			while( !false == ($wysiwyg_section = $result->fetchRow(MYSQL_ASSOC) ) )
            {
				$temp_id   = abs(intval($wysiwyg_section['section_id']));
				$id_list[] = 'content'.$temp_id;
			}
			require_once( CAT_PATH."/modules/wysiwyg/classes/pathfinder.php");
			$wb_path_info = new c_pathfinder($database);
			require_once(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
		}
	}
}

if (isset($preview) && $preview == true) return false;

$parser->setPath(dirname(__FILE__).'/templates/default');

$parser->output(
    'modify',
    array(
        'section_id' => $section_id,
        'page_id'    => $page_id,
        'action'     => CAT_URL.'/modules/wysiwyg/save.php',
        'WYSIWYG'    => show_wysiwyg_editor('content'.$section_id,'content'.$section_id,$content,$config['width'],$config['height'],false)
    )
);