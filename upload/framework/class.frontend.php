<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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

require_once(CAT_PATH.'/framework/class.wb.php');

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
	
	// website settings
	var $website_title,$website_description,$website_keywords,$website_header,$website_footer;

	// ugly database stuff
	var $extra_where_sql, $sql_where_language;

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
			define('LOGIN_URL', CAT_URL.'/account/login.php');
			define('LOGOUT_URL', CAT_URL.'/account/logout.php');
			define('FORGOT_URL', CAT_URL.'/account/forgot.php');
			define('PREFERENCES_URL', CAT_URL.'/account/preferences.php');
			define('SIGNUP_URL', CAT_URL.'/account/signup.php');
            global $parser;
            $parser->setGlobals( array(
                'username_fieldname' => CAT_Helper_Validate::getInstance()->createFieldname('username_'),
                'password_fieldname' => CAT_Helper_Validate::getInstance()->createFieldname('password_'),
            ));
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
		$regexp = array( '/\[cmsplink([0-9]+)\]/isU' );
        if(defined('WB_PREPROCESS_PREG')) $regexp[] = WB_PREPROCESS_PREG;
        foreach($regexp as $preg) {
    		if(preg_match_all( $preg, $content, $ids ) ) {
			$new_ids = array_unique($ids[1]);
			foreach($new_ids as $key => &$page_id) {
				$link = $database->get_one( 'SELECT `link` FROM `'.CAT_TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id );
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
		$query_menu = $database->query("SELECT page_id,menu_title,page_title,link,target,level,visibility,viewing_groups,viewing_users FROM ".CAT_TABLE_PREFIX."pages WHERE parent = '$this->menu_parent' AND $menu_number AND $this->extra_where_sql ORDER BY position ASC");
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
					$link = CAT_URL;
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
			CAT_PATH."/templates/" . DEFAULT_TEMPLATE . "/templates/under_construction.htt",
			CAT_PATH."/templates/" . DEFAULT_TEMPLATE . "/htt/under_construction.htt",
			CAT_PATH."/templates/" . DEFAULT_TEMPLATE . "/under_construction.htt",
			CAT_PATH."/templates/" . DEFAULT_THEME . "/templates/under_construction.lte"
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
				'{CAT_THEME_URL}' => CAT_URL."/templates/" . DEFAULT_THEME . "/"
			);
			$html = str_replace(array_keys($values),array_values($values), $html);
		}
		
		echo $html;
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
		
		$s = CAT_URL.$aPath;
		
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
				$f = CAT_PATH.$aBasename.$temp_name;
				if (file_exists($f)) {
					$aStorrage[$type][] = $this->__wb_build_link($aBasename.$temp_name, $type);
				}
			}
		}
	}

    /***************************************************************************
     * DEPRECATED
     * moved to CAT_Pages
     **************************************************************************/
  	public function page_select() {
        global $no_intro, $page_id, $wb;
        return $wb->pg->getPage($no_intro,$page_id);
	}

	public function get_page_details() {
        global $wb;
        return $wb->pg->getPageDetails();
	}

}

?>