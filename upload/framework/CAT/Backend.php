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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Object', false))
{
    @include dirname(__FILE__) . '/Object.php';
}

if (!class_exists('CAT_Backend', false))
{
    class CAT_Backend extends CAT_Object
    {

        protected      $_config         = array( 'loglevel' => 8 );
        private static $instance        = array();

//$section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true
        /**
         * get instance; forwards to login page if the user is not logged in
         *
         * @access public
         * @return object
         **/
        public static function getInstance($section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                if(!CAT_Registry::defined('CAT_INITIALIZED'))
    include CAT_PATH.'/framework/initialize.php';
                $user = CAT_Users::getInstance();
       			if($user->is_authenticated() == false) {
    				header('Location: '.CAT_ADMIN_URL.'/login/index.php');
    				exit(0);
    			}
                self::$instance->section_name = $section_name;
                // Auto header code
        		if($auto_header == true) {
        			self::$instance->print_header();
        		}
            }
            return self::$instance;
        }   // end function getInstance()

    	/**
    	 * Returns a system permission for a menu link
    	 *
    	 * !!!FAKE FOR NOW!!!
    	 *
    	 * @access public
    	 * @return boolean
    	 **/
    	public function get_link_permission($title) {
            return true;
    	}

        /**
         *  Print the admin header
         *
         *  @access public
         */
        public function print_header()
        {
            global $parser;
            $tpl_data = array();
            $addons   = CAT_Helper_Addons::getInstance();
            $user     = CAT_Users::getInstance();

            // Connect to database and get website title
            $title = $this->db()->get_one(sprintf(
                "SELECT `value` FROM `%ssettings` WHERE `name`='website_title'",
                CAT_TABLE_PREFIX
            ));

            // ===================================
            // ! initialize template search path
            // ===================================
            $parser->setPath(CAT_THEME_PATH . '/templates');
            $parser->setFallbackPath(CAT_THEME_PATH . '/templates');

            // =================================
            // ! Add permissions to $tpl_data
            // =================================
            $tpl_data['permission']['pages']          = $user->checkPermission('pages', 'pages', false);
            $tpl_data['permission']['pages_add']      = $user->checkPermission('pages', 'pages_add', false);
            $tpl_data['permission']['pages_add_l0']   = $user->checkPermission('pages', 'pages_add_l0', false);
            $tpl_data['permission']['pages_modify']   = $user->checkPermission('pages', 'pages_modify', false);
            $tpl_data['permission']['pages_delete']   = $user->checkPermission('pages', 'pages_delete', false);
            $tpl_data['permission']['pages_settings'] = $user->checkPermission('pages', 'pages_settings', false);
            $tpl_data['permission']['pages_intro']    = ($user->checkPermission('pages', 'pages_intro', false) != true || INTRO_PAGE != 'enabled') ? false : true;

            if ($tpl_data['permission']['pages'] == true)
            {
                #$this->pg->setPerms($tpl_data['permission']);

                $tpl_data['DISPLAY_MENU_LIST']     = CAT_Registry::get('MULTIPLE_MENUS') != false ? true : false;
                $tpl_data['DISPLAY_LANGUAGE_LIST'] = CAT_Registry::get('PAGE_LANGUAGES') != false ? true : false;
                $tpl_data['DISPLAY_SEARCHING']     = CAT_Registry::get('SEARCH')         != false ? true : false;

                // ==========================
                // ! Get info for pagesTree
                // ==========================
                $pages = CAT_Helper_Page::getPages();
                // create LI content for ListBuilder
                foreach($pages as $i => $page)
                {
    $text = $parser->get('backend_pagetree_item',$page);
    $pages[$i]['text'] = $text;
                }

                // list of first level of pages
                $tpl_data['pages']          = CAT_Helper_ListBuilder::getInstance()->config(array(
                    '__li_level_css'       => true,
                    '__li_id_prefix'       => 'pageid_',
                    '__li_css_prefix'      => 'fc_page_',
                    '__li_has_child_class' => 'fc_expandable',
                    '__title_key'          => 'text',
                ))->tree( CAT_Helper_Page::getPages(), 0 );
                // todo: count editables first
                $tpl_data['pages_editable'] = true;


                // ==========================================
                // ! Get info for the form to add new pages
                // ==========================================
                $tpl_data['templates'] = $addons->get_addons(CAT_Registry::get('DEFAULT_TEMPLATE'), 'template', 'template');
                $tpl_data['languages'] = $addons->get_addons(CAT_Registry::get('DEFAULT_LANGUAGE'), 'language');
                $tpl_data['modules']   = $addons->get_addons('wysiwyg', 'module', 'page', CAT_Helper_Validate::fromSession('MODULE_PERMISSIONS'));
                $tpl_data['groups']    = $user->get_groups();

                // list of all parent pages for dropdown parent
                #$tpl_data['parents_list']  = $this->pg->pages_list(0, 0);
                // List of available Menus of default template
                #$tpl_data['TEMPLATE_MENU'] = $this->pg->get_template_menus();

                // ===========================================
                // ! Check and set permissions for templates
                // ===========================================
                foreach ($tpl_data['templates'] as $key => $template)
                {
                    $tpl_data['templates'][$key]['permissions'] = ($user->get_permission($template['VALUE'], 'template')) ? true : false;
                }
            }

            // =========================
            // ! Add Metadatas to Dwoo
            // =========================
            $tpl_data['META']['CHARSET']       = (true === defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : 'utf-8';
            $tpl_data['META']['LANGUAGE']      = strtolower(CAT_Registry::get('LANGUAGE'));
            $tpl_data['META']['WEBSITE_TITLE'] = $title;
            $tpl_data['CAT_VERSION']           = CAT_Registry::get('CAT_VERSION');
            $tpl_data['CAT_CORE']              = CAT_Registry::get('CAT_CORE');
            $tpl_data['PAGE_EXTENSION']        = CAT_Registry::get('PAGE_EXTENSION');

            $date_search             = array(
                'Y',
                'j',
                'n',
                'jS',
                'l',
                'F'
            );
            $date_replace            = array(
                'yy',
                'y',
                'm',
                'd',
                'DD',
                'MM'
            );
            $tpl_data['DATE_FORMAT'] = str_replace($date_search, $date_replace, CAT_Registry::get('DATE_FORMAT'));
            $time_search             = array(
                'H',
                'i',
                's',
                'g'
            );
            $time_replace            = array(
                'hh',
                'mm',
                'ss',
                'h'
            );
            $tpl_data['TIME_FORMAT'] = str_replace($time_search, $time_replace, CAT_Registry::get('TIME_FORMAT'));

            $tpl_data['HEAD']['SECTION_NAME'] = $this->lang()->translate(strtoupper(self::$instance->section_name));
            $tpl_data['DISPLAY_NAME']         = $user->get_display_name();
            $tpl_data['USER']                 = $user->get_user_details($user->get_user_id());

            // ===================================================================
            // ! Add arrays for main menu, options menu and the Preferences-Button
            // ===================================================================
            $tpl_data['MAIN_MENU'] = array();

            $tpl_data['MAIN_MENU'][0] = array(
                'link' => CAT_ADMIN_URL . '/start/index.php',
                'title' => $this->lang()->translate('Start'),
                'permission_title' => 'start',
                'permission' => ($user->checkPermission('start', 'start')) ? true : false,
                'current' => ('start' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][1] = array(
                'link' => CAT_ADMIN_URL . '/media/index.php',
                'title' => $this->lang()->translate('Media'),
                'permission_title' => 'media',
                'permission' => ($user->checkPermission('media', 'media')) ? true : false,
                'current' => ('media' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][2] = array(
                'link' => CAT_ADMIN_URL . '/settings/index.php',
                'title' => $this->lang()->translate('Settings'),
                'permission_title' => 'settings',
                'permission' => ($user->checkPermission('settings', 'settings')) ? true : false,
                'current' => ('settings' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][3] = array(
                'link' => CAT_ADMIN_URL . '/addons/index.php',
                'title' => $this->lang()->translate('Addons'),
                'permission_title' => 'addons',
                'permission' => ($user->checkPermission('addons', 'addons')) ? true : false,
                'current' => ('addons' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][4] = array(
                'link' => CAT_ADMIN_URL . '/admintools/index.php',
                'title' => $this->lang()->translate('Admin-Tools'),
                'permission_title' => 'admintools',
                'permission' => ($user->checkPermission('admintools', 'admintools')) ? true : false,
                'current' => ('admintools' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][5] = array(
                'link' => CAT_ADMIN_URL . '/users/index.php',
                'title' => $this->lang()->translate('Access'),
                'permission_title' => 'access',
                'permission' => ($user->checkPermission('access', 'access')) ? true : false,
                'current' => ('access' == strtolower($this->section_name)) ? true : false
            );

            // =======================================
            // ! Seperate access-link by permissions
            // =======================================
            if ($user->get_permission('users'))
            {
                $tpl_data['MAIN_MENU'][5]['link'] = CAT_ADMIN_URL . '/users/index.php';
            }
            elseif ($user->get_permission('groups'))
            {
                $tpl_data['MAIN_MENU'][5]['link'] = CAT_ADMIN_URL . '/groups/index.php';
            }

            $tpl_data['PREFERENCES'] = array(
                'link' => CAT_ADMIN_URL . '/preferences/index.php',
                'title' => $this->lang()->translate('Preferences'),
                'permission_title' => 'preferences',
                'permission' => ($this->get_link_permission('preferences')) ? true : false,
                'current' => ('preferences' == strtolower($this->section_name)) ? true : false
            );

            $tpl_data['section_name'] = strtolower($this->section_name);
            $tpl_data['page_id']
                = (CAT_Helper_Validate::sanitizeGet('page_id','numeric') && CAT_Helper_Validate::sanitizeGet('page_id') != '')
                ? CAT_Helper_Validate::sanitizeGet('page_id')
                : (
                      (CAT_Helper_Validate::sanitizePost('page_id','numeric') && CAT_Helper_Validate::sanitizePost('page_id') != '')
                    ? CAT_Helper_Validate::sanitizePost('page_id')
                    : false
                  );

            // ====================
            // ! Parse the header
            // ====================
            $parser->output('header', $tpl_data);

        } // end function print_header()

        /**
        * Print the admin footer
        *
        * @access public
        **/
        public function print_footer()
        {
            global $parser;
            $tpl_data = array();

            // initialize template search path
            $parser->setPath(CAT_THEME_PATH . '/templates');
            $parser->setFallbackPath(CAT_THEME_PATH . '/templates');

            $data['CAT_VERSION']                = CAT_Registry::get('CAT_VERSION');
            $data['CAT_CORE']                   = CAT_Registry::get('CAT_CORE');
            $data['permissions']['pages']       = CAT_Users::getInstance()->get_permission('pages','') ? true : false;

            // ========================================================================
            // ! Try to get the actual version of the backend-theme from the database
            // ========================================================================
            $backend_theme_version = '-';
            if (defined('DEFAULT_THEME'))
            {
                $backend_theme_version
                    = $this->db()->get_one(sprintf(
                          "SELECT `version` from `%saddons` where `directory`= '%s'",
                          CAT_TABLE_PREFIX,DEFAULT_THEME
                      ));
            }
            $data['THEME_VERSION'] = $backend_theme_version;
            $data['THEME_NAME']    = DEFAULT_THEME;

            // ====================
            // ! Parse the footer
            // ====================
            $parser->output('footer', $data);

            // ===================
            // ! Droplet support
            // ===================
            /*
            $this->html_output_storage = ob_get_contents();
            ob_clean();

            if ( true === $this->droplets_ok )
            {
                $this->html_output_storage = evalDroplets($this->html_output_storage);
            }
            echo $this->html_output_storage;
            */

        }   // end function print_footer()

        public function print_error($message, $link = 'index.php', $auto_footer = true)
        {
            CAT_Object::printError($message,$link);
        }
    	public function print_success($message, $redirect = 'index.php', $auto_footer = true)
    	{
    		CAT_Object::printMsg($message,$redirect,$auto_footer);
    	}


    }
}