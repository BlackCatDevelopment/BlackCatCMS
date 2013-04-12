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

/**
 *	Get page content
 *
 */
$query       = "SELECT `content` FROM `".CAT_TABLE_PREFIX."mod_wysiwyg` WHERE `section_id`= '".$section_id."'";
$get_content = $database->query($query);
$data        = $get_content->fetchRow( MYSQL_ASSOC );
$content     = htmlspecialchars($data['content']);

if(!isset($wysiwyg_editor_loaded))
{
	$wysiwyg_editor_loaded=true;
    // get settings
    $query  = "SELECT * from `".CAT_TABLE_PREFIX."mod_wysiwyg_admin_v2` where `editor`='".WYSIWYG_EDITOR."' AND (`set_name`='width' OR `set_name`='height')";
    $result = $database->query($query);
    $config = array('width'=>'100%','height'=>'250px');
    if($result->numRows())
    {
        while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
        {
            $config[$row['set_name']] = $row['set_value'];
        }
    }
	if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php'))
    {
		function show_wysiwyg_editor( $name,$id,$content,$width,$height)
        {
			echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
		}
	}
    else
    {
		$id_list       = array();
		$query_wysiwyg = $database->query(
              "SELECT `section_id` FROM `".CAT_TABLE_PREFIX."sections` "
            . "WHERE `page_id`= '".$page_id."' AND `module`= 'wysiwyg' "
            . "ORDER BY position"
        );
		if ( $query_wysiwyg->numRows() > 0)
        {
			while( !false == ($wysiwyg_section = $query_wysiwyg->fetchRow( MYSQL_ASSOC ) ) )
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
    'modify.tpl',
    array(
        'section_id' => $section_id,
        'page_id'    => $page_id,
        'action'     => CAT_URL.'/modules/wysiwyg/save.php',
        'WYSIWYG'    => show_wysiwyg_editor('content'.$section_id,'content'.$section_id,$content,$config['width'],$config['height'])
    )
);