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

$table = TABLE_PREFIX."mod_code2";

$all_jobs = array();

/**
 *	Delete the table
 */
$query = "DROP TABLE IF EXISTS `".$table."`";

$all_jobs[] = $query;

/**
 *	Creating the table new
 */
$query  = "CREATE TABLE `".$table."` (";
$query .= "`section_id`	INT NOT NULL DEFAULT '0',";
$query .= "`page_id`	INT NOT NULL DEFAULT '0',";
$query .= "`whatis`		INT NOT NULL DEFAULT '0',";
$query .= "`content`	TEXT NOT NULL,";
$query .= " PRIMARY KEY ( `section_id` ) )";

$all_jobs[] = $query;

/**
 *	Preparing the db-connector
 */
$use_job_numbers = false;

$c_vars = get_class_vars ('database');
if ( true === in_array("log_querys", $c_vars) ) {
	$database->log_querys = true;
	$database->log_path = WB_PATH."/logs/";
	$database->log_filename = "code2_install.log";
	
	$use_job_numbers = true;
	$counter = 103000;
}

/**
 *	Processing the jobs/querys all in once
 */
foreach( $all_jobs as $q ) {
	
	$use_job_numbers === false ? $database->query($q) : $database->query($q, $counter++);
	
	if ( $database->is_error() ) 
		$admin->print_error($database->get_error(), $js_back);

}

?>