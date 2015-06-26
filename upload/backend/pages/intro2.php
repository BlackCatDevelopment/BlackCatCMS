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

$backend  = CAT_Backend::getInstance('Pages', 'pages_intro');
$data     = $_REQUEST;
$content  = NULL;
$filename = CAT_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;

if(isset($data['intro_forward_by']))
{
    switch($data['intro_forward_by'])
    {
        case 'lang':
            $lang_map = array();
            foreach(array_keys($data) as $key)
            {
                if(preg_match('~^intro_page_for_([a-z][a-z])$~i',$key,$m))
                {
                    $lang_map[strtolower($m[1]).'-?.*'] = $data[$key];
                }
            }
            if(isset($data['intro_page_for_default']))
            {
                $lang_map['default'] = $data['intro_page_for_default'];
            }
            if(!isset($lang_map['default']))
            {
                $lang_map['default'] = CAT_Helper_Page::getDefaultPage();
            }
            $content = str_ireplace(
                '%map%',
                var_export($lang_map,1),
                cat_intro_page_content_langswitch()
            );
            $backend->db()->query(
                'UPDATE `:prefix:settings` SET `value`=:value WHERE `name`=:name',
                array('value'=>'lang','name'=>'intro_forward_by')
            );
            break;
        case 'domain':
            if(!isset($data['domains']) || !isset($data['pages'])) break;
            if(!is_array($data['domains']) && is_scalar($data['domains'])) $data['domains'] = array($data['domains']);
            if(!is_array($data['pages'])   && is_scalar($data['pages']))   $data['pages']   = array($data['pages']);
            // default page
            $default = array_pop($data['pages']);
            if(count($data['domains']) != count($data['pages']))   break;
            $domain_map = array();
            for($i=0;$i<count($data['domains']);$i++)
            {
                $domain_map[$data['domains'][$i]] = $data['pages'][$i];
            }
            $domain_map['default'] = $default;
            $content = str_ireplace(
                '%map%',
                var_export($domain_map,1),
                cat_intro_page_content_domainswitch()
            );
            $backend->db()->query(
                'UPDATE `:prefix:settings` SET `value`=:value WHERE `name`=:name',
                array('value'=>'domain','name'=>'intro_forward_by')
            );
            $backend->db()->query(
                'UPDATE `:prefix:settings` SET `value`=:value WHERE `name`=:name',
                array('value'=>serialize($domain_map),'name'=>'intro_forward_by_domain_settings')
            );
            break;
        case 'disabled':
        default:
            if(file_exists($filename))
                unlink($filename);
            $backend->print_success($backend->lang()->translate('Intro page saved'), 'intro.php');
            $backend->print_footer();
            break;
    }
}

if($content)
{
	$handle   = fopen($filename, 'w');
	if(is_writable($filename)) {
        $content  = '<'.'?'.'php'."\n".$content;
		if(fwrite($handle, $content)) {
			fclose($handle);
			$backend->print_success($backend->lang()->translate('Intro page saved'), 'intro.php');
		} else {
			fclose($handle);
			$backend->print_error($backend->lang()->translate('Intro page not writable!'), 'intro.php');
		}
	} else {
		$backend->print_error($backend->lang()->translate('Intro page not writable!'), 'intro.php');
	}
}

// Print admin footer
$backend->print_footer();

function cat_intro_page_content_langswitch()
{
    // path to config.php
    $page_path = PAGES_DIRECTORY;
    if(!substr_compare($page_path,'/',0,1)) $page_path = substr_replace($page_path,'',0,1);
    $count     = explode('/',$page_path);

    return '
require_once dirname(__FILE__)."' . ( count($count) > 0 ? str_repeat('/..',count($count)) : '' ) . '/config.php";

$cat_intro_lang_to_page_map = %map%;
$page_id      = NULL;
require dirname(__FILE__)."/../framework/CAT/Helper/I18n.php";
require dirname(__FILE__)."/../framework/CAT/Helper/Page.php";
$lang         = new CAT_Helper_I18n();
$langs        = $lang->getBrowserLangs();
foreach($langs as $l)
{
    foreach(array_keys($cat_intro_lang_to_page_map) as $m)
    {
        if(preg_match("~$m~i",$l,$match))
        {
            $page_id = $cat_intro_lang_to_page_map[$m];
            break 2;
        }
    }
}
if(!$page_id) $page_id = $cat_intro_lang_to_page_map["default"];
$properties = CAT_Helper_Page::getPage($page_id);
echo header("Location: ".$properties["href"]);
exit();
';
}

function cat_intro_page_content_domainswitch()
{
    // path to config.php
    $page_path = PAGES_DIRECTORY;
    if(!substr_compare($page_path,'/',0,1)) $page_path = substr_replace($page_path,'',0,1);
    $count     = explode('/',$page_path);

    return '
require_once dirname(__FILE__)."' . ( count($count) > 0 ? str_repeat('/..',count($count)) : '' ) . '/config.php";

$cat_intro_domain_to_page_map = %map%;
$page_id      = NULL;
$domain       = $_SERVER["HTTP_HOST"];
if(array_key_exists($domain,$cat_intro_domain_to_page_map))
{
    $page_id = $cat_intro_domain_to_page_map[$key];
}
if(!$page_id) $page_id = CAT_Helper_Page::getDefaultPage();
$properties = CAT_Helper_Page::getPage($page_id);
echo header("Location: ".$properties["href"]);
exit();
';
}