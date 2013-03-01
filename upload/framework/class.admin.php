<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 * 
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 * @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

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


require_once(CAT_PATH.'/framework/class.wb.php');

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
	 *  This is left for backward compatibility!
	 *
	 */
	public function __construct($section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true)
    {
		global $database;
		
		parent::__construct();
		
		$this->db_handle = clone($database);
		
		// Specify the current applications name
		$this->section_name = $section_name;
		$this->section_permission = $section_permission;

        $user = CAT_Users::getInstance();

		// Authenticate the user for this application
		if($auto_auth == true) {
			// First check if the user is logged-in
			if($user->is_authenticated() == false) {
				header('Location: '.CAT_ADMIN_URL.'/login/index.php');
				exit(0);
			}
			}

		// Auto header code
		if($auto_header == true) {
			$this->print_header();
		}

        if ( ! $user->checkPermission($section_name,$section_permission) )
        {
            $user->printFatalError('You are not allowed to do this!');
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
		$tpl_data['permission']['pages']		  = $user->checkPermission('pages','pages',false);
		$tpl_data['permission']['pages_add']	  = $user->checkPermission('pages','pages_add',false);
		$tpl_data['permission']['pages_add_l0']	  = $user->checkPermission('pages','pages_add_l0',false);
		$tpl_data['permission']['pages_modify']	  = $user->checkPermission('pages','pages_modify',false);
		$tpl_data['permission']['pages_delete']	  = $user->checkPermission('pages','pages_delete',false);
		$tpl_data['permission']['pages_settings'] = $user->checkPermission('pages','pages_settings',false);
		$tpl_data['permission']['pages_intro']	  = ( $user->checkPermission('pages','pages_intro',false) != true || INTRO_PAGE != 'enabled' ) ? false : true;

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
			'permission'			=> ( $user->checkPermission('start','start') ) ? true : false,
					'current'				=> ( 'start' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][1]	= array(
					'link'					=> CAT_ADMIN_URL . '/media/index.php',
			'title'					=> $this->lang->translate('Media'),
					'permission_title'		=> 'media',
			'permission'			=> (  $user->checkPermission('media','media') ) ? true : false,
					'current'				=> ( 'media' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][2]	= array(
					'link'					=> CAT_ADMIN_URL . '/settings/index.php',
			'title'					=> $this->lang->translate('Settings'),
					'permission_title'		=> 'settings',
			'permission'			=> (  $user->checkPermission('settings','settings') ) ? true : false,
					'current'				=> ( 'settings' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][3]	= array(
					'link'					=> CAT_ADMIN_URL . '/addons/index.php',
			'title'					=> $this->lang->translate('Addons'),
					'permission_title'		=> 'addons',
			'permission'			=> (  $user->checkPermission('addons','addons') ) ? true : false,
					'current'				=> ( 'addons' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][4]	= array(
					'link'					=> CAT_ADMIN_URL . '/admintools/index.php',
			'title'					=> $this->lang->translate('Admin-Tools'),
					'permission_title'		=> 'admintools',
			'permission'			=> (  $user->checkPermission('admintools','admintools') ) ? true : false,
					'current'				=> ( 'admintools' == strtolower($this->section_name) ) ? true : false
					);
		$tpl_data['MAIN_MENU'][5]	= array(
            'link'					=> CAT_ADMIN_URL . '/users/index.php',
			'title'					=> $this->lang->translate('Access'),
					'permission_title'		=> 'access',
			'permission'			=> ( $user->checkPermission('access','access') ) ? true : false,
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
	
	// Returns a system permission for a menu link
	public function get_link_permission($title) {
return true;
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
    public function get_page_permission($page,$action='admin') { return CAT_Pages::getInstance(-1)->getPagePermission($page,$action); }
}

?>