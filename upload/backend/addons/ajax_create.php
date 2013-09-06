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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
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

$backend = CAT_Backend::getInstance('Addons', 'modules_install', false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();
$success = false;
$message = '';

header('Content-type: application/json');

if ( !$users->checkPermission('Addons','modules_install') )
{
	$message = $backend->lang()->translate("Sorry, but you don't have the permissions for this action");
    printResult();
}

$type    = $val->sanitizePost('new_moduletype');
$name    = $val->sanitizePost('new_modulename');
$dir     = $val->sanitizePost('new_moduledir');
$desc    = $val->sanitizePost('new_moduledesc');
$author  = $val->sanitizePost('new_moduleauthor');
$func    = 'page';
$pre     = 'module_';
$full    = '';

if(!$type || !$name || !$dir || !$desc)
{
	$message = $backend->lang()->translate("Incomplete data, please fill out all fields!");
    printResult();
}

// directory must not exist
$err = NULL;
if( $type != 'language' )
{
    if( $type == 'template' )
    {
        $full = CAT_PATH.'/templates/'.$dir;
        if( file_exists($full) )
            $err = $backend->lang()->translate('A template with the same directory name already exists');
    }
    else
    {
        $full = CAT_PATH.'/modules/'.$dir;
        if( file_exists($full) )
            $err = $backend->lang()->translate('A module with the same directory name already exists');
    }
}
else
{
    $full = CAT_PATH.'/languages/'.$dir.'.php';
    if( file_exists($full) )
        $err = $backend->lang()->translate('A language file with the same name already exists');
}

if($err)
{
	$message = $err;
    printResult();
}

// map module type to correct settings
switch ( $type ) {
    case 'tool':
        $func = 'tool';
        $type = 'module';
        break;
    case 'library':
        $func = 'library';
        $type = 'module';
        break;
    case 'wysiwyg':
        $func = 'wysiwyg';
        $type = 'module';
        break;
    case 'template':
        $pre  = 'template_';
        $func = 'template';
        break;
    case 'language':
        $func = 'language';
        $pre  = 'language_';
        break;
}

$info = array(
    $pre.'name' => $name,
    $pre.'directory' => $dir,
    $pre.'type' => $type,
    $pre.'function' => $func,
    $pre.'description' => $desc,
    $pre.'version' => '0.1',
    $pre.'platform' => '1.x',
    $pre.'author' => $author,
    $pre.'guid' => CAT_Object::createGUID(),
    $pre.'license' => 'GNU General Public License',
);

if($type == 'language')
{
    $info[$pre.'code'] = $dir;
}

// create directories
if( $type != 'language' )
{
    if(!CAT_Helper_Directory::createDirectory($full))
    {
        $message = $backend->lang()->translate('Directory could not be created!');
        printResult();
    }
    foreach(array('templates','css','js') as $sub)
        CAT_Helper_Directory::createDirectory($full.'/'.$sub);
    if($type != 'module')
        CAT_Helper_Directory::createDirectory($full.'/languages');
    CAT_Helper_Directory::recursiveCreateIndex($full);
}

// create info.php
if ( $type !== 'language' )
    $fh = fopen($full.'/info.php','w');
else
    $fh = fopen($full,'w');

if(!$fh)
{
    $message = $backend->lang()->translate('Unable to create info.php!');
    printResult();
}
writeHeader($fh,$name,$author);
foreach($info as $key => $value )
{
    if($type=='language' && $key == 'language_directory') continue;
    fwrite($fh,'$'.$key.' = "'.$value.'";'."\n");
}
if($type == 'language')
    fwrite($fh,'
$LANG = array(

);
');
fclose($fh);

// create some more default files
if($type=='module')
{
    foreach(array('install','uninstall','view','modify') as $n)
    {
        $fh = fopen($full.'/'.$n.'.php','w');
        if($fh)
        {
            writeHeader($fh,$name,$author);
            fclose($fh);
        }
    }
}

// insert module into DB
foreach($info as $key => $value)
{
    $key = str_replace($pre,'module_',$key);
    $info[$key] = $value;
}
$info['addon_function'] = $info[$pre.'function'];

CAT_Helper_Addons::loadModuleIntoDB( $dir, 'install', $info);

$success = true;
$message = $backend->lang()->translate('Module created successfully!');
printResult();

function printResult()
{
    global $message, $success;
   	$ajax	= array(
		'message'	=> $message,
		'success'	=> $success
	);
	print json_encode( $ajax );
	exit();
}

function writeHeader($fh,$name,$author)
{
    fwrite($fh,'<'.'?'.'php

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
 *   @author          '.$author.'
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         '.$name.'
 *
 */

if (defined(\'CAT_PATH\')) {
    if (defined(\'CAT_VERSION\')) include(CAT_PATH.\'/framework/class.secure.php\');
} elseif (file_exists($_SERVER[\'DOCUMENT_ROOT\'].\'/framework/class.secure.php\')) {
    include($_SERVER[\'DOCUMENT_ROOT\'].\'/framework/class.secure.php\');
} else {
    $subs = explode(\'/\', dirname($_SERVER[\'SCRIPT_NAME\']));    $dir = $_SERVER[\'DOCUMENT_ROOT\'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= \'/\'.$sub;
        if (file_exists($dir.\'/framework/class.secure.php\')) {
            include($dir.\'/framework/class.secure.php\'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can\'t include class.secure.php!", $_SERVER[\'SCRIPT_NAME\']), E_USER_ERROR);
}

');
}