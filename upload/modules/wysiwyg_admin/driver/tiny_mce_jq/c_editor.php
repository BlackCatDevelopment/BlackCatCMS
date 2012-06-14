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
	private $name = "tiny_mce_jq";
	
	private $guid = "722635C4-8B51-489D-AF6A-3E92B3ADB491";
	
	public $skins = array();
	
	public $toolbars = array();
	
	public $toolbar_sets = array();
	
	public function __construct() {

		$this->__define_toolbar_sets();

		$this->skins[] = "cirkuit";		
		$this->skins[] = "default";
		$this->skins[] = "o2k7";
		
		$this->toolbars = array_keys( $this->toolbar_sets );
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
	
	private function __define_toolbar_sets() {
		
		/**
		 *	Default full toolbar
		 *
		 */
		$this->toolbar_sets['Full'] = array(
			'theme_advanced_buttons1'	=> "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			'theme_advanced_buttons2'	=> "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,wbdroplets,dropleps,pagelink,wbmodules,|,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			'theme_advanced_buttons3'	=> "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			'theme_advanced_buttons4'	=> "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak"
		);

		/**
		 *	Smart toolbar within only first two rows.
		 *
		 */
		$this->toolbar_sets['Smart'] = array(
			'theme_advanced_buttons1'	=> "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			'theme_advanced_buttons2'	=> "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,wbdroplets,dropleps,pagelink,wbmodules,|,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			'theme_advanced_buttons3'	=> "",
			'theme_advanced_buttons4'	=> ""
		);
		
		/**
		 *	Simple toolbar within only one row.
		 *
		 */
		$this->toolbar_sets['Simple'] = array(
			'theme_advanced_buttons1'	=> "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull|,undo,redo,|,link,unlink,anchor,|,image,wbdroplets,dropleps,pagelink,wbmodules,|,cleanup",
			'theme_advanced_buttons2'	=> "",
			'theme_advanced_buttons3'	=> "",
			'theme_advanced_buttons4'	=> ""
		);

	}
}

?>