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

  
 
abstract class wysiwyg_driver
{	
	/**
	 *	@var	string	Name of the driver; e.g. ckeditor
	 *	@acces	private
	 *
	 */
	private $name="";
	
	/**
	 *	@var	string	The guid of the class
	 *	@access	private
	 *
	 */
	private $guid="";
	
	/**
	 *	Public function witch is called first
	 *
	 *	@param	mixed	A valid database-connector resource.
	 *	@param	mixed	Any job-identifier, Normaly a string.
	 *
	 */
	abstract public function prepare( &$db, $what );

	/**
	 *	Public function witch is called at runtime
	 *
	 *	@param	mixed	A valid database-connector resource.
	 *	@param	mixed	Any job-identifier, Normaly a string.
	 *
	 */
	abstract public function execute( &$db, $what );
	
	/**
	 *	Public function witch is called at last.
	 *
	 *	@param	mixed	A valid database-connector resource.
	 *	@param	mixed	Any job-identifier, Normaly a string.
	 *
	 */
	abstract public function finish( &$db, $what );
	
	/**
	 *
	 *
	 */
	public function __get_dirs( $aPath ) {
		
		$d = dir($aPath);
		while( !false == ($f = $d->read() ) ) {
			if ( substr($f, 0, 1) == "." ) continue;
			$temp = $aPath."/".$f;
			if ( true == is_dir($temp) ) $this->skins[] = $f;
		}
		$d->close();
		natsort($this->skins);
	}

	/**
	 *
	 *
	 */	
	public function build_select( $what="skins", $aName, $aSelected, $aOptions=array() ) {
		$ref = NULL;
		switch( $what ) {
			case 'skins':
				$ref= &$this->skins;
				break;
			case 'toolbars':
				$ref= &$this->toolbars;
				break;
		}
		
		$html = "\n<select name='".$aName."' >";
		
		if ($ref != NULL) {
			foreach($ref as $item) {
				$s = ($item == $aSelected) ? "selected='selected'" : "";
				$html .="\n\t<option value='".$item."' ".$s.">".$item."</option>";
			}
		}
		
		$html .= "\n</select>\n";
		
		return $html;
	}
	
	/**
	 *
	 *
	 */
	public function info($what='all') {
		switch( $what ) {
			case 'all':
				$info = array(
					'name' => $this->name,
					'guid' => $this->guid,
					'skins' => $this->skins,
					'toolbars' => $this->toolbars
				);
				break;
			
			case 'skins':
				$info = $this->skins;
				break;
				
			case 'toolbars':
				$info = $this->toolbars;
				break;
				
			default:
				$info = array();
		}
		return $info;
	}
}

?>