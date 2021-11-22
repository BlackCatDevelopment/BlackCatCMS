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
 *   @copyright       2017, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *   @review          15.04.2015 18:00:59
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

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Auf die richtige function mappen, also etwa module -> page
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

$backend = CAT_Backend::getInstance('Addons', 'modules_install', false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Addons','modules_install') )
{
	$message = $backend->lang()->translate("Sorry, but you don't have the permissions for this action");
    printResult();
}

$type     = 'module';
$func     = $val->sanitizePost('new_moduletype');
$name     = $val->sanitizePost('new_modulename');
$dir      = $val->sanitizePost('new_moduledir');
$desc     = $val->sanitizePost('new_moduledesc');
$author   = $val->sanitizePost('new_moduleauthor');
$headinc  = $val->sanitizePost('new_headersinc');
$footinc  = $val->sanitizePost('new_footersinc');
$jquery   = $val->sanitizePost('new_usejquery');
$ui       = $val->sanitizePost('new_usejqueryui');
$precheck = $val->sanitizePost('new_precheck');
$pre      = 'module_';
$full     = '';

if($func=='template')
    $type = 'template';
if($func=='language')
    $type = 'language';

if(!$type || !$name || !$dir || !$desc)
{
	CAT_Object::json_error('Incomplete data, please fill out all fields!');
}

// addon must not exist!
module_create_check_dir($type,$dir);

$files_needed = array(
    'page'     => array( 'index.php', 'install.php', 'upgrade.php', 'uninstall.php', 'view.php', 'modify.php', 'add.php', 'delete.php', 'save.php', 'search.php', 'css/frontend.css', 'css/backend.css', 'languages/DE.php', 'templates/default/index.tpl' ),
    'library'  => array( 'index.php', 'install.php', 'upgrade.php' ),
    'tool'     => array( 'index.php', 'install.php', 'upgrade.php', 'uninstall.php', 'tool.php', 'css/frontend.css', 'css/backend.css', 'languages/DE.php', 'templates/default/tool.tpl' ),
    'template' => array( 'index.php', 'templates/default/index.tpl' ),
    'wysiwyg'  => array( 'index.php', 'c_editor.php' ),
);
$dirs_needed = array(
    'page'     => array( 'languages', 'templates', 'templates/default', 'css', 'js' ),
    'library'  => array( 'vendor' ),
    'tool'     => array( 'languages', 'templates', 'templates/default', 'css', 'js' ),
    'template' => array( 'languages', 'templates', 'templates/default', 'css', 'js' ),
    'wysiwyg'  => array( ),
);
$info_prefix = array(
    'module'   => 'module_',
    'library'  => 'module_',
    'tool'     => 'module_',
    'template' => 'template_',
    'language' => 'language_',
    'wysiwyg'  => 'module_',
);
$type_path   = array(
    'page'     => 'modules',
    'library'  => 'modules',
    'tool'     => 'modules',
    'template' => 'templates',
    'language' => 'languages',
    'wysiwyg'  => 'modules',
);

// ----- create directories -----
if( $type != 'language' )
{
    $full = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$type_path[$func].'/'.$dir);
    // base
    if(!CAT_Helper_Directory::createDirectory($full))
    {
        CAT_Object::json_error('Directory could not be created!'." ($full)");
    }
    // subdirs
    foreach($dirs_needed[$func] as $sub)
        CAT_Helper_Directory::createDirectory($full.'/'.$sub);
    // create index.php
    CAT_Helper_Directory::recursiveCreateIndex($full);
}
else
{
    $full = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/languages/'.$dir.'.php');
}

// ----- create info.php -----
$info = module_create_info($full,$type,$name,$dir,$func,$desc,$author);

// ----- create extra files -----
if(in_array($type,array('module','template')) && $headinc == 'Y')
    $files_needed[$func][] = 'headers.inc.php';
if(in_array($type,array('module','template')) && $footinc == 'Y')
    $files_needed[$func][] = 'footers.inc.php';
if($precheck == 'Y')
    $files_needed[$func][] = 'precheck.php';

if( $type != 'language' )
{
    foreach($files_needed[$func] as $file)
    {
        $ext = pathinfo($file,PATHINFO_EXTENSION);
        $fh  = fopen($full.'/'.$file,'w');
        if($fh)
        {
            if($ext !== 'css' && $ext != 'tpl' && substr_compare('languages',$file,0,9))
            {
                module_create_writeHeader($fh,$name,$author,$type);
            }
            if(function_exists('code_for_'.str_replace('.','_', pathinfo($file,PATHINFO_BASENAME))))
            {
                $func = 'code_for_'.str_replace('.','_',pathinfo($file,PATHINFO_BASENAME));
                $func($fh,$type);
            }
            fclose($fh);
        }
    }
}

// ----- if it's a template... -----
if($type=='template')
{
    $contents  = file_get_contents($full.'/index.php');
    $contents .= '
$dwoodata	= array(); // if you need to set some additional template vars, add them here
global $page_id;
$variant  = CAT_Helper_Page::getPageSettings($page_id,\'internal\',\'template_variant\');
if(!$variant)
    $variant = ( defined(\'DEFAULT_TEMPLATE_VARIANT\') && DEFAULT_TEMPLATE_VARIANT != \'\' )
             ? DEFAULT_TEMPLATE_VARIANT
             : \'default\';
$parser->setPath(CAT_TEMPLATE_DIR.\'/templates/\'.$variant);
$parser->setFallbackPath(CAT_TEMPLATE_DIR.\'/templates/default\');
$parser->output(\'index.tpl\',$dwoodata);
';
    file_put_contents($full.'/index.php', $contents);
}

// insert module into DB
foreach($info as $key => $value)
{
    $key = str_replace($pre,'module_',$key);
    $info[$key] = $value;
}
$info['addon_function'] = $type;

CAT_Helper_Addons::loadModuleIntoDB( $dir, 'install', $info);

CAT_Object::json_success('Module created successfully');

/**
 * checks if addon dir / file already exists
 **/
function module_create_check_dir($type,$dir)
{
    $err = false;
    if( $type != 'language' )
    {
        if( $type == 'template' )
        {
            $full = CAT_PATH.'/templates/'.$dir;
            if( file_exists($full) )
                $err = 'A template with the same directory name already exists';
        }
        else
        {
            $full = CAT_PATH.'/modules/'.$dir;
            if( file_exists($full) )
                $err = 'A module with the same directory name already exists';
        }
    }
    else
    {
        $dir  = strtoupper($dir);
        $full = CAT_PATH.'/languages/'.$dir.'.php';
        if( file_exists($full) )
            $err = 'A language file with the same name already exists';
    }
    if($err) CAT_Object::json_error($err);
    else     return true;
}

function module_create_info($full,$type,$name,$dir,$func,$desc,$author)
{
    global $info_prefix;
    // data for info.php
    $pre  = $info_prefix[$type];
    $info = array(
        $pre.'name'        => $name,
        $pre.'directory'   => $dir,
        $pre.'type'        => $type,
        $pre.'function'    => $func,
        $pre.'description' => $desc,
        $pre.'version'     => '0.1',
        $pre.'platform'    => '1.x',
        $pre.'author'      => $author,
        $pre.'guid'        => CAT_Object::createGUID(),
        $pre.'license'     => 'GNU General Public License',
    );
    if($type == 'language')
    {
        $info[$pre.'code'] = $dir;
    }

    // create info.php
    if ( $type !== 'language' )
        $fh = fopen($full.'/info.php','w');
    else
        $fh = fopen($full,'w');

    if(!$fh)
    {
        CAT_Object::json_error('Unable to create info.php!');
    }
    module_create_writeHeader($fh,$name,$author,$type);
    foreach($info as $key => $value )
    {
        if($type=='language' && $key == 'language_directory') continue;
        fwrite($fh,'$'.$key.' = "'.$value.'";'."\n");
    }
    if($type == 'language')
    {
        fwrite($fh,'
    $LANG = array(

    );
    ');
    }
    fclose($fh);

    return $info;
}

function module_create_writeHeader($fh,$name,$author,$type)
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
 *   @copyright       '.date('Y').', '.$author.'
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_'.ucfirst($type).'s
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
}   // end function module_create_writeHeader()

function code_for_footers_inc_php($fh,$type)
{
    switch($type)
    {
        case 'module':
            fwrite($fh,'
$mod_footers = array(
    \'backend\'    => array(
        \'js\'     => array(),
    ),
    \'frontend\' => array(
        \'js\'  => array(),
    ),
);
');
            break;
        case 'template':
            fwrite($fh,'
$mod_footers = array(
    \'frontend\' => array(
        \'js\'  => array(),
    ),
);
');
            break;
    }
}

function code_for_headers_inc_php($fh,$type)
{
    global $jquery, $ui;
    $use_jquery = ( $jquery && $jquery == 'Y' ) ? 'true' : 'false';
    $use_ui     = ( $ui     && $ui     == 'Y' ) ? 'true' : 'false';
    switch($type)
    {
        case 'module':
            fwrite($fh,'
$mod_headers = array(
    \'backend\'    => array(
        \'js\'     => array(),
        \'css\'    => array(),
        \'jquery\' => array( \'core\' => '.$use_jquery.', \'ui\' => '.$use_ui.' ),
        \'meta\'   => array(),
    ),
    \'frontend\' => array(
        \'js\'  => array(),
        \'css\' => array(),
    ),
);
');
            break;
        case 'template':
            fwrite($fh,'
$mod_headers = array(
    \'frontend\' => array(
        \'js\'  => array(),
        \'css\' => array(),
        \'jquery\' => array( \'core\' => '.$use_jquery.', \'ui\' => '.$use_ui.' ),
    ),
);

global $page_id;
$variant  = CAT_Helper_Page::getPageSettings($page_id,\'internal\',\'template_variant\');
if(!$variant)
    $variant = ( defined(\'DEFAULT_TEMPLATE_VARIANT\') && DEFAULT_TEMPLATE_VARIANT != \'\' )
             ? DEFAULT_TEMPLATE_VARIANT
             : \'default\';

');
            break;
    }
}

function code_for_index_tpl($fh,$type)
{
    fwrite($fh,'<!doctype html>
  <html>
  <head>
	{get_page_headers}
  </head>
  <body>

  </body>
</html>
');
}

function code_for_c_editor_php($fh,$type)
{
    fwrite($fh,'
require CAT_Helper_Directory::sanitizePath(realpath(dirname(__FILE__).\'/../wysiwyg_admin/c_editor_base.php\'));
final class c_editor extends c_editor_base
{
    protected static $default_skin   = \'\';
    protected static $editor_package = \'standard\';

    public function getFilemanagerPath()
    {
    }

    public function getSkinPath()
    {
    }

    public function getPluginsPath()
    {
    }

    public function getToolbars()
    {
    }

    public function getAdditionalSettings()
    {
    }

    public function getAdditionalPlugins()
    {
    }

}
');
}

function code_for_precheck_php($fh,$type)
{
    fwrite($fh,'

// if your module requires a particular core version, insert it here:
// $PRECHECK[\'CAT_VERSION\'] = \'\';

// if your module requires particular addons, insert them here:
// $PRECHECK[\'CAT_ADDONS\']  = array();

');
}