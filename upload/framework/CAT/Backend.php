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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *   @review          17.07.2014 17:04:07
 *
 */

global $_be_mem, $_be_time;
$_be_time = microtime(TRUE);
$_be_mem  = memory_get_usage();
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
        private static $form            = NULL;

        /**
         * get instance; forwards to login page if the user is not logged in
         *
         * @access public
         * @return object
         **/
        public static function getInstance($section_name = 'Start', $section_permission = 'start', $auto_header = true, $auto_auth = true)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                if(!CAT_Registry::defined('CAT_INITIALIZED'))
                    include CAT_PATH.'/framework/initialize.php';
                $user = CAT_Users::getInstance();
                if($user->is_authenticated() == false && !defined('CAT_INSTALL_PROCESS'))
                {
                    header('Location: '.CAT_ADMIN_URL.'/login/index.php');
                    exit(0);
                }
    			elseif ( !defined('CAT_INSTALL_PROCESS') )
    				$user->checkPermission($section_name, $section_permission,$auto_auth);
                self::$instance->section_name = $section_name;
                global $parser;
                self::initPaths();
                $parser->setGlobals('TEMPLATE_MENU', CAT_Helper_Template::get_template_menus());
                // Auto header code
                if($auto_header == true)
                    self::$instance->print_header();
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
         * returns a list of backend pages (used for "initial page")
         *
         * @access public
         * @return array
         **/
        public static function getPages()
        {
            $self = self::getInstance('start','start',false);
            return array (
                $self->lang()->translate('Start')       => 'start/index.php',
                $self->lang()->translate('Addons')      => 'addons/index.php',
                $self->lang()->translate('Admin-Tools') => 'admintools/index.php',
                $self->lang()->translate('Groups')      => 'groups/index.php',
                $self->lang()->translate('Media')       => 'media/index.php',
                $self->lang()->translate('Preferences') => 'preferences/index.php',
                $self->lang()->translate('Settings')    => 'settings/index.php',
                $self->lang()->translate('Users')       => 'users/index.php',
            );
        }   // end function getPages()

        /**
         *
         * @access public
         * @return
         **/
        public static function getForms($section)
        {
            if(!self::$form)
            {
                $init = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME').'/forms.init.php');
                if(file_exists($init))
                    require $init;
                self::$form = \wblib\wbForms::getInstance();
                self::$form->set('wblib_url',CAT_URL.'/modules/lib_wblib/wblib');
                self::$form->set('lang_path',CAT_PATH.'/languages');
            }
            self::$form->loadFile('inc.forms.php',CAT_PATH.'/'.CAT_BACKEND_PATH.'/'.$section.'/inc');
            return self::$form;
        }   // end function getForms()


        /**
         * prints the top of the backend page
         *
         * @access public
         * @return void
         **/
        public static function print_banner()
        {
            global $page_id, $parser;
            $results_array = CAT_Helper_Page::properties($page_id);
            $user          = CAT_Users::get_user_details( $results_array['modified_by'] );
            $tpl_data      = array();
            foreach($results_array as $key => $value)
                $tpl_data[strtoupper($key)] = $value;
            $tpl_data['MODIFIED_BY']          = $user['display_name'];
            $tpl_data['MODIFIED_BY_USERNAME'] = $user['username'];
            $tpl_data['MODIFIED_WHEN']        = ($results_array['modified_when'] != 0)
                                              ? $modified_ts = CAT_Helper_DateTime::getDateTime($results_array['modified_when'])
                                              : false;
            $tpl_data['PAGE_HEADER']          = self::getInstance('')->lang()->translate('Modify page');
            $tpl_data['CUR_TAB']              = 'modify';
            $tpl_data['PAGE_LINK']            = CAT_Helper_Page::getLink($results_array['page_id']);
            $parser->output('backend_pages_header',$tpl_data);
            $parser->output('backend_pages_banner',$tpl_data);
        }   // end function print_banner()()
        

        /**
         *  Print the admin header
         *
         *  @access public
         *  @return void
         */
        public function print_header()
        {
            global $parser;
            $tpl_data = array();
            $addons   = CAT_Helper_Addons::getInstance();
            $user     = CAT_Users::getInstance();

            // Connect to database and get website title
            if(!CAT_Registry::exists('WEBSITE_TITLE'))
            {
                $title = $this->db()->query(
                    "SELECT `value` FROM `:prefix:settings` WHERE `name`='website_title'"
                )->fetchColumn();
                CAT_Registry::define('WEBSITE_TITLE',$title,true);
            }

            // check current URL for page tree
            $uri = CAT_Helper_Validate::get('_SERVER','SCRIPT_NAME');

            // init template search paths
            self::initPaths();

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

                $tpl_data['DISPLAY_MENU_LIST']     = CAT_Registry::get('MULTIPLE_MENUS') != false ? true : false;
                $tpl_data['DISPLAY_LANGUAGE_LIST'] = CAT_Registry::get('PAGE_LANGUAGES') != false ? true : false;
                $tpl_data['DISPLAY_SEARCHING']     = CAT_Registry::get('SEARCH')         != false ? true : false;

                // ==========================
                // ! Get info for pagesTree
                // ==========================
                $pages    = CAT_Helper_Page::getPages(true);
                $sections = CAT_Helper_Page::getSections();

                // create LI content for ListBuilder
                foreach($pages as $i => $page)
                {
                    if(isset($sections[$page['page_id']]) && count($sections[$page['page_id']]))
                    {
                        $page['page_title'] .= "\n".count($sections[$page['page_id']]).' '.$user->lang()->translate('active sections').':';
                        foreach($sections[$page['page_id']] as $block_id => $section)
                            foreach( $section as $item )
                                $page['page_title'] .= "\n".$item['module'].' (ID:'.$item['section_id'].')';
                    }
                    $text = $parser->get(
                        'backend_pagetree_item',
                        array_merge(
                            array_merge($page,$tpl_data),
                            array(
                                'action' => ( pathinfo($uri,PATHINFO_FILENAME) == 'lang_settings' )
                                         ? 'lang_settings'
                                         : 'modify'
                            )
                        )
                    );
                    $pages[$i]['text'] = $text;
                }

                // list of first level of pages
                $tpl_data['pages']          = CAT_Helper_ListBuilder::getInstance()->config(array(
                    '__li_level_css'       => true,
                    '__li_id_prefix'       => 'pageid_',
                    '__li_css_prefix'      => 'fc_page_',
                    '__li_has_child_class' => 'fc_expandable',
                    '__is_open_key'        => 'be_tree_is_open',
                    '__li_is_open_class'   => 'fc_tree_open',
                    '__li_is_closed_class' => 'fc_tree_close',
                    '__title_key'          => 'text',
                ))->tree( $pages, 0 );

                // number of editable pages (for current user)
                $tpl_data['pages_editable'] = CAT_Helper_Page::getEditable();

                // ==========================================
                // ! Get info for the form to add new pages
                // ==========================================
                $tpl_data['templates'] = $addons->get_addons(CAT_Registry::get('DEFAULT_TEMPLATE'), 'template', 'template');
                $tpl_data['languages'] = $addons->get_addons(CAT_Registry::get('DEFAULT_LANGUAGE'), 'language');
                $tpl_data['modules']   = $addons->get_addons('wysiwyg', 'module', 'page');
                $tpl_data['groups']    = $user->get_groups();

                // ===========================================
                // ! Check and set permissions for templates
                // ===========================================
                foreach ($tpl_data['templates'] as $key => $template)
                    $tpl_data['templates'][$key]['permissions']
                        = ($user->get_permission($template['VALUE'], 'template'))
                        ? true
                        : false
                        ;
            }

            // =========================
            // ! Add Metadatas to Dwoo
            // =========================
            $tpl_data['META']['CHARSET']       = (true === defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : 'utf-8';
            $tpl_data['META']['LANGUAGE']      = strtolower(CAT_Registry::get('LANGUAGE'));
            $tpl_data['META']['WEBSITE_TITLE'] = WEBSITE_TITLE;
            $tpl_data['CAT_VERSION']           = CAT_Registry::get('CAT_VERSION');
            $tpl_data['CAT_CORE']              = CAT_Registry::get('CAT_CORE');
            $tpl_data['PAGE_EXTENSION']        = CAT_Registry::get('PAGE_EXTENSION');

            $date_search  = array('Y','j','n','jS','l','F');
            $date_replace = array('yy','y','m','d','DD','MM');
            $tpl_data['DATE_FORMAT'] = str_replace($date_search, $date_replace, CAT_Registry::get('CAT_DATE_FORMAT'));

            $time_search  = array('H','i','s','g');
            $time_replace = array('hh','mm','ss','h');
            $tpl_data['TIME_FORMAT'] = str_replace($time_search, $time_replace, CAT_Registry::get('TIME_FORMAT'));

            $tpl_data['SESSION']     = session_name();

            $tpl_data['HEAD']['SECTION_NAME'] = $this->lang()->translate(strtoupper(self::$instance->section_name));
            $tpl_data['DISPLAY_NAME']         = $user->get_display_name();
            $tpl_data['USER']                 = $user->get_user_details($user->get_user_id());

            // ===================================================================
            // ! Add arrays for main menu, options menu and the Preferences-Button
            // ===================================================================
            $tpl_data['MAIN_MENU'] = array();

            $tpl_data['MAIN_MENU'][0] = array(
                'link'             => CAT_ADMIN_URL . '/start/index.php',
                'title'            => $this->lang()->translate('Start'),
                'permission_title' => 'start',
                'permission'       => ($user->checkPermission('start', 'start')) ? true : false,
                'current'          => ('start' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][1] = array(
                'link'             => CAT_ADMIN_URL . '/media/index.php',
                'title'            => $this->lang()->translate('Media'),
                'permission_title' => 'media',
                'permission'       => ($user->checkPermission('media', 'media')) ? true : false,
                'current'          => ('media' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][2] = array(
                'link'             => CAT_ADMIN_URL . '/settings/index.php',
                'title'            => $this->lang()->translate('Settings'),
                'permission_title' => 'settings',
                'permission'       => ($user->checkPermission('settings', 'settings')) ? true : false,
                'current'          => ('settings' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][3] = array(
                'link'             => CAT_ADMIN_URL . '/addons/index.php',
                'title'            => $this->lang()->translate('Addons'),
                'permission_title' => 'addons',
                'permission'       => ($user->checkPermission('addons', 'addons')) ? true : false,
                'current'          => ('addons' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][4] = array(
                'link'             => CAT_ADMIN_URL . '/admintools/index.php',
                'title'            => $this->lang()->translate('Admin-Tools'),
                'permission_title' => 'admintools',
                'permission'       => ($user->checkPermission('admintools', 'admintools')) ? true : false,
                'current'          => ('admintools' == strtolower($this->section_name)) ? true : false
            );
            $tpl_data['MAIN_MENU'][5] = array(
                'link'             => CAT_ADMIN_URL . '/users/index.php',
                'title'            => $this->lang()->translate('Access'),
                'permission_title' => 'access',
                'permission'       => ($user->checkPermission('access', 'access')) ? true : false,
                'current'          => ('access' == strtolower($this->section_name)) ? true : false
            );

            // =======================================
            // ! Seperate access-link by permissions
            // =======================================
            if ($user->get_permission('users'))
                $tpl_data['MAIN_MENU'][5]['link'] = CAT_ADMIN_URL . '/users/index.php';
            elseif ($user->get_permission('groups'))
                $tpl_data['MAIN_MENU'][5]['link'] = CAT_ADMIN_URL . '/groups/index.php';

            $tpl_data['PREFERENCES'] = array(
                'link' => CAT_ADMIN_URL . '/preferences/index.php',
                'title' => $this->lang()->translate('Preferences'),
                'permission_title' => 'preferences',
                'permission'       => ($this->get_link_permission('preferences'))
                                   ? true
                                   : false,
                'current'          => ('preferences' == strtolower($this->section_name))
                                   ? true
                                   : false,
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
        public static function print_footer()
        {
            global $parser;
            $tpl_data = array();

            // init template search paths
            self::initPaths();

            $data['CAT_VERSION']          = CAT_Registry::get('CAT_VERSION');
            $data['CAT_BUILD']            = CAT_Registry::get('CAT_BUILD');
            $data['CAT_CORE']             = CAT_Registry::get('CAT_CORE');
            $t = ini_get('session.gc_maxlifetime');
            $data['SESSION_TIME'] = sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
            $data['permissions']['pages'] = CAT_Users::checkPermission('pages','pages') ? true : false;

            $self = ( isset($this) && is_object($this) ) ? $this : self::getInstance();

            // ========================================================================
            // ! Try to get the actual version of the backend-theme from the database
            // ========================================================================
            $backend_theme_version = '-';
            if (defined('DEFAULT_THEME'))
            {
                $backend_theme_version
                    = $self->db()->query(
                          "SELECT `version` from `:prefix:addons` where `directory`=:theme",
                          array('theme'=>DEFAULT_THEME)
                      )->fetchColumn();
            }
            $data['THEME_VERSION'] = $backend_theme_version;
            $data['THEME_NAME']    = DEFAULT_THEME;

            global $_be_mem, $_be_time;
            $data['system_information'] = array(
                array(
                    'name'      => $self->lang()->translate('PHP version'),
                    'status'    => phpversion(),
                ),
                array(
                    'name'      => $self->lang()->translate('Memory usage'),
                    'status'    => '~ ' . sprintf('%0.2f',( (memory_get_usage() - $_be_mem) / (1024 * 1024) )) . ' MB'
                ),
                array(
                    'name'      => $self->lang()->translate('Script run time'),
                    'status'    => '~ ' . sprintf('%0.2f',( microtime(TRUE) - $_be_time )) . ' sec'
                ),
            );

            // ====================
            // ! Parse the footer
            // ====================
            $parser->output('footer', $data);

            // ======================================
            // ! make sure to flush the output buffer
            // ======================================
            if(ob_get_level()>1)
                while (ob_get_level() > 0)
                    ob_end_flush();

        }   // end function print_footer()

        /**
         * print error message and exit
         *
         * @access public
         * @param  string  $message
         * @param  string  $redirect     - default 'index.php'
         * @param  boolean $print_header - default true
         **/
        public function print_error($message, $redirect = 'index.php', $print_header = true)
        {
            if(isset($_REQUEST['_cat_ajax']))
            {
                echo json_encode(array('message'=>$message,'success'=>false));
            }
            CAT_Object::printError($message,$redirect,$print_header);
            self::print_footer();
            exit();
        }   // end function print_error()
        /**
         * print message
         *
         * @access public
         * @param  string  $message
         * @param  string  $redirect    - default 'index.php'
         * @param  boolean $auto_footer - default true
         **/
        public function print_success($message, $redirect = 'index.php', $auto_footer = true)
        {
            CAT_Backend::updateWhenModified();
            CAT_Object::printMsg($message,$redirect,$auto_footer);
        }   // end function print_success()

        /**
         * checks if the current path is inside the backend folder
         *
         * @access public
         * @return boolean
         **/
        public static function isBackend()
        {
            $url = CAT_Helper_Validate::sanitizeServer('SCRIPT_NAME');
            if ( preg_match( '~/'.CAT_BACKEND_FOLDER.'/~i', $url ) )
                return true;
            else
                return false;
        }   // end function isBackend()
        
        /**
         * initializes template search paths for backend
         *
         * @access public
         * @return
         **/
        public static function initPaths()
        {
            global $parser;
            // ===================================
            // ! initialize template search path
            // ===================================
            $parser->setPath(CAT_THEME_PATH.'/templates/default','backend');
            $parser->setFallbackPath(CAT_THEME_PATH.'/templates/default','backend');

            if(file_exists(CAT_THEME_PATH.'/templates/default'))
            {
                $parser->setPath(CAT_THEME_PATH.'/templates/default','backend');
                if(!CAT_Registry::exists('DEFAULT_THEME_VARIANT') || CAT_Registry::get('DEFAULT_THEME_VARIANT') == '')
                {
                    CAT_Registry::set('DEFAULT_THEME_VARIANT','default');
                    $parser->setGlobals('DEFAULT_THEME_VARIANT','default');
                }
            }
            if(CAT_Registry::get('DEFAULT_THEME_VARIANT') != '' && file_exists(CAT_THEME_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME_VARIANT')))
            {
                $parser->setPath(CAT_THEME_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME_VARIANT'),'backend');
            }
        }   // end function initPaths()

        /**
         *
         * @access public
         * @return
         **/
        public static function updateWhenModified()
        {
            global $update_when_modified, $page_id, $section_id;
            // if changes were made, the var might be set
            if(isset($update_when_modified) && $update_when_modified == true) {
                self::getInstance()->db()->query(
                    "UPDATE `:prefix:pages` SET modified_when=:mod, modified_by=:by WHERE page_id=:id",
                    array('mod'=>time(),'by'=>CAT_Users::get_user_id(),'id'=>$page_id)
                );
                if ( $section_id )
                {
                    self::getInstance()->db()->query(
                        "UPDATE `:prefix:sections` SET modified_when=:mod, modified_by=:by WHERE section_id=:id",
                        array('mod'=>time(),'by'=>CAT_Users::get_user_id(),'id'=>$section_id)
                    );
                }
            }
        }   // end function updateWhenModified()
        
        /**
         * methods declared in class.wb.php in WB, needed here for modules like Bakery
         **/
        public function add_slashes($input)     { return CAT_Helper_Validate::add_slashes($input);    }

    }
}