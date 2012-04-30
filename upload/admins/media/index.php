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
  
  function get_include($IncludeFile)
  {
      global $MESSAGE, $admin;
      $retvalue = '';
      if (file_exists($IncludeFile))
      {
          $retvalue = $IncludeFile;
      }
      else
      {
          $IncludeFile = basename($IncludeFile);
          $admin->print_error($IncludeFile . '<br />' . $MESSAGE['MEDIA_DIR_ACCESS_DENIED']);
      }
      return $retvalue;
  }
  
  // put all inside a function to prevent global vars
  function build_page(&$admin, &$database)
  {
      global $HEADING, $TEXT, $MENU, $MESSAGE;
      // Include the WB functions file
      
      include_once(get_include(WB_PATH . '/framework/functions.php'));
      include_once(get_include(ADMIN_PATH . '/media/function.inc.php'));
      
      $memory_limit = ini_get('memory_limit');
      $post_max_size = ini_get('post_max_size');
      $upload_max_filesize = ini_get('upload_max_filesize');
      
      $maxUploadFiles = 12;
      
      $request = $_SERVER['REQUEST_METHOD'];
      $allowed_img_types = 'jpg|png|gif|tif';
      
      $actions = isset($_POST['action']) ? trim($admin->strip_slashes($admin->get_post('action'))) : 'show';
      $actions = isset($_POST['media_reload']) && ($_POST['media_reload'] == true) ? 'media_reload' : $actions;
      
      $actions = isset($_POST['cancel']) ? 'show' : $actions;
      
      // Get home folder not to show
      $home_folders = get_home_folders();
      // $dirs = directory_list(WB_PATH.MEDIA_DIRECTORY);
      $currentHome = $admin->get_home_folder();
      $pathsettings = get_media_settings();
      
      // Get the user specified dir  parent_path
      if (($request == 'GET') && isset($_REQUEST))
      {
          $directory = rawurldecode(trim($admin->strip_slashes($admin->get_get('dir'))));
      }
      elseif (isset($_POST['current_select']))
      {
          $directory = str_replace(MEDIA_DIRECTORY, '', rawurldecode(trim($admin->strip_slashes($admin->get_post('current_select')))));
      }
      elseif (isset($_POST['current_dir']))
      {
          $directory = rawurldecode(trim($admin->strip_slashes($admin->get_post('current_dir'))));
      }
      
      //$directory = is_null($directory) ? $currentHome : $directory;
      // $directory is not always null ... 8-/
      $directory = (is_null($directory) || empty($directory)) ? $currentHome : $directory;
      
      $directory = ($directory == '/' || $directory == '\\') ? '' : $directory;
      $target = $current_dir = $directory;
      $backlink = 'index.php?dir=' . $directory;
      
      $FILE = array();
      
      $dirs = directory_list(WB_PATH . MEDIA_DIRECTORY);
      array_walk($dirs, 'remove_path', WB_PATH);
      
      // dirs with readWrite access
      $dirs_rw = media_dirs_rw($admin);
      array_walk($dirs_rw, 'remove_path', WB_PATH);
      if ($admin->get_user_id() == 1)
      {
          $id = array_unshift($dirs_rw, MEDIA_DIRECTORY);
      }
      
      // Define absolute path to WB media directory (using Unix path seperator)
      $mediaPath = str_replace('\\', '/', WB_PATH . MEDIA_DIRECTORY);
      
      /* comment out to show only Home Folder  till yet not build in overall
       $acess_denied = (($currentHome != '') && (strpos($mediaPath.$directory, $currentHome))) ? false : true;
       */
      
      // sytem_admin if not superadmin, no homefolder, groupmember 1
      $system_admin = ($admin->ami_group_member('1') == true) || ($admin->get_user_id() == 1);
      $group_admin = (empty($currentHome) == true) && ($admin->ami_group_member('1') == true);
      
      //$full_home_folder_access = $directory == '' || in_array(MEDIA_DIRECTORY.$directory, $dirs_rw) || $group_admin ;
      /*
       * If HOME_FOLDERS are not active the user have access to all media files,
       * otherwise check if the shown folders in list are within the personal folder
       * and grant desired rights only for this folders (upload, create directory a.s.o.)
       */
      $full_home_folder_access = (!HOME_FOLDERS) ? true : (empty($_SESSION['HOME_FOLDER']) || in_array(MEDIA_DIRECTORY . $directory, $dirs_rw) || $group_admin);
      
      if (strstr($current_dir, '..'))
      {
          // target_path contains ../
          $admin->print_error($MESSAGE['MEDIA_TARGET_DOT_DOT_SLASH'], $backlink);
      }
      
      // Build canonicalized absolute path from user input and check if path exists (False if not)
      $userPath = str_replace('\\', '/', realpath($mediaPath . $directory));
      // Ensure that the user specified path is located inside WB media folder
      if ($userPath == false || (strpos($userPath, $mediaPath) !== 0))
      {
          // User defined path is invalid or is located outside the WB media directory
          $admin->print_error($MESSAGE['MEDIA_DIR_ACCESS_DENIED'], $backlink);
      }
      
      if (!is_writeable($mediaPath . $directory))
      {
          $admin->print_error($MESSAGE['GENERIC_BAD_PERMISSIONS'], $backlink);
      }
      
      $tpl = new Template(THEME_PATH . '/templates', 'keep');
      // false | true
      $tpl->debug = false;
      
      $file_array = array('page' => 'media.htt', 'browse' => 'media_browse.htt', 'rename' => 'media_rename.htt', 'settings' => 'setparameter.htt');
      
      $tpl->set_file($file_array);
      
      $tpl->set_block('page', 'main_block', 'main');
      
      // BEGIN left side always with main_block and the dropdown list may later as dirtree
      // First insert language text and messages
      $tpl->set_var(array('TEXT_RELOAD' => $TEXT['RELOAD'], 'TEXT_TARGET_FOLDER' => $TEXT['TARGET_FOLDER'], 'TEXT_CREATE_FOLDER' => $TEXT['CREATE_FOLDER'], 'TEXT_NAME' => $TEXT['TITLE'], 'TEXT_UPLOAD_FILES' => $TEXT['UPLOAD_FILES'], 'TEXT_UNZIP_FILE' => $TEXT['UNZIP_FILE'], 'TEXT_DELETE_ZIP' => $TEXT['DELETE_ZIP'], 'TEXT_OVERWRITE_EXISTING' => $TEXT['OVERWRITE_EXISTING'], 'TEXT_FILES' => $TEXT['FILES']));
      
      $tpl->set_var(array('USER_ID' => $admin->is_authenticated() ? $admin->get_user_id() : '', 'ADMIN_URL' => ADMIN_URL, 'WB_URL' => WB_URL, 'WB_PATH' => WB_PATH, 'THEME_URL' => THEME_URL));
      //  && (($admin->ami_group_member('1') != true) || ($admin->get_user_id() != 1))
      // set optionen media_settings_block
      $tpl->set_block('main_block', 'media_settings_block', 'media_settings');
      
      // Only show admin the settings link
      if (($pathsettings['global']['admin_only'] == true))
      {
          if ($system_admin != true)
          {
              $tpl->set_var('DISPLAY_SETTINGS', 'hide');
              $tpl->set_block('media_settings', '');
          }
          else
          {
              $tpl->parse('media_settings', 'media_settings_block', true);
          }
      }
      else
      {
          $tpl->parse('media_settings', 'media_settings_block', true);
      }
      
      // set optionen media_upload_block
      $tpl->set_var(array('CHANGE_SETTINGS' => $TEXT['MODIFY_SETTINGS'], 'HEADING_BROWSE_MEDIA' => $HEADING['BROWSE_MEDIA'], 'HEADING_MEDIA' => $MENU['MEDIA'] . ' ' . $TEXT['FOLDERS'], 'HEADING_CREATE_FOLDER' => $HEADING['CREATE_FOLDER'], 'HEADING_UPLOAD_FILES' => $HEADING['UPLOAD_FILES'], 'OPTIONS' => $TEXT['OPTION'], 'SETTINGS_URL' => $_SERVER['SCRIPT_NAME']));
      
      $tpl->set_var(array('HOME_DIRECTORY' => $currentHome, //
      'MEDIA_DIRECTORY' => MEDIA_DIRECTORY, 'CURRENT_DIR' => $directory));
      
      // create dropdownlist dir_list_block
      $tpl->set_block('main_block', 'dir_list_block', 'dir_list');
      // select the correct directory list
      $use_dirs = (!HOME_FOLDERS) ? $dirs : (empty($_SESSION['HOME_FOLDER'])) ? $dirs : $dirs_rw;
      if (count($use_dirs) > 0)
      {
          foreach ($use_dirs as $name)
          {
              // prevent duplicate entries - default directory is also set by template!
              if ($name == MEDIA_DIRECTORY . $currentHome)
                  continue;
              $tpl->set_var(array('MEDIA_NAME' => $name, 'SELECTED' => (MEDIA_DIRECTORY . $directory == $name) ? ' selected="selected"' : ''));
              $tpl->parse('dir_list', 'dir_list_block', true);
          }
      }
      else
      {
          $tpl->set_var('dir_list', '');
      }
      
      // Insert permissions values, hide for some actions
      // workout action should show default blocks
      switch ($actions)
          : // all others remove from left side
          case 'none':
          case 'show':
          case 'media_reload':
          case 'media_create':
          case 'media_upload':
          case 'media_delete':
          case 'save_media_rename':
              $tpl->set_block('main_block', 'media_create_block', 'media_create');
              if (($admin->get_permission('media_create') != true) || ($full_home_folder_access == false))
              {
                  $tpl->set_var('DISPLAY_CREATE', 'hide');
                  $tpl->set_block('media_create', '');
              }
              else
              {
                  $tpl->set_var(array('DISPLAY_CREATE' => '', 'MAX_UPLOADS' => $maxUploadFiles, 'ALLOW_EXTS' => RENAME_FILES_ON_UPLOAD));
                  $tpl->parse('media_create', 'media_create_block', true);
              }
              
              $tpl->set_block('main_block', 'input_upload_block', 'input_upload');
              for ($x = 0; $x <= $maxUploadFiles; $x++)
              {
                  $tpl->parse('input_upload', 'input_upload_block', true);
              }
              
              $tpl->set_block('main_block', 'media_upload_block', 'media_upload');
              if (($admin->get_permission('media_upload') != true) || ($full_home_folder_access == false))
              {
                  $tpl->set_var('DISPLAY_UPLOAD', 'hide');
                  $tpl->set_block('media_upload', '');
              }
              else
              {
                  $tpl->set_var(array('DISPLAY_UPLOAD' => ''));
                  $tpl->parse('media_upload', 'media_upload_block', true);
              }
              break;
      default:
          // all the other action has to hide the blocks
          $tpl->set_block('main_block', 'media_create_block', 'media_create');
          $tpl->set_var('DISPLAY_CREATE', 'hide');
          $tpl->parse('media_create', '');
          
          $tpl->set_block('main_block', 'media_upload_block', 'media_upload');
          $tpl->set_var('DISPLAY_UPLOAD', 'hide');
          $tpl->parse('media_upload', '');
          
          break;
          endswitch;
          
          // END workout main_wrapper
          
          // Now prepare and parse values for the wrapper template show modus
          switch ($actions)
              : case 'none':
              case 'show':
              case 'media_reload':
              case 'media_create':
              case 'media_upload':
              case 'media_delete':
              case 'save_media_rename':
                  $tpl->loadfile('browse');
                  $tpl->set_block('main_block', 'main_wrapper_block', 'browse');
                  
                  // Workout the parent dir link PARENT_PATH
                  //$parent_path = !empty($directory) ? dirname($directory) : $directory;
                  if (!empty($directory))
                  {
                      if (HOME_FOLDERS && !empty($_SESSION['HOME_FOLDER']))
                      {
                          $parent_path = $_SESSION['HOME_FOLDER'];
                      }
                      else
                      {
                          $parent_path = dirname($directory);
                      }
                  }
                  else
                  {
                      $parent_path = $directory;
                  }
                  // $parent_dir_link = ADMIN_URL.'/media/index.php?dir='.$directory.'&amp;up=1';
                  $parent_dir_link = 1;
                  // Workout if the up arrow should be shown
                  $display_up_arrow = '';
                  // $display_up_arrow = (($directory == '') || ($directory == $currentHome)) ? 'hide' : '';
                  
                  // Insert header info values main_wrapper_block
                  $tpl->set_var(array('THEME_URL' => THEME_URL, 'ROOT_DIRECTORY' => MEDIA_DIRECTORY, 'MEDIA_DIRECTORY' => MEDIA_DIRECTORY, 'CURRENT_PATH' => $directory, 'PARENT_DIR_LINK' => $parent_dir_link, 'PARENT_PATH' => $parent_path));
                  
                  $tpl->set_block('browse', 'up_link_block', 'up_link');
                  if (!empty($directory) && $directory != $parent_path)
                  {
                      // show only if parent <> directory
                      $tpl->set_var(array('PARENT_DIR_LINK' => $parent_dir_link, 'TEXT_UP' => $TEXT['UP'], 'DISPLAY_UP_ARROW' => ''));
                      $tpl->parse('up_link', 'up_link_block', true);
                  }
                  else
                  {
                      $tpl->set_block('up_link', '');
                      $tpl->set_var(array('UP_LINK_COL' => ' display_up_arrow', 'TEXT_UP' => $TEXT['UP'], 'DISPLAY_UP_ARROW' => ' display_up_arrow'));
                  }
                  
                  // now set the dirs and files  file_list_block  and permissions
                  $tpl->set_block('browse', 'file_list_block', 'file_list');
                  $tpl->set_block('file_list', 'media_rename_block', 'media_rename');
                  $tpl->set_block('file_list', 'media_delete_block', 'media_delete');
                  
                  // get dirs and files in currentDir
                  $FILE = scan_current_dir(WB_PATH . MEDIA_DIRECTORY . '/' . $directory);
                  
                  $temp_id = 0;
                  
                  $line = $row_id = 1;
                  if (isset($FILE['path']) && (sizeof($FILE['path']) > 0))
                  {
                      foreach ($FILE['path'] as $name)
                      {
                          $temp_id++;
                          
                          $link_name = str_replace(' ', '%20', $name);
                          $tpl->set_var(array('NAME' => $name, 'NAME_SLASHED' => addslashes($name), 'TEMP_ID' => $temp_id, 'LINK' => 'index.php?dir=' . $directory . '/' . $link_name, 'LINK_RELATION' => '', 'ROW_ID' => ($line++ & 1), 'FT_ICON' => THEME_URL . '/images/folder_16.png', 'FILETYPE_ICON' => THEME_URL . '/images/folder_16.png', 'FILETYPE' => 'dir', 'FILENAME' => '/' . addslashes($name), 'LINK_TARGET' => '_self', 'ENABLE_OVERLIB' => '', 'EXTENSION' => '', 'MOUSEOVER' => '', 'CLASS_PREVIEW' => '', 'IMAGEDETAIL' => '', 'DISPLAY_ICON' => '', 'SIZE' => '', 'DATE' => '', 'PREVIEW' => '', 'LINK_PATH' => $directory . '/' . $link_name, 'MEDIA_PATH' => MEDIA_DIRECTORY));
                          $tpl->parse('file_list', 'file_list_block', true);
                      }
                  }
                  // now set the files  file_list_block  and permissions
                  if (isset($FILE['filename']) && (sizeof($FILE['filename']) > 0))
                  {
                      // convert to correct searchpattern
                      $allowed_file_types = str_replace(',', '|', RENAME_FILES_ON_UPLOAD);
                      
                      foreach ($FILE['filename'] as $name)
                      {
                          $preview = 'preview';
                          if (!preg_match("/\." . $allowed_file_types . "$/i", $name))
                          {
                              // && (trim($name) == '')
                              $preview = '';
                              continue;
                          }
                          
                          $temp_id++;
                          
                          $overlib = (preg_match("/\." . $allowed_img_types . "$/i", $name)) ? ' overlib' : '';
                          
                          if ($preview)
                          {
                              $filetype = get_filetype(WB_URL . MEDIA_DIRECTORY . $directory . '/' . $name);
                              $size = filesize(WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $name);
                              $bytes = byte_convert($size);
                              $fdate = filemtime(WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $name);
                              $date = date(DATE_FORMAT . ' ' . TIME_FORMAT, $fdate);
                              $filetypeicon = get_filetype_icon(WB_URL . MEDIA_DIRECTORY . $directory . '/' . $name);
                              
                              $tooltip = '';
                              $imgdetail = $bytes;
                              $icon = THEME_URL . '/images/files/unknown.png';
                              
                              if (!$pathsettings['global']['show_thumbs'])
                              {
                                  $info = @getimagesize(WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $name);
                                  if ($info[0])
                                  {
                                      $imgdetail = fsize(filesize(WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $name)) . '<br /> ' . $info[0] . ' x ' . $info[1] . ' px';
                                      $icon = 'thumb.php?t=1&amp;img=' . $directory . '/' . $name;
                                      $tooltip = ShowTip('thumb.php?t=2&amp;img=' . $directory . '/' . $name, $allowed_img_types);
                                  }
                                  else
                                  {
                                      $icon = THEME_URL . '/images/files/' . $filetypeicon . '.png';
                                  }
                              }
                              else
                              {
                                  $filetypeicon = get_filetype_icon(WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $name);
                                  $icon = THEME_URL . '/images/files/' . $filetypeicon . '.png';
                              }
                              
                              $tpl->set_var(array('NAME' => $name, 'NAME_SLASHED' => addslashes($name), 'TEMP_ID' => $temp_id, 'LINK' => WB_URL . MEDIA_DIRECTORY . $directory . '/' . $name, 'LINK_RELATION' => '', 'ROW_ID' => ($line++ & 1), 'FT_ICON' => $icon, 'FILETYPE_ICON' => THEME_URL . '/images/files/' . $filetypeicon . '.png', 'FILENAME' => addslashes($name), 'LINK_TARGET' => '_top', 'ENABLE_OVERLIB' => $overlib, 'FILETYPE' => 'file', 'EXTENSION' => $filetype, 'MOUSEOVER' => $tooltip, 'CLASS_PREVIEW' => '', 'IMAGEDETAIL' => $imgdetail, 'DISPLAY_ICON' => '', 'SIZE' => $bytes, 'DATE' => $date, 'PREVIEW' => $preview));
                              $tpl->parse('file_list', 'file_list_block', true);
                          }
                      }
                  }
                  
                  $tpl->set_var(array('TEXT_CURRENT_FOLDER' => $TEXT['CURRENT_FOLDER'], 'TEXT_RELOAD' => $TEXT['RELOAD'], 'TEXT_RENAME' => $TEXT['RENAME'], 'TEXT_DELETE' => $TEXT['DELETE'], 'TEXT_SIZE' => $TEXT['SIZE'], 'TEXT_DATE' => $TEXT['DATE'], 'TEXT_NAME' => $TEXT['NAME'], 'TEXT_TYPE' => $TEXT['TYPE'], 'MEDIA_BROWSE' => '', 'NONE_FOUND' => $MESSAGE['MEDIA_NONE_FOUND'], 'CHANGE_SETTINGS' => $TEXT['MODIFY_SETTINGS'], 'CONFIRM_DELETE' => js_alert_encode($MESSAGE['MEDIA_CONFIRM_DELETE']), 'CONFIRM_DELETE_FILE' => js_alert_encode($MESSAGE['MEDIA_CONFIRM_DELETE_FILE']), 'CONFIRM_DELETE_DIR' => js_alert_encode($MESSAGE['MEDIA_CONFIRM_DELETE_DIR'])));
                  
                  // If no files are in the media folder say so
                  if (($temp_id == 0))
                  {
                      $tpl->set_var('DISPLAY_LIST_TABLE', ' hide');
                      $tpl->set_var('DISPLAY_NONE_FOUND', ' center');
                      $tpl->set_var("file_list_block", "<tr><td></td></tr>");
                      $tpl->parse('file_list', 'file_list_block', true);
                  }
                  else
                  {
                      $tpl->set_var('DISPLAY_LIST_TABLE', '');
                      $tpl->set_var('DISPLAY_NONE_FOUND', ' hide');
                  }
                  
                  $tpl->set_block('file_list', 'media_rename_block', 'media_rename');
                  $tpl->set_block('file_list', 'media_delete_block', 'media_delete');
                  // Insert permissions values
                  if (($admin->get_permission('media_rename') != true) || ($full_home_folder_access == false))
                  {
                      $tpl->set_var('DISPLAY_RENAME', 'hide');
                      $tpl->set_var('RENHAME_CONTENT', '');
                      $tpl->parse('media_rename', '');
                  }
                  else
                  {
                      $tpl->set_var('RENHAME_CONTENT', '');
                      $tpl->parse('media_rename', 'media_rename_block', true);
                  }
                  
                  if (($admin->get_permission('media_delete') != true) || ($full_home_folder_access == false))
                  {
                      $tpl->set_var('DISPLAY_DELETE', 'hide');
                      $tpl->set_var('DELETE_CONTENT', '');
                      $tpl->parse('media_delete', '');
                  }
                  else
                  {
                      $tpl->set_var('DELETE_CONTENT', '');
                      $tpl->parse('media_delete', 'media_delete_block', true);
                  }
                  break;
                  endswitch;
                  
                  // begin with save modus actions
                  switch ($actions)
                      : // save actions
                      case 'save_media_settings':
                          if (($x = save_media_settings($pathsettings)) == 0)
                          {
                              $admin->print_error($MESSAGE['SETTINGS_UNABLE_WRITE_CONFIG'], $backlink);
                          }
                          else
                          {
                              $admin->print_success($MESSAGE['SETTINGS_SAVED'], $backlink);
                          }
                          
                          break;
      case 'save_media_rename':
          $ext = trim($admin->strip_slashes($admin->get_post('extension')));
          $ext = (empty($ext)) ? '' : '.' . $ext;
          $old_file = media_filename(trim($admin->strip_slashes($admin->get_post('old_name')))) . $ext;
          $rename_file = media_filename(trim($admin->strip_slashes($admin->get_post('name')))) . $ext;
          $type = trim($admin->strip_slashes($admin->get_post('filetype')));
          // perhaps change dots in underscore by tpye = directory
          $rename_file = trim($rename_file, '.');
          $old_file = WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $old_file;
          $rename_file = WB_PATH . MEDIA_DIRECTORY . $directory . '/' . $rename_file;
          if (($type == 'dir'))
          {
              $rename_file = str_replace('.', '_', $rename_file);
          }
          elseif (!preg_match("/\." . $allowed_file_types . "$/i", $rename_file))
          {
              $admin->print_error($TEXT['EXTENSION'] . ': ' . $MESSAGE['GENERIC_INVALID'], $backlink);
          }
          
          if (rename($old_file, $rename_file))
          {
              $admin->print_success($MESSAGE['MEDIA_RENAMED'], $backlink);
          }
          else
          {
              $admin->print_error($MESSAGE['MEDIA_CANNOT_RENAME'], $backlink);
          }
          
          break;
          
          endswitch;
          
          // mask input modus
          switch ($actions)
              : case 'media_rename':
              clearstatcache();
          
          $rename_file = media_filename(trim($admin->strip_slashes($admin->get_post('filename'))));
          $ext = trim($admin->strip_slashes($admin->get_post('fileext')));
          $type = trim($admin->strip_slashes($admin->get_post('filetype')));
          $rename_file = basename($rename_file);
          
          $tpl->loadfile('rename');
          $tpl->set_block('main_block', 'main_wrapper_block', 'rename');
          // false | true
          $tpl->debug = false;
          
          $tpl->set_var(array('THEME_URL' => THEME_URL, 'TEXT_CURRENT_FOLDER' => $TEXT['CURRENT_FOLDER'], 'FILENAME' => $rename_file, 'BASENAME' => trim(str_replace($ext, '', basename($rename_file)), '.'), 'ROOT_DIRECTORY' => MEDIA_DIRECTORY, 'DISPLAY_UP_ARROW' => ' display_up_arrow', 'CURRENT_PATH' => $directory, 'DIR' => $directory, 'FILE_TYPE' => $type, 'EXTENSION' => '.' . ltrim($ext, '.'), 'FILE_EXT' => ltrim($ext, '.'), 'TEXT_OVERWRITE_EXIST' => $TEXT['OVERWRITE_EXISTING'], 'TEXT_TO' => '', // $TEXT['TO'],
          'MEDIA_BROWSE' => '', 'TEXT_RENAME' => $TEXT['RENAME'], 'TEXT_CANCEL' => $TEXT['CANCEL']));
          $tpl->parse('rename', 'main_wrapper_block', true);
          
          break;
      case 'media_settings':
          // load template language file
          $lang = THEME_PATH . '/languages/' . LANGUAGE . '.php';
          include_once(!file_exists($lang) ? THEME_PATH . '/languages/EN.php' : $lang);
          
          $tpl->loadfile('settings');
          $tpl->set_block('main_block', 'main_wrapper_block', 'settings');
          // false | true
          $tpl->debug = false;
          
          $admin_only = isset($pathsettings['global']['admin_only']) && ($pathsettings['global']['admin_only'] == true) ? ' checked="checked"' : '';
          $show_thumbs = isset($pathsettings['global']['show_thumbs']) && ($pathsettings['global']['show_thumbs'] == true) ? ' checked="checked"' : '';
          
          $tpl->set_var(array('TEXT_HEADER' => $TEXT['TEXT_HEADER'], 'SAVE_TEXT' => $TEXT['SAVE'], 'CANCEL' => $TEXT['CANCEL'], 'RESET' => $TEXT['RESET'], 'NO_SHOW_THUMBS' => $TEXT['NO_SHOW_THUMBS'], 'MEDIA_BROWSE' => '', 'ADMIN_ONLY' => $TEXT['ADMIN_ONLY'], 'SETTINGS' => $TEXT['SETTINGS'], 'CURRENT_PATH' => $directory, 'ADMIN_URL' => ADMIN_URL, 'WIDTH' => $TEXT['WIDTH'], 'HEIGHT' => $TEXT['HEIGHT'], 'ADMIN_ONLY_SELECTED' => $admin_only, 'NO_SHOW_THUMBS_SELECTED' => $show_thumbs, 'NONE_FOUND' => '', 'DISPLAY_NONE' => ''));
          
          // get dirs in currentDir
          // $FILE = scan_current_dir(WB_PATH.MEDIA_DIRECTORY.$directory);
          $dirs = directory_list(WB_PATH . MEDIA_DIRECTORY);
          $line = $row_id = 1;
          $tpl->set_block('settings', 'dir_settings_block', 'dir_settings');
          
          if (isset($dirs))
          {
              $good_dirs = 0;
              $dir_filter = MEDIA_DIRECTORY . $directory;
              $parent = (substr_count($dir_filter, '/') + 1);
              $dir_filter = str_replace(array('/', ' '), '_', $dir_filter);
              
              foreach ($dirs as $name)
              {
                  $relative = str_replace(WB_PATH, '', $name);
                  $subparent = (substr_count($relative, '/') + 1);
                  // $relative = MEDIA_DIRECTORY.$directory.'/'.$name;
                  $safepath = str_replace(array('/', ' '), '_', $relative);
                  $continue = strlen(str_replace($safepath, '', $dir_filter));
                  
                  // if( (substr_count($safepath,$dir_filter) == 0) || ( $dir_filter == $safepath )      )
                  if ((($parent) != ($subparent - 1)) || (substr_count($safepath, $dir_filter) == 0) || ($dir_filter == $safepath))
                  {
                      continue;
                  }
                  $good_dirs++;
                  $cur_width = $cur_height = '';
                  if (isset($pathsettings[$safepath]['width']))
                  {
                      $cur_width = $pathsettings[$safepath]['width'];
                  }
                  if (isset($pathsettings[$safepath]['height']))
                  {
                      $cur_height = $pathsettings[$safepath]['height'];
                  }
                  $cur_width = ($cur_width != 0) ? (int)$cur_width : '-';
                  $cur_height = ($cur_height != 0) ? (int)$cur_height : '-';
                  
                  $tpl->set_var(array('PATH_NAME' => basename($relative), 'FIELD_NAME' => $safepath, 'CUR_WIDTH' => $cur_width, 'CUR_HEIGHT' => $cur_height, 'ROW_ID' => ($line++ & 1)));
                  
                  $tpl->parse('dir_settings', 'dir_settings_block', true);
              }
              if ($good_dirs == 0)
              {
                  $tpl->set_var(array('PATH_NAME' => '', 'FIELD_NAME' => '', 'CUR_WIDTH' => '', 'CUR_HEIGHT' => '', 'ROW_ID' => '', 'DISPLAY_NONE' => ' hide'));
                  $tpl->parse('dir_settings', 'dir_settings_block', true);
                  $tpl->set_var('NONE_FOUND', $MESSAGE['MEDIA_NONE_FOUND']);
                  $tpl->parse('settings', 'dir_settings_block', true);
              }
          }
          else
          {
              $tpl->set_var('NONE_FOUND', $MESSAGE['MEDIA_NONE_FOUND']);
              $tpl->parse('settings', 'dir_settings_block', true);
          }
          break;
          endswitch;
          
          // normal actions
          switch ($actions)
              : case 'media_upload':
              $target_path = str_replace('\\', '/', WB_PATH . MEDIA_DIRECTORY . $directory);
          
          // Create relative path of the new dir name
          $resizepath = MEDIA_DIRECTORY . $directory;
          $resizepath = str_replace(array('/', ' '), '_', $resizepath);
          // Find out whether we should replace files or give an error
          $overwrite = ($admin->get_post('overwrite') != '') ? true : false;
          // convert to correct searchpattern
          $allowed_file_types = str_replace(',', '|', RENAME_FILES_ON_UPLOAD);
          $good_uploads = 0;
          // If the user chose to unzip the first file, unzip into the current folder
          if (isset($_POST['unzip']) && ($_POST['unzip'] == true))
          {
              // include_once(get_include('thumb.php'));
              
              if (isset($_FILES['upload']['error'][0]) && $_FILES['upload']['error'][0] == UPLOAD_ERR_OK)
              {
                  $src_file = isset($_FILES['upload']['name'][0]) ? $_FILES['upload']['name'][0] : null;
                  if ($src_file && preg_match('/\.zip$/i', $src_file))
                  {
                      /*
                       * Callback function to skip files not in white-list
                       */
                      function pclzipCheckValidFile($p_event, &$p_header)
                      {
                          //  return 1;
                          $allowed_file_types = str_replace(',', '|', RENAME_FILES_ON_UPLOAD);
                          $info = pathinfo($p_header['filename']);
                          $ext = isset($info['extension']) ? $info['extension'] : '';
                          $dots = (substr($info['basename'], 0, 1) == '.') || (substr($info['basename'], -1, 1) == '.');
                          if (preg_match('/' . $allowed_file_types . '$/i', $ext) && $dots != '.')
                          {
                              // ----- allowed file types are extracted
                              return 1;
                          }
                          else
                          {
                              // ----- all other files are skiped
                              return 0;
                          }
                      }
                      /* ********************************* */
                      require_once(get_include(WB_PATH . '/modules/lib_lepton/pclzip/pclzip.lib.php'));
                      $archive = new PclZip($_FILES['upload']['tmp_name'][0]);
                      $list = $archive->extract(PCLZIP_OPT_PATH, $target_path, PCLZIP_CB_PRE_EXTRACT, 'pclzipCheckValidFile');
                      $good_uploads = sizeof($list);
                      
                      if ($archive->error_code != 0)
                      {
                          $admin->print_error('UNABLE TO UNZIP FILE' . ' :: ' . $archive->errorInfo(true), $backlink);
                      }
                  }
              }
          }
          else
          {
              // proceed normal multi-upload
              $file_count = sizeof($_FILES['upload']['error']);
              for ($x = 0; $x < $file_count; $x++)
              {
                  // If file was upload to tmp
                  if (isset($_FILES['upload']['name'][$x]))
                  {
                      // Remove bad characters
                      $filename = media_filename($_FILES['upload']['name'][$x]);
                      // Check if there is still a filename left and allowed filetyp
                      if (($filename != '') && preg_match("/\." . $allowed_file_types . "$/i", $filename))
                      {
                          // Move to relative path (in media folder)
                          if (file_exists($target_path . '/' . $filename) && $overwrite === true)
                          {
                              if (move_uploaded_file($_FILES['upload']['tmp_name'][$x], $target_path . '/' . $filename))
                              {
                                  $good_uploads++;
                                  // Chmod the uploaded file
                                  change_mode($target_path . '/' . $filename, 'file');
                              }
                          }
                          elseif (!file_exists($target_path . '/' . $filename))
                          {
                              if (move_uploaded_file($_FILES['upload']['tmp_name'][$x], $target_path . '/' . $filename))
                              {
                                  $good_uploads++;
                                  // Chmod the uploaded file
                                  change_mode($target_path . '/' . $filename);
                              }
                          }
                          if (file_exists($target_path . '/' . $filename) && preg_match("/\." . $allowed_img_types . "$/i", $filename))
                          {
                              if (isset($pathsettings[$resizepath]))
                              {
                                  include_once(get_include(ADMIN_PATH . '/media/resize_img.php'));
                                  if ($pathsettings[$resizepath]['width'] || $pathsettings[$resizepath]['height'])
                                  {
                                      $rimg = new RESIZEIMAGE($target_path . '/' . $filename);
                                      $rimg->resize_limitwh($pathsettings[$resizepath]['width'], $pathsettings[$resizepath]['height'], $target_path . '/' . $filename);
                                      $rimg->close();
                                  }
                              }
                          }
                          // store file name of first file for possible unzip action
                          if ($x == 1)
                          {
                              $filename1 = $target_path . '/' . $filename;
                          }
                      }
                  }
              }
          }
          
          if (isset($_POST['delzip']))
          {
              if (file_exists($filename1))
              {
                  unlink($filename1);
              }
          }
          
          if (($good_uploads == 1))
          {
              $admin->print_success($good_uploads . ' ' . $MESSAGE['MEDIA_SINGLE_UPLOADED'], $backlink);
          }
          else
          {
              $admin->print_success($good_uploads . ' ' . $MESSAGE['MEDIA_UPLOADED'], $backlink);
          }
          
          
          break;
      case 'media_create':
          // $directory = rawurldecode(trim($admin->strip_slashes($admin->get_post('current_dir'))));
          // Remove bad characters from user folder name
          $target = ($admin->get_post('target') != null) ? media_filename(trim($admin->strip_slashes($admin->get_post('target')))) : $current_dir;
          $userPath = WB_PATH . MEDIA_DIRECTORY;
          $err_msg = array();
          if (($target == null) || ($target == $current_dir))
          {
              $err_msg[] = $MESSAGE['MEDIA_BLANK_NAME'];
          }
          else
          {
              // Try and make the dir
              $target = trim($target, '.');
              
              $dirname = $userPath . $current_dir . '/' . $target;
              if (file_exists($dirname))
              {
                  $err_msg[] = $MESSAGE['MEDIA_DIR_EXISTS'];
              }
              else
              {
                  if (make_dir($dirname))
                  {
                      change_mode($dirname);
                      if (is_writable($dirname))
                      {
                          // Create default "index.php" file
                          $rel_pages_dir = str_replace(WB_PATH . MEDIA_DIRECTORY, '', dirname($dirname));
                          $step_back = str_repeat('../', substr_count($rel_pages_dir, '/') + 1);
                          
                          $content = '<?php' . "\n";
                          $content .= '// This file is generated by LEPTON Ver.' . VERSION . ';' . "\n";
                          $content .= "\t" . 'header(\'Location: ' . $step_back . 'index.php\');' . "\n";
                          $content .= '?>';
                          
                          $filename = $dirname . '/index.php';
                          // write content into file
                          $handle = fopen($filename, 'w');
                          fwrite($handle, $content);
                          fclose($handle);
                          change_mode($filename, 'file');
                      }
                      else
                      {
                          $err_msg[] = $MESSAGE['GENERIC_BAD_PERMISSIONS'];
                      }
                  }
                  else
                  {
                      $err_msg[] = $MESSAGE['GENERIC_BAD_PERMISSIONS'];
                  }
              }
          }
          
          if (sizeof($err_msg) > 0)
          {
              $admin->print_error(implode('<br />', $err_msg));
          }
          else
          {
              $admin->print_success($MESSAGE['MEDIA_DIR_MADE'], $backlink);
          }
          
          break;
      case 'media_delete':
          $filetype = isset($_POST['filetype']) ? trim($admin->strip_slashes($admin->get_post('filetype'))) : '';
          $filename = isset($_POST['filename']) ? trim($admin->strip_slashes($admin->get_post('filename'))) : '';
          $relative_path = WB_PATH . MEDIA_DIRECTORY . $directory;
          // Find out whether its a file or folder
          if ($filetype == 'dir')
          {
              // Try and delete the directory
              if (rm_full_dir($relative_path . '/' . $filename))
              {
                  $admin->print_success($MESSAGE['MEDIA_DELETED_DIR'], $backlink);
              }
              else
              {
                  $admin->print_error($MESSAGE['MEDIA_CANNOT_DELETE_DIR'], $backlink);
              }
          }
          elseif ($filetype == 'file')
          {
              // Try and delete the file
              if (unlink($relative_path . '/' . $filename))
              {
                  $admin->print_success($MESSAGE['MEDIA_DELETED_FILE'], $backlink);
              }
              else
              {
                  $admin->print_error($MESSAGE['MEDIA_CANNOT_DELETE_FILE'], $backlink);
              }
          }
          else
          {
              $admin->print_error($MESSAGE['MEDIA_CANNOT_DELETE_FILE'], $backlink);
          }
          break;
          endswitch;
          
          // Parse template for preferences form
          $tpl->parse('main', 'main_wrapper_block', false);
          $tpl->parse('main', 'main_block', false);
          $output = $tpl->finish($tpl->parse('output', 'page'));
          return $output;
  }
  
  // test if valid $admin-object already exists (bit complicated about PHP4 Compatibility)
  if (!(isset($admin) && is_object($admin) && (get_class($admin) == 'admin')))
  {
      require_once(WB_PATH . '/framework/class.admin.php');
  }
  
  $admin = new admin('Media', 'media');
  
  print build_page($admin, $database);
  
  $admin->print_footer();
?>