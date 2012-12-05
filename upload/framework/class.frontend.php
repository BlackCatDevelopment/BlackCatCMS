<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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

require_once(LEPTON_PATH.'/framework/class.wb.php');

/**
 *	@version	2.8.3
 *	@date		2010-10-07
 *	@last		Dietrich Roland Pehlke (Aldus)
 *
 *	@notice		Please keep in mind, that class "frontend" extends class "wb" and also "secure_form"
 *
 */
class frontend extends wb {
	// defaults
	var $default_link,$default_page_id;
	// when multiple blocks are used, show home page blocks on 
	// pages where no content is defined (search, login, ...)
	var $default_block_content=true;

	// page details
	// page database row
	var $page;
	var $page_id,$page_title,$menu_title,$parent,$root_parent,$level,$position,$visibility;
	var $page_description,$page_keywords,$page_link;
	var $page_trail=array();
	
	var $page_access_denied;
	var $page_no_active_sections;
	
	// website settings
	var $website_title,$website_description,$website_keywords,$website_header,$website_footer;

	// ugly database stuff
	var $extra_where_sql, $sql_where_language;

	function page_select() {
		global $page_id,$no_intro;
		global $database;
		// We have no page id and are supposed to show the intro page
		if((INTRO_PAGE AND !isset($no_intro)) AND (!isset($page_id) OR !is_numeric($page_id))) {
			// Since we have no page id check if we should go to intro page or default page
			// Get intro page content
			$filename = LEPTON_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;
			if(file_exists($filename)) {
				$handle = @fopen($filename, "r");
				$content = @fread($handle, filesize($filename));
				@fclose($handle);
				$this->preprocess($content);
				header("Location: ".LEPTON_URL.PAGES_DIRECTORY."/intro".PAGE_EXTENSION."");   // send intro.php as header to allow parsing of php statements
				echo ($content);
				return false;
			}
		}
		// Check if we should add page language sql code
		if(PAGE_LANGUAGES) {
			$this->sql_where_language = " AND language = '".LANGUAGE."'";
		}
		// Get default page
		// Check for a page id
		$table_p = TABLE_PREFIX.'pages';
		$table_s = TABLE_PREFIX.'sections';
		$now = time();
		$query_default = "
			SELECT `p`.`page_id`, `link`
			FROM `$table_p` AS `p` INNER JOIN `$table_s` USING(`page_id`)
			WHERE `parent` = '0' AND `visibility` = 'public'
			AND (($now>=`publ_start` OR `publ_start`=0) AND ($now<=`publ_end` OR `publ_end`=0))
			$this->sql_where_language
			ORDER BY `p`.`position` ASC LIMIT 1";
		$get_default = $database->query($query_default);
		$default_num_rows = $get_default->numRows();
		if(!isset($page_id) OR !is_numeric($page_id)){
			// Go to or show default page
			if($default_num_rows > 0) {
				$fetch_default = $get_default->fetchRow( MYSQL_ASSOC );
				$this->default_link = $fetch_default['link'];
				$this->default_page_id = $fetch_default['page_id'];
				// Check if we should redirect or include page inline
				if(HOMEPAGE_REDIRECTION) {
					// Redirect to page
					header("Location: ".$this->page_link($this->default_link));
					exit();
				} else {
					// Include page inline
					$this->page_id = $this->default_page_id;
				}
			} else {
		   		// No pages have been added, so print under construction page
				$this->print_under_construction();
				exit();
			}
		} else {
			$this->page_id=$page_id;
		}
		// Get default page link
		if(!isset($fetch_default)) {
		  	$fetch_default = $get_default->fetchRow( MYSQL_ASSOC );
	 		$this->default_link = $fetch_default['link'];
			$this->default_page_id = $fetch_default['page_id'];
		}
		return true;
	}

	function get_page_details() {
		global $database;
	    if($this->page_id != 0) {
			// Query page details
			$query_page = "SELECT * FROM ".TABLE_PREFIX."pages WHERE page_id = '{$this->page_id}'";
			$get_page = $database->query($query_page);
			// Make sure page was found in database
			if($get_page->numRows() == 0) {
				// Print page not found message
				exit("Page not found");
			}
			// Fetch page details
			$this->page = $get_page->fetchRow( MYSQL_ASSOC );
			// Check if the page language is also the selected language. If not, send headers again.
			if ($this->page['language']!=LANGUAGE) {
				if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') { // check if there is an query-string
					header('Location: '.$this->page_link($this->page['link']).'?'.$_SERVER['QUERY_STRING'].'&lang='.$this->page['language']);
				} else {
					header('Location: '.$this->page_link($this->page['link']).'?lang='.$this->page['language']);
				}
				exit();
			}
			// Begin code to set details as either variables of constants
			// Page ID
			if(!defined('PAGE_ID')) {define('PAGE_ID', $this->page['page_id']);}
			// Page Title
			if(!defined('PAGE_TITLE')) {define('PAGE_TITLE', $this->page['page_title']);}
			$this->page_title=PAGE_TITLE;
			// Menu Title
			$menu_title = $this->page['menu_title'];
			if($menu_title != '') {
				if(!defined('MENU_TITLE')) {define('MENU_TITLE', $menu_title);}
			} else {
				if(!defined('MENU_TITLE')) {define('MENU_TITLE', PAGE_TITLE);}
			}
			$this->menu_title = MENU_TITLE;
			// Page parent
			if(!defined('PARENT')) {define('PARENT', $this->page['parent']);}
			$this->parent=$this->page['parent'];
			// Page root parent
			if(!defined('ROOT_PARENT')) {define('ROOT_PARENT', $this->page['root_parent']);}
			$this->root_parent=$this->page['root_parent'];
			// Page level
			if(!defined('LEVEL')) {define('LEVEL', $this->page['level']);}
			$this->level=$this->page['level'];
			$this->position = $this->page['position'];
			// Page visibility
			if(!defined('VISIBILITY')) {define('VISIBILITY', $this->page['visibility']);}
			$this->visibility = $this->page['visibility'];
			// Page trail
			foreach(explode(',', $this->page['page_trail']) AS $pid) {
				$this->page_trail[$pid]=$pid;
			}
			// Page description
			$this->page_description=$this->page['description'];
			if($this->page_description != '') {
				define('PAGE_DESCRIPTION', $this->page_description);
			} else {
				define('PAGE_DESCRIPTION', WEBSITE_DESCRIPTION);
			}
			// Page keywords
			$this->page_keywords=$this->page['keywords'];
			// Page link
			$this->link=$this->page_link($this->page['link']);

		// End code to set details as either variables of constants
		}

		// Figure out what template to use
		if(!defined('TEMPLATE')) {
			if(isset($this->page['template']) AND $this->page['template'] != '') {
				if(file_exists(LEPTON_PATH.'/templates/'.$this->page['template'].'/index.php')) {
					define('TEMPLATE', $this->page['template']);
				} else {
					define('TEMPLATE', DEFAULT_TEMPLATE);
				}
			} else {
				define('TEMPLATE', DEFAULT_TEMPLATE);
			}
		}
		// Set the template dir
		define('TEMPLATE_DIR', LEPTON_URL.'/templates/'.TEMPLATE);

		// Check if user is allowed to view this page
		if($this->page && $this->page_is_visible($this->page) == false) {
			if(VISIBILITY == 'deleted' OR VISIBILITY == 'none') {
				// User isnt allowed on this page so tell them
				$this->page_access_denied=true;
			} elseif(VISIBILITY == 'private' OR VISIBILITY == 'registered') {
				// Check if the user is authenticated
				if($this->is_authenticated() == false) {
					// User needs to login first
					header("Location: ".LEPTON_URL."/account/login.php?redirect=".$this->link);
					exit(0);
				} else {
					// User isnt allowed on this page so tell them
					$this->page_access_denied=true;
				}
				
			}
		}
		// check if there is at least one active section
		if($this->page && $this->page_is_active($this->page) == false) {
			$this->page_no_active_sections=true;
		}
	}

	function get_website_settings()
    {
		global $database;

		// set visibility SQL code
		// never show no-vis, hidden or deleted pages
		$this->extra_where_sql = "visibility != 'none' AND visibility != 'hidden' AND visibility != 'deleted'";
		// Set extra private sql code
		if($this->is_authenticated()==false) {
			// if user is not authenticated, don't show private pages either
			$this->extra_where_sql .= " AND visibility != 'private'";
			// and 'registered' without frontend login doesn't make much sense!
			if (FRONTEND_LOGIN==false) {
				$this->extra_where_sql .= " AND visibility != 'registered'";
			}
		}
		$this->extra_where_sql .= $this->sql_where_language;

		// Work-out if any possible in-line search boxes should be shown
		if(SEARCH == 'public') {
			define('SHOW_SEARCH', true);
		} elseif(SEARCH == 'private' AND VISIBILITY == 'private') {
			define('SHOW_SEARCH', true);
		} elseif(SEARCH == 'private' AND $this->is_authenticated() == true) {
			define('SHOW_SEARCH', true);
		} elseif(SEARCH == 'registered' AND $this->is_authenticated() == true) {
			define('SHOW_SEARCH', true);	
		} else {
			define('SHOW_SEARCH', false);
		}
		// Work-out if menu should be shown
		if(!defined('SHOW_MENU')) {
			define('SHOW_MENU', true);
		}
		// Work-out if login menu constants should be set
		if(FRONTEND_LOGIN) {
			// Set login menu constants
			define('LOGIN_URL', LEPTON_URL.'/account/login.php');
			define('LOGOUT_URL', LEPTON_URL.'/account/logout.php');
			define('FORGOT_URL', LEPTON_URL.'/account/forgot.php');
			define('PREFERENCES_URL', LEPTON_URL.'/account/preferences.php');
			define('SIGNUP_URL', LEPTON_URL.'/account/signup.php');
		}
	}

	/**
	 *	replace all "[wblink{page_id}]" with real links
	 *	@param	string &$content : reference to global $content
	 *	@return	nothing
	 *	@history 	100216 17:00:00 optimise errorhandling, speed, SQL-strict
	 *				110315 12:00:00	- avoid unnessesary querys and replacements via array_unique.
	 *								- remove unused vars.
	 */
	function preprocess( &$content )
	{
		global $database;
		
		if(preg_match_all( '/\[wblink([0-9]+)\]/isU', $content, $ids ) ) {
			$new_ids = array_unique($ids[1]);
			foreach($new_ids as $key => &$page_id) {
				$link = $database->get_one( 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id );
				if( !is_null($link) ) {
					$content = str_replace(
						$ids[0][ $key ],
						$this->page_link($link),
						$content
					);
				}
			}
		}
	}

	function menu() {
		global $wb;
	   if (!isset($wb->menu_number)) {
	   	$wb->menu_number = 1;
	   }
	   if (!isset($wb->menu_start_level)) {
	   	$wb->menu_start_level = 0;
	   }
	   if (!isset($wb->menu_recurse)) {
	   	$wb->menu_recurse = -1;
	   }
	   if (!isset($wb->menu_collapse)) {
	   	$wb->menu_collapse = true;
	   }
	   if (!isset($wb->menu_item_template)) {
	   	$wb->menu_item_template = '<li><span[class]>[a] [menu_title] [/a]</span>';
	   }
	   if (!isset($wb->menu_item_footer)) {
	   	$wb->menu_item_footer = '</li>';
	   }
	   if (!isset($wb->menu_header)) {
	   	$wb->menu_header = '<ul>';
	   }
	   if (!isset($wb->menu_footer)) {
	   	$wb->menu_footer = '</ul>';
	   }
	   if (!isset($wb->menu_default_class)) {
	   	$wb->menu_default_class = ' class="menu_default"';
	   }
	   if (!isset($wb->menu_current_class)) {
	   	$wb->menu_current_class = ' class="menu_current"';
	   }
	   if (!isset($wb->menu_parent)) {
	   	$wb->menu_parent = 0;
	   }
	   $wb->show_menu();
	}
	
	function show_menu() {
		global $database;
		if ($this->menu_start_level>0) {
			$key_array=array_keys($this->page_trail);
			if (isset($key_array[$this->menu_start_level-1])) {
				$real_start=$key_array[$this->menu_start_level-1];
				$this->menu_parent=$real_start;
				$this->menu_start_level=0;
			} else {
				return;
			}
		}
		if ($this->menu_recurse==0)
	       return;
		// Check if we should add menu number check to query
		if($this->menu_parent == 0) {
			$menu_number = "menu = '$this->menu_number'";
		} else {
			$menu_number = '1';
		}
		// Query pages
		$query_menu = $database->query("SELECT page_id,menu_title,page_title,link,target,level,visibility,viewing_groups,viewing_users FROM ".TABLE_PREFIX."pages WHERE parent = '$this->menu_parent' AND $menu_number AND $this->extra_where_sql ORDER BY position ASC");
		// Check if there are any pages to show
		if($query_menu->numRows() > 0) {
			// Print menu header
			echo "\n".$this->menu_header;
			// Loop through pages
			while($page = $query_menu->fetchRow()) {
				// check whether to show this menu-link
				if($this->page_is_active($page)==false && $page['link']!=$this->default_link && !INTRO_PAGE) {
					continue; // no active sections
				}
				if($this->page_is_visible($page)==false) {
					if($page['visibility'] != 'registered') // special case: page_to_visible() check wheter to show the page contents, but the menu should be visible allways
						continue;
				}
				// Create vars
				$vars = array('[class]','[a]', '[/a]', '[menu_title]', '[page_title]');
				// Work-out class
				if($page['page_id'] == PAGE_ID) {
					$class = $this->menu_current_class;
				} else {
					$class = $this->menu_default_class;
				}
				// Check if link is same as first page link, and if so change to WB URL
				if($page['link'] == $this->default_link AND !INTRO_PAGE) {
					$link = LEPTON_URL;
				} else {
					$link = $this->page_link($page['link']);
				}
				// Create values
				$values = array($class,'<a href="'.$link.'" target="'.$page['target'].'" '.$class.'>', '</a>', $page['menu_title'], $page['page_title']);
				// Replace vars with value and print
				echo "\n".str_replace($vars, $values, $this->menu_item_template);
				// Generate sub-menu
				if($this->menu_collapse==false OR ($this->menu_collapse==true AND isset($this->page_trail[$page['page_id']]))) {
					$this->menu_recurse--;
					$this->menu_parent=$page['page_id'];
					$this->show_menu();
				}
				echo "\n".$this->menu_item_footer;
			}
			// Print menu footer
			echo "\n".$this->menu_footer;
		}
	}


	/**
	 *	Function to show the "Under Construction" page
	 *
	 *	If no template is found, only the message will displayed in the front-end.
	 *	There are four places the method is looking for:
	 *	- Inside the default_template, or "templates", or "htt".
	 *	- Inside the default_theme inside "templates"-directory.
	 *
	 *	@notice	The template-file has to be named "under_construction.htt" (case sensitive)!
	 *
	 */
	function print_under_construction() {
		global $MESSAGE;

		$search_files = array(
			LEPTON_PATH."/templates/" . DEFAULT_TEMPLATE . "/templates/under_construction.htt",
			LEPTON_PATH."/templates/" . DEFAULT_TEMPLATE . "/htt/under_construction.htt",
			LEPTON_PATH."/templates/" . DEFAULT_TEMPLATE . "/under_construction.htt",
			LEPTON_PATH."/templates/" . DEFAULT_THEME . "/templates/under_construction.lte"
		);

		$template_file = NULL;
		foreach($search_files as $f) {
			if (file_exists($f)) {
				$template_file = &$f;
				break;
			}
		}

		if ($template_file === NULL) {
			$html = "<p>".$MESSAGE['GENERIC_WEBSITE_UNDER_CONSTRUCTION']."\n<br />".$MESSAGE['GENERIC_PLEASE_CHECK_BACK_SOON']."</p>";
		} else {
			$html = file_get_contents($template_file);
			$values = array(
				'{TITLE}' => $MESSAGE['GENERIC_WEBSITE_UNDER_CONSTRUCTION'],
				'{UNDER_CONSTRUCTION}' => $MESSAGE['GENERIC_WEBSITE_UNDER_CONSTRUCTION'],
				'{PLEASE_CHECK_BACK_SOON}' => $MESSAGE['GENERIC_PLEASE_CHECK_BACK_SOON'],
				'{THEME_URL}' => LEPTON_URL."/templates/" . DEFAULT_THEME . "/"
			);
			$html = str_replace(array_keys($values),array_values($values), $html);
		}
		
		echo $html;
	}
	
	/**
	 *	Public function to look for modules specific css and js files for the frontend.
	 *	There are no params.
	 *
	 *	@return	string	The complete (x)HTML block within the links, first the css, than the js files.
	 *
	 *	@notice	Makes use of the admin-tool "register_modfiles".
	 *
	 */
	public function register_frontend_modfiles() {
		global $database;
		
		$html  =  "\n<!-- begin frontend modules files -->\n";
		
		/**
		 *	droplet patch
		 */
		$droplet_js = LEPTON_PATH."/modules/droplets/js/mdcr.js";
		if (file_exists($droplet_js))
		$html .= "<script src=\"".LEPTON_URL."/modules/droplets/js/mdcr.js\" type=\"text/javascript\"></script>\n";
		unset($droplet_js);
		
		$files = array(
			'css'	=> array(
				'frontend.css',
				'css/frontend.css'
				), 
			'js'	=> array(
				'frontend.js',
				'js/frontend.js',
				'scripts/frontend.js'
				)
		);
		
		$query = "SELECT `module` from `".TABLE_PREFIX."sections` where `page_id`='".$this->page['page_id']."' AND `module` <> 'wysiwyg'";
		$result = $database->query( $query );
		
		if (!$result) return $database->get_error();
		
		$all_modules = array();
		
		$html_results = array('css' => array(), 'js' => array() );
		
		while(false !== ($data = $result->fetchRow( MYSQL_ASSOC ) ) ) {
			$all_modules[]=$data['module'];
		}
		$all_modules = array_unique($all_modules);
		
		$exeptions = array();
		
		foreach($all_modules as $mod) {
			
			$base_name = "/modules/".$mod."/";
			
			$this->__find_files( $files, $base_name, $html_results, $exeptions);
		}
		
		$test_path = LEPTON_PATH."/modules/register_modfiles/classes/c_register_modfiles.php";
		if (file_exists($test_path)) {
			require_once($test_path);
			$ins = new c_register_modfiles( $database );
			$all = $ins->get_files();
			$sql_add = " AND `name` <>'Register Modfiles' AND `name` <> 'show_menu2' AND `name` <> 'wysiwyg Admin'";
		} else {
			$all = NULL;
			$sql_add = "";
		}
		
		$mod_types = array(
			'tool', 'snippet'
		);
		
		$query = "SELECT `directory` from `".TABLE_PREFIX."addons` where `function` in ('".implode("','", $mod_types)."')".$sql_add;
		$result = $database->query( $query );
		if (!$result) return $database->get_error();
		
		while(false !== ($data = $result->fetchRow( MYSQL_ASSOC ) ) ) {
			
			$exeptions = array();
			
			/**
			 *	Part of the register-modfiles admin-tool
			 *
			 */
			if ($all != NULL) {
				if (array_key_exists($data['directory'], $all)) {
					$info = $all[ $data['directory'] ];
					if ($info['use_css'] == 0) $exeptions[]='css';
					if ($info['use_js'] == 0) $exeptions[]='js';
				}
			}
			$base_name = "/modules/".$data['directory']."/";
			
			$this->__find_files( $files, $base_name, $html_results, $exeptions);
		}
		
		foreach($html_results as $ref=>$values) {
			if (count($values) > 0) $html .= implode("\n", $values)."\n";
		}
		
		$html .= "<!-- end frontend modules files -->\n";
		
		if(!defined('MOD_FRONTEND_CSS_REGISTERED')) define('MOD_FRONTEND_CSS_REGISTERED', true);
		if(!defined('MOD_FRONTEND_JAVASCRIPT_REGISTERED')) define('MOD_FRONTEND_JAVASCRIPT_REGISTERED', true);
		
		/**
		 *	Needed for the front-end login.
		 *	Were looking for the preferences css-file.
		 *
		 */
		if (defined("PAGE_CONTENT") ) {
			$look_up = array(
				'/account/css/preferences.css',
				"/templates/" . DEFAULT_THEME . "/preferences.css",
				"/templates/" . DEFAULT_THEME . "/css/preferences.css",              
				"/templates/".TEMPLATE.'/css/preferences.css',
				"/templates/".TEMPLATE.'/preferences.css'
			);
			foreach($look_up as $f) {
				if (file_exists(LEPTON_PATH.$f)) {
					$html .= "\n".$this->__wb_build_link($f)."\n";
					break;
				}
			}
		}
		
		return $html;
	}
	
	/**
	 *	Private function to build a simple (x)HTML link
	 *
	 *	@param	string	A valid path to the file. There is no test for the file itself.
	 *	@param	string	A type. Possibilities are "css" or "js". If no match, only the url will return.
	 *	@return string	A valid (x)HTML link tag - or simple the url, if no type match.
	 *
	 */
	private function __wb_build_link( $aPath, $aType="css") {
		
		$s = LEPTON_URL.$aPath;
		
		switch(strtolower($aType) ) {
			
			case "css":
				$s = "<link href=\"".$s."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />";
				break;
				
			case "js":
				$s = "<script src=\"".$s."\" type=\"text/javascript\"></script>";
				break;
		}
		
		return $s;			
	}
	
	/**
	 *	Private function for finding files and store the complete html-link
	 *	inside a given storrage_array witch has at last two keys: "css" and "js". 
	 *
	 *	@param	array	Assoc. Array (pass by reference)
	 *	@param	string	Basic foldername (pass by reference)
	 *	@param	array	Assoc. Array for the results (pass by reference)
	 *	@param	array	Assoc. array for the exeptions (pass by reference)
	 *
	 *	@example	$myFilenames = array(
	 *					'css' => array(
	 * 							'/css/private.css',
	 *							'/private.css',
	 *							'/jquery/jquery_what.css'
	 *						),
	 *					'js' => array(
	 *							'/js/modul_fe.js',
	 *							'/scripts/no_name_project/init.js'
	 *						)
	 *				);
	 *
	 *				$basename = "/modules/modul_xxx";
	 *
	 *				$exeptions = array('js');
	 *
	 *				$storrage = array('css' => array(), 'js' => array() );
	 *
	 *				// after calling
	 *				$this->__find_files($myFilenames, $basename, $storrage, $exeptions);
	 *
	 *				// storage will only filled up within the css files/links.
	 *
	 */
	private function __find_files(&$aFilenames, &$aBasename, &$aStorrage, &$exeptions=array() ) {
		foreach($aFilenames as $type=>$filenames) {
			if (true == in_array($type, $exeptions)) continue;
			foreach($filenames as $temp_name) {
				$f = LEPTON_PATH.$aBasename.$temp_name;
				if (file_exists($f)) {
					$aStorrage[$type][] = $this->__wb_build_link($aBasename.$temp_name, $type);
				}
			}
		}
	}
}

?>