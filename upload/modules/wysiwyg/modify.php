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

/**
 *	Get content
 */
$result = CAT_Helper_Page::getInstance()->db()->query(
    "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`= :section_id",
    array('section_id'=>$section_id)
);
if( $result && $result->rowCount() > 0 )
{
    $data    = $result->fetch();
    $content = htmlspecialchars(stripslashes($data['content']));
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
    $result = CAT_Helper_Page::getInstance()->db()->query(
        "SELECT * from `:prefix:mod_wysiwyg_admin_v2` where `editor`=:name AND (`set_name`='width' OR `set_name`='height')",
        array('name'=>WYSIWYG_EDITOR)
    );
    if($result->rowCount())
    {
        while( false !== ( $row = $result->fetch() ) )
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
		$result  = CAT_Helper_Page::getInstance()->db()->query(
              "SELECT `section_id` FROM `:prefix:sections` "
            . "WHERE `page_id`= :page_id AND `module`= 'wysiwyg' "
            . "ORDER BY position",
            array('page_id'=>$page_id)
        );
		if ( $result->rowCount() > 0)
        {
			while( !false == ($wysiwyg_section = $result->fetch() ) )
            {
				$temp_id   = abs(intval($wysiwyg_section['section_id']));
				$id_list[] = 'content'.$temp_id;
			}
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