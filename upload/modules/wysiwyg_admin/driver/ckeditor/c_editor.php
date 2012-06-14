<?php

/**
 *	@module			wysiwyg Admin
 *	@version		see info.php of this module
 *	@authors		Dietrich Roland Pehlke
 *	@copyright		2010-2011 Dietrich Roland Pehlke
 *	@license		GNU General Public License
 *	@license terms	see info.php of this module
 *	@platform		see info.php of this module
 *	@requirements	PHP 5.2.x and higher
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

  
 
require_once ( dirname(__FILE__)."/../c_wysiwyg_driver.php" );

class c_editor extends wysiwyg_driver
{
	private $name = "ckeditor";
	
	private $guid = "DDD07C89-76D2-4DA4-8747-0A5B4D2B6FB7";
	
	public $skins = array();
	
	public $toolbars = array();
	
	public function __construct() {
		$path = "../../modules/ckeditor/ckeditor/skins";
		$this->__get_dirs($path);
		
		$this->toolbars[] = "Full";
		$this->toolbars[] = "Smart";
		$this->toolbars[] = "Simple";
	}
	
	public function prepare(&$db, $what) {
		return "call prepare";
	}
	
	public function execute(&$db, $what) {
		return "call execute";
	}
	
	public function finish(&$db, $what) {
		return "call finish";
	}
}

?>