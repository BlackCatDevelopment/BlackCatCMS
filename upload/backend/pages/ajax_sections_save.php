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
 * @license			http://www.gnu.org/licenses/gpl.html
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

$backend = CAT_Backend::getInstance('Pages', 'pages_modify', false);
$val     = CAT_Helper_Validate::getInstance();
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

// Make sure people are allowed to access this page
if ( ! CAT_Registry::exists('MANAGE_SECTIONS') || CAT_Registry::get('MANAGE_SECTIONS') != 'enabled' )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You cannot modify sections. Please enable "Manage section".'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$delete_section_id		= $val->sanitizePost('delete_section_id','numeric');
$update_section_id		= $val->sanitizePost('update_section_id','numeric');
$section_id             = ( $delete_section_id )
                        ? $delete_section_id
                        : $update_section_id;

// =============== 
// ! Get page id   
// =============== 
$page_id = CAT_Helper_Section::getSectionPage($section_id);
if ( !$page_id )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value.')
                    .  ' ' . $backend->lang()->translate('Unable to get page_id for section [{{section}}].',array('section'=>$section_id)),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ============= 
// ! Get perms   
// ============= 
$page             = CAT_Helper_Page::getPage($page_id);
$old_admin_groups = explode(',', $page['admin_groups']);
$old_admin_users  = explode(',', $page['admin_users']);
$in_old_group     = false;

foreach ( $users->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
	}
}
if ( (!$in_old_group) && !is_numeric( array_search($users->get_user_id(), $old_admin_users) ) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permissions to modify this page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ========================== 
// ! Set module permissions   
// ========================== 
$module_permissions		= $_SESSION['MODULE_PERMISSIONS'];
$add_module				= $val->add_slashes($val->sanitizePost('add_module'));
$add_to_block			= $val->add_slashes($val->sanitizePost('add_to_block'));

if ( $add_module != '' )
{
	// Get section info
	$module = preg_replace("/\W/", "", $add_module);  // fix secunia 2010-91-4
    // check if the module exists
	if ( !CAT_Helper_Addons::isModuleInstalled($add_module) )
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('The module [{{module}}] does not exist / is not installed',array('module'=>$add_module)),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}

	// check module permission
	if (!CAT_Helper_Addons::checkModulePermissions($add_module))
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('Sorry, but you don\'t have the permissions for this action.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}

	// make sure we have a valid block id
	$add_to_block	= ( is_numeric($add_to_block) && $add_to_block > 0 ) ? $add_to_block : 1;

    // re-order
	require( CAT_PATH . '/framework/class.order.php');
	$order		= new order(CAT_TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
	$position	= $order->get_new($page_id);

    if(!CAT_Helper_Section::addSection($page_id, $module, $position, $add_to_block))
		{
        $ajax	= array(
			'message'	=> $backend->lang()->translate('Unable to add a section for module [{{module}}]',array('module'=>$module)),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}
// ===================================================== 
// ! If delete_section_id is sent, delete this section
// ===================================================== 
elseif ( $delete_section_id )
{

    $section = CAT_Helper_Section::getSection($delete_section_id);
	if(!$section || !is_array($section) || !count($section))
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('Section not found.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}

	// ================================================ 
	// ! Include the modules delete file if it exists   
	// ================================================ 
	if ( file_exists( CAT_PATH . '/modules/' . $section['module'] . '/delete.php') )
	{
        $section_id = $delete_section_id;
		require( CAT_PATH . '/modules/' . $section['module'] . '/delete.php');
	}

    if(!CAT_Helper_Section::deleteSection($delete_section_id))
	{
		$ajax	= array(
			'message'	=> CAT_Helper_Section::getInstance()->db()->get_error(),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
	else
	{
		// ======================= 
		// ! Reorder the sections
		// ======================= 
		require( CAT_PATH . '/framework/class.order.php');

		$order = new order(CAT_TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
		$order->clean( $page_id );

		$ajax	= array(
			'message'	=> $backend->lang()->translate('Section deleted successfully.'),
			'success'	=> true
		);
		print json_encode( $ajax );
		exit();
	}
}
// ===================================================== 
// ! If update_section_id is send, update this section
// ===================================================== 
elseif ( $update_section_id )
{
	$block			= $val->sanitizePost('set_block');
	$name			= $val->sanitizePost('blockname');

	$day_from		= is_numeric( $val->sanitizePost('day_from') )	? $val->sanitizePost('day_from')   : 0;
	$month_from		= is_numeric( $val->sanitizePost('month_from') )	? $val->sanitizePost('month_from') : 0;
	$year_from		= is_numeric( $val->sanitizePost('year_from') )	? $val->sanitizePost('year_from')  : 0;
	$hour_from		= is_numeric( $val->sanitizePost('hour_from') )	? $val->sanitizePost('hour_from')  : 0;
	$minute_from	= is_numeric( $val->sanitizePost('minute_from') )? $val->sanitizePost('minute_from'): 0;

	$day_to			= is_numeric( $val->sanitizePost('day_to') )		? $val->sanitizePost('day_to')     : 0;
	$month_to		= is_numeric( $val->sanitizePost('month_to') )	? $val->sanitizePost('month_to')   : 0;
	$year_to		= is_numeric( $val->sanitizePost('year_to') )	? $val->sanitizePost('year_to')    : 0;
	$hour_to		= is_numeric( $val->sanitizePost('hour_to') )	? $val->sanitizePost('hour_to')    : 0;
	$minute_to		= is_numeric( $val->sanitizePost('minute_to') )	? $val->sanitizePost('minute_to')  : 0;

    $section = CAT_Helper_Section::getSection($update_section_id);
	if(!$section || !is_array($section) || !count($section))
				{
					$ajax	= array(
						'message'	=> $backend->lang()->translate('Section not found.'),
						'success'	=> false
					);
					print json_encode( $ajax );
					exit();
				}

	#if ( !is_numeric (array_search($section['module'], $module_permissions) ) )
	#{
	$options = array();
	if($block) $options['block'] = $val->add_slashes($block);
	if($name)  $options['name']  = mysql_real_escape_string($name);

	$date_from
		= ($day_from * $month_from * $year_from) > 0
			? mktime( $hour_from, $minute_from, 0, $month_from, $day_from, $year_from )
			: 0;
	$date_to
		= ($day_to * $month_to * $year_to) > 0
			? mktime( $hour_to, $minute_to, 0, $month_to, $day_to, $year_to )
			: 0;
	if ( $date_from > $date_to )
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('Please check your entries for dates.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
	else
	{
		$options['publ_start'] = $date_from;
		$options['publ_end']   = $date_to;
	}
	if(!CAT_Helper_Section::updateSection($update_section_id,$options))
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('Unable to save section: '.CAT_Helper_Section::getInstance()->db()->get_error()),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
	#}
	#else
	#{
	#	$ajax	= array(
	#		'message'	=> $backend->lang()->translate('You do not have permissions to modify this page'),
	#		'success'	=> false
	#	);
	#	print json_encode( $ajax );
	#	exit();
	#}
	$updated_section	= CAT_Helper_Section::getSection($update_section_id);
	$updated_block		= $parser->get_template_block_name(
							CAT_Helper_Page::getPageTemplate($page_id), $updated_section['block'] ) .
							' ('.$backend->lang()->translate('Block number').': '.$updated_section['block'].')';
;
}

// ============================================ 
// ! Check for error or print success message   
// ============================================ 

$ajax	= array(
	'message'			=> $backend->lang()->translate('Section properties saved successfully.'),
	'updated_section'	=> isset($updated_section) ? $updated_section : false,
	'updated_block'		=> isset($updated_block) ? $updated_block : false,
	'success'			=> true
);
print json_encode( $ajax );
exit();
