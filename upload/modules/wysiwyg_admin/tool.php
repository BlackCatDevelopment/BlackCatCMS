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
 *   @category        CAT_Modules
 *   @package         wysiwyg_admin
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


// backend only
$backend = CAT_Backend::getInstance('admintools');
$user    = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('admintools','admintools');

// check if any editor is set
if(!defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR == '')
    $admin->print_error('No WYSIWYG editor set, please set one first (Settings -&gt; Backend settings -&gt; WYSIWYG Editor)',NULL,false);

// check for config driver
$cfg_file = sanitize_path(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/c_editor.php');
if(file_exists($cfg_file))
{
    require $cfg_file;
}
elseif(file_exists(sanitize_path(dirname(__FILE__)."/driver/".WYSIWYG_EDITOR."/c_editor.php")))
{
    require_once( dirname(__FILE__)."/driver/".WYSIWYG_EDITOR."/c_editor.php");
}
else {
    $admin->print_error($backend->lang()->translate('No configuration file for editor [{{editor}}]',array('editor'=>WYSIWYG_EDITOR)),NULL,false);
}

// check for language file
if (file_exists(sanitize_path(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/languages/'.LANGUAGE.'.php')))
{
    $backend->lang()->addFile(LANGUAGE.'.php',sanitize_path(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/languages'));
}

$config       = wysiwyg_admin_config();

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
$plugins      = $c->getAdditionalPlugins();
$filemanager  = $c->getFilemanager();
$toolbars     = $c->getToolbars();
$preview      = NULL;
$plugins_checked = array();
$filemanager_checked = array();

$enable_htmlpurifier = ( isset($config['enable_htmlpurifier'])
                     ? $config['enable_htmlpurifier']
                     : false );

if(file_exists(sanitize_path(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/images/'.$current_skin.'.png')))
{
    $preview = '<img src="'
             . sanitize_url(CAT_URL.'/modules/'.WYSIWYG_EDITOR.'/images/'.$current_skin.'.png')
             . '" alt="'.$current_skin.'" title="'.$current_skin.'" />';
}

// something to save?
$job = $val->sanitizePost('job');

if ($job && $job=="save") {
    $_POST = array_map("wysiwyg_admin_escape",$_POST);
    $new_width = $new_height = $new_skin = $new_toolbar = $new_plugins = $new_fm = NULL;
    // validate width and height
    foreach( array('width','height') as $key )
    {
        if ( $val->sanitizePost($key) )
        {
            if ( ! is_numeric($val->sanitizePost($key)) )
            {
                $errors[$key] = $backend->lang()->translate('Not numeric!');
                continue;
            }
            if ( $val->sanitizePost($key.'_unit') && in_array($val->sanitizePost($key.'_unit'),array('em','px','%')) )
            {
                if ( $val->sanitizePost($key.'_unit') == '%' && $val->sanitizePost($key) > 100 )
                {
                    $errors[$key] = $backend->lang()->translate('Invalid '.$key.': {{width}}% > 100%!', array('width'=>$val->sanitizePost($key)));
                    continue;
                }
                if ( $val->sanitizePost($key) > 10000 )
                {
                    $errors[$key] = $backend->lang()->translate('Invalid '.$key.': Too large! (>10000)');
                    continue;
                }
            }
            ${$key}         = $val->sanitizePost($key);
            ${$key.'_unit'} = $val->sanitizePost($key.'_unit');
        }
    }
    // check skin
    if ( $val->sanitizePost('skin') )
    {
        if ( ! in_array($val->sanitizePost('skin'),$skins) )
    {
            $errors[$key] = $backend->lang()->translate('Invalid skin!');
        continue;
    }
        else
        {
            $new_skin = $val->sanitizePost('skin');
        }
    }
    // check HTMLPurifier
    if (
           CAT_Helper_Addons::getInstance()->isModuleInstalled('lib_htmlpurifier')
        && $val->sanitizePost(enable_htmlpurifier)
        && $val->sanitizePost('enable_htmlpurifier') == 'true'
    ) {
        $enable_htmlpurifier = true;
    }
    else {
        $enable_htmlpurifier = false;
    }
    // check toolbar
    if($val->sanitizePost('toolbar') )
    {
        if ( ! in_array($val->sanitizePost('toolbar'),$toolbars) )
        {
            $errors[$key] = $backend->lang()->translate('Invalid toolbar!');
            continue;
        }
        else
        {
            $new_toolbar = $val->sanitizePost('toolbar');
        }
    }

    // check additionals
    if(count($settings))
    {
        foreach($settings as $item)
        {
            if ( ! isset($_POST[$item['name']]) ) $_POST[$item['name']] = $item['default'];
            if ( $item['type'] == 'boolean' && ( $_POST[$item['name']] != 'true' && $_POST[$item['name']] != 'false' ) )
            {
                $errors[$item['name']] = $backend->lang()->translate('Invalid boolean value!');
                continue;
            }
            
        }
    }
    // check plugins
    if(isset($_POST['plugins']) && count($_POST['plugins']))
    {
        // check against $plugins array
        $unknown = array_diff($_POST['plugins'],$plugins);
        if(count($unknown))
        {
            $errors['plugins'] = $backend->lang()->translate('Invalid plugin(s) encountered!');
        }
        else
        {
            $new_plugins = implode(',',$_POST['plugins']);
        }
    }
    // check filemanager
    if($val->sanitizePost('filemanager'))
    {
        $fm    = $val->sanitizePost('filemanager');
        $known = array_keys($filemanager);
        if(! in_array($fm,$known) )
        {
            $errors['filemanager'] = $backend->lang()->translate('Invalid filemanager!');
        }
        else
        {
            $new_fm = $fm;
        }
    }

    // only save changes if there were no errors
    if ( ! count($errors) )
    {
        $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'width\', \''.$width.$width_unit.'\' )' );
        $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'height\', \''.$height.$height_unit.'\' )' );
        $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'enable_htmlpurifier\', \''.$enable_htmlpurifier.'\' )' );
        // save additionals
        if(count($settings))
        {
            foreach($settings as $item)
            {
                $value = NULL;
                if(!isset($_POST[$item['name']]))
                {
                    if(isset($item['default']))
                    {
                        $value = $item['default'];
                    }
                }
                else
                {
                    $value = $_POST[$item['name']];
                }
                $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \''.$item['name'].'\', \''.$value.'\' )' );
            }
        }
        // save plugins
        if($new_plugins)
        {
            $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'plugins\', \''.$new_plugins.'\' )' );
        }
        if($new_fm)
        {
            $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'filemanager\', \''.$new_fm.'\' )' );
        }
        if($new_toolbar)
        {
            $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'toolbar\', \''.$new_toolbar.'\' )' );
        }
        if($new_skin)
        {
            $backend->db()->query( 'REPLACE INTO '.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2 VALUES ( \''.WYSIWYG_EDITOR.'\', \'skin\', \''.$new_skin.'\' )' );
        }
        // reload settings
        $config       = wysiwyg_admin_config();
    }
}

if ( ( isset($config['plugins']) && $config['plugins'] != '' ) )
{
    $seen = explode(',',$config['plugins']);
    foreach($seen as $item)
    {
        $plugins_checked[$item] = 1;
    }
}

if ( ( isset($config['filemanager']) && $config['filemanager'] != '' ) )
{
    $filemanager_checked[$config['filemanager']] = true;
}

$parser->setPath(dirname(__FILE__)."/templates/default");
$parser->output(
    'tool',
    array(
        'width_unit_em'    => '',
        'width_unit_px'    => '',
        'width_unit_proz'  => '',
        'height_unit_em'   => '',
        'height_unit_px'   => '',
        'height_unit_proz' => '',
        'action'           => CAT_ADMIN_URL.'/admintools/tool.php?tool=wysiwyg_admin',
        'id'               => WYSIWYG_EDITOR,
        'skins'            => $skins,
        'toolbars'         => $toolbars,
        'current_toolbar'  => $c->getToolbar($config),
        'width'            => $width,
        'height'           => $height,
        'current_skin'     => $c->getSkin($config),
        'preview'          => $preview,
        'settings'         => $settings,
        'config'           => $config,
        'errors'           => $errors,
        'plugins'          => $plugins,
        'filemanager'      => $filemanager,
        'plugins_checked'  => $plugins_checked,
        'filemanager_checked' => $filemanager_checked,
        'htmlpurifier'        => CAT_Helper_Addons::isModuleInstalled('lib_htmlpurifier'),
        'enable_htmlpurifier' => $enable_htmlpurifier,
        'width_unit_'.($width_unit=='%'?'proz':$width_unit) => 'checked="checked"',
        'height_unit_'.($height_unit=='%'?'proz':$height_unit) => 'checked="checked"',
    )
);

function wysiwyg_admin_escape($item) {
    return is_scalar( $item )
         ? mysql_real_escape_string($item)
         : array_map("wysiwyg_admin_escape",$item)
         ;
}

// get current settings
function wysiwyg_admin_config() {
    global $backend;
    $query  = "SELECT * from `".CAT_TABLE_PREFIX."mod_wysiwyg_admin_v2` where `editor`='".WYSIWYG_EDITOR."'";
    $result = $backend->db()->query ($query );
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
    return $config;
}