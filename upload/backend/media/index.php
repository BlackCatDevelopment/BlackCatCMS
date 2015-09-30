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
    include(CAT_PATH . '/framework/class.secure.php');
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

$backend = CAT_Backend::getInstance('Media','media');
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

// ============================================================================= 
// ! Set the initial folder to view (mediaroot or homefolder). If the user
// ! doesn't have permissions to see media, redirect to admin_url
// ============================================================================= 
if ($user->checkPermission('media','media',false)==true){
    $tpl_data['initial_folder']
        = ( $user->get_user_id() == 1 || (HOME_FOLDERS && $user->get_home_folder()=='') || !HOME_FOLDERS )
        ? MEDIA_DIRECTORY
        : $dirh->sanitizePath(MEDIA_DIRECTORY.$user->get_home_folder());
    if(!file_exists(CAT_PATH.'/'.$tpl_data['initial_folder']))
        $dirh->createDirectory(CAT_PATH.'/'.$tpl_data['initial_folder']);
}
else {
    header('Location: ' . CAT_ADMIN_URL);
}

// ======================================== 
// ! Get contents for the intitial folder   
// ======================================== 
$current_folder = CAT_PATH.'/'.$tpl_data['initial_folder'];
$folders        = $dirh->setRecursion(false)
                ->getDirectories($current_folder,$current_folder.'/');
$files          = $dirh->setRecursion(false)
                ->scanDirectory(
                    $current_folder,
                    true,                // $with_files
                    true,                // $files_only
                    $current_folder.'/', // $remove_prefix
                    array_merge(         // $suffixes
                        $allowed_img_types,
                        explode('|',$tpl_data['allowed_file_types'])
                    )
                  );

// ============================= 
// ! Add folders to $tpl_data   
// ============================= 
if(is_array($folders) && count($folders))
{
    foreach(array_values($folders) as $folder)
    {
        $tpl_data['folders'][]['NAME'] = $dirh->getName($folder);
    }
}
// ================================================ 
// ! Add files and infos about them to $tpl_data   
// ================================================ 
if(is_array($files) && count($files))
{
    foreach(array_values($files) as $file)
    {
        $file_path                                   = $dirh->sanitizePath(CAT_PATH . $tpl_data['initial_folder'].'/'.$file);
        $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $tpl_data['files'][] = array(
            'FILETYPE'     => $file_type,
            'show_preview' => ( in_array($file_type,$allowed_img_types) ) ? true : false,
            'FILESIZE'     => $dirh->getSize($file_path,true),
            'FILEDATE'     => CAT_Helper_DateTime::getDateTime(filemtime($file_path)),
            'FILETIME'     => CAT_Helper_DateTime::getDateTime(filemtime($file_path)),
            'FULL_NAME'    => $dirh->getName($file),
            'NAME'         => pathinfo($file,PATHINFO_FILENAME)
        );
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
