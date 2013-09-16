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


$backend = CAT_Backend::getInstance('Pages','pages_add',false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Pages','pages_add') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to add a page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// note: all pages are listed in the dropdown, even hidden / private AND deleted!
$dropdown_list = CAT_Helper_ListBuilder::sort(CAT_Helper_Page::getPages(1),0);

// template / variant
$template = CAT_Helper_Page::properties( $val->sanitizePost('parent_id','numeric'), 'template' );
$variant  = CAT_Helper_Page::getPageSettings($val->sanitizePost('parent_id','numeric'),'internal','template_variant');
$variants = array();
$info     = CAT_Helper_Addons::checkInfo(
    CAT_PATH.'/templates/'.CAT_Helper_Page::getPageTemplate($val->sanitizePost('parent_id','numeric'))
);
if(isset($info['module_variants']) && is_array($info['module_variants']) && count($info['module_variants'])) {
    $variants = $info['module_variants'];
    array_unshift($variants,'');
}

// ============================================= 
// ! Add result_array to the template variable   
// ============================================= 
$ajax	= array(
		'parent_id'		=> $val->sanitizePost('parent_id','numeric'),
		'parent_list'	=> $dropdown_list,
        'template'      => $template,
        'template_variant' => $variant,
        'variants'      => $variants,
		'target'		=> '_self',
		'success'		=> true
);

// ==================== 
// ! Return values 	
// ==================== 

print json_encode( $ajax );
exit();

?>