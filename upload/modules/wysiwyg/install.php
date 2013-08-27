<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          wysiwyg
 * @author          Ryan Djurovich
 * @author          LEPTON Project
 * @copyright       2004-2010 WebsiteBaker Project
 * @copyright       2010-2011 LEPTON Project 
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

 

if(defined('CAT_URL'))
{
	
	// Create table
	//$database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_wysiwyg`");
	$mod_wysiwyg = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_wysiwyg` ( '
		. ' `section_id` INT NOT NULL DEFAULT \'0\','
		. ' `page_id` INT NOT NULL DEFAULT \'0\','
		. ' `content` LONGTEXT NOT NULL ,'
		. ' `text` LONGTEXT NOT NULL ,'
		. ' PRIMARY KEY ( `section_id` ) '
		. ' )';
	$database->query($mod_wysiwyg);
	

    $mod_search = "SELECT * FROM ".CAT_TABLE_PREFIX."search  WHERE value = 'wysiwyg'";
    $insert_search = $database->query($mod_search);
    if( $insert_search->numRows() == 0 )
    {
    	// Insert info into the search table
    	// Module query info
    	$field_info = array();
    	$field_info['page_id'] = 'page_id';
    	$field_info['title'] = 'page_title';
    	$field_info['link'] = 'link';
    	$field_info['description'] = 'description';
    	$field_info['modified_when'] = 'modified_when';
    	$field_info['modified_by'] = 'modified_by';
    	$field_info = serialize($field_info);
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('module', 'wysiwyg', '$field_info')");
    	// Query start
    	$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title,	[TP]pages.link, [TP]pages.description, [TP]pages.modified_when, [TP]pages.modified_by	FROM [TP]mod_wysiwyg, [TP]pages WHERE ";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_start', '$query_start_code', 'wysiwyg')");
    	// Query body
    	$query_body_code = " [TP]pages.page_id = [TP]mod_wysiwyg.page_id AND [TP]mod_wysiwyg.text [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_body', '$query_body_code', 'wysiwyg')");
    	// Query end
    	$query_end_code = "";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_end', '$query_end_code', 'wysiwyg')");

    	// Insert blank row (there needs to be at least on row for the search to work)
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_wysiwyg (page_id,section_id, `content`, `text`) VALUES ('0','0', '', '')");


    }

    // add files to class_secure
    $addons_helper = new CAT_Helper_Addons();
    foreach(
        array( 'save.php' )
        as $file
    ) {
        if ( false === $addons_helper->sec_register_file( 'wysiwyg', $file ) )
        {
             error_log( "Unable to register file -$file-!" );
        }
    }

}

?>