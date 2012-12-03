<?php

 /**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 */

// THIS IS A TEMPORARY AND SMALL SOLUTION!
// @todo integration of IP check and check of requested params!

if (! defined ( 'LEPTON_PATH' ))
{
	if (! defined( 'LEPTON_INSTALL' ) )
	{
		// try to load config.php
		if (strpos(__FILE__, '/framework/class.secure.php') !== false)
		{
			$config_path = str_replace('/framework/class.secure.php', '', __FILE__);
		}
		else
		{
			$config_path = str_replace('\framework\class.secure.php', '', __FILE__);	
		}
		if (!file_exists($config_path.'/config.php'))
		{
			if (file_exists($config_path.'/install/index.php'))
			{
				header("Location: ../install/index.php");
				exit();
			}
			else
			{
				// Problem: no config.php nor installation files...
				exit('<p><b>Sorry, but this installation seems to be damaged! Please contact your webmaster!</b></p>');
			}
		}
		require_once($config_path.'/config.php');
		
		$admin_dir = str_replace(LEPTON_PATH, '', ADMIN_PATH);
		
		// some core files must be allowed to load the config.php by themself!
		$direct_access_allowed = array(
			PAGES_DIRECTORY.'/index.php',

			// Dwoo
			$admin_dir.'/addons/index.php',
			$admin_dir.'/addons/manual_install.php',
			$admin_dir.'/addons/install.php',
			$admin_dir.'/addons/uninstall.php',
			//////////////////////////////////////////


			// phplib ////////////////////////////////
			$admin_dir.'/access/index.php',
			$admin_dir.'/addons/index.php',
			$admin_dir.'/addons/reload.php',
			//////////////////////////////////////////

			$admin_dir.'/admintools/index.php',
			$admin_dir.'/admintools/tool.php',
			$admin_dir.'/groups/add.php',

			// Dwoo
			$admin_dir.'/groups/delete.php',
			//////////////////////////////////////////

			// phplib
			$admin_dir.'/groups/groups.php',
			//////////////////////////////////////////

			$admin_dir.'/groups/index.php',
			$admin_dir.'/groups/save.php',

			// phplib
			$admin_dir.'/languages/details.php',
			$admin_dir.'/languages/index.php',
			$admin_dir.'/languages/install.php',
			$admin_dir.'/languages/uninstall.php',
			$admin_dir.'/login/index.php',
			//////////////////////////////////////////

			// Dwoo
			$admin_dir.'/login/index_ajax.php',
			//////////////////////////////////////////

			$admin_dir.'/login/forgot/index.php',
			$admin_dir.'/logout/index.php',

			// phplib
			$admin_dir.'/media/thumb.php',
			//////////////////////////////////////////

			// Dwoo
			$admin_dir.'/media/create_folder.php',
			$admin_dir.'/media/delete.php',
			$admin_dir.'/media/get_contents.php',
			$admin_dir.'/media/rename.php',
			$admin_dir.'/media/upload.php',

			$admin_dir.'/media/ajax_get_contents.php',
			$admin_dir.'/media/ajax_delete.php',
			$admin_dir.'/media/ajax_create_folder.php',
			$admin_dir.'/media/ajax_rename.php',
			//////////////////////////////////////////

			// phplib
			$admin_dir.'/modules/details.php',
			$admin_dir.'/modules/index.php',
			$admin_dir.'/modules/install.php',
			$admin_dir.'/modules/manual_install.php',
			$admin_dir.'/modules/uninstall.php',
			$admin_dir.'/modules/save_permissions.php',
			//////////////////////////////////////////

			$admin_dir.'/pages/add.php',
			$admin_dir.'/pages/delete.php',
			$admin_dir.'/pages/empty_trash.php',
			$admin_dir.'/pages/index.php',

			// Dwoo
			$admin_dir.'/pages/lang_settings.php',
 	 		$admin_dir.'/pages/lang_settings_save.php',
			//////////////////////////////////////////

			// phplib
			$admin_dir.'/pages/move_down.php',
			$admin_dir.'/pages/move_up.php',
			//////////////////////////////////////////

			// Dwoo
			$admin_dir.'/pages/ajax_page_settings.php',
			$admin_dir.'/pages/get_page_tree.php',
			$admin_dir.'/pages/reorder.php',
			//////////////////////////////////////////

			$admin_dir.'/pages/intro.php',

			// phplib
			$admin_dir.'/pages/intro2.php',
			//////////////////////////////////////////

			// Dwoo
			$admin_dir.'/pages/intro_save.php',
			//////////////////////////////////////////

			$admin_dir.'/pages/modify.php',
			$admin_dir.'/pages/restore.php',
			$admin_dir.'/pages/save.php',

			// Dwoo
			$admin_dir.'/pages/sections_save.php',
			//////////////////////////////////////////

			// phplib
			$admin_dir.'/pages/sections.php',
			//////////////////////////////////////////

			$admin_dir.'/pages/settings.php',

			// phplib
			$admin_dir.'/pages/settings2.php',
			//////////////////////////////////////////

			// Dwoo
			$admin_dir.'/pages/settings_save.php',
			$admin_dir.'/pages/lang_settings.php',
			$admin_dir.'/pages/lang_settings_save.php',
			//////////////////////////////////////////

			$admin_dir.'/pages/trash.php',
			$admin_dir.'/preferences/save.php',
			$admin_dir.'/profiles/index.php',
			$admin_dir.'/settings/ajax_testmail.php',
			$admin_dir.'/settings/index.php',
			$admin_dir.'/settings/save.php',
			$admin_dir.'/start/index.php',

			// phplib
			$admin_dir.'/templates/details.php',
			$admin_dir.'/templates/index.php',
			$admin_dir.'/templates/install.php',
			$admin_dir.'/templates/uninstall.php',
			//////////////////////////////////////////

			$admin_dir.'/users/add.php',
			$admin_dir.'/users/index.php',
			$admin_dir.'/users/save.php',

			// Dwoo
			$admin_dir.'/users/delete.php',
			//////////////////////////////////////////

			// phplib
			$admin_dir.'/users/users.php',
			//////////////////////////////////////////

			'/account/forgot.php',
			'/account/login.php',
			'/account/logout.php',
			'/account/preferences.php',
			'/account/signup.php',
			'/include/captcha/captchas/calc_image.php',
			'/include/captcha/captchas/calc_ttf_image.php',
			'/include/captcha/captchas/old_image.php',
			'/include/captcha/captchas/ttf_image.php',
			'/include/captcha/captcha.php',
			'/modules/edit_modules_files.php',
			'/modules/edit_module_files.php',
			'/modules/code2/save.php',
			'/modules/news/add_group.php',
			'/modules/news/modify_group.php',
			'/modules/news/save_group.php',
			'/modules/news/save_settings.php',
			'/modules/news/delete_group.php',
			'/modules/news/modify_post.php',
			'/modules/news/move_up.php',
			'/modules/news/move_down.php',
			'/modules/news/save_post.php',
			'/modules/news/delete_post.php',
			'/modules/news/comment.php',
			'/modules/news/submit_comment.php',
			'/modules/news/modify_comment.php',
			'/modules/news/save_comment.php',
			'/modules/news/delete_comment.php',
			'/modules/news/add_post.php',
			'/modules/news/modify_settings.php',
			'/modules/news/rss.php',
			'/modules/wysiwyg/save.php',
			'/modules/form/modify_settings.php',
			'/modules/form/save_settings.php',
			'/modules/form/modify_field.php',
			'/modules/form/move_up.php',
			'/modules/form/move_down.php',
			'/modules/form/save_field.php',
			'/modules/form/add_field.php',
			'/modules/form/delete_field.php',
			'/modules/form/delete_submission.php',
			'/modules/form/view_submission.php',
			'/modules/menu_link/save.php',
			'/modules/wrapper/save.php',
			'/modules/jsadmin/move_to.php',
			'/search/index.php'
		);

		$allowed = false;
		foreach ($direct_access_allowed as $allowed_file)
		{
			if (strpos($_SERVER['SCRIPT_NAME'], $allowed_file) !== false)
			{
				$allowed = true; 
				break;
			}
		}
		if (!$allowed)
		{
			if (((strpos($_SERVER['SCRIPT_NAME'], $admin_dir.'/media/index.php')) !== false) ||
					((strpos($_SERVER['SCRIPT_NAME'], $admin_dir.'/preferences/index.php')) !== false) ||
					((strpos($_SERVER['SCRIPT_NAME'], $admin_dir.'/support/index.php')) !== false))
			{
				// special: do absolute nothing!
			}
			elseif ((strpos($_SERVER['SCRIPT_NAME'], $admin_dir.'/index.php') !== false) ||
					(strpos($_SERVER['SCRIPT_NAME'], $admin_dir.'/interface/index.php') !== false))
			{
				// special: call start page of admins directory
				header("Location: ".ADMIN_URL.'/start/index.php');
				exit();
			}
			elseif (strpos($_SERVER['SCRIPT_NAME'], '/index.php') !== false)
			{
				// call the main page
				header("Location: ../index.php");
				exit();
			}
			else
			{
				if (!headers_sent())
				{
					// set header to 403
					header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
				}
				// stop program execution
				exit('<p><b>ACCESS DENIED!</b> - Invalid call of <i>'.$_SERVER ['SCRIPT_NAME'].'</i></p>');
			}
		}
	}
}

/**
 * strip droplets
 **/
if( ! function_exists('__lep_sec_formdata') )
{
	function __lep_sec_formdata(&$arr)
	{
		foreach( $arr as $key => $value )
		{
			if ( is_array( $value ) )
			{
				__lep_sec_formdata($value);
			}
			else
			{
				// remove <script> tags
				$value     = str_replace( array( '<script', '</script' ), array( '&lt;script', '&lt;/script' ), $value );
				$value     = preg_replace( '#(\&lt;script.+?)>#i', '$1&gt;', $value );
				$value     = preg_replace( '#(\&lt;\/script)>#i', '$1&gt;', $value );
				//$arr[$key] = preg_replace( '#\[\[.+?\]\]#', '', __strip($value) );
				$arr[$key] = str_replace( array( '[', ']' ), array( '&#91;', '&#93;' ), $value );
			}
		}
	}
}

// secure form input
if ( isset($_SESSION) && ! defined('LEP_SEC_FORMDATA') && ! isset( $_SESSION['USER_ID'] ) )
{
	if ( count($_GET) )
	{
		__lep_sec_formdata($_GET);
	}
	if ( count($_POST) )
	{
		__lep_sec_formdata($_POST);
	}
	if ( count($_REQUEST) )
	{
		__lep_sec_formdata($_REQUEST);
	}
	define('LEP_SEC_FORMDATA',true);
}

?>