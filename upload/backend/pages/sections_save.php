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

// Make sure people are allowed to access this page
if ( MANAGE_SECTIONS != 'enabled' )
{
	header('Location: '.CAT_ADMIN_URL);
	exit(0);
}

$backend = CAT_Backend::getInstance('Pages', 'pages_modify');
$addons  = CAT_Helper_Addons::getInstance();
$val     = CAT_Helper_Validate::getInstance();

$page_id = $val->get('_REQUEST','page_id','numeric');
if ( !$page_id )
{
	header("Location: index.php");
	exit(0);
}

if ( !CAT_Helper_Page::getPagePermission($page_id,'admin') )
{
	$backend->print_error( 'You do not have permissions to modify this page' );
}

$page_details = CAT_Helper_Page::properties($page_id);

if (!count($page_details))
{
	$backend->print_error( 'Page not found' );
}

// ==========================
// ! Set module permissions
// ==========================
$module_permissions		= $_SESSION['MODULE_PERMISSIONS'];

// =========================
// ! Get delete_section_id
// =========================
$delete_section_id		= $val->sanitizeGet('delete_section_id','numeric');
$update_section_id		= $val->sanitizeGet('update_section_id','numeric');
$add_module				= $val->sanitizePost('add_module',NULL,true);
$add_to_block           = $val->sanitizePost('add_to_block','numeric');

// add section
if ( $add_module != '' )
{
	// Get section info
	$module = preg_replace("/\W/", "", $add_module);  // fix secunia 2010-91-4
    // check if module exists
    if(!$addons->isModuleInstalled($module))
		$backend->print_error( 'The required module is not (properly) installed!' );

    if(!$addons->checkModulePermissions($module))
        $backend->print_error( "Sorry, but you don't have the permissions for this action" );

    // make sure we have a valid block id
    $add_to_block = $add_to_block > 0 ? $add_to_block : 1;
    $section_id   = CAT_Sections::addSection($page_id,$module,$add_to_block);

    if($section_id===false)
    {
        $backend->print_error('Error adding module');
    }
    else
    {
        if(file_exists(CAT_PATH.'/modules/'.$module.'/add.php'))
        {
            global $backend, $admin;
            $admin =& $backend;
            require(CAT_PATH.'/modules/'.$module.'/add.php');
        }
    }
}
// delete section
elseif ( $delete_section_id != '' )
{
	$section_id	= $delete_section_id;
    $section    = CAT_Sections::getSection($section_id);
	if ( file_exists(CAT_PATH.'/modules/'.$section['module'].'/delete.php') )
		require CAT_PATH.'/modules/'.$section['module'].'/delete.php';
    if(CAT_Sections::deleteSection($section_id,$page_id))
    {
        $backend->print_success( 'Success', CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
  		exit();
    }
}
// update section
elseif ( $update_section_id != '' )
{
	$block			= $val->sanitizeGet('block');
	$name			= $val->sanitizeGet('name');

	$day_from		= is_numeric( $val->sanitizeGet('day_from') )			? $val->sanitizeGet('day_from') : 0;
	$month_from		= is_numeric( $val->sanitizeGet('month_from') )		? $val->sanitizeGet('month_from') : 0;
	$year_from		= is_numeric( $val->sanitizeGet('year_from') )		? $val->sanitizeGet('year_from') : 0;
	$hour_from		= is_numeric( $val->sanitizeGet('hour_from') )		? $val->sanitizeGet('hour_from') : 0;
	$minute_from	= is_numeric( $val->sanitizeGet('minute_from') )		? $val->sanitizeGet('minute_from') : 0;

	$day_to			= is_numeric( $val->sanitizeGet('day_to') )		? $val->sanitizeGet('day_to') : 0;
	$month_to		= is_numeric( $val->sanitizeGet('month_to') )		? $val->sanitizeGet('month_to') : 0;
	$year_to		= is_numeric( $val->sanitizeGet('year_to') )		? $val->sanitizeGet('year_to') : 0;
	$hour_to		= is_numeric( $val->sanitizeGet('hour_to') )		? $val->sanitizeGet('hour_to') : 0;
	$minute_to		= is_numeric( $val->sanitizeGet('minute_to') )	? $val->sanitizeGet('minute_to') : 0;

	// =============================
	// ! Get section from database
	// =============================
	$query_sections		= $backend->db()->query('SELECT `module` FROM `' . CAT_TABLE_PREFIX . 'sections` WHERE `page_id`= ' . $page_id . ' AND `section_id` = ' . $update_section_id);
	if ( $query_sections->numRows() == 1 )
	{
		if ( $section = $query_sections->fetchRow( MYSQL_ASSOC ) )
		{
			if ( !is_numeric (array_search($section['module'], $module_permissions) ) )
			{
                $sql       = ( $block != '' )
                           ? '`block` = ' . $backend->add_slashes($block) . ', '
                           : ''
                           ;
                $sql      .= ( $name != '' )
                           ? '`name` = "' . mysql_real_escape_string($name) . '", '
                           : ''
                           ;
                $date_from = ($day_from * $month_from * $year_from) > 0
                           ? mktime( $hour_from, $minute_from, 0, $month_from, $day_from, $year_from )
                           : 0
                           ;
                $date_to   = ($day_to * $month_to * $year_to) > 0
                           ? mktime( $hour_to, $minute_to, 0, $month_to, $day_to, $year_to )
                           : 0
                           ;

				if ( $date_from > $date_to )
				{
					$backend->print_error($backend->lang->translate( 'Please check your entries for dates' ), CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
				}

				$sql	.= '`publ_start` = ' . $date_from . ', ';
                $sql .= '`publ_end` = '   . $date_to   . ', ';
                $sql .= '`modified_when` = "'.time().'", ';
                $sql .= '`modified_by` = '.CAT_Users::get_user_id();

				$backend->db()->query('UPDATE ' . CAT_TABLE_PREFIX . 'sections SET ' . $sql . ' WHERE `page_id`= ' . $page_id . ' AND section_id = ' . $update_section_id . ' LIMIT 1');
			}
		}
		else
		{
			$backend->print_error( 'You do not have permissions to modify this page', CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id);
		}
	}
	else
	{
		$backend->print_error( 'Section not found', CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
	}
}

// ============================================
// ! Check for error or print success message
// ============================================
if ( $backend->db()->is_error() )
{
	$backend->print_error( $backend->db()->get_error(), CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}
else
{
	$backend->print_success( 'Section properties saved successfully' , CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}

// ======================
// ! Print admin footer
// ======================
$backend->print_footer();

?>
