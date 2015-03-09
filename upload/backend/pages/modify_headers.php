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

include 'functions.php';

$val = CAT_Helper_Validate::getInstance();

// ===============
// ! Get page id
// ===============
$page_id = $val->get('_REQUEST', 'page_id', 'numeric');
if (!$page_id)
{
    header("Location: index.php");
    exit(0);
}


$backend = CAT_Backend::getInstance('Pages', 'pages_settings');
$page    = CAT_Helper_Page::getPage($page_id);
$user    = CAT_Users::get_user_details( $page['modified_by'] );
$files   = CAT_Helper_Page::getExtraHeaderFiles($page_id);

// ==================================
// ! Add globals to the template data
// ==================================
$tpl_data['CUR_TAB']              = 'headers';
$tpl_data['PAGE_HEADER']          = $backend->lang()->translate('Modify header files');
$tpl_data['PAGE_ID']			  = $page_id;
$tpl_data['PAGE_LINK']			  = CAT_Helper_Page::getLink($page['link']);
$tpl_data['PAGE_TITLE']			  = $page['page_title'];
$tpl_data['MODIFIED_BY']		  = $user['display_name'];
$tpl_data['MODIFIED_BY_USERNAME'] = $user['username'];
$tpl_data['MODIFIED_WHEN']		  = ($page['modified_when'] != 0)
                                  ? CAT_Helper_DateTime::getDateTime($page['modified_when'])
                                  : false;
$tpl_data['page_js']              = isset($files['js'])  ? $files['js']  : '';
$tpl_data['page_css']             = isset($files['css']) ? $files['css'] : '';
$tpl_data['use_core']             = isset($files['use_core']) ? $files['use_core'] : NULL;
$tpl_data['use_ui']               = isset($files['use_ui'])   ? $files['use_ui']   : NULL;

$tpl_data['jquery_plugins']       = CAT_Helper_Directory::getInstance()
                                    ->maxRecursionDepth(0)
                                    ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins',false,false,CAT_PATH.'/modules/lib_jquery/plugins/');
$tpl_data['js_files']             = CAT_Helper_Directory::getInstance()
                                    ->maxRecursionDepth(5)
                                    ->setSuffixFilter(array('js'))
                                    ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins',true,true,CAT_PATH.'/modules/lib_jquery/plugins');
$tpl_data['css_files']            = CAT_Helper_Directory::getInstance()
                                    ->maxRecursionDepth(5)
                                    ->setSuffixFilter(array('css'))
                                    ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins',true,true,CAT_PATH.'/modules/lib_jquery/plugins');

// CSS files in CKEditor (if installed); useful for Font Awesome integration, for example
if(CAT_Helper_Addons::isModuleInstalled('ckeditor4','0.21'))
{
    $plugin_css = CAT_Helper_Directory::getInstance()
                ->maxRecursionDepth(5)
                ->setSuffixFilter(array('css'))
                ->setSkipDirs(array('codemirror','codesnippet'))
                ->scanDirectory(CAT_PATH.'/modules/ckeditor4/ckeditor/plugins',true,true,CAT_PATH.'/modules/ckeditor4/ckeditor/plugins');
    if(count($plugin_css))
    {
        $tpl_data['ckeditor_files'] = $plugin_css;
    }
}

$backend->print_header();

// ====================
// ! Parse output tpl
// ====================
$parser->output('backend_pages_headerfiles', $tpl_data);

// ======================
// ! Print admin footer
// ======================
$backend->print_footer();