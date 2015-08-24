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
 *   This file is for backward compatibility with Website Baker 2.8.x and
 *   LEPTON 1.x, and it's useful for direct calls from the template.
 *   All methods are moved to CAT_* classes.
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

// 
// include snippets
//
$snippets = CAT_Helper_Addons::get_addons(0,'module','snippet');
foreach($snippets as $s)
{
    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$s['VALUE'].'/include.php');
    if(file_exists($file))
        include $file;
}


/* 'one liners' */
function get_page_link($page_id)  { return CAT_Helper_Page::properties($page_id,'link'); }
function page_content($block=1)   { global $page_id; return CAT_Page::getInstance($page_id)->getPageContent($block); }
function page_header($mode=false) { if($mode) return WEBSITE_HEADER; echo WEBSITE_HEADER; }

if(!function_exists('page_link'))
{
    function page_link($link)         { return CAT_Helper_Page::getLink($link); }
}

function page_description($mode=false)
{
    global $page_id;
    $value = CAT_Page::getInstance($page_id)->getDescription();
    if($mode) return $value;
    echo $value;
}   // end function page_description()

function page_footer( $date_format = 'Y', $mode = false )
{
	global $starttime;
    $vars   = array( '[YEAR]', '[PROCESS_TIME]' );
    $ptime  = array_sum( explode( " ", microtime() ) ) - $starttime;
    $values = array( date($date_format), $ptime );
    $temp   = str_replace( $vars, $values, WEBSITE_FOOTER );
        if ( true === $mode )
        {
            return $temp;
        }
        else
        {
            echo $temp;
        #return true;
        }
}   // end function page_footer()

function page_keywords($mode=false)
{
    global $page_id;
    $temp = CAT_Page::getInstance($page_id)->getKeywords();
    if($mode) return $temp;
            echo $temp;
}   // end function page_keywords()

function page_title($spacer=' - ',$template='[WEBSITE_TITLE][SPACER][PAGE_TITLE]',$mode=false)
{
    $vars   = array('[WEBSITE_TITLE]', '[PAGE_TITLE]', '[MENU_TITLE]', '[SPACER]');
	$values = array(CAT_Registry::get('WEBSITE_TITLE'), CAT_Registry::get('PAGE_TITLE'), CAT_Registry::get('MENU_TITLE'), $spacer);
	$temp = str_replace($vars, $values, $template);
	if ( true === $mode ) {
            return $temp;
	} else {
            echo $temp;
            return true;
        }
}   // end function page_title()
