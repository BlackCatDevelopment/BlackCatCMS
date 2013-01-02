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
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

require_once(LEPTON_PATH.'/framework/class.wb.php');

// Include PHPLIB template class
require_once(LEPTON_PATH."/include/phplib/template.inc");

// Get LEPTON version
require_once(ADMIN_PATH.'/interface/version.php');

// Include EditArea wrapper functions
require_once(LEPTON_PATH . '/include/editarea/wb_wrapper_edit_area.php');

class admin extends wb
{
	private $db_handle = NULL;

	public $header_storrage = array(
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
				header('Location: '.ADMIN_URL.'/login/index.php');
				exit(0);
			}
			
			// Now check whether he has a valid token
			if (!$this->checkToken()) {
				unset($_SESSION['USER_ID']);
				header('Location: '.ADMIN_URL.'/login/index.php');
				exit(0);
			}
						
			// Now check if they are allowed in this section
			if($this->get_permission($section_permission) == false) {
				die($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES']);
			}
		}
		
		// Check if the backend language is also the selected language. If not, send headers again.

		$get_user_language = $this->db_handle->query("SELECT language FROM ".TABLE_PREFIX.
			"users WHERE user_id = '" .(int) $this->get_user_id() ."'");
		$user_language = ($get_user_language) ? $get_user_language->fetchRow() : '';
		// prevent infinite loop if language file is not XX.php (e.g. DE_du.php)
		$user_language = substr($user_language[0],0,2);
		// obtain the admin folder (e.g. /admin)
		$admin_folder = str_replace(LEPTON_PATH, '', ADMIN_PATH);
		if((LANGUAGE != $user_language) && file_exists(LEPTON_PATH .'/languages/' .$user_language .'.php')
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
		$parser->setPath(THEME_PATH . '/templates');
		$parser->setFallbackPath(THEME_PATH . '/templates');

		// Auto header code
		if($auto_header == true) {
			$this->print_header();
		}
		/**
		 *	Droplet support
		 *
		 */
		if ( file_exists(LEPTON_PATH .'/modules/dropleps/droplets.php') ) {
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
				require_once(LEPTON_PATH .'/modules/dropleps/droplets.php');
				# $this->droplets_ok = true;
			}
		}
	}
	
	/**
	 *	Print the admin header
	 *
	 */
	public function print_header($body_tags = '')
	{
		// Get vars from the language file
		global $MENU;
		global $MESSAGE;
		global $TEXT;

		// Connect to database and get website title
		$title = $this->db_handle->get_one("SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='website_title'");

		// ======================================================================================= 
		// ! Try to include the info.php  of the template to seperate old and new TemplateEngine   
		// ======================================================================================= 
		if ( file_exists(THEME_PATH.'/info.php') )
		{
			include( THEME_PATH . '/info.php' );
			// ================================================================= 
			// ! Current controller to check, if it is a new template for Dwoo   
			// ================================================================= 
			if ( isset($template_engine) && $template_engine == 'dwoo' )
			{
				global $parser;
				$data_dwoo = array();

				// ============================================= 
				// ! Create the controller, if it is not set yet
				// ============================================= 
				if ( !is_object($parser) )
				{
					$parser					= new Dwoo();
					// ==================== 
					// ! Add URLs to Dwoo 	
					// ==================== 
					$data_dwoo['LEPTON_URL']	= LEPTON_URL;
					$data_dwoo['LEPTON_PATH']	= LEPTON_PATH;
					$data_dwoo['ADMIN_URL']		= ADMIN_URL;
					$data_dwoo['THEME_URL']		= THEME_URL;
					$data_dwoo['URL_HELP']		= 'http://www.lepton2.org/';
					// ============================= 
					// ! Add languages to Dwoo 	
					// ============================= 
					$data_dwoo['HEADING']		= $HEADING;
					$data_dwoo['TEXT']			= $TEXT;
					$data_dwoo['MESSAGE']		= $MESSAGE;
					$data_dwoo['MENU']			= $MENU;
				}

				// =================================== 
				// ! initialize template search path   
				// =================================== 
				$parser->setPath(THEME_PATH . '/templates');
				$parser->setFallbackPath(THEME_PATH . '/templates');

				// ================================= 
				// ! Add permissions to $data_dwoo   
				// ================================= 
				$data_dwoo['permission']['pages']			= $this->get_permission('pages') ? true : false;
				$data_dwoo['permission']['pages_add']		= $this->get_permission('pages_add') ? true : false;
				$data_dwoo['permission']['pages_add_l0']	= $this->get_permission('pages_add_l0') ? true : false;
				$data_dwoo['permission']['pages_modify']	= $this->get_permission('pages_modify') ? true : false;
				$data_dwoo['permission']['pages_delete']	= $this->get_permission('pages_delete') ? true : false;
				$data_dwoo['permission']['pages_settings']	= $this->get_permission('pages_settings') ? true : false;
				$data_dwoo['permission']['pages_intro']		= ( $this->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;


				if ( $data_dwoo['permission']['pages'] == true )
				{
					// Will be reviewed and optimized!
					require_once(LEPTON_PATH . '/framework/class.pages.php');
					$pages = new pages( $data_dwoo['permission'] );

					$data_dwoo['DISPLAY_MENU_LIST']				= MULTIPLE_MENUS	!= false ? true : false;
					$data_dwoo['DISPLAY_LANGUAGE_LIST']			= PAGE_LANGUAGES	!= false ? true : false;
					$data_dwoo['DISPLAY_SEARCHING']				= SEARCH			!= false ? true : false;

					// ========================== 
					// ! Get info for pagesTree   
					// ========================== 
					// list of first level of pages
					$data_dwoo['pages']				= $pages->make_list( 0, true );
					//$data_dwoo['pages']				= $pages->get_sections();
					$data_dwoo['pages_editable']	= $pages->pages_editable;
					//print_r($data_dwoo['pages']);

					// ========================================== 
					// ! Get info for the form to add new pages   
					// ========================================== 
					$data_dwoo['templates']			= $pages->get_addons( DEFAULT_TEMPLATE , 'template', 'template' );
					$data_dwoo['languages']			= $pages->get_addons( DEFAULT_LANGUAGE , 'language' );
					$data_dwoo['modules']			= $pages->get_addons( 'wysiwyg' , 'module', 'page',  $_SESSION['MODULE_PERMISSIONS'] );
					$data_dwoo['groups']			= $pages->get_groups();

					// list of all parent pages for dropdown parent
					$data_dwoo['parents_list']		= $pages->pages_list(0 , 0);
					// List of available Menus of default template
					$data_dwoo['TEMPLATE_MENU']		= $pages->get_template_menus();

					// =========================================== 
					// ! Check and set permissions for templates 	
					// =========================================== 
					foreach ($data_dwoo['templates'] as $key => $template)
					{
						$data_dwoo['templates'][$key]['permissions']	= ( $this->get_permission($template['VALUE'], 'template') ) ? true : false;
					}
				}

				// ========================= 
				// ! Add Metadatas to Dwoo 	
				// ========================= 
				$data_dwoo['META']['CHARSET']				= true === defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8';
				$data_dwoo['META']['LANGUAGE']				= strtolower(LANGUAGE);
				$data_dwoo['META']['WEBSITE_TITLE']			= $title;
				$data_dwoo['VERSION']						= VERSION;
				$data_dwoo['CORE']							= CORE;
				$data_dwoo['PAGE_EXTENSION']				= PAGE_EXTENSION;

				$date_search	= array('Y','j','n','jS','l','F');
				$date_replace	= array('yy','y','m','d','DD','MM');
				$data_dwoo['DATE_FORMAT']					= str_replace( $date_search, $date_replace, DATE_FORMAT );
				$time_search	= array('H','i','s','g');
				$time_replace	= array('hh','mm','ss','h');
				$data_dwoo['TIME_FORMAT']					= str_replace( $time_search, $time_replace, TIME_FORMAT );

				$data_dwoo['HEAD']['SECTION_NAME']			= $MENU[strtoupper($this->section_name)];
				$data_dwoo['HEAD']['BACKEND_MODULE_FILES']	= $this->__admin_register_backend_modfiles();
				$data_dwoo['DISPLAY_NAME']					= $this->get_display_name();
				$data_dwoo['USER']							= $this->get_user_details($this->get_user_id());
				/** 
				 * For what is this needed? - creativecat
				*/
				$data_dwoo['BODY_TAGS']						= $body_tags;

				// ===================================================================
				// ! Add arrays for main menu, options menu and the Preferences-Button
				// ===================================================================
				$data_dwoo['MAIN_MENU']		= array();

				$data_dwoo['MAIN_MENU'][0]	= array(
					'link'					=> ADMIN_URL . '/start/index.php',
					'title'					=> $MENU['START'],
					'permission_title'		=> 'start',
					'permission'			=> ( $this->get_link_permission('start') ) ? true : false,
					'current'				=> ( 'start' == strtolower($this->section_name) ) ? true : false
					);
				$data_dwoo['MAIN_MENU'][1]	= array(
					'link'					=> ADMIN_URL . '/media/index.php',
					'title'					=> $MENU['MEDIA'],
					'permission_title'		=> 'media',
					'permission'			=> ( $this->get_link_permission('media') ) ? true : false,
					'current'				=> ( 'media' == strtolower($this->section_name) ) ? true : false
					);
				$data_dwoo['MAIN_MENU'][2]	= array(
					'link'					=> ADMIN_URL . '/settings/index.php',
					'title'					=> $MENU['SETTINGS'],
					'permission_title'		=> 'settings',
					'permission'			=> ( $this->get_link_permission('settings') ) ? true : false,
					'current'				=> ( 'settings' == strtolower($this->section_name) ) ? true : false
					);
				$data_dwoo['MAIN_MENU'][3]	= array(
					'link'					=> ADMIN_URL . '/addons/index.php',
					'title'					=> $MENU['ADDONS'],
					'permission_title'		=> 'addons',
					'permission'			=> ( $this->get_link_permission('addons') ) ? true : false,
					'current'				=> ( 'addons' == strtolower($this->section_name) ) ? true : false
					);
				$data_dwoo['MAIN_MENU'][4]	= array(
					'link'					=> ADMIN_URL . '/admintools/index.php',
					'title'					=> $MENU['ADMINTOOLS'],
					'permission_title'		=> 'admintools',
					'permission'			=> ( $this->get_link_permission('admintools') ) ? true : false,
					'current'				=> ( 'admintools' == strtolower($this->section_name) ) ? true : false
					);
				$data_dwoo['MAIN_MENU'][5]	= array(
					'title'					=> $MENU['ACCESS'],
					'permission_title'		=> 'access',
					'permission'			=> ( $this->get_link_permission('access') ) ? true : false,
					'current'				=> ( 'access' == strtolower($this->section_name) ) ? true : false
					);

				// ======================================= 
				// ! Seperate access-link by permissions   
				// ======================================= 
				if ( $this->get_permission('users') )
				{
					$data_dwoo['MAIN_MENU'][5]['link']	= ADMIN_URL . '/users/index.php';
				}
				elseif ( $this->get_permission('groups') )
				{
					$data_dwoo['MAIN_MENU'][5]['link']	= ADMIN_URL . '/groups/index.php';
				}

				$data_dwoo['PREFERENCES']	= array(
					'link'					=> ADMIN_URL . '/preferences/index.php',
					'title'					=> $MENU['PREFERENCES'],
					'permission_title'		=> 'preferences',
					'permission'			=> ( $this->get_link_permission( 'preferences' ) ) ? true : false,
					'current'				=> ( 'preferences' == strtolower($this->section_name) ) ? true : false
					);

				// =========================================================== 
				// ! If Service is active add the Servicemenu to the options 	
				// =========================================================== 
				if ( (true === defined("LEPTON_SERVICE_ACTIVE")) && ( 1 == LEPTON_SERVICE_ACTIVE ) )
				{
					$data_dwoo['MAIN_MENU'][6]	= array(
						'link'					=> ADMIN_URL . '/service/index.php',
						'title'					=> $MENU['SERVICE'],
						'permission_title'		=> 'service',
						'permission'			=> ( $this->get_link_permission( 'service' ) ) ? true : false,
						'current'				=> ( 'service' == strtolower( $this->section_name ) ) ? true : false
						);
				}

				$data_dwoo['section_name']		= strtolower($this->section_name);
				$data_dwoo['page_id']			= ( is_numeric( $this->get_get('page_id') ) && $this->get_get('page_id') != '' ) ?
														$this->get_get('page_id') : ( ( is_numeric( $this->get_post('page_id') ) && $this->get_post('page_id') != '' ) ? 
														$this->get_post('page_id') : false );

				// ==================== 
				// ! Parse the header 	
				// ==================== 
				$parser->output('header.lte', $data_dwoo);

			}
			/**
			 * Marked as deprecated
			 * This is only for the old TE and will be removed in future versions
			*/
			else
			{
				$header_template	= new Template(THEME_PATH.'/templates');
		
				$header_template->set_file('page', 'header.htt');
				$header_template->set_block('page', 'header_block', 'header');
				
				$charset = ( true === defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : 'utf-8';
				
				// work out the URL for the 'View menu' link in the WB backend
				// if the page_id is set, show this page otherwise show the root directory of WB
				$view_url = LEPTON_URL;
				if ( isset($_GET['page_id']) )
				{
					// extract page link from the database
					$result		= $this->db_handle->query("SELECT `link` FROM `" .TABLE_PREFIX ."pages` WHERE `page_id`= '" .(int) addslashes($_GET['page_id']) ."'");
					$row		= $result->fetchRow( MYSQL_ASSOC );
					if ($row) $view_url .= PAGES_DIRECTORY .$row['link']. PAGE_EXTENSION;
				}
				
				/**
				 *	Try to get the actual version of the backend-theme from the database
				 *
				 */
				$backend_theme_version = "";
				if (defined('DEFAULT_THEME')) {
					$backend_theme_version = $this->db_handle->get_one("SELECT `version` from `".TABLE_PREFIX."addons` where `directory`='".DEFAULT_THEME."'");	
				}
				
				$header_template->set_var(	array(
						'SECTION_NAME' => $MENU[strtoupper($this->section_name)],
						'BODY_TAGS' => $body_tags,
						'WEBSITE_TITLE' => $title,
						'TEXT_ADMINISTRATION' => $TEXT['ADMINISTRATION'],
						'CURRENT_USER' => $MESSAGE['START_CURRENT_USER'],
						'DISPLAY_NAME' => $this->get_display_name(),
						'CHARSET' => $charset,
						'LANGUAGE' => strtolower(LANGUAGE),
						'VERSION' => VERSION,
						'CORE' => CORE,
						'LEPTON_URL' => LEPTON_URL,
						'WB_URL' => LEPTON_URL,
						'ADMIN_URL' => ADMIN_URL,
						'THEME_URL' => THEME_URL,
						'TITLE_START' => $MENU['START'],
						'TITLE_VIEW' => $MENU['VIEW'],
						'TITLE_HELP' => $MENU['HELP'],
						'TITLE_LOGOUT' =>  $MENU['LOGOUT'],
						'URL_VIEW' => $view_url,
						'URL_HELP' => 'http://www.lepton2.org/',
						'BACKEND_MODULE_FILES' => $this->__admin_register_backend_modfiles(),
						'THEME_VERSION'	=> $backend_theme_version,
						'THEME_NAME'	=> DEFAULT_THEME
					)
				);
		
				// Create the menu
				$menu = array(
					array(ADMIN_URL.'/pages/index.php', '', $MENU['PAGES'], 'pages', 1),
					array(ADMIN_URL.'/media/index.php', '', $MENU['MEDIA'], 'media', 1),
					array(ADMIN_URL.'/addons/index.php', '', $MENU['ADDONS'], 'addons', 1),
					array(ADMIN_URL.'/preferences/index.php', '', $MENU['PREFERENCES'], 'preferences', 0),
					array(ADMIN_URL.'/settings/index.php', '', $MENU['SETTINGS'], 'settings', 1),
					array(ADMIN_URL.'/admintools/index.php', '', $MENU['ADMINTOOLS'], 'admintools', 1),
					array(ADMIN_URL.'/access/index.php', '', $MENU['ACCESS'], 'access', 1)
				);
				if ( (true === defined("LEPTON_SERVICE_ACTIVE")) && ( 1 == LEPTON_SERVICE_ACTIVE )) {
						$menu[] = array(ADMIN_URL.'/service/index.php', '', $MENU['SERVICE'], 'service', 1);
				}
				$header_template->set_block('header_block', 'linkBlock', 'link');
				foreach($menu AS $menu_item) {
					$link = $menu_item[0];
					$target = ($menu_item[1] == '') ? '_self' : $menu_item[1];
					$title = $menu_item[2];
					$permission_title = $menu_item[3];
					$required = $menu_item[4];
					$replace_old = array(ADMIN_URL, LEPTON_URL, '/', 'index.php');
					if($required == false OR $this->get_link_permission($permission_title)) {
						$header_template->set_var('LINK', $link);
						$header_template->set_var('TARGET', $target);
						// If link is the current section apply a class name
						if($permission_title == strtolower($this->section_name)) {
							$header_template->set_var('CLASS', $menu_item[3] . ' current');
						} else {
							$header_template->set_var('CLASS', $menu_item[3]);
						}
						$header_template->set_var('TITLE', $title);
						// Print link
						$header_template->parse('link', 'linkBlock', true);
					}
				}
				$header_template->parse('header', 'header_block', false);
				$header_template->pparse('output', 'page');
			}
		}
		// If the script couldn't include the info.php, print an error message
		else
		{
			$this->print_error('info.php is missing in theme directory. Please check your backend theme if there is a info.php.');
		}
	}
	
	// Print the admin footer
	public function print_footer()
	{
		// ======================================================================================= 
		// ! Try to include the info.php  of the template to seperate old and new TemplateEngine   
		// ======================================================================================= 
		if ( file_exists(THEME_PATH.'/info.php') )
		{
			include( THEME_PATH . '/info.php' );
			// ================================================================= 
			// ! Current controller to check, if it is a new template for Dwoo   
			// ================================================================= 
			if ( isset($template_engine) && $template_engine == 'dwoo' )
			{
				global $parser;
				$data_dwoo = array();

				// ============================================= 
				// ! Create the controller, if it is not set yet
				// ============================================= 
				if (!is_object($parser))
				{
					$parser = new Dwoo();
					// ==================== 
					// ! Add URLs to Dwoo 	
					// ==================== 
					$data['LEPTON_URL']			= LEPTON_URL;
					$data['LEPTON_PATH']		= LEPTON_PATH;
					$data['ADMIN_URL']		= ADMIN_URL;
					$data['THEME_URL']		= THEME_URL;
					$data['URL_HELP']		= 'http://www.lepton2.org/';
					// ============================= 
					// ! Add languages to Dwoo 	
					// ============================= 
					$data['HEADING']		= $HEADING;
					$data['TEXT']			= $TEXT;
					$data['MESSAGE']		= $MESSAGE;
					$data['MENU']			= $MENU;
				}

				// initialize template search path
				$parser->setPath(THEME_PATH . '/templates');
				$parser->setFallbackPath(THEME_PATH . '/templates');

				$data['VERSION']					= VERSION;
				$data['CORE']						= CORE;
				$data['permissions']['pages']		= ($this->get_permission('pages')) ? true : false;

				// ======================================================================== 
				// ! Try to get the actual version of the backend-theme from the database 	
				// ======================================================================== 
				$backend_theme_version = '-';
				if (defined('DEFAULT_THEME'))
				{
					$backend_theme_version	= $this->db_handle->get_one( "SELECT `version` from `" . TABLE_PREFIX . "addons` where `directory`= '" . DEFAULT_THEME . "'");
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

				// ================================================== 
				// ! CSRF protection - add tokens to internal links 	
				// ================================================== 
				if ($this->is_authenticated()) {
					if (file_exists(LEPTON_PATH .'/framework/tokens.php')) {
						include_once(LEPTON_PATH .'/framework/tokens.php');
						if (function_exists('addTokens')) addTokens($this->html_output_storage, $this);
					}
				}
			}
			/**
			 * Marked as deprecated
			 * This is only for the old TE and will be removed in future versions
			*/
			else
			{
				$footer_template = new Template(THEME_PATH.'/templates');
				$footer_template->set_file('page', 'footer.htt');
				$footer_template->set_block('page', 'footer_block', 'header');
				$footer_template->set_var(array(
								'LEPTON_URL' => LEPTON_URL,
								'LEPTON_PATH' => LEPTON_PATH,
								'ADMIN_URL' => ADMIN_URL,
								'THEME_URL' => THEME_URL
					 			));
				$footer_template->parse('header', 'footer_block', false);
				$footer_template->pparse('output', 'page');
				
				/**
				 *	Droplet support
				 *
				 */
				$this->html_output_storage = ob_get_clean();
				if ( true === $this->droplets_ok ) {
					$this->html_output_storage = evalDroplets($this->html_output_storage);
				}
				
				// CSRF protection - add tokens to internal links
				if ($this->is_authenticated()) {
					if (file_exists(LEPTON_PATH .'/framework/tokens.php')) {
						include_once(LEPTON_PATH .'/framework/tokens.php');
						if (function_exists('addTokens')) addTokens($this->html_output_storage, $this);
					}
				}
			}

			// ================== 
			// ! Print the html 	
			// ================== 
			echo $this->html_output_storage;

		}
		// If the script couldn't include the info.php, print an error message
		else
		{
			$this->print_error('info.php is missing in theme directory. Please check your backend theme if there is a info.php.');
		}
	}

	// Return a system permission
	public function get_permission($name, $type = 'system') {
		// Append to permission type
		$type .= '_permissions';
		// Check if we have a section to check for
		if($name == 'start') {
			return true;
		} else {
			// Set system permissions var
			$system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
			// Set module permissions var
			$module_permissions = $this->get_session('MODULE_PERMISSIONS');
			// Set template permissions var
			$template_permissions = $this->get_session('TEMPLATE_PERMISSIONS');
			// Return true if system perm = 1
			if (isset($$type) && is_array($$type) && is_numeric(array_search($name, $$type))) {
				if($type == 'system_permissions') {
					return true;
				} else {
					return false;
				}
			} else {
				if($type == 'system_permissions') {
					return false;
				} else {
					return true;
				}
			}
		}
	}
		
	public function get_user_details($user_id) {
		$query_user = "SELECT username,display_name FROM ".TABLE_PREFIX."users WHERE user_id = '$user_id'";
		$get_user = $this->db_handle->query($query_user);
		if($get_user->numRows() != 0) {
			$user = $get_user->fetchRow(MYSQL_ASSOC);
		} else {
			$user['display_name'] = 'Unknown';
			$user['username'] = 'unknown';
		}
		return $user;
	}	
	
	public function get_page_details($page_id)
	{
		$query = "SELECT page_id,link,page_title,menu_title,modified_by,modified_when FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'";
		$results = $this->db_handle->query($query);
		if ( $this->db_handle->is_error() )
		{
			//$this->print_header(); --> Causes many problems for me! Why should we print the header again as it mostly done within other functions!
			$this->print_error($database->get_error());
		}
		if ( $results->numRows() == 0 )
		{
			//$this->print_header(); --> Causes many problems for me! Why should we print the header again as it mostly done within other functions!
			$this->print_error($MESSAGE['PAGES_NOT_FOUND']);
		}
		$results_array = $results->fetchRow(MYSQL_ASSOC);

		return $results_array;
	}	
	
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
			$results = $this->db_handle->query("SELECT $action_groups,$action_users FROM ".TABLE_PREFIX."pages WHERE page_id = '$page'");
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
			$s = LEPTON_URL."/modules/jsadmin/backend.css";
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
				$s = LEPTON_URL."/modules/jsadmin/backend.css";
				$html .=  "<link href=\"".$s."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
				$html .= "<!-- end addons backend files -->\n";
			}
			return $html;
		}
		
		$query = "SELECT `".$look_up_field."` from `".TABLE_PREFIX.$look_up_table."` where ".$look_up_where;
		
		$result = $this->db_handle->query( $query );
		
		if ($result) {

			while( false !== ($data = $result->fetchRow( MYSQL_ASSOC ) ) ) {
				
				if (in_array($data[$look_up_field], $this->header_storrage['modules'] ) ) continue;
				
				$this->header_storrage['modules'][] = $data[$look_up_field];
				
				$basepath = "/modules/".$data[$look_up_field]."/";
				
				foreach($files as $type=>$temp_files) {
					foreach($temp_files as $filename) {
						$f = $basepath.$filename;
						if (true == file_exists(LEPTON_PATH.$f)) {
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
}

?>