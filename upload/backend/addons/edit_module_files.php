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

$backend = CAT_Backend::getInstance('Addons','addon_files');
$users   = CAT_Users::getInstance();
$code    = NULL;
$js      = NULL;
$css     = NULL;
$list    = false;
$debug   = true;

// check permissions
if ( !$users->checkPermission('Addons','addon_files') )
	$backend->printError("Sorry, but you don't have the permissions for this action");

// check options
if( ($page_id = CAT_Helper_Validate::sanitizePost('page_id','numeric')) == false)
    $backend->printFatalError('You sent an invalid value' . ( $debug ? ' (missing page_id)' : ''));
if( ($section_id = CAT_Helper_Validate::sanitizePost('section_id','numeric')) == false)
    $backend->printFatalError('You sent an invalid value' . ( $debug ? ' (missing section_id)' : ''));

// check module dir
$mod_path = CAT_Helper_Validate::sanitizePost('mod_dir');
if(!$mod_path)
    $backend->printError("Missing param" . ( $debug ? ' (missing mod_dir)' : ''));
$path     = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$mod_path);
if(!is_dir($path))
    $backend->printError("No such module");

// save changes
if(CAT_Helper_Validate::sanitizePost('action') && CAT_Helper_Validate::sanitizePost('action')=='save')
{
    if(!CAT_Helper_Validate::sanitizePost('cancel'))
    {
    	$content = '';
        $bytes   = 0;
    	if (CAT_Helper_Validate::sanitizePost('code') && strlen(CAT_Helper_Validate::sanitizePost('code')) > 0)
        {
    		$content  = CAT_Helper_Validate::strip_slashes(CAT_Helper_Validate::sanitizePost('code'));
            $file     = CAT_Helper_Directory::sanitizePath($path.'/'.CAT_Helper_Validate::sanitizePost('edit_file'));
            if(!file_exists($file))
                $backend->printFatalError("No such file");
    		$mod_file = fopen($file,'wb');
    		$bytes    = fwrite($mod_file, $content);
    		fclose($mod_file);
    	}
    	if($bytes == 0 )
     		$backend->print_error('Cannot save file', CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
    	else
    		$backend->print_success('Success', CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
    }
}
else
{
    if(!CAT_Helper_Validate::sanitizePost('edit_file'))
    {
        // find JS files
        $js  = CAT_Helper_Directory::getInstance()
               ->maxRecursionDepth(5)
               ->setSuffixFilter(array('js'))
               ->scanDirectory($path,true,true,$path);
        // find CSS files
        $css = CAT_Helper_Directory::getInstance()
               ->maxRecursionDepth(5)
               ->setSuffixFilter(array('css'))
               ->scanDirectory($path,true,true,$path);
        $list = true;
    }
    else
    {
        $file = CAT_Helper_Directory::sanitizePath($path.'/'.CAT_Helper_Validate::sanitizePost('edit_file'));
        if(!file_exists($file))
             $backend->printFatalError("No such file");
        $in   = fopen($file,'r');
        $code = fread($in, filesize($file));
        fclose($in);

        if(file_exists(CAT_PATH.'/modules/edit_area/include.php'))
        {
            include_once CAT_PATH.'/modules/edit_area/include.php';
            ea_syntax('css');
            $js   = show_wysiwyg_editor('code', 'code', $code, '100%', '350px', false);
            $code = NULL;
        }
    }

    $page = CAT_Helper_Page::properties($page_id);
    $parser->output(
        'backend_addons_editfile.tpl',
        array(
            'code'        => $code,
            'js'          => $js,
            'css'         => $css,
            'page_id'     => $page_id,
            'section_id'  => $section_id,
            'mod_dir'     => CAT_Helper_Validate::sanitizePost('mod_dir'),
            'edit_file'   => CAT_Helper_Validate::sanitizePost('edit_file'),
            'list'        => $list,
            // for banner
            'PAGE_TITLE'  => $page['page_title'],
            'PAGE_ID'     => $page_id,
            'PAGE_HEADER' => $backend->lang()->translate('Modify file'),
        )
    );

}

$backend->print_footer();

