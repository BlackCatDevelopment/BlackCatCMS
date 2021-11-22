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
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) {
		include($root.'framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$backend = CAT_Backend::getInstance('Addons', 'addons');
// Check if user uploaded a file
if (!isset($_FILES['userfile']) || $_FILES['userfile']['size'] == 0)
{
    if(isset($_FILES['userfile']) && isset($_FILES['userfile']['error']))
    {
        switch($_FILES['userfile']['error'])
        {
            case UPLOAD_ERR_INI_SIZE:
                $error = 'File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'File upload error (the uploaded file was only partially uploaded).';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'File upload error (no file was uploaded).';
                break;
            case @UPLOAD_ERR_NO_TMP_DIR:
                $error = 'File upload error (missing a temporary folder).';
                break;
            case @UPLOAD_ERR_CANT_WRITE:
                $error = 'File upload error (failed to write file to disk).';
                break;
            case @UPLOAD_ERR_EXTENSION:
                $error = 'File upload error (file upload stopped by extension).';
                break;
            default:
                $error = 'File upload error (unknown error code) ('.$this->file_src_error.')';
                break;
        }
        $backend->print_error($error);
    }
	header("Location: index.php");
	exit(0);
}


// Check if module dir is writable (doesn't make sense to go on if not)
if ( !(is_writable(CAT_PATH.'/modules/') && is_writable(CAT_PATH.'/templates/') && is_writable(CAT_PATH.'/languages/')) )
{
    $backend->print_error( 'Unable to write to the target directory' );
}

// keep old modules happy
require_once CAT_PATH.'/framework/class.admin.php';
$admin = new admin('Addons','addons');

if(CAT_Helper_Addons::installUploaded($_FILES['userfile']['tmp_name'],$_FILES['userfile']['name']))
{
    $backend->print_success( 'Installed successfully' );
}
else
{
    // error is already printed by the helper
    $backend->print_footer( 'Unable to install the module' );
}
