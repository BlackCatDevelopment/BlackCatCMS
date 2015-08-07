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

// check permissions
$backend = CAT_Backend::getInstance('Pages', 'pages_seo');

// check page_id
$page_id = CAT_Helper_Validate::get('_REQUEST', 'page_id', 'numeric');
if (!$page_id)
{
    header("Location: index.php");
    exit(0);
}

if (!CAT_Helper_Page::getPagePermission($page_id, 'admin'))
{
    $backend->print_error('You do not have permissions to modify this page');
}

$tpl_data = array();

include dirname(__FILE__).'/setglobals.php';
setglobals($page_id);

$tpl_data['CUR_TAB']        = 'seo';
$tpl_data['PAGE_HEADER']    = $backend->lang()->translate('Modify SEO settings');

// get the form
$form = $backend->getForms('pages');
$form->setForm('seo');

if($form->isSent() && $form->isValid())
{
    $data = $form->getData(1,1);
    $sql  = 'INSERT INTO `:prefix:pages_settings` ( `page_id`, `set_type`, `set_name`, `set_value` ) VALUES ( ?, ?, ?, ?)';
    $sql2 = 'DELETE FROM `:prefix:pages_settings` WHERE `page_id`=?';
    // delete old settings
    $database->query($sql2,array($page_id));
    // insert new settings
    foreach($data as $key => $value)
    {
        // skip setting if default value is set
        if($key == 'sitemap_priority' && $value == '0.5')       continue;
        if($key == 'sitemap_include'  && $value == 'auto')      continue;
        if($key == 'sitemap_update_freq' && $value == 'weekly') continue;
        // insert new setting
        if(!is_array($value))
        {
            $database->query($sql, array($page_id,'seo',$key,$value));
        }
        else
        {
            if($key == 'robots' || $key == 'robots_advanced')
            {
                $new = array();
                foreach(array_values($value) as $v)
                {
                    $new[] = $v;
                }
                $database->query($sql, array($page_id,'seo',$key,implode(',',$new)));
            }
        }
    }
    CAT_Helper_Page::reset(); // reload page cache
}

// get current settings
$page  = CAT_Helper_Page::getPage($page_id);
$data   = $page['settings']['seo'];
$fdata  = array();
$robots = array();
foreach($data as $key => $value)
{
    if($key=='robots')
    {
        $fdata['robots'] = explode(',',$value[0]);
    }
    else
    {
        $fdata[$key] = $value[0];
    }
}

$form->setData($fdata);
$tpl_data['form'] = $form->getForm();

/*
Array
(
    [link] => /comments
    [page_title] => Comments
    [menu_title] => Comments
    [description] =>
    [keywords] =>
    [language] => DE
    [modified_when] => 1438260895
    [href] => //localhost/blackcat/bcwa12/page/comments.php
)
*/
// seo check
if($page['keywords']!='')
{
    $keywords = preg_split('~[\s,]+~',$page['keywords']);
    $i        = 1;
    foreach(array_values($keywords) as &$k)
    {
        if($i==5) break; // only the first 3 keywords
        $k = strtolower(trim($k));
        if(substr_count(strtolower($page['href']),$k))        $tpl_data['keyword_in_url'] = true;
        if(substr_count(strtolower($page['page_title']),$k))  $tpl_data['keyword_in_title'] = true;
        if(substr_count(strtolower($page['description']),$k)) $tpl_data['keyword_in_meta'] = true;
        $i++;
    }
}
if(strlen($page['page_title'])<=55)   $tpl_data['title_length'] = true;
if(strlen($page['description'])<=156) $tpl_data['descr_length'] = true;

$parser->output('backend_pages_seo', $tpl_data);
$backend->print_footer();
