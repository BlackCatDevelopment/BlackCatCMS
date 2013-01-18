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
 *   @author          LEPTON v2.0 Black Cat Edition Development
 *   @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 *   @link            http://www.lepton2.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        LEPTON2BCE_Modules
 *   @package         wysiwyg_admin
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
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
// end include class.secure.php

if ( !defined('WB_PATH')) die(header('Location: ../../index.php'));

$debug = false;
if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

if (!isset($admin) || !is_object($admin)) die();

// check for config driver
$cfg_file = sanitize_path(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/c_editor.php');
if(file_exists($cfg_file))
{
    require $cfg_file;
}
elseif(file_exists(sanitize_path(dirname(__FILE__)."/driver/".WYSIWYG_EDITOR."/c_editor.php")))
{
    require_once( dirname(__FILE__)."/driver/".WYSIWYG_EDITOR."/c_editor.php");
}
else {
    $admin->print_error($admin->lang->translate('No configuration file for editor ['.WYSIWYG_EDITOR.']'));
}

// check for language file
if (file_exists(sanitize_path(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/languages/'.LANGUAGE.'.php')))
{
    $admin->lang->addFile(LANGUAGE.'.php',sanitize_path(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/languages'));
}

// get current settings
$query  = "SELECT * from `".TABLE_PREFIX."mod_wysiwyg_admin_v2` where `editor`='".WYSIWYG_EDITOR."'";
$result = $database->query ($query );
$config = array();
if($result->numRows())
{
    while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
    {
        if ( substr_count( $row['set_value'], '#####' ) ) // array values
        {
            $row['set_value'] = explode( '#####', $row['set_value'] );
        }
        $config[$row['set_name']] = $row['set_value'];
    }
}

// load driver class
$c            = new c_editor();
$errors       = array();
$width_unit   = $height_unit = '%';
$width        = $c->getWidth($config);
$height       = $c->getHeight($config);
if(preg_match('~(\d+)(.*)~',$width,$match))
{
    $width = $match[1];
    $width_unit = $match[2];
}
if(preg_match('~(\d+)(.*)~',$height,$match))
{
    $height = $match[1];
    $height_unit = $match[2];
}

$skins        = $c->getSkins($c->getSkinPath());
$current_skin = $c->getSkin($config);
$settings     = $c->getAdditionalSettings();
$preview      = NULL;

if(file_exists(sanitize_path(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/images/'.$current_skin.'.png')))
{
    $preview = '<img src="'
             . sanitize_url(LEPTON_URL.'/modules/'.WYSIWYG_EDITOR.'/images/'.$current_skin.'.png')
             . '" alt="'.$current_skin.'" title="'.$current_skin.'" />';
}

// something to save?
if (isset($_POST['job']) && $_POST['job']=="save") {
    $_POST = array_map("mysql_real_escape_string",$_POST);
    $new_width = $new_height = $new_skin = NULL;
    // validate width and height
    foreach( array('width','height') as $key )
    {
        if ( isset($_POST[$key]) )
        {
            if ( ! is_numeric($_POST[$key]) )
            {
                $errors[$key] = $admin->lang->translate('Not numeric!');
                continue;
            }
            if ( isset($_POST[$key.'_unit']) && in_array($_POST[$key.'_unit'],array('em','px','%')) )
            {
                if ( $_POST[$key.'_unit'] == '%' && $_POST[$key] > 100 )
                {
                    $errors[$key] = $admin->lang->translate('Invalid '.$key.': {{width}}% > 100%!', array('width'=>$_POST[$key]));
                    continue;
                }
                if ( $_POST[$key] > 10000 )
                {
                    $errors[$key] = $admin->lang->translate('Invalid '.$key.': Too large! (>10000)');
                    continue;
                }
            }
            ${$key} = $_POST[$key];
            ${$key.'_unit'} = $_POST[$key.'_unit'];
        }
    }
    // check skin
    if ( isset($_POST['skin']) && ! in_array($_POST['skin'],$skins) )
    {
        $errors[$key] = $admin->lang->translate('Invalid skin!');
        continue;
    }
    // check additionals
    if(count($settings))
    {
        foreach($settings as $item)
        {
            if ( ! isset($_POST[$item['name']]) ) $_POST[$item['name']] = $item['default'];
            if ( $item['type'] == 'boolean' && ( $_POST[$item['name']] != 'true' && $_POST[$item['name']] != 'false' ) )
            {
                $errors[$item['name']] = $admin->lang->translate('Invalid boolean value!');
                continue;
            }
            
        }
    }

    // only save changes if there were no errors
    if ( ! count($errors) )
    {
        $database->query( 'REPLACE INTO '.TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'width\', \''.$width.$width_unit.'\' )' );
        $database->query( 'REPLACE INTO '.TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'height\', \''.$height.$height_unit.'\' )' );
        $database->query( 'REPLACE INTO '.TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'skin\', \''.$_POST['skin'].'\' )' );
    }
}


$parser->setPath(dirname(__FILE__)."/templates/default");
echo $parser->get(
    'tool.lte',
    array(
        'width_unit_em'    => '',
        'width_unit_px'    => '',
        'width_unit_proz'  => '',
        'height_unit_em'   => '',
        'height_unit_px'   => '',
        'height_unit_proz' => '',
        'action'           => ADMIN_URL.'/admintools/tool.php?tool=wysiwyg_admin',
        'id'               => WYSIWYG_EDITOR,
        'skins'            => $skins,
        'toolbars'         => $c->getToolbars(),
        'width'            => $width,
        'height'           => $height,
        'current_skin'     => $current_skin,
        'preview'          => $preview,
        'settings'         => $settings,
        'config'           => $config,
        'errors'           => $errors,
        'width_unit_'.($width_unit=='%'?'proz':$width_unit) => 'checked="checked"',
        'height_unit_'.($height_unit=='%'?'proz':$height_unit) => 'checked="checked"',
    )
);