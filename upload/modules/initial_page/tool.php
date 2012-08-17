<?php

/**
 *
 * @module          initial_page
 * @author          Ralf Hertsch, Dietrich Roland Pehlke 
 * @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
 * @license_terms   please see info.php of this module
 *
 *
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

$lang = (dirname(__FILE__))."/languages/". LANGUAGE .".php";
require ( !file_exists($lang) ? (dirname(__FILE__))."/languages/EN.php" : $lang );

require_once(dirname(__FILE__)."/classes/c_init_page.php");

$ref = new c_init_page( $database );

if (isset($_POST['job'])) {

	if (!isset($_SESSION['init_page_h']) || ($_SESSION['init_page_h'] <> $_POST['sh']) ) die();
	
	unset($_SESSION['init_page_h']);
	
	switch($_POST['job']) {
		case 'save':
			foreach($_POST['init_page_select'] as $item=>$value) {
				$temp = explode("_", $item);
				$uid = (int) array_pop($temp);
				$ref->update_user( $uid, $value, $_POST['param'][$uid]);
			}
			break;
		
		default:
			# nothing
	}
}

$query = "SELECT * from `".TABLE_PREFIX."users";
$result = $database->query( $query );
if ($database->is_error()) {
	echo $database->get_error();
} else {
	
	/**
 	 *	Build hash
 	 *
 	 */
 	
 	$temp = sha1( MICROTIME().$_SERVER['HTTP_USER_AGENT'] );
	$_SESSION['init_page_h'] = $temp;
	
	$form  = "\n<form method='post' action='".ADMIN_URL."/admintools/tool.php?tool=initial_page'>\n";
	$form .=  "\n<input type='hidden' name='job' value='save' />\n";
	$form .= "\n<input type='hidden' name='sh' value='".$temp."' />\n";
	$form .= "<table class='initial_page'>\n";
	$form .= "<tbody>\n";
	$form .= "<tr>
		<td class='head left'>".$MOD_INITIAL_PAGE['label_user']."</td>
		<td class='head right'>".$MOD_INITIAL_PAGE['label_page']."</td>
		<td class='head param'>".$MOD_INITIAL_PAGE['label_param']."</td>
	</tr>\n";
	while(false != ($data = $result->fetchRow( MYSQL_ASSOC ))) {
		
		$temp_info = $ref->get_user_info( $data['user_id'] );
		
		$select = $ref->get_backend_pages_select( "init_page_select[user_".$data['user_id']."]", $temp_info['init_page'] );
		
		$form .= "<tr><td class='left'>".$data['user_id']." ".$data['username']."</td><td class='right'>".$select."</td><td class='param'><input type='text' name='param[".$data['user_id']."]' value='".$temp_info['page_param']."' /></td></tr>\n";
	}
	
	$form .= "<tr><td class='left'>&nbsp;</td><td class='right'><input type='submit' value='".$TEXT['SAVE']."' /></td></tr>\n";	
	$form .= "\n</tbody>\n</table>\n";
	
	$form .= "</form>\n\n";
	
	echo $form;
	
	unset($form);
	unset($select);
	unset($temp_path);
	unset($query);
	unset($result);
	unset($temp);
}
?>