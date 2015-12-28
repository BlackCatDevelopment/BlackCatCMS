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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         wysiwyg_admin
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

$debug = false;
if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

if(!class_exists('c_editor_base',false))
{
    abstract class c_editor_base
    {

        private $default_skin    = NULL;
        private $default_toolbar = NULL;
        private $default_height  = '250px';
        private $default_width   = '100%';

        abstract public function getFilemanagerPath();
        abstract public function getSkinPath();
        abstract public function getPluginsPath();
        abstract public function getToolbars();
        abstract public function getAdditionalSettings();
        abstract public function getAdditionalPlugins();
        #abstract public function getFrontendCSS();

        private function get($name,&$config)
        {
            if(isset($config[$name]))
            {
                $val = $config[$name];
                return $val;
            }
        }

        public function getFilemanager()
        {
            $fm_path = $this->getFilemanagerPath();
            $d  = CAT_Helper_Directory::getInstance(1);
            $fm = $d->maxRecursionDepth(1)->findFiles('info.php',$fm_path,$fm_path.'/');
            $r  = array();
            $d->maxRecursionDepth();
            if ( is_array($fm) && count($fm) )
            {
                foreach( $fm as $file )
                {
                    $filemanager_name = $filemanager_dirname = $filemanager_version = $filemanager_sourceurl = $filemanager_registerfiles = $filemanager_include = NULL;
                    @include $fm_path.$file;
                    $r[$filemanager_dirname] = array(
                        'name' => $filemanager_name,
                        'version' => $filemanager_version,
                        'url' => $filemanager_sourceurl,
                        'inc' => $filemanager_include,
                        'dir' => $filemanager_dirname,
                    );
                }
            }
            return $r;
        }

        public function getHeight(&$config)
        {
            $val = $this->get('height',$config);
            return ( $val != '' ) ? $val : $this->default_height;
        }

        public function getWidth(&$config)
        {
            $val = $this->get('width',$config);
            return ( $val != '' ) ? $val : $this->default_width;
        }

        public function getSkin(&$config)
        {
            $val = $this->get('skin',$config);
            return ( $val != '' ) ? $val : $this->default_skin;
        }

        public function getToolbar(&$config)
        {
            $val = $this->get('toolbar',$config);
            return ( $val != '' ) ? $val : $this->default_toolbar;
        }

        public function getSkins($skin_path)
        {
            $d = CAT_Helper_Directory::getInstance();
            $d->setRecursion(false);
            $skins = $d->getDirectories($skin_path,$skin_path.'/');
            $d->setRecursion(true);
            return $skins;
        }
    }
}