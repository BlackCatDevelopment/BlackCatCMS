<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          Dwoo Template Engine
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.lepton-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

require_once WB_PATH.'/framework/functions.php';

function Dwoo_Plugin_show_menu(Dwoo $dwoo, $aMenu=0, $aStart=SM2_ROOT, $aMaxLevel=-1999, $aOptions=SM2_TRIM) {
	if (strpos($aOptions, 'SM2_PRETTY') !== false) return "<b>Error:</b> show_menu() does not support the SM2_PRETTY Flag!";
	ob_start();
	show_menu2($aMenu, $aStart, $aMaxLevel, $aOptions);
	$result = ob_get_contents();
	ob_end_clean();
	$search 	= array('</li><li',  '</a><ul',  '</li></ul>',  '</ul></li>',  '</a></li>');
	$replace	= array('</li>|<li', '</a>|<ul', '</li>|</ul>', '</ul>|</li>', '</a>|</li>');
	$result = str_replace($search, $replace, $result); 
	$walk = explode('|', $result); 
	$menu = array();
	$level = 0;
	$i = 0;
	foreach ($walk as $item) {
		trim($item);
		$i++;
		if ($item == '</li>') {
			$menu[] = array('type' => 'link_end', 'level' => $level);
			continue;
		}
		if ($item == '</ul>') {
			$menu[] = array('type' => 'level_end', 'level' => $level);
			$level--;
			continue;
		}
		if (strpos($item, '<ul') !== false) {
			$ul = substr($item, 0, strpos($item, '<li'));
			$level++;
			$link = array();
			$link['type'] = 'level_start';
			$link['level'] = $level;
			
			preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $ul, $attr);
			foreach ($attr as $attributes) {
				foreach ($attributes as $attribut) {
					if (strpos($attribut, "=") !== false) {
						list($key, $value) = explode("=", $attribut);
						$value = trim($value);
						$value = trim(substr($value, 1, strlen($value)-2));
						if (!empty($value)) $link[$key] = $value;
					}
				}
			}	
			
			$menu[] = $link;
			$item = trim(substr($item, strpos($item, '<li')));
		}
		if (strpos($item, '<li') !== false) {
			$link = array();
			$link['type'] = 'link_start';
			$link['level'] = $level;
			preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $item, $attr);
			foreach ($attr as $attributes) {
				foreach ($attributes as $attribut) {
					if (strpos($attribut, "=") !== false) {
						list($key, $value) = explode("=", $attribut);
						$value = trim($value);
						$value = trim(substr($value, 1, strlen($value)-2));
						$link[$key] = $value;
					}
				}
			}	
			$menu[] = $link;
		}
	}
	return $menu;
} // Dwoo_Plugin_show_menu()

?>