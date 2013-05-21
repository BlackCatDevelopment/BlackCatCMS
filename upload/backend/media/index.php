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
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
 
if (defined('CAT_PATH')) {
    include(CAT_PATH . '/framework/class.secure.php');
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

$backend = CAT_Backend::getInstance('Media','media');

// ================================= 
// ! Include the WB functions file   
// ================================= 
include_once(CAT_PATH . '/framework/functions.php');

$dirh  = CAT_Helper_Directory::getInstance();
$user  = CAT_Users::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('Media','media',false);

global $parser;
$tpl_data = array();

// ************ TODO: Move this to DB ************
$allowed_img_types = array('jpg','jpeg','png','gif','tif');
// ************ TODO: Move this to DB ************

$tpl_data['maxUploadFiles']     = 12;
$tpl_data['allowed_file_types'] = str_replace(',','|',UPLOAD_ALLOWED);
$tpl_data['MEDIA_DIRECTORY']    = MEDIA_DIRECTORY;

// ==================================================================================================================================== 
// ! Set the initial folder to view (mediaroot or homefolder). If the user doesn't have permissions to see media, redirect to admin_url
// ==================================================================================================================================== 
if ($user->checkPermission('media','media',false)==true){
    $tpl_data['initial_folder']
        = ( $user->get_user_id() == 1 || (HOME_FOLDERS && $user->get_home_folder()=='') || !HOME_FOLDERS )
        ? MEDIA_DIRECTORY
        : $dirh->sanitizePath(MEDIA_DIRECTORY.$user->get_home_folder());
    #$tpl_data['initial_folder'] = preg_replace( '~^/~', '', $tpl_data['initial_folder'] );
}
else {
    header('Location: ' . CAT_ADMIN_URL);
}

// ======================================== 
// ! Get contents for the intitial folder   
// ======================================== 
$dir = scan_current_dir(CAT_PATH.'/'.$tpl_data['initial_folder']);

// ============================= 
// ! Add folders to $tpl_data   
// ============================= 
if(isset($dir['path']) && is_array($dir['path']))
{
    foreach($dir['path'] as $counter => $folder)
    {
        $tpl_data['folders'][$counter]['NAME'] = $folder;
    }
}
// ================================================ 
// ! Add files and infos about them to $tpl_data   
// ================================================ 
if(isset($dir['filename']) && is_array($dir['filename']))
{
    foreach($dir['filename'] as $counter => $file)
    {
        $file_path                                   = $dirh->sanitizePath(CAT_PATH . $tpl_data['initial_folder'].'/'.$file);
        $tpl_data['files'][$counter]['FILETYPE']     = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $tpl_data['files'][$counter]['show_preview'] = ( in_array( strtolower($tpl_data['files'][$counter]['FILETYPE']), $allowed_img_types ) ) ? true : false;
        $tpl_data['files'][$counter]['FILESIZE']     = $dirh->getSize($file_path,true);
        $tpl_data['files'][$counter]['FILEDATE']     = date (DEFAULT_DATE_FORMAT, filemtime($file_path));
        $tpl_data['files'][$counter]['FILETIME']     = date (DEFAULT_TIME_FORMAT, filemtime($file_path));
        $tpl_data['files'][$counter]['FULL_NAME']    = $file;
        $tpl_data['files'][$counter]['NAME']         = substr($file , 0 , -( strlen($tpl_data['files'][$counter]['FILETYPE'])+1 ) );
    }
}

// ================================= 
// ! Add permissions to $tpl_data   
// ================================= 
$tpl_data['permissions']['media_upload'] = $user->checkPermission('media','media_upload',false);
$tpl_data['permissions']['media_create'] = $user->checkPermission('media','media_create',false);
$tpl_data['permissions']['media_rename'] = $user->checkPermission('media','media_rename',false);
$tpl_data['permissions']['media_delete'] = $user->checkPermission('media','media_delete',false);

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_media_index', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>