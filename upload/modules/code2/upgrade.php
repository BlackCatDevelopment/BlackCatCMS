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

$query = "show fields from `".TABLE_PREFIX."mod_code2`";

$result = $database->query ( $query );

if ($database->is_error() ) {

	$admin->print_error( $database->get_error() );

} else {
	
	$alter = 1;
	
	while ( !false == $data = $result->fetchRow( MYSQL_ASSOC ) ) {
		if ($data['Field'] == "whatis") {
			$alter = 0;
			break;
		}
	}

	if ( $alter != 0 ) {

		$thisQuery = "ALTER TABLE `".TABLE_PREFIX."mod_code2` ADD `whatis` INT NOT NULL DEFAULT 0";
		$r = $database->query($thisQuery);

		if ( $database->is_error() ) {

			$admin->print_error( $database->get_error() );

		} else {

			$admin->print_success( "Update Table for modul 'code2' with success." );
		}
	}
}

?>