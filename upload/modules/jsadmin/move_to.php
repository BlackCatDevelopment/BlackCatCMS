<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the BSD License.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          jsadmin 
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, Ryan Djurovich,WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         BSD License
 * @license_terms   please see info.php of this module
 * @version         $Id$
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

 

if(isset($_GET['page_id']) AND is_numeric($_GET['page_id']) AND is_numeric(@$_GET['position'])) {
	$position = (int)$_GET['position'];

	// Include WB admin wrapper script
	$update_when_modified = true;
	// Tells script to update when this page was last updated
	require(WB_PATH.'/modules/admin.php');

if( isset($_GET['file_id']) || (isset($_GET['group_id'])) ) {
	if(isset($_GET['group_id']) && is_numeric($_GET['group_id'])) {
		$id = (int)$_GET['group_id'];
		$id_field = 'group_id';
		$table = TABLE_PREFIX.'mod_download_gallery_groups';
		$common_field = 'section_id';
	} else {
		$id = (int)$_GET['file_id'];
		$id_field = 'file_id';
		$table = TABLE_PREFIX.'mod_download_gallery_files';
		$common_field = 'group_id';
	}
} elseif( isset($_GET['page_id']) || (isset($_GET['section_id'])) ) {
	// Get common fields
	if(isset($_GET['section_id']) && is_numeric($_GET['section_id'])) {
		$page_id = (int)$_GET['page_id'];
		$id = (int)$_GET['section_id'];
		$id_field = 'section_id';
		$common_field = 'page_id';
		$table = TABLE_PREFIX.'sections';
	} else {
		$id = (int)$_GET['page_id'];
		$id_field = 'page_id';
		$common_field = 'parent';
		$table = TABLE_PREFIX.'pages';
	}
}

	// Get current index
	$sql = <<<EOT
SELECT $common_field, position FROM $table WHERE $id_field = $id
EOT;
	echo "$sql<br>";
	$rs = $database->query($sql);
	if($row = $rs->fetchRow()) {
		$common_id = $row[$common_field];
		$old_position = $row['position'];
	}
	echo "$old_position<br>";
	if($old_position == $position)
		return;
	
	// Build query to update affected rows
	if($old_position < $position)
		$sql = <<<EOT
UPDATE $table SET position = position - 1
	WHERE position > $old_position AND position <= $position
		AND $common_field = $common_id
EOT;
	else
		$sql = <<<EOT
UPDATE $table SET position = position + 1
	WHERE position >= $position AND position < $old_position
		AND $common_field = $common_id
EOT;
	echo "<pre>$sql</pre>";
	$database->query($sql);

	// Build query to update specified row
	$sql = <<<EOT
UPDATE $table SET position = $position
	WHERE $id_field = $id
EOT;
	echo "<pre>$sql</pre>";
	$database->query($sql);
} else {
	die("Missing parameters");
	header("Location: index.php");
	exit(0);
}
