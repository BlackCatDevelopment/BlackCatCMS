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
 *   @copyright       2015, Black Cat Development
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

function wysiwyg_search($func_vars)
{
	extract($func_vars, EXTR_PREFIX_ALL, 'func');
	
	// how many lines of excerpt we want to have at most
	$max_excerpt_num = $func_default_max_excerpt;
	$divider         = ".";
	$result          = false;
	
	// we have to get 'content' instead of 'text', because strip_tags()
    // doesn't remove scripting well.
	// scripting will be removed later on automatically
	$query = $func_database->query(
        "SELECT content FROM `:prefix:mod_wysiwyg` WHERE section_id=:id",
        array('id'=>$func_section_id)
	);

	if($query->numRows() > 0)
    {
		if($res = $query->fetch(PDO::MYSQL_ASSOC))
        {
            if(CAT_Helper_Addons::isModuleInstalled('kit_framework'))
            {
                // remove all kitCommands from the content
                preg_match_all('/(~~)( |&nbsp;)(.){3,512}( |&nbsp;)(~~)/', $res['content'], $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $res['content'] = str_replace($match[0], '', $res['content']);
                }
            }
			$mod_vars = array(
				'page_link'          => $func_page_link,
				'page_link_target'   => SEC_ANCHOR."#section_$func_section_id",
				'page_title'         => $func_page_title,
				'page_description'   => $func_page_description,
				'page_modified_when' => $func_page_modified_when,
				'page_modified_by'   => $func_page_modified_by,
				'text'               => $res['content'].$divider,
				'max_excerpt_num'    => $max_excerpt_num
			);
			if(print_excerpt2($mod_vars, $func_vars)) {
				$result = true;
			}
		}
	}
	return $result;
}