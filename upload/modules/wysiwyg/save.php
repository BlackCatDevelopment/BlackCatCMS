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

$update_when_modified = true;

$val     = CAT_Helper_Validate::getInstance();
$user    = CAT_Users::getInstance();
$backend = CAT_Backend::getInstance('Pages', 'pages_modify');

// ===============
// ! Get page id
// ===============
$page_id    = $val->get('_REQUEST','page_id','numeric');
$section_id = $val->get('_REQUEST','section_id','numeric');

if ( !$page_id )
{
	header("Location: index.php");
	exit(0);
}

// =============
// ! Get perms
// =============
if ( CAT_Helper_Page::getPagePermission($page_id,'admin') !== true )
{
	$backend->print_error( 'You do not have permissions to modify this page!' );
}

// =================
// ! Get new content
// =================
$content = $val->sanitizePost('content'.$section_id);

// if HTMLPurifier is enabled...
$r = $backend->db()->get_one(
    'SELECT * FROM `:prefix:settings` WHERE `name`="enable_htmlpurifier" AND `value`="true"'
);
if($r)
{
    // use HTMLPurifier to clean up the contents
    $content = CAT_Helper_Protect::getInstance()->purify($content,array('Core.CollectErrors'=>true));
}

/**
 *	searching in $text will be much easier this way
 */
$text = umlauts_to_entities(strip_tags($content), strtoupper(DEFAULT_CHARSET), 0);

/**
 *  save
 **/
$query = "REPLACE INTO `:prefix:mod_wysiwyg` VALUES ( ?, ?, ?, ? );";
$backend->db()->query($query,array($section_id, $page_id, $content, $text));
$edit_page = CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.SEC_ANCHOR.$section_id;

// Check if there is a database error, otherwise say successful
if($backend->db()->isError())
{
	$backend->print_error($backend->db()->getError(), $js_back);
}
else
{
	$backend->print_success('Page saved successfully', $edit_page );
}

// Print admin footer
$backend->print_footer();
