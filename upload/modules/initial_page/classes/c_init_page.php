<?php

/**
 *
 * @module          initial_page
 * @author          Ralf Hertsch, Dietrich Roland Pehlke 
 * @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
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



class c_init_page
{
	private $db = NULL;
	
	private $table = "mod_initial_page";
	
	private $backend_pages = array ();
		
	public function __construct( &$db_ref=NULL, $aUser_id=NULL, $aPath_ref= NULL ) {
		global $MENU;
		
		$this->db = $db_ref;
		
		$this->backend_pages = array (
			'Start'		=> 'start/index.php',
			$MENU['ADDON']		=> 'addons/index.php',
			$MENU['ADMINTOOLS']	=> 'admintools/index.php',
			$MENU['GROUPS']		=> 'groups/index.php',
			$MENU['LANGUAGES']		=> 'languages/indes.php',
			$MENU['MEDIA']			=> 'media/index.php',
			$MENU['MODULES']		=> 'modules/index.php',
			$MENU['PAGES']			=> 'pages/index.php',
			$MENU['PREFERENCES']	=> 'preferences/index.php',
			$MENU['SETTINGS']		=> 'settings/index.php',
			$MENU['TEMPLATES']		=> 'templates/index.php',
			$MENU['USERS']			=> 'users/index.php'
		);
		
		$this->table = TABLE_PREFIX.$this->table;
		
		if ( ($aUser_id != NULL) && ($aPath_ref != NULL) ) {
			$this->__test_user($aUser_id, $aPath_ref);
		} else {
			$this->___U();
		}
	}
	
	public function __destruct() {
	
	}
	
	public function set_db (&$db_ref = NULL ) {
		$this->db = $db_ref;
	}
	
	public function get_backend_pages_select($name="init_page_select", $selected = "") {
		global $MENU;
		
		$values = array();
		
		/**
		 *	first: add pages ...
		 *
		 */
		$temp = $this->db->query( "SELECT `page_id`,`page_title`,`menu_title` from `".TABLE_PREFIX."pages` order by `page_title`");
		if ($temp) {
			while( false != ($data = $temp->fetchRow( MYSQL_ASSOC ) ) ) {
				$values[ $MENU['PAGES'] ][ $data['page_title'] ] = "pages/modify.php?page_id=".$data['page_id'];
			}
		}
		
		/**
		 *	second: add tools
		 *
		 */
		$temp = $this->db->query("SELECT `name`,`directory` from `".TABLE_PREFIX."addons` where `function`='tool' order by `name`");
		if ($temp) {
			while( false != ($data = $temp->fetchRow( MYSQL_ASSOC ) ) ) {
					$values[ $MENU['ADMINTOOLS'] ][ $data['name'] ] = "admintools/tool.php?tool=".$data['directory'];
			}
		}
		
		/**
		 *	At last the backend-pages
		 *
		 */
		$values['Backend'] = &$this->backend_pages;
		$options = array(
			'name' => $name,
			'class' => "init_page_select"
		);
		
		return $this->__build_select($options, $values, $selected);
	}
	
	private function __build_select(&$options, &$values, &$selected) {
		$s = "<select ".$this->__build_args($options).">\n";
		foreach( $values as $theme=>$sublist ) {
			$s .= "<optgroup label='".$theme."'>";
			foreach($sublist as $item=>$val) {
				$sel = ($val == $selected) ? " selected='selected'" : "";
				$s .= "<option value='".$val."'".$sel.">".$item."</option>\n";
			}
			$s .= "</optgroup>";
		}
		$s.= "</select>\n";
		return $s;
	}
	
	private function __build_args(&$aArgs) {
		$s = "";
		foreach($aArgs as $name=>$value) $s .= " ".$name."='".$value."'";
		return $s;
	}

	public function get_user_info( &$aUserId=0 ) {
		$q = "SELECT `init_page`, `page_param` from `".$this->table."` where `user_id`='".$aUserId."'";
		$r = $this->db->query($q);
		if ($r) {
			if ( 0 === $r->numRows() ) {
				$this->db->query("INSERT into `".$this->table."` (`user_id`, `init_page`,`page_param`) VALUES ('".$aUserId."', 'start/index.php', '')");
				return array('init_page' => "start/index.php", 'page_param' => '') ;
			} else {
				return $r->fetchRow( MYSQL_ASSOC );
			}
		}
		return '';
	}
	
	public function update_user(&$aId, &$aValue, &$aParam = -1) {
		/**
		 *	M.f.i.	Aldus:	- 1 [-] does the params make sence at all? E.g. as for a internal page only the section makes sense,
		 *							but for a tool-page we're in the need to get more, e.g. details about the correct params.
		 *					- 2 [+] if the aParam parameter is not set - we'll ignore it.
		 */
		$temp_param = ($aParam == -1)  ? "" : ", `page_param`='".$aParam."' " ;
		$q = "UPDATE `".$this->table."` set `init_page`='".$aValue."'".$temp_param." where `user_id`='".$aId."'";
		$this->db->query( $q ) ;
	}
	
	private function __test_user( $aID, $path_ref ) {
		$info = $this->get_user_info( $aID );
		$path = ADMIN_URL."/".$info['init_page'];
		if (( $path <> $path_ref ) && ($info['init_page'] != "start/index.php" ) && ($info['init_page'] != "") ) {
			if (strlen($info['page_param']) > 0) $path .= $info['page_param'];
			$this->__test_leptoken( $path );
			header('Location: '.$path );
			die();
		}
	}
	
	private function __test_leptoken( &$aURL ) {
		if (isset($_GET['leptoken'])) {
			$temp_test = explode("?", $aURL );
			$aURL .= (count($temp_test) == 1) ? "?" : "&amp;";
			
			$aURL .= "leptoken=".$_GET['leptoken'];
		}
	}
	
	public function get_single_user_select (
		$aUserId,
		$aName,
		$selected="", 
		&$options=array(
			'backend_pages'=>true,
			'tools' => true,
			'pages' => true
			)
		) {
	
		global $MENU;
		
		$values = Array();
		
		if (array_key_exists('backend_pages', $options) && ($options['backend_pages'] == true))
			$values['Backend'] = $this->backend_pages;
		
		/**
		 *	Add tools
		 *
		 */
		if (array_key_exists('tools', $options) && ($options['tools'] == true)) {

			$temp = $this->db->query("SELECT `name`,`directory` from `".TABLE_PREFIX."addons` where `function`='tool' order by `name`");
			if ($temp) {
				while( false != ($data = $temp->fetchRow( MYSQL_ASSOC ) ) ) {
						$values[ $MENU['ADMINTOOLS'] ][ $data['name'] ] = "admintools/tool.php?tool=".$data['directory'];
				}
			}
		}

		/**
		 *	Add pages ...
		 *
		 */
		
		if (array_key_exists('pages', $options) && ($options['pages'] == true)) {

			$temp = $this->db->query( "SELECT `page_id`,`page_title`,`menu_title` from `".TABLE_PREFIX."pages` order by `page_title`");
			if ($temp) {
				while( false != ($data = $temp->fetchRow( MYSQL_ASSOC ) ) ) {
					$values[ $MENU['PAGES'] ][ $data['page_title'] ] = "pages/modify.php?page_id=".$data['page_id'];
				}
			}
		}
		
		$options = array(
			'name' => $aName,
			'class' => "init_page_select"
		);
		
		return $this->__build_select($options, $values, $selected);
	}
	
	/**
	 *
	 *
	 *
	 */
	protected function ___U () {
		$q="SELECT `user_id` from `".TABLE_PREFIX."users` order by `user_id`";
		$r= $this->db->query( $q );
		if ($r) {
			$ids = array();
			while(false !== ($data = $r->fetchRow(MYSQL_ASSOC))) {
				$ids[] = $data['user_id'];
			}
			
			$q = "DELETE from `".TABLE_PREFIX."mod_initial_page` where `user_id` not in (".implode (",",$ids).")";
			$this->db->query( $q );
		}
	}
	
	public function get_language() {
		$lang = (dirname(__FILE__))."/../languages/". LANGUAGE .".php";
		require_once ( !file_exists($lang) ? (dirname(__FILE__))."/../languages/EN.php" : $lang );
		return $MOD_INITIAL_PAGE;
	}
}
?>