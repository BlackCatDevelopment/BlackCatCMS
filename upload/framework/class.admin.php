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

require_once(CAT_PATH.'/framework/class.wb.php');

// Include PHPLIB template class
require_once(CAT_PATH."/include/phplib/template.inc");

// Get CMS version
require_once(CAT_ADMIN_PATH.'/interface/version.php');

// Include EditArea wrapper functions
require_once(CAT_PATH . '/include/editarea/wb_wrapper_edit_area.php');

class admin extends wb
{
	private $db_handle = NULL;

	public $header_storage = array(
		'css'	=> array(),
		'js'	=> array(),
		'html'	=> array(),
		'modules' => array()
	);
	
	/**
	 *
	 */
	private $html_output_storage = "";
	
	/**
	 *
	 *
	 */
	private $droplets_ok = false;
	
	/**
	 *	Constructor of the class
	 *
	 *	Authenticate user then auto print the header
	 *
	 */
	public function __construct($section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true) {
		global $database;
		global $MESSAGE;
		global $parser;
		
		parent::__construct();
		
		/**
		 *	Droplet support
		 *
		 */
		ob_start();
		
		$this->db_handle = clone($database);
		
		// Specify the current applications name
		$this->section_name = $section_name;
		$this->section_permission = $section_permission;
		// Authenticate the user for this application
		if($auto_auth == true) {
			// First check if the user is logged-in
			if($this->is_authenticated() == false) {
				header('Location: '.CAT_ADMIN_URL.'/login/index.php');
				exit(0);
			}
			
			// Now check whether he has a valid token
			if (!$this->checkToken()) {
				unset($_SESSION['USER_ID']);
				header('Location: '.CAT_ADMIN_URL.'/login/index.php');
				exit(0);
			}
						
			// Now check if they are allowed in this section
			if($this->get_permission($section_permission) == false) {
				die($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES']);
			}
		}
		
		// Check if the backend language is also the selected language. If not, send headers again.

		$get_user_language = $this->db_handle->query("SELECT language FROM ".CAT_TABLE_PREFIX.
			"users WHERE user_id = '" .(int) $this->get_user_id() ."'");
		$user_language = ($get_user_language) ? $get_user_language->fetchRow() : '';
		// prevent infinite loop if language file is not XX.php (e.g. DE_du.php)
		$user_language = substr($user_language[0],0,2);
		// obtain the admin folder (e.g. /admin)
		$admin_folder = str_replace(CAT_PATH, '', CAT_ADMIN_PATH);
		if((LANGUAGE != $user_language) && file_exists(CAT_PATH .'/languages/' .$user_language .'.php')
			&& strpos($_SERVER['SCRIPT_NAME'],$admin_folder.'/') !== false) {
			// check if page_id is set
			$page_id_url = (isset($_GET['page_id'])) ? '&page_id=' .(int) $_GET['page_id'] : '';
			$section_id_url = (isset($_GET['section_id'])) ? '&section_id=' .(int) $_GET['section_id'] : '';
			if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') { // check if there is an query-string
				header('Location: '.$_SERVER['SCRIPT_NAME'] .'?lang='.$user_language .$page_id_url .$section_id_url.'&'.$_SERVER['QUERY_STRING']);
			} else {
				header('Location: '.$_SERVER['SCRIPT_NAME'] .'?lang='.$user_language .$page_id_url .$section_id_url);
			}
			exit();
		}
		
		// initialize template search path
		$parser->setPath(CAT_THEME_PATH . '/templates');
		$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

		// Auto header code
		if($auto_header == true) {
			$this->print_header();
		}
		/**
		 *	Droplet support
		 *
		 */
		if ( file_exists(CAT_PATH .'/modules/dropleps/droplets.php') ) {
			/**
			 *	avoid loading on the Droplets Admin Tool itself and on the
			 *	Settings page (this would compile Droplets added to the
			 *	page footer)
			 */
			if (
         		( !isset( $_GET['tool'] ) || 
         		( $_GET['tool'] !== 'droplets' && $_GET['tool'] !== 'jqueryadmin' ) )
        		 && $this->section_name !== 'Settings' && $this->section_name !== 'Page'
    		) {
				require_once(CAT_PATH .'/modules/dropleps/droplets.php');
			}
		}
	}
	
	/**
	 *	Print the admin header
	 *
	 *  @access public
	 */
	public function print_header()
	{
        global $database;

        $addons = CAT_Helper_Addons::getInstance();
        $user   = CAT_Users::getInstance();

		// Connect to database and get website title
		$title = $database->get_one("SELECT `value` FROM `".CAT_TABLE_PREFIX."settings` WHERE `name`='website_title'");

				global $parser;
		$tpl_data = array();

				// ============================================= 
				// ! Create the controller, if it is not set yet
				// ============================================= 
				if ( !is_object($parser) )
				{
            $parser = CAT_Helper_Template::getInstance('Dwoo');
				}

				// =================================== 
				// ! initialize template search path   
				// =================================== 
				$parser->setPath(CAT_THEME_PATH . '/templates');
				$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

				// ================================= 
		// ! Add permissions to $tpl_data   
				// ================================= 
		$tpl_data['permission']['pages']		  = $user->get_permission('pages')          ? true : false;
		$tpl_data['permission']['pages_add']	  = $user->get_permission('pages_add')      ? true : false;
		$tpl_data['permission']['pages_add_l0']	  = $user->get_permission('pages_add_l0')   ? true : false;
		$tpl_data['permission']['pages_modify']	  = $user->get_permission('pages_modify')   ? true : false;
		$tpl_data['permission']['pages_delete']	  = $user->get_permission('pages_delete')   ? true : false;
		$tpl_data['permission']['pages_settings'] = $user->get_permission('pages_settings') ? true : false;
		$tpl_data['permission']['pages_intro']	  = ( $user->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

		if ( $tpl_data['permission']['pages'] == true )
		{
			$this->pg->setPerms($tpl_data['permission']);

			$tpl_data['DISPLAY_MENU_LIST']	   = MULTIPLE_MENUS != false ? true : false;
			$tpl_data['DISPLAY_LANGUAGE_LIST'] = PAGE_LANGUAGES != false ? true : false;
			$tpl_data['DISPLAY_SEARCHING']	   = SEARCH	        != false ? true : false;

					// ========================== 
					// ! Get info for pagesTree   
					// ========================== 
					// list of first level of pages
			$tpl_data['pages']			= $this->pg->make_list( 0, true );
			$tpl_data['pages_editable']	= $this->pg->pages_editable;

					// ========================================== 
					// ! Get info for the form to add new pages   
					// ========================================== 
			$tpl_data['templates']		= $addons->get_addons( DEFAULT_TEMPLATE , 'template', 'template' );
			$tpl_data['languages']		= $addons->get_addons( DEFAULT_LANGUAGE , 'language' );
			$tpl_data['modules']		= $addons->get_addons( 'wysiwyg' , 'module', 'page',  $_SESSION['MODULE_PERMISSIONS'] );
			$tpl_data['groups']			= $user->get_groups();

					// list of all parent pages for dropdown parent
			$tpl_data['parents_list']	= $this->pg->pages_list(0 , 0);
					// List of available Menus of default template
			$tpl_data['TEMPLATE_MENU']	= $this->pg->get_template_menus();

					// =========================================== 
					// ! Check and set permissions for templates 	
					// =========================================== 
			foreach ($tpl_data['templates'] as $key => $template)
					{
				$tpl_data['templates'][$key]['permissions']	= ( $this->get_permission($template['VALUE'], 'template') ) ? true : false;
					}
				}

				// ========================= 
				// ! Add Metadatas to Dwoo 	
				// ========================= 
		$tpl_data['META']['CHARSET']		= (true === defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : 'utf-8';
		$tpl_data['META']['LANGUAGE']		= strtolower(LANGUAGE);
		$tpl_data['META']['WEBSITE_TITLE']	= $title;
		$tpl_data['CAT_VERSION']			= CAT_VERSION;
		$tpl_data['CAT_CORE']				= CAT_CORE;
		$tpl_data['PAGE_EXTENSION']			= PAGE_EXTENSION;

				$date_search	= array('Y','j','n','jS','l','F');
				$date_replace	= array('yy','y','m','d','DD','MM');
		$tpl_data['DATE_FORMAT']            = str_replace( $date_search, $date_replace, DATE_FORMAT );
				$time_search	= array('H','i','s','g');
				$time_replace	= array('hh','mm','ss','h');
		$tpl_data['TIME_FORMAT']            = str_replace( $time_search, $time_replace, TIME_FORMAT );

		$tpl_data['HEAD']['SECTION_NAME']			= $this->lang->translate(strtoupper($this->section_name));
		$tpl_data['HEAD']['BACKEND_MODULE_FILES']	= $this->__admin_register_backend_modfiles();
		$tpl_data['DISPLAY_NAME']					= $this->get_display_name();
		$tpl_data['USER']							= $user->get_user_details($user->get_user_id());

				// ===================================================================
				// ! Add arrays for main menu, options menu and the Preferences-Button
				// ===================================================================
		$tpl_data['MAIN_MENU']		= array();

		$tpl_data['MAIN_MENU'][0]	= array(
					'link'					=> CAT_ADMIN_URL . '/start/index.php',
			'title'					=> $this->lang->translate('Start'),
					'permission_title'		=> 'start',
					'permission'			=> ( $this->get_link_permission('start') ) ? true : false,
					'current'				=> ( 'start' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][1]	= array(
					'link'					=> CAT_ADMIN_URL . '/media/index.php',
			'title'					=> $this->lang->translate('Media'),
					'permission_title'		=> 'media',
					'permission'			=> ( $this->get_link_permission('media') ) ? true : false,
					'current'				=> ( 'media' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][2]	= array(
					'link'					=> CAT_ADMIN_URL . '/settings/index.php',
			'title'					=> $this->lang->translate('Settings'),
					'permission_title'		=> 'settings',
					'permission'			=> ( $this->get_link_permission('settings') ) ? true : false,
					'current'				=> ( 'settings' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][3]	= array(
					'link'					=> CAT_ADMIN_URL . '/addons/index.php',
			'title'					=> $this->lang->translate('Addons'),
					'permission_title'		=> 'addons',
					'permission'			=> ( $this->get_link_permission('addons') ) ? true : false,
					'current'				=> ( 'addons' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][4]	= array(
					'link'					=> CAT_ADMIN_URL . '/admintools/index.php',
			'title'					=> $this->lang->translate('Admin-Tools'),
					'permission_title'		=> 'admintools',
					'permission'			=> ( $this->get_link_permission('admintools') ) ? true : false,
					'current'				=> ( 'admintools' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][5]	= array(
			'title'					=> $this->lang->translate('Access'),
					'permission_title'		=> 'access',
					'permission'			=> ( $this->get_link_permission('access') ) ? true : false,
					'current'				=> ( 'access' == strtolower($this->section_name) ) ? true : false
					);

				// ======================================= 
				// ! Seperate access-link by permissions   
				// ======================================= 
		if ( $user->get_permission('users') )
				{
			$tpl_data['MAIN_MENU'][5]['link']	= CAT_ADMIN_URL . '/users/index.php';
				}
		elseif ( $user->get_permission('groups') )
				{
			$tpl_data['MAIN_MENU'][5]['link']	= CAT_ADMIN_URL . '/groups/index.php';
				}

		$tpl_data['PREFERENCES']	= array(
					'link'					=> CAT_ADMIN_URL . '/preferences/index.php',
			'title'					=> $this->lang->translate('Preferences'),
					'permission_title'		=> 'preferences',
					'permission'			=> ( $this->get_link_permission( 'preferences' ) ) ? true : false,
					'current'				=> ( 'preferences' == strtolower($this->section_name) ) ? true : false
					);

		$tpl_data['section_name']	= strtolower($this->section_name);
		$tpl_data['page_id']		= ( is_numeric( $this->get_get('page_id') ) && $this->get_get('page_id') != '' )
                                    ? $this->get_get('page_id')
                                    : (
                                          ( is_numeric( $this->get_post('page_id') ) && $this->get_post('page_id') != '' )
                                        ? $this->get_post('page_id')
                                        : false
						);

				// ==================== 
				// ! Parse the header 	
				// ==================== 
		$parser->output('header.lte', $tpl_data);
				
	}   // end function print_header()
				
				/**
	 * Print the admin footer
				 *
	 * @access public
	 **/
	public function print_footer()
	{
		global $parser, $database;
		$tpl_data = array();

				if (!is_object($parser))
				{
            $parser = CAT_Helper_Template::getInstance();
				}

				// initialize template search path
				$parser->setPath(CAT_THEME_PATH . '/templates');
				$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

				$data['CAT_VERSION']				= CAT_VERSION;
				$data['CAT_CORE']					= CAT_CORE;
				$data['permissions']['pages']		= ($this->get_permission('pages')) ? true : false;

				// ======================================================================== 
				// ! Try to get the actual version of the backend-theme from the database 	
				// ======================================================================== 
				$backend_theme_version = '-';
				if (defined('DEFAULT_THEME'))
				{
			$backend_theme_version	= $database->get_one( "SELECT `version` from `" . CAT_TABLE_PREFIX . "addons` where `directory`= '" . DEFAULT_THEME . "'");
				}
				$data['THEME_VERSION']		= $backend_theme_version;
				$data['THEME_NAME']			= DEFAULT_THEME;

				// ==================== 
				// ! Parse the footer 	
				// ==================== 
				$parser->output('footer.lte', $data);

				// =================== 
				// ! Droplet support 	
				// =================== 
				$this->html_output_storage = ob_get_clean();
				if ( true === $this->droplets_ok )
				{
					$this->html_output_storage = evalDroplets($this->html_output_storage);
				}

			echo $this->html_output_storage;

	}   // end function print_footer()
	
	/** 
	 *	Function get_page_permission takes either a numerical page_id,
	 *	upon which it looks up the permissions in the database,
	 *	or an array with keys admin_groups and admin_users  
	 */
	public function get_page_permission($page,$action='admin') {
		if ($action!='viewing') $action='admin';
		$action_groups=$action.'_groups';
		$action_users=$action.'_users';
		if (is_array($page)) {
				$groups=$page[$action_groups];
				$users=$page[$action_users];
		} else {				
			$results = $this->db_handle->query("SELECT $action_groups,$action_users FROM ".CAT_TABLE_PREFIX."pages WHERE page_id = '$page'");
			$result = $results->fetchRow( MYSQL_ASSOC );
			$groups = explode(',', str_replace('_', '', $result[$action_groups]));
			$users = explode(',', str_replace('_', '', $result[$action_users]));
		}

		$in_group = FALSE;
		foreach($this->get_groups_id() as $cur_gid){
		    if (in_array($cur_gid, $groups)) {
		        $in_group = TRUE;
		    }
		}
		if((!$in_group) AND !is_numeric(array_search($this->get_user_id(), $users))) {
			return false;
		}
		return true;
	}
		

	// Returns a system permission for a menu link
	public function get_link_permission($title) {
		$title = str_replace('_blank', '', $title);
		$title = strtolower($title);
		// Set system permissions var
		$system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
		// Set module permissions var
		$module_permissions = $this->get_session('MODULE_PERMISSIONS');
		if($title == 'start') {
			return true;
		} else {
			// Return true if system perm = 1
			if(is_numeric(array_search($title, $system_permissions))) {
				return true;
			} else {
				return false;
			}
		}
	}

		private function __admin_register_backend_modfiles() {

		$files = array(
			'css'	=> array(
				'backend.css',
				'css/backend.css'
				), 
			'js'	=> array(
				'backend.js',
				'js/backend.js',
				'scripts/backend.js'
				)
		);
		
		$html = "\n<!-- addons backend files -->\n";
		$html_results = array( "css" => array(), "js" => array() );
		
		if (strpos( $_SERVER['REQUEST_URI'],'admins/pages/sections.php') > 0) {
			$s = CAT_URL."/modules/jsadmin/backend.css";
			$html .=  "<link href=\"".$s."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
			$html .= "<!-- end addons backend files -->\n";
			return $html;
		}

		if (isset($_REQUEST['page_id'])) {
			$look_up_field = "module";
			$look_up_table = "sections";
			$look_up_where = "`page_id`='".$_REQUEST['page_id']."'";
		} elseif (isset($_REQUEST['tool'])) {
			$look_up_field = "directory";
			$look_up_table = "addons";
			$look_up_where = "`type`='module' AND `function`='tool' AND directory='".$_REQUEST['tool']."'";
		} else {
			if (strpos( $_SERVER['REQUEST_URI'],'admins/pages/index.php') > 0) {
				$s = CAT_URL."/modules/jsadmin/backend.css";
				$html .=  "<link href=\"".$s."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
				$html .= "<!-- end addons backend files -->\n";
			}
			return $html;
		}
		
		$query = "SELECT `".$look_up_field."` from `".CAT_TABLE_PREFIX.$look_up_table."` where ".$look_up_where;
		
		$result = $this->db_handle->query( $query );
		
		if ($result) {

			while( false !== ($data = $result->fetchRow( MYSQL_ASSOC ) ) ) {
				
				if (in_array($data[$look_up_field], $this->header_storage['modules'] ) ) continue;
				
				$this->header_storage['modules'][] = $data[$look_up_field];
				
				$basepath = "/modules/".$data[$look_up_field]."/";
				
				foreach($files as $type=>$temp_files) {
					foreach($temp_files as $filename) {
						$f = $basepath.$filename;
						if (true == file_exists(CAT_PATH.$f)) {
							$html_results[ $type ][] = $this->__admin_build_link($f, $type);
						}
					}
				}
			}
		}

		foreach($html_results as $ref=>$data) {
			$html .= implode("\n", $data)."\n";
		}
				
		$html .= "<!-- end addons backend files -->\n";
		
		return $html;
	}
	
	private function __admin_build_link( $aPath, $aType="css") {
		
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

    /***************************************************************************
     * moved to CAT_Users
     **************************************************************************/
    public function get_permission($name, $type = 'system') { return CAT_Users::getInstance()->get_permission($name,$type); }
    public function get_user_details($user_id)              { return CAT_Users::getInstance()->get_user_details($user_id);  }
}

?>