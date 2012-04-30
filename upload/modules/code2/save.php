<?php

/**
 *  @module         code2
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2011 Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php
/**
 *	Include WB admin wrapper script
 *
 */
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

if ( $admin->get_permission('start') == false ) die( header('Location: ../../index.php') );

/**
 *	Update the mod_wysiwygs table with the contents
 *
 */
if ( isset($_POST['content']) ) {
	$tags		= array('<?php', '?>' , '<?');
	$content	= $admin->add_slashes(str_replace($tags, '', $_POST['content']));
	$whatis		= $_POST['whatis'] + ($_POST['mode'] * 10);

	$fields = array(
		'content'	=> $content,
		'whatis'	=> $whatis,
	);
	
	$query = "UPDATE `".TABLE_PREFIX."mod_code2` SET ";
	foreach($fields as $key=>$value) $query .= "`".$key."`=  '".$value."', ";
	$query = substr($query, 0, -2)." where `section_id`='".$section_id."'";

	$database->query($query);
	
	/** 
	 *	Check if there is a database error, otherwise say successful
	 *
	 */
	if ( true === $database->is_error() ) {
		$admin->print_error($database->get_error(), $js_back, true );
	} else {
		$admin->print_success($MESSAGE['PAGES']['SAVED'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
	}
}

/**
 *	Print admin footer
 *
 */
$admin->print_footer();

?>