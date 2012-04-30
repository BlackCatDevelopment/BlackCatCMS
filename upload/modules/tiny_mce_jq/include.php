<?php
/**
 *  @module         TinyMCE-jQ
 *  @version        see info.php of this module
 *  @authors        erpe, Dietrich Roland Pehlke (Aldus)
 *  @copyright      2010-2011 erpe, Dietrich Roland Pehlke (Aldus)
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 *  Please Notice: TINYMCE is distibuted under the <a href="http://tinymce.moxiecode.com/license.php">(LGPL) License</a> 
 *                 Ajax Filemanager is distributed under the <a href="http://www.gnu.org/licenses/gpl.html)">GPL </a> and <a href="http://www.mozilla.org/MPL/MPL-1.1.html">MPL</a> open source licenses 
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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

global $id_list;
global $database;

unset($_SESSION['TINY_MCE_INIT']);

/**
 * Decode HTML Special chars
 * 
 * @param STR $mixed
 * @return STR
 * @deprecated - why not use the standard function "htmlspecialchars_decode()"? 
 */
function reverse_htmlentities($mixed) {
	$mixed = str_replace(array('&gt;','&lt;','&quot;','&amp;'), array('>','<','"','&'), $mixed);
	return $mixed;
}

/**
 *	returns the template name of the current displayed page
 * 
 *	@param string	A path to the editor.css - if there is one. Default is an empty string. Pass by reference!
 *	@return STR $tiny_template_dir
 */
function get_template_name( &$css_path = "") {
	global $database;
	
	$lookup_paths = array(
		'/css/editor.css',
		'/editor.css'
	);
	
	$tiny_template_dir = "none";

	/**
	 *	Looking up for an editor.css file for TinyMCE
	 *
	 */
	foreach($lookup_paths as $temp_path) {
		if (file_exists(WB_PATH .'/templates/' .DEFAULT_TEMPLATE .$temp_path ) ) {
			$css_path = $temp_path; // keep in mind, that this one is pass_by_reference
			$tiny_template_dir = DEFAULT_TEMPLATE;
			break;
		}
	}
		
	// check if a editor.css file exists in the specified template directory of current page
	if (isset($_GET["page_id"]) && ((int) $_GET["page_id"] > 0)) {
		$pageid = (int) $_GET["page_id"];
		// obtain template folder of current page from the database
		$query_page = "SELECT `template` FROM `" .TABLE_PREFIX ."pages` WHERE `page_id`='".$pageid."'";
		$pagetpl = $database->get_one($query_page);
		
		/**
		 *	check if a specific template is defined for current page
		 *
		 */
		if (isset($pagetpl) && ($pagetpl != '')) {	
			/**
			 *	check if a specify editor.css file is contained in that folder
			 *
			 */
			foreach($lookup_paths as $temp_path) {
				if (file_exists(WB_PATH.'/templates/'.$pagetpl.$temp_path)) {
					$css_path = $temp_path; // keep in mind, that this one is pass_by_reference
					$tiny_template_dir = $pagetpl;
					break;
				}
			}
		}
	}
	return $tiny_template_dir;
} // get_template_name()


/**
 * Initialize Tiny MCE and create an textarea
 * 
 * @param STR $name		Name of the textarea.
 * @param STR $id		Id of the textarea.
 * @param STR $content	The content to edit.
 * @param INT $width	The width of the editor, not overwritten by wysiwyg-admin.
 * @param INT $height	The height of the editor, not overwritten by wysiwyg-admin.
 * @param BOOL $prompt	Direct output to the client via echo (true) or returnd as HTML-textarea (false)?
 * @return MIXED		Could be a BOOL or STR (textarea-tags).
 *
 */
function show_wysiwyg_editor($name, $id, $content, $width, $height, $prompt=true) {
	global $id_list;
	global $database;
	global $parser;
	
	if (!is_object($parser)) $parser = new c_tiny_mce_parser();
	
	if (!isset($_SESSION['TINY_MCE_INIT'])) {
		// write Tiny MCE init script only once!
		$_SESSION['TINY_MCE_INIT'] = true;
		
		if (!isset($id_list)) $id_list = array('short','long');
		
		if (!in_array($name, $id_list)) {
			$id_list = array($name);
			// Special case, editors will be created dynamically - write init script for each editor!
			unset($_SESSION['TINY_MCE_INIT']);
		}
		if (is_array($id_list) and (count($id_list)>0)) { // get all sections we want ... in page...
		  foreach ($id_list as &$ref) $ref = "#".$ref;
		  $elements = implode(',',$id_list);
		} 
		else { 
			/**
			 *	Try to et all wysiwyg sections... on the page...
			 *	Keep in Mind that there could be also a wysiwyg inside an admin-tool!
			 */
			$elements = "";
			if (isset($page_id)) {
				$qs = $database->query("SELECT section_id FROM ".TABLE_PREFIX."sections WHERE page_id = '$page_id' AND module = 'wysiwyg' ORDER BY position ASC");
				if ($qs->numRows() > 0) {
					while($sw = $qs->fetchRow( MYSQL_ASSOC )) {
						$elements .= '#content'.$sw['section_id'].',';
					}
					$elements = substr($elements,0,-1);
				}
			}
		}
		
		$lang = strtolower(LANGUAGE);
		$tiny_url = WB_URL.'/modules/tiny_mce_jq/tiny_mce';
		$tiny_path = WB_PATH.'/modules/tiny_mce_jq/tiny_mce';
		$language = file_exists($tiny_path.'/langs/'.$lang.'.js') ? $lang : 'en';

		$skin = 'o2k7';
		// load tinymce language file by actuelly wb language
		$tiny_path = WB_PATH.'/modules/tiny_mce_jq/tiny_mce';
		// obtain template name of current page (if empty, no editor.css files exists)
		
		$temp_css_path = "editor.css";
		$template_name = get_template_name( $temp_css_path );
		
		// work out default CSS file to be used for TINY textarea
		// no editor.css file exists in default template folder, or template folder of current page
		// editor.css file exists in default template folder or template folder of current page
		$css_file = ($template_name == "none")
			?	$tiny_url .'/themes/advanced/skins/'.$skin.'/content.css'
			:	WB_URL .'/templates/' .$template_name .$temp_css_path;
		
		$path = WB_PATH."/modules/wysiwyg_admin/driver/tiny_mce_jq/c_editor.php";
		
		$toolbar_set = array();
		$use_toolbar_set = 0;
				
		if (true === file_exists($path)) {
		  require_once($path);
			$query = "SELECT `menu`,`skin`,`height`,`width` from `".TABLE_PREFIX."mod_wysiwyg_admin` where `editor`='tiny_mce_jq'";
			$result= $database->query( $query );
			if (!$result) die ("Error: ".$database->get_error() );
			$data = $result->fetchRow( MYSQL_ASSOC );
			$tiny_mce_jq = new c_editor();
			$ref = &$tiny_mce_jq->toolbar_sets[ $data['menu'] ];
			if ($ref) {
				$use_toolbar_set = 1;
				foreach($ref as $key=>&$str) {
					$toolbar_set[$key] = $str;
				}
			}
			$skin = $data['skin'];
			$parser->height = $data['height'];
			$parser->width = $data['width'];
		} else {
			$parser->height = $height;
			$parser->width = $width;
		}
		
		$data = array(
			'tiny'	=> array(
				'elements' => $elements,
				'url' => WB_URL.'/modules/tiny_mce_jq/tiny_mce',
				'script' => WB_URL.'/modules/tiny_mce_jq/tiny_mce/tiny_mce.js'
			),
			'language' => file_exists($tiny_path.'/langs/'.strtolower(LANGUAGE).'.js') ? strtolower(LANGUAGE) : 'en',
			'use_toolbar_set' => $use_toolbar_set,
			'toolbar_set' => $toolbar_set,
			'skin' => $skin,
			'css_file' => $css_file,
			'LEPTON_URL' => WB_URL,
			'media_view' => (isset($_SESSION['SYSTEM_PERMISSIONS']) && array_search('media_view', $_SESSION['SYSTEM_PERMISSIONS']) !== false) ? 1 : 0,
			'ajax_filemanager' => WB_URL."/modules/tiny_mce_jq/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php",
		);

		$result = $parser->get(WB_PATH.'/modules/tiny_mce_jq/htt/tiny_mce.htt', $data);
		
		/**
		 *	the script block will be always prompted!
		 *
		 */
		echo $result;
	}
	
	if ("#".$id == end($id_list))
	{
		unset($_SESSION['TINY_MCE_INIT']);
	}

	$data = array(
		'id'	=> $id,
		'name'	=> $name,
		'width'	=> $parser->width,
		'height'	=> $parser->height,
		'content'	=> $content
	);
	
	$result = $parser->get(WB_PATH.'/modules/tiny_mce_jq/htt/textarea.htt', $data);
	if ($prompt) {
		echo $result;
		return true;
	}
	return $result;
} // show_wysiwyg_editor()

class c_tiny_mce_parser
{
	public $width = '100%';
	public $height = '250px';
	
	public function __construct() {
	
	}
	
	public function get( $aTemplatePath, &$data) {
		$result = file_get_contents($aTemplatePath);
		
		if ($result) {
			foreach( $data as $lookup => &$value) {
				if (is_array($value)) {
					foreach($value as $subkey => &$val) {
						$result = str_replace("{\$".$lookup.".".$subkey."}", $val, $result);
					}
				} else {
					$result = str_replace("{\$".$lookup."}", $value, $result);
				}
			}
		}
		return $result;
	}
}
?>