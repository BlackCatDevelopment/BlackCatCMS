<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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



require_once (WB_PATH.'/framework/class.admin.php');
// create Admin object with admin header
// check user permissions for admintools (redirect users with wrong permissions)
$admin = new admin('Admintools', 'admintools', true);
$msg = array();
$error_msg = array();
$backlink = 'index.php?advanced=yes';
if ($admin->get_permission('admintools') == true)
{

/*'reload_all', not yet*/
    $post_check = array('reload_modules', 'reload_templates', 'reload_languages');

    /**
    * check if there is anything to do
    */
    foreach ($post_check as $index => $key)
    {
        if (!isset ($_POST[$key]) && !isset ($_POST['reload_all']))
        {
            unset ($post_check[$index]);
        }
    }
    if (count($post_check) == 0)
    {
        $error_msg[] = '<span class="normal bold red">'.$MESSAGE['GENERIC_PLEASE_CHECK_BACK_SOON'].'</span>';
    }
    else
    {

    /**
    * check if user has permissions to access this file
    */
    // include WB configuration file and WB admin class
    // check if the referer URL if available
        $referer = isset ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : (isset ($HTTP_SERVER_VARS['HTTP_REFERER']) ? $HTTP_SERVER_VARS['HTTP_REFERER'] : '');
        // if referer is set, check if script was invoked from "admin/modules/index.php"
        $required_url = ADMIN_URL.'/addons/index.php';
        if ($referer != '' && (!(strpos($referer, $required_url) !== false || strpos($referer, $required_url) !== false)))
        {
        // die( header('Location: ../../index.php'));
            $error_msg[] = '<span class="normal bold red">'.$MESSAGE['GENERIC_BAD_PERMISSIONS'].'</span>';
        }
        else
        {
        // include WB functions file
            require_once (WB_PATH.'/framework/functions.php');
            // load WB language file
            require_once (WB_PATH.'/languages/'.LANGUAGE.'.php');

            /**
            * Reload all specified Addons
            */
            $table = TABLE_PREFIX.'addons';
            foreach ($post_check as $key)
            {
                switch ($key) :
                    case 'reload_all' :
                        //            //delete addons - table
                        //            $database->query('DELETE FROM `'.TABLE_PREFIX.'addons`');
                        //            // reset auto_increment to 1 , because in MySQL 3.23 TRUNCATE works just like DELETE
                        //            $database->query('ALTER TABLE `'.TABLE_PREFIX.'addons` AUTO_INCREMENT = 1');
                        //            break;
                        //
                    case 'reload_modules' :
                        // first remove addons entrys for module that don't exists
                        $sql = 'SELECT `directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = \'module\' ';
                        if (($res_addons = $database->query($sql)))
                        {
                            while (($value = $res_addons->fetchRow()))
                            {
								if(file_exists(WB_PATH.'/modules/'.$value['directory']))
								{
									continue;
                                }
                                $sql = 'SELECT `section_id`,`module`, `page_id` FROM `'.TABLE_PREFIX.'sections` ';
                                $sql .= 'WHERE `module` = \''.$value['directory'].'\' LIMIT 1 ';
                                if (($info = $database->query($sql)) && ($info->numRows() > 0))
                                {
                                /**
                                *	Modul is in use, so we have to warn the user
                                */
                                    while ($data = $info->fetchRow())
                                    {
                                        $sql = 'SELECT `menu_title` FROM `'.TABLE_PREFIX.'pages` ';
                                        $sql .= 'WHERE `page_id` = '.$data['page_id'].' ';
                                        $temp_info = $database->get_one($sql);
	 									$page_url = '<span class="normal bold"> -> <a href="'.ADMIN_URL.'/pages/sections.php?page_id='.$data['page_id'].'">'.$temp_info.'</a> ('.$TEXT['PAGE'].' '.$data['page_id'].')</span>';
                                        $error_msg[] = '<span class="normal bold">'.$data['module'].'</span> <span class="normal bold red">'.$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE'].'</span>'.$page_url;
                                    }
                                }
                                else
                                {
                                // loop through all installed modules
                                    $directory = WB_PATH.'/modules/'.$value['directory'];
                                    if (!is_dir($directory) && !file_exists($directory.'/info.php'))
                                    {
                                        $sql = 'DELETE FROM `'.TABLE_PREFIX.'addons` ';
                                        $sql .= 'WHERE type = \'module\' AND directory = \''.$value['directory'].'\' ';
                                        if ($database->query($sql))
                                        {
                                            $tables = array();
                                            $sql = 'SHOW TABLES LIKE \''.TABLE_PREFIX.'mod_'.$value['directory'].'%\'';
                                            if (($res = $database->query($sql)) && ($res->numRows() > 0))
                                            {
                                                while ($tables = $res->fetchRow())
                                                {
                                                    $sql = 'DROP TABLES `'.$tables['0'].'` ';
                                                    if ($database->query($sql))
                                                    {
                                                        $msg[] = '<span class="normal bold">'.$tables['0'].' '.$MESSAGE['GENERIC_UNINSTALLED'].'</span>';
                                                    }
                                                    else
                                                    {
                                                        $error_msg[] = '<span class="normal bold red">'.$tables['0'].' '.$MESSAGE['RECORD_MODIFIED_FAILED'].'</span>';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $error_msg[] = '<span class="normal bold red">'.$value['directory'].' '.$MESSAGE['RECORD_MODIFIED_FAILED'].'</span> ';
                                    }
                                }
                            }
                        }
                        // now check modules folder with entries in addons
                        $modules = scan_current_dir(WB_PATH.'/modules');
                        if (sizeof($modules['path']) > 0)
                        {
                            foreach ($modules['path'] as $value)
                            {
                                $code_version = get_modul_version($value);
                                $db_version = get_modul_version($value, false);
                                if (($db_version != null) && ($code_version != null))
                                {
                                    if (version_compare($db_version, $code_version, '!='))
                                    {
                                        $error_msg[] = '<span class="normal bold red">'.$value.' ( '.$db_version.' :: '.$code_version.' ) '.$MESSAGE['GENERIC_MODULE_VERSION_ERROR'].'</span> ';
                                        continue;
                                    }
                                    else
                                    {
                                    	require(WB_PATH.'/modules/'.$value."/info.php");
                                        load_module(WB_PATH.'/modules/'.$value);
                                        $msg[] = '<span class="normal bold green">'.$value.' :: '.$MESSAGE['ADDON_MODULES_RELOADED'].'</span>';
                                    }
                                }
                                else
                                {

                                /* not found */
                                }
                            }
                        }
                        else
                        {
                            $error_msg[] = '<span class="normal bold red">'.$MESSAGE['ADDON_ERROR_RELOAD'].'</span>';
                        }
                        break;

                    case 'reload_templates' :
                        if ($handle = opendir(WB_PATH.'/templates'))
                        {
                        // delete templates from database
                            $sql = 'DELETE FROM  `'.TABLE_PREFIX.'addons`  WHERE `type` = \'template\'';
                            $database->query($sql);
                            // loop over all templates
                            while (false !== ($file = readdir($handle)))
                            {
                                if ($file != '' && substr($file, 0, 1) != '.' && $file != 'index.php')
                                {
                                	require(WB_PATH.'/templates/'.$file."/info.php");
                                    load_template(WB_PATH.'/templates/'.$file);
                                }
                            }
                            closedir($handle);
                            // add success message
                            $msg[] = '<span class="normal bold green">'.$MESSAGE['ADDON_TEMPLATES_RELOADED'].'</span>';
                        }
                        else
                        {
                        // provide error message and stop
                            $error_msg[] = '<span class="normal bold red">'.$MESSAGE['ADDON_ERROR_RELOAD'].'</span> ';
                        }
                        break;

                    case 'reload_languages' :
                        if ($handle = opendir(WB_PATH.'/languages/'))
                        {
                        // delete languages from database
                            $sql = 'DELETE FROM  `'.TABLE_PREFIX.'addons`  WHERE `type` = \'language\'';
                            $database->query($sql);
                            // loop over all languages
                            while (false !== ($file = readdir($handle)))
                            {
                                if ($file != '' && substr($file, 0, 1) != '.' && $file != 'index.php')
                                {
                                    load_language(WB_PATH.'/languages/'.$file);
                                }
                            }
                            closedir($handle);
                            // add success message
                            $msg[] = '<span class="normal bold green">'.$MESSAGE['ADDON_LANGUAGES_RELOADED'].'</span>';
                        }
                        else
                        {
                        // provide error message and stop
                            $error_msg[] = '<span class="normal bold red">'.$MESSAGE['ADDON_ERROR_RELOAD'].'</span>';
                        }
                        break;
                    endswitch;
            }
        }
    }
}
else
{
    $error_msg[] = '<span class="big bold red">'.$MESSAGE['ADMIN_INSUFFICIENT_PRIVILEGES'].'</span> ';
}
//
if (sizeof($error_msg) > 0)
{
    $error_msg = array_merge($error_msg, $msg);
    $admin->print_error( implode($error_msg, '<br />'), $backlink );
}
else
{
// output success message
    $admin->print_success( implode($msg, '<br />'), $backlink );
}
// $admin->print_footer();

?>