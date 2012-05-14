<?php

/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Dietrich Roland Pehlke
 *  @copyright      2010-2012 Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *  @version        $Id$
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
 
$debug = false;

if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

/**
 *	prevent this file from being accessed directly
 *
 */
if ( !defined('WB_PATH')) die(header('Location: ../../index.php'));

/**
 *
 *
 */
$files = array(
	'contentsCss' => Array(
		'/editor.css',
		'/css/editor.css',
		'/editor/editor.css'
	),
	'stylesSet' => Array(
		'/editor.styles.js',
		'/js/editor.styles.js',
		'/editor/editor.styles.js'
	),
	'templates_files' => Array(
		'/editor.templates.js',
		'/js/editor.templates.js',
		'/editor/editor.templates.js'
	), 
	'customConfig' => Array(
		'/ckconfig.js',
		'/js/ckconfig.js',
		'/editor/ckconfig.js'
	)
);

/**
 *	If you also want to look for the template-specific css, you can simple add the files like e.g.:
 *
 *	$files['contentsCss'][]= '/template.css';
 *	$files['contentsCss'][]= '/css/template.css';
 *
 *	Or just uncomment one or both of the following two lines ;-) by removing the double-slashes ...
 *
 */
//	$files['contentsCss'][]= '/template.css';
//	$files['contentsCss'][]= '/css/template.css';

/**
 *
 *
 */
global $paths;
$paths = Array(
	'contentsCss' => "",
	'stylesSet' => "",
	'template_files' => "",
	'customConfig' => ""
);

$temp = "";
if (isset($page_id)) {
	$query = "SELECT `template` from `".TABLE_PREFIX."pages` where `page_id`='".$page_id."'";
	$temp = $database->get_one( $query );
}

$base_folder = ($temp == "") ? DEFAULT_TEMPLATE : $temp;

foreach($files as $key=>$p) {
	foreach($p as $temp_path) {
		$base = "/templates/".$base_folder.$temp_path;
		if (true == file_exists(WB_PATH.$base) ){
			$paths[$key] = (($key=="stylesSet") ? "lepton:" : "").WB_URL.$base;
			break;
		}
	}
}

/**
 *	Create new CKeditor instance.
 *	But first - we've got to revamp this pretty old class a little bit.
 *
 */
require_once ( WB_PATH.'/modules/ckeditor/ckeditor/ckeditor.php' );

class CKEditor_Plus extends CKEditor
{
	/**
	 *	@var	boolean
	 *
	 */
	public $pretty = false;
	
	/**
	 *	@var	array
	 *
	 */
	private $lookup_html = array(
		'&gt;'	=> ">",
		'&lt;'	=> "<",
		'&quot;' => "\"",
		'&amp;'	 => "&"
	);

	/**
	 *	Public var to force the editor to use the given params for width and height
	 *
	 */
	public $force = false;

	/**
	 *	@param	string	Any HTML-Source, pass by reference
	 *
	 */
	public function reverse_htmlentities(&$html_source) {
	
		$html_source = str_replace(
			array_keys( $this->lookup_html ), 
			array_values( $this->lookup_html ), 
			$html_source
		);
    }
    
    /**	*************************************
     *	Additional test for the wysiwyg-admin
     */
     
    /**
     *	@var	boolean
     *
     */
    public $wysiwyg_admin_exists = false;
    
    /**
     *	Public function to look for the wysiwyg-admin table in the used database
     *
     *	@param	object	Any DB-Connector instance. Must be able to use a "query" method inside.
     *
     */
    public function looking_for_wysiwyg_admin( &$db ) {
		$result = $db->query("SHOW TABLES");
		if ($result) {
			while(false !== ($data = $result->fetchRow( MYSQL_NUM ) ) ) {
				if (TABLE_PREFIX."mod_wysiwyg_admin" == $data[0]) {
					$this->wysiwyg_admin_exists = true;
					break;
				}
			}
		}
	}
    
    /**
     *	Looks for an (local) url
     *
     *	@param	string	Key for tha assoc. config array
     *	@param	string	Local file we are looking for
     *	@param	string	Optional file-default-path if it not exists
     *	@param	string	Optional a path_addition, e.g. "wb:"
     *
     */
    public function resolve_path($key= "", $aPath, $aPath_default, $path_addition="") {
    	global $paths;
    	
    	$temp = WB_PATH.$aPath;
    	
    	if (true === file_exists($temp)) {
    		$aPath = $path_addition.WB_URL.$aPath;
    	} else {
    		$aPath = $path_addition.WB_URL.$aPath_default;
    	}

		if (array_key_exists($key, $paths)) {
    		$this->config[$key] = (($paths[$key ] == "") ? $aPath : $paths[$key]) ;
    	} else {
    		$this->config[$key] = $aPath;
    	}
    }
    
    /**
     *	More or less for debugging
     *
     *	@param	string	Name
     *	@param	string	Any content. Pass by reference!
     *	@return	string	The "editor"-JS HTML code
     *
     */
    public function to_HTML( $name, &$content ) {
    	
    	$old_return = $this->returnOutput;
    	
    	$this->returnOutput = true;
    	
    	$temp_HTML= $this->editor( $name, $content );
    	
    	$this->returnOutput = $old_return;
    	
    	if (true === $this->pretty) {
    		$temp_HTML = str_replace (",", ",\n ", $temp_HTML);
    		$temp_HTML = "\n\n\n".$temp_HTML."\n\n\n";
    	}
    	
    	return $temp_HTML;
    }
}
global $ckeditor;
$ckeditor = new CKEditor_Plus( WB_URL.'/modules/ckeditor/ckeditor/' );

/**
 *	Looking for the styles
 *
 */
$ckeditor->resolve_path( 
	'contentsCss',
	'/modules/ckeditor/config/custom/editor.css',
	'/modules/ckeditor/config/default/editor.css'
);

/**
 *	Looking for the editor.styles at all ...
 *
 */
$ckeditor->resolve_path(
	'stylesSet',
	'/modules/ckeditor/config/custom/editor.styles.js',
	'/modules/ckeditor/config/default/editor.styles.js',
	'lepton:'
);

/**
 *	Setup the template
 *
 */
$ckeditor->config['templates'] = 'default';

/**
 *	The list of templates definition files to load.
 *
 */
$ckeditor->resolve_path(
	'templates_files',
	'/modules/ckeditor/config/custom/editor.templates.'. strtolower( LANGUAGE ) .'.js',
	'/modules/ckeditor/config/default/editor.templates.en.js'
);

/**
 *	Bugfix for the template files as the ckeditor want an array instead a string ...
 *
 */
$ckeditor->config['templates_files'] = array($ckeditor->config['templates_files']);

/**
 *	The filebrowser are called in the include, because later on we can make switches, use WB_URL and so on
 *
 */
$connectorPath = $ckeditor->basePath.'filemanager/connectors/php/connector.php';
$ckeditor->config['filebrowserBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Connector='.$connectorPath;
$ckeditor->config['filebrowserImageBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Image&Connector='.$connectorPath;
$ckeditor->config['filebrowserFlashBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Flash&Connector='.$connectorPath;

/**
 *	The Uploader has to be called, too.
 *
 */
$uploadPath = $ckeditor->basePath.'filemanager/connectors/php/upload.php?Type=';
$ckeditor->config['filebrowserUploadUrl'] = $uploadPath.'File';
$ckeditor->config['filebrowserImageUploadUrl'] = $uploadPath.'Image';
$ckeditor->config['filebrowserFlashUploadUrl'] = $uploadPath.'Flash';
    
/**
 *	Setup the CKE language
 *
 */
$ckeditor->config['language'] = strtolower(LANGUAGE);
    
/**
 *	Get the config file
 *
 */
$ckeditor->resolve_path(
	'customConfig',
	'/modules/ckeditor/config/custom/ckconfig.js',
	'/modules/ckeditor/config/default/ckconfig.js'
);

/**
 *	Getting the values from the editor_admin db-field
 *
 */
 
/**
 *	Additional test for wysiwyg-admin
 *
 */
$ckeditor->looking_for_wysiwyg_admin( $database );

/**
 *	To avoid a double "else" inside the following condition, we set the 
 *	default toolbar here to "WB_Full". Keep in mind, that the config will overwrite all
 *	settings inside the config.js or config.js BUT you will have to defined the toolbar inside
 *	them at all!
 *
 */
$ckeditor->config['toolbar'] = "WB_Full";

if (true === $ckeditor->wysiwyg_admin_exists) {
	$query = "SELECT `skin`,`menu`,`width`,`height` from `".TABLE_PREFIX."mod_wysiwyg_admin` where `editor`='ckeditor'";
	$result = $database->query( $query );
	if ($result && $result->numRows() > 0) {
		$data = $result->fetchRow( MYSQL_ASSOC );
		
		/**
		 *	Setup the (predefined) toolbar
		 *
		 */
		$ckeditor->config['toolbar'] = $data['menu'];
	
		/**
		 *	Setup the default height
		 *
		 */
		$ckeditor->config['height'] = $data['height'];
		
		/**
		 *	Setup the default width
		 *
		 */
		$ckeditor->config['width'] = $data['width'];
		
		/**
		 *	Setup the default skin
		 *
		 */
		$ckeditor->config['skin'] = $data['skin'];
	
	} 
}

/**
 *	Force the object to print/echo direct instead of returning the 
 *	HTML source string.
 *
 */
$ckeditor->returnOutput = true;

/**
 *	SCAYT
 *	Spellchecker settings.
 *
 */
$ckeditor->config['scayt_sLang'] = strtolower(LANGUAGE)."_".(LANGUAGE == "EN" ? "US" : LANGUAGE);

$ckeditor->config['scayt_autoStartup'] = false;

/**
 *	Setting the utf-8/entities-handling.
 *
 */
if ( "utf-8" === DEFAULT_CHARSET ) {
	$ckeditor->config['entities'] = false;
	$ckeditor->config['entities_latin'] = false;
	$ckeditor->config['entities_greek'] = false;
}

/**
 *	Function called by parent, default by the wysiwyg-module
 *	
 *	@param	string	The name of the textarea to watch
 *	@param	mixed	The "id" - some other modules handel this param differ
 *	@param	string	Optional the width, default "100%" of given space.
 *	@param	string	Optional the height of the editor - default is '250px'
 *
 *
 */
function show_wysiwyg_editor($name, $id, $content, $width = '100%', $height = '250px') {
	global $ckeditor;
	
	if ( (false === $ckeditor->wysiwyg_admin_exists) || ( true === $ckeditor->force ) )  {
		$ckeditor->config['height'] = $height;
		$ckeditor->config['width'] = $width;
	}
	
	$ckeditor->reverse_htmlentities($content);

	echo $ckeditor->to_HTML( $name, $content );
}
?>