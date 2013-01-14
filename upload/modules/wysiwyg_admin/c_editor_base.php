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
 *   @author          LEPTON v2.0 Black Cat Edition Development
 *   @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 *   @link            http://www.lepton2.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        LEPTON2BCE_Modules
 *   @package         ckeditor4
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
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
// end include class.secure.php

if ( !defined('WB_PATH')) die(header('Location: ../../index.php'));

$debug = false;
if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

abstract class c_editor_base
{
    abstract public function getSkinPath();
    abstract public function getToolbars();

    public function getHeight(&$config)
    {
        foreach($config as $item)
        {
            if($item['set_name']=='height') return $item['set_value'];
        }
        return '250px';
    }

    public function getWidth(&$config)
    {
        foreach($config as $item)
        {
            if($item['set_name']=='width') return $item['set_value'];
        }
        return '100%';
    }

    public function getSkins($skin_path)
    {
        global $admin;
        $admin->get_helper('Directory')->setRecursion(false);
        $skins = $admin->get_helper('Directory')->getDirectories($skin_path,$skin_path.'/');
        $admin->get_helper('Directory')->setRecursion(true);
        return $skins;
    }
}

?>