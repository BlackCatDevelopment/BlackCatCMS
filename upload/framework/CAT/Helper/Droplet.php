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

if (!class_exists('CAT_Helper_Droplet')) {

    if (!class_exists('CAT_Object', false)) {
        @include dirname(__FILE__).'/../Object.php';
    }

    if(file_exists(CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/search.droplets.php'))
        require_once CAT_PATH.'/modules/lib_search/search.droplets.php';

    class CAT_Helper_Droplet extends CAT_Object    {

        const field_id                = 'drop_id';
        const field_droplet_name      = 'drop_droplet_name';
        const field_page_id           = 'drop_page_id';
        const field_module_directory  = 'drop_module_dir';
        const field_type              = 'drop_type';
        const field_file              = 'drop_file';
        const field_timestamp         = 'drop_timestamp';

        const type_css                = 'css';
        const type_search             = 'search';
        const type_header             = 'header';
        const type_javascript         = 'javascript';
        const type_undefined          = 'undefined';

        protected      $_config       = array( 'loglevel' => 8 );
        private static $instance      = NULL;

        /**
         * create / get an instance (singleton)
         **/
        public static function getInstance()
        {
            if(!defined('CR'))   define('CR',chr(13));
            if(!defined('LF'))   define('LF',chr(10));
            if(!defined('CRLF')) define('CRLF',chr(13)+chr(10));
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * This is called by CAT_Helper_Page to load droplet files into the
         * page header
         *
         * @access public
         * @param  integer $page_id
         * @param  boolean $open_graph
         * @return string
         **/
        public static function getDropletsForHeader( $page_id, $open_graph=false )
        {
            $title         = NULL;
            $description   = NULL;
            $keywords      = NULL;
            $load_css      = '';
            $load_js       = '';
            $meta          = NULL;

            // set the default values for the Open Graph support
            $image         = '';
            $site_name     = WEBSITE_TITLE;
            $og_type       = 'article';
            $exec_droplets = true;

            $droplets      = self::getDropletsFromDB($page_id,'header');
            if(count($droplets))
            {
                foreach($droplets as $droplet)
                {
                    if (self::droplet_exists($droplet['drop_droplet_name'], $page_id))
                    {
                        // the droplet exists
                        if (file_exists(CAT_PATH.'/modules/'.$droplet['drop_module_dir'].'/droplet.extension.php'))
                        {
                            // we have to use the header informations from the droplet!
                            include(CAT_PATH.'/modules/'.$droplet['drop_module_dir'].'/droplet.extension.php');
                            $user_func = $droplet['drop_module_dir'].'_droplet_header';
                            if (function_exists($user_func))
                            {
                                $header = call_user_func($user_func, $page_id);
                                if (is_array($header))
                                {
                                    if (isset($header['title']) && !empty($header['title']))
                                        $title       = $header['title'];
                                    if (isset($header['description']) && !empty($header['description']))
                                        $description = $header['description'];
                                    if (isset($header['keywords']) && !empty($header['keywords']))
                                        $keywords    = $header['keywords'];
                                }
                            }
                        }
                    }
                    else {
                        // the droplet does not exists, so unregister it to avoid an overhead
                        self::unregister_droplet_header($droplet['drop_droplet_name'], $page_id);
                    }
                }
            }

            $droplets = self::getCSSJS($page_id);
            if(count($droplets))
            {
                foreach($droplets as $droplet)
                {
                    // go only ahead if the droplet exists
                    if (self::droplet_exists($droplet['drop_droplet_name']))
                    {
                        $checked      = false;
                        // first check if there exists a custom.* file ...
                        $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.$droplet['drop_module_dir'].'/custom.'.$droplet['drop_file']);
                        if (file_exists($file))
                        {
                            $checked = true;
                        }
                        else
                        {
                            // check for the regular file ...
                            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.$droplet['drop_module_dir'].'/'.$droplet['drop_file']);
                            if (file_exists($file))
                                $checked = true;
                        }
                        if ($checked)
                        {
                            // load the file
                            $file = str_replace(CAT_Helper_Directory::sanitizePath(CAT_PATH), CAT_URL, $file);
                            if ($droplet['drop_type'] == 'css')
                                $load_css .= sprintf(' <link rel="stylesheet" type="text/css" href="%s" media="screen" />', $file);
                            else
                                $load_js  .= sprintf(' <script type="text/javascript" src="%s"></script>', $file);
                        }
                    }
                    else
                    {
                        // unregister the droplet to prevent overhead
                        self::unregister_droplet($droplet['drop_droplet_name'], $droplet['drop_type'], $page_id);
                        // also unregister droplet search
                        self::unregister_droplet_search($droplet['drop_droplet_name'], $page_id);
                    }
                }
            }

            // check if a configuration file exists
            if (file_exists(CAT_PATH.'/modules/droplets/config.json'))
                $config = json_decode(file_get_contents(CAT_PATH.'/modules/droplets/config.json'), true);

            if (isset($config['og:website_title']))
                $site_name = $config['og:website_title'];

            if (!empty($keywords))
            {
                // process the keywords
                $ka            = explode(',', $keywords);
                $keyword_array = array();
                foreach ($ka as $keyword)
                {
                    $keyword = trim($keyword);
                    if (false !== strpos($keyword, '='))
                    {
                        list($command, $value) = explode('=', $keyword);
                        $command = trim(strtolower($command));
                        $value   = trim($value);
                        switch ($command)
                        {
                            case 'og:image':
                                $image = $value;
                                continue;
                            case 'og:type':
                                $og_type = strtolower($value);
                                continue;
                            case 'og:droplet':
                                if (strtolower($value) == 'false')
                                    $exec_droplets = false;
                                continue;
                            case 'og:exec':
                                if (strtolower($value) == 'false')
                                    $open_graph = false;
                                continue;
                        }
                    }
                    $keyword_array[] = $keyword;
                }
                // at least rewrite the keywords
                $keywords = implode(',', $keyword_array);
            }

            if (isset($config['og:droplets']))
                if (trim(strtolower($config['og:droplets'])) == 'false')
                    $exec_droplets = false;

            // try to get the first image from the content
            if (empty($image))
                if (false !== ($test = self::getFirstImageFromContent($page_id, $exec_droplets)))
                    $image = $test;

            // if no image is available look if a image is defined in config.json
            if (empty($image))
                if (isset($config['og:image']))
                    $image = $config['og:image'];

            if (!empty($image))
            {
                $ext = pathinfo(CAT_PATH.substr($image, strlen(CAT_URL)), PATHINFO_EXTENSION);
                if (!in_array(strtolower($ext), array('png','gif','jpg','jpeg')))
                    $image = '';
            }

            if ($open_graph && !empty($image))
            {
                $url = self::getURLbyPageID($page_id);
                $image_dimensions = '';
                if (substr($image, 0, strlen(CAT_URL)) == CAT_URL)
                {
                    list($width,$height) = getimagesize(CAT_PATH.substr($image, strlen(CAT_URL)));
                    $meta = '<meta property="og:image:width" content="'.$width.'" />'
                          . '<meta property="og:image:height" content="'.$height.'" />'
                          . '<meta property="og:image" content="'.$image.'" />'
                          . $image_dimensions
                          . '<meta property="og:type" content="'.$og_type.'" />'
                          . '<meta property="og:title" content="'.$title.'" />'
                          . '<meta property="og:description" content="'.$description.'" />'
                          . '<meta property="og:url" content="'.$url.'" />'
                          . '<meta property="og:site_name" content="'.$site_name.'" />'
                          . '<link rel="image_src" href="'.$image.'" />'
                          ;
                }
                else
                {
                    $meta = '<meta property="og:type" content="'.$og_type.'" />'
                          . '<meta property="og:title" content="'.$title.'" />'
                          . '<meta property="og:description" content="'.$description.'" />'
                          . '<meta property="og:url" content="'.$url.'" />'
                          . '<meta property="og:site_name" content="'.$site_name.'" />'
                          ;
                }
            }

            return array(
                'title'       => $title,
                'keywords'    => $keywords,
                'description' => $description,
                'css'         => $load_css,
                'js'          => $load_js,
                'meta'        => $meta,
            );

        } // getDropletsForHeader()

/*******************************************************************************
 * Droplets extensions
 ******************************************************************************/

        /**
         * cleans up droplet name by replacing [] and whitespaces
         *
         * @param  string $droplet_name
         * @return string $droplet_name
         */
        public static function clear_droplet_name($droplet_name) {
            $droplet_name = strtolower($droplet_name);
            $droplet_name = str_replace('[', '', $droplet_name);
            $droplet_name = str_replace(']', '', $droplet_name);
            $droplet_name = trim($droplet_name);
            return $droplet_name;
        }  // end function clear_droplet_name()

        /**
         * Checks if a droplet is installed
         *
         * @param  string $droplet_name
         * @param  int    $page_id
         * @return boolean
         */
        public static function droplet_exists($droplet_name)
        {
            $droplet_name = self::clear_droplet_name($droplet_name);
            $SQL          = "SELECT * FROM `:prefix:mod_droplets` WHERE `name`=:name";
            $params       = array('name'=>$droplet_name);
            $result       = self::getInstance()->db()->query($SQL,$params);
            if (is_object($result) && $result->rowCount() > 0)
                return true;
            return false;
        }  // end function droplet_exists()

        /**
         * Checks if given droplet is registered
         *
         * @param  string  $droplet_name
         * @param  string  $register_type
         * @param  int     $page_id - die PAGE_ID fuer die das Droplet registriert ist
         * @return boolean
         */
        public static function is_registered_droplet($droplet_name, $register_type, $page_id)
        {
            $droplet_name = self::clear_droplet_name($droplet_name);
            $SQL          = "SELECT `drop_page_id` FROM `:prefix:mod_droplets_extension` "
                          . "WHERE :field1:=:value1 AND :field2:=:value2 AND :field3:=:value3"
                          ;
            $params       = array(
                'field1' => self::field_droplet_name,
                'value1' => $droplet_name,
                'field2' => self::field_type,
                'value2' => $register_type,
                'field3' => self::field_page_id,
                'value3' => $page_id
            );
            $check = self::getInstance()->db()->query($SQL,$params)->fetchColumn();
            if(self::getInstance()->db()->isError() || !$check)
                return false;
            $result = ($check == $page_id) ? true : false;
            return $result;
        }   // end function is_registered_droplet()

        /***********************************************************************
         * SHORTCUT FUNCTIONS
         **********************************************************************/

        /**
         * Check if the Droplet $droplet_name is registered for search
         *
         * @param STR $droplet_name
         * @param INT REFRENCE $page_id - die PAGE_ID fuer die das Droplet registriert ist
         * @return BOOL
         */
        public static function is_registered_droplet_search($droplet_name, $page_id) {
            return self::is_registered_droplet($droplet_name, 'search', $page_id);
        }   // end function is_registered_droplet_search()

        /**
         * Check if the Droplet $droplet_name is registered for template header
         *
         * @param STR $droplet_name
         * @param INT REFERENCE $page_id
         * @return BOOL
         */
        public static function is_registered_droplet_header($droplet_name, $page_id) {
            return self::is_registered_droplet($droplet_name, 'header', $page_id);
        }   // end function is_registered_droplet_header()

        /**
         * Check wether the Droplet $droplet_name is registered for setting CSS Headers
         *
         * @param STR $droplet_name
         * @param INT $page_id
         * @return BOOL
         */
        public static function is_registered_droplet_css($droplet_name, $page_id) {
            return self::is_registered_droplet($droplet_name, 'css', $page_id);
        }   // end function is_registered_droplet_css()

        /**
         * Check if the Droplet $droplet_name is registered for JavaScript
         *
         * @param STR $droplet_name
         * @param INT $page_id
         * @return BOOL
         */
        public static function is_registered_droplet_js($droplet_name, $page_id) {
            return self::is_registered_droplet($droplet_name, 'javascript', $page_id);
        }   // end function is_registered_droplet_js()

        /**
         * Register Droplet
         *
         * @param  string  $droplet_name
         * @param  int     $page_id
         * @param  string  $module_directory - Modul Verzeichnis in dem die Suche die Datei droplet.extension.php findet
         * @return boolean
         */
        public static function register_droplet($droplet_name, $page_id, $module_directory, $register_type, $file='')
        {
            $self = self::getInstance();
            // clear the droplet name
            $droplet_name = self::clear_droplet_name($droplet_name);
            // nothing to do if the droplet does not exists
            if (!self::droplet_exists($droplet_name))
                return false;
            // nothing to do if the droplet is already registered
            if (self::is_registered_droplet($droplet_name, $register_type, $page_id))
                return true;
            // clear the module directory
            $module_directory = CAT_Helper_Directory::sanitizePath($module_directory);
            // insert
            $SQL = "INSERT INTO `:prefix:mod_droplets_extension` "
                 . "(`drop_droplet_name`,`drop_page_id`,`drop_module_dir`,`drop_type`,`drop_file`) "
                 . "VALUES ( ?, ?, ?, ?, ? )"
                 ;
            $params = array(
                $droplet_name, $page_id, $module_directory, $register_type, $file
            );
            $self->db()->query($SQL,$params);
            return $self->db()->isError();
        }   // end function register_droplet()

        /***********************************************************************
         * SHORTCUT FUNCTIONS
         **********************************************************************/

        /**
         * Register droplet for search
         *
         * @param STR $droplet_name
         * @param STR $page_id
         * @param STR $module_directory
         * @return BOOL
         */
        public static function register_droplet_search($droplet_name, $page_id, $module_directory) {
            return self::register_droplet($droplet_name, $page_id, $module_directory, 'search');
        } // end function register_droplet_search()

        /**
         * Register droplet for template header
         *
         * @param STR $droplet_name
         * @param STR $page_id
         * @param STR $module_directory
         * @return BOOL
         */
        public static function register_droplet_header($droplet_name, $page_id, $module_directory) {
            return self::register_droplet($droplet_name, $page_id, $module_directory, 'header');
        } // end function register_droplet_header()

        /**
         * Register CSS file for droplet
         *
         * @param STR $droplet_name
         * @param STR $page_id
         * @param STR $module_directory
         * @param STR $file
         * @return BOOL
         */
        public static function register_droplet_css($droplet_name, $page_id, $module_directory, $file) {
            return self::register_droplet($droplet_name, $page_id, $module_directory, 'css', $file);
        } // end function register_droplet_css()

        /**
         * Register JavaScript for droplet
         *
         * @param STR $droplet_name
         * @param STR $page_id
         * @param STR $module_directory
         * @param STR $file
         * @return BOOL
         */
        public static function register_droplet_js($droplet_name, $page_id, $module_directory, $file) {
            return self::register_droplet($droplet_name, $page_id, $module_directory, 'javascript', $file);
        } // end function register_droplet_js()

        /**
         * Removes a droplet
         *
         * @param STR $droplet_name
         * @return BOOL
         */
        public static function unregister_droplet($droplet_name, $register_type, $page_id)
        {
            $self = self::getInstance();
            // clear Droplet name
            $droplet_name = self::clear_droplet_name($droplet_name);
            // delete the record
            $SQL =  "DELETE FROM `:prefix:mod_droplets_extension` WHERE `drop_droplet_name`=:name "
                 . "AND `drop_type`=:type AND `drop_page_id`=:id"
                 ;
            $params = array(
                'name' => $droplet_name,
                'type' => $register_type,
                'id'   => $page_id
            );
            $self->db()->query($SQL,$params);
            if (!$self->db()->isError())
                return false;
            return true;
        }   // end function unregister_droplet()

        /***********************************************************************
         * SHORTCUT FUNCTIONS
         **********************************************************************/

        /**
         * removes droplet from search
         *
         * @param STR $droplet_name
         * @return BOOL
         */
        public static function unregister_droplet_search($droplet_name, $page_id) {
            return self::unregister_droplet($droplet_name, 'search', $page_id);
        }   // end function unregister_droplet_search()

        /**
         * Entfernt das angegebene Droplet aus dem Template Header
         *
         * @param STR $droplet_name
         * @return BOOL
         */
        public static function unregister_droplet_header($droplet_name, $page_id) {
            return self::unregister_droplet($droplet_name, 'header', $page_id);
        }   // end function unregister_droplet_header()

        /**
         * Entfernt die CSS Registrierung fuer das angegebene Droplet
         *
         * @param STR $droplet_name
         * @return BOOL
         */
        public static function unregister_droplet_css($droplet_name, $page_id) {
            return self::unregister_droplet($droplet_name, 'css', $page_id);
        }   // end function unregister_droplet_css()

        /**
         * Entfernt die JavaScript Registrierung fuer das angegebene Droplet
         *
         * @param STR $droplet_name
         * @return BOOL
         */
        public static function unregister_droplet_js($droplet_name, $page_id) {
            return self::unregister_droplet($droplet_name, 'javascript', $page_id);
        } // unregister_droplet_css()

        /**
         * Check the syntax of some PHP code.
         *
         * Found here:
         * https://stackoverflow.com/questions/3223899/php-eval-and-capturing-errors-as-much-as-possible
         *
         * @param  string  $code PHP code to check.
         * @return boolean|array If true, then check was successful, otherwise an array(message,line) of errors is returned.
         */
        public static function check_syntax($code){
            $braces=0;
            $inString=0;
            foreach (token_get_all('<?php ' . $code) as $token)
            {
                if (is_array($token)) {
                    switch ($token[0]) {
                        case T_CURLY_OPEN:
                        case T_DOLLAR_OPEN_CURLY_BRACES:
                        case T_START_HEREDOC: ++$inString; break;
                        case T_END_HEREDOC:   --$inString; break;
                    }
                } else if ($inString & 1) {
                    switch ($token) {
                        case '`': case '\'':
                        case '"': --$inString; break;
                    }
                } else {
                    switch ($token) {
                        case '`': case '\'':
                        case '"': ++$inString; break;
                        case '{': ++$braces; break;
                        case '}':
                            if ($inString) {
                                --$inString;
                            } else {
                                --$braces;
                                if ($braces < 0) break 2;
                            }
                            break;
                    }
                }
            }
            $inString = @ini_set('log_errors', 'off');
            $err_set = @ini_set('display_errors','on');
            ob_start();
            $braces || $code = "if(0){{$code}\n}";
            if (@eval($code) === false) {
                if ($braces) {
                    $braces = PHP_INT_MAX;
                } else {
                    false !== strpos($code,CR) && $code = strtr(str_replace(CRLF,LF,$code),CR,LF);
                    $braces = substr_count($code,LF);
                }
                $code = ob_get_clean();
                $code = strip_tags($code);
                if (preg_match('~syntax error, (.+) in .+ on line (\d+)~is', $code, $code)) {
                    $code[2] = (int) $code[2];
                    $code = $code[2] <= $braces
                        ? array($code[1] => $code[2])
                        : array('unexpected $end' . substr($code[1], 14) => $braces);
                } else $code = array('syntax error' => 0);
            } else {
                ob_end_clean();
                $code = true;
            }
            @ini_set('display_errors', $err_set);
            @ini_set('log_errors', $inString);
            return $code;
        }   // end function check_syntax()

        /**
         * gets the data of given droplet
         *
         * @access public
         * @param  integer  $id
         * @return array
         **/
        public static function getDroplet($id)
        {
            $query = self::getInstance()->db()->query(
                "SELECT * FROM `:prefix:mod_droplets` WHERE id = :id",
                array('id'=>$id)
            );
            $data = $query->fetch();
            return $data;
        }   // end function getDroplet()

        /**
         *
         * @access public
         * @return
         **/
        public static function getDropletByName($name)
        {
            $stmt = self::getInstance()->db()->query(
                'SELECT * FROM `:prefix:mod_droplets` WHERE name=:name',
                array('name'=>$name)
            );
            return $stmt->fetch();
        }   // end function getDropletByName()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getDropletData($id)
        {
            $query = $backend->db()->query(
                'SELECT * FROM `:prefix:mod_droplets` AS t1 LEFT OUTER JOIN `:prefix:mod_droplets_permissions` AS t2 ' .
                'ON t1.id=t2.id WHERE t1.id=:id',
                array('id'=>$id)
            );
            return $query->fetch();
        }   // end function getDropletData()
        
        /**
         * returns a list of droplets the current user is allowed to use
         *
         * @access public
         * @return array
         **/
        public static function getDroplets( $with_code = false )
        {
            $self    = self::getInstance();
            $groups  = CAT_Users::get_groups_id();
            $rows    = array();
            $fields  = 't1.id, `name`, `description`, `active`, `comments`, `view_groups`, `edit_groups`';

            if ( $with_code )
                $fields .= ', `code`';

            $query = $self->db()->query(
                  "SELECT $fields FROM `:prefix:mod_droplets` AS t1 "
                . "LEFT OUTER JOIN `:prefix:mod_droplets_permissions` AS t2 "
                . "ON t1.id=t2.id ORDER BY `name` ASC"
            );

            if ( $query->rowCount() )
            {
                while ( $droplet = $query->fetch() )
                {
                    // the current user needs global edit permissions, or specific edit permissions to see this droplet
                    if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
                    {
                        // get edit groups for this drople
                        if ( $droplet['edit_groups'] )
                        {
                            if ( CAT_Users::get_user_id() != 1 && !is_in_array( $droplet['edit_groups'], $groups ) )
                                continue;
                            else
                                $droplet['user_can_modify_this'] = true;
                        }
                    }
                    $comments = str_replace( array("\r\n", "\n", "\r"), '<br />', $droplet['comments'] );
                    if ( !strpos( $comments, "[[" ) ) //
                    {
                        $comments = '<span class="usage">'
                                  . $self->lang()->translate( 'Use' )
                                  . ": [[" . $droplet['name'] . "]]</span><br />"
                                  . $comments;
                    }
                    $comments = str_replace( array("[[","]]"), array('<b>[[',']]</b>'), $comments );
                    if ( $with_code )
                        $droplet['valid_code']   = self::check_syntax( $droplet['code'] );

                    $droplet['comments']     = $comments;

                    // droplet included in search?
                    //$droplet['is_in_search'] = self::is_registered_droplet_search($droplet['name']);

                    // is there a data file for this droplet?
                    if ( file_exists( dirname( __FILE__ ) . '/data/' . $droplet['name'] . '.txt' ) || file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $droplet['name'] ) . '.txt' ) || file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet['name'] ) . '.txt' ) )
                    {
                        $droplet['datafile'] = true;
                    }
                    array_push( $rows, $droplet );
                }
            }

            return $rows;

        }   // end function getDroplets()

        /**
         * Returns the URL of the first image found in a WYSIWYG section
         *
         * @param INT $page_id
         * @return STR URL oder BOOL FALSE
         */
        public static function getFirstImageFromContent($page_id, $exec_droplets=true)
        {
            $self = self::getInstance();
            $img  = array();
            $__CAT_Helper_Droplets_content = '';

            $section = CAT_Sections::getSectionsByType($page_id);
            if(count($section))
            {
                $SQL    = "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=:id";
                $params = array('id'=>$section[0]['section_id']);
                $result = $self->db()->query($SQL,$params)->fetchColumn();
                if ($self->db()->isError())
                    return false;
                if (is_string($result))
                    $__CAT_Helper_Droplets_content = self::unsanitizeText($result);
            }

            if (!empty($__CAT_Helper_Droplets_content))
            {
                // scan content for images
                if ($exec_droplets && file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH .'/modules/droplets/droplets.php')))
                {
                    // we must process the droplets to get the real output content
                    $_SESSION['DROPLET_EXECUTED_BY_DROPLETS_EXTENSION'] = true;
                    ob_start();
                        include_once CAT_Helper_Directory::sanitizePath(CAT_PATH .'/modules/droplets/droplets.php');
                        if (function_exists('evalDroplets')) {
                            try {
                                $__CAT_Helper_Droplets_content = evalDroplets($__CAT_Helper_Droplets_content);
                            } catch (Exception $e) {
                                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $e->getMessage()), E_USER_ERROR);
                            }
                        }
                    ob_end_clean();
                    unset($_SESSION['DROPLET_EXECUTED_BY_DROPLETS_EXTENSION']);
                }
                if (preg_match('/<img[^>]*>/', $__CAT_Helper_Droplets_content, $matches))
                {
                    preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $matches[0], $attr);
                    foreach ($attr as $attributes)
                    {
                        foreach ($attributes as $attribut)
                        {
                            if (strpos($attribut, "=") !== false)
                            {
                                list($key, $value) = explode("=", $attribut);
                                $value = trim($value);
                                $value = substr($value, 1, strlen($value) - 2);
                                $img[strtolower(trim($key))] = trim($value);
                            }
                        }
                    }
                }
            }

            if (isset($img['src']))
            {
                $image = $img['src'];
                if (strpos($image, '..') !== false)
                {
                    $image = substr($image, strpos($image, MEDIA_DIRECTORY.'/'));
                    $image = CAT_URL.$image;
                }
                return $image;
            }
            else
            {
                return false;
            }
        }   // end function getFirstImageFromContent()

        public static function getURLbyPageID($page_id) {
            $link = CAT_Helper_Page::properties($page_id,'link');
            return
                  ($link)
                ? CAT_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION
                : false;
        }   // end function getURLbyPageID()

        /**
         * delete droplet; returns NULL in case of success, error text otherwise
         *
         * @access public
         * @param  integer  $id
         * @return mixed
         **/
        public static function deleteDroplet($id)
        {
            $self  = self::getInstance();
            $error = NULL;
            $self->db()->query(
                "DELETE FROM `:prefix:mod_droplets` WHERE id = :id",
                array('id'=>$id)
            );
            if ( $self->db()->isError() )
            {
                $error = $self->lang()->translate(
                    'Unable to delete Droplet [{{id}}] - {{error}}',
                    array( 'id' => $id, 'error' => $self->db()->getError() )
                );
            }
            return $error;
        }   // end function deleteDroplet()

        /**
         * insert a new droplet; returns new id on success, false on error
         *
         * @access public
         * @param  array  $values - droplet data
         * @return mixed
         **/
        public static function insertDroplet($values)
        {
            $self  = self::getInstance();
            $query = "INSERT INTO `:prefix:mod_droplets` VALUES "
        		   . "(NULL, :name, :code, :description, :time, :userid, :active, 1, 1, :wysiwyg, :comment )";

            // some defaults
            foreach(array('active','wysiwyg') as $key)
                if(!isset($values[$key]))
                    $values[$key] = 1;

            // add new droplet
            $result = $self->db()->query(
                $query, $values
            );
            if ( !$self->db()->isError() )
                return $self->db()->lastInsertId();
            else
                return false;

        }   // end function insertDroplet()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function updateDroplet($id,$values)
        {
            $self  = self::getInstance();
            // some defaults
            foreach(array('active','wysiwyg') as $key)
                if(!isset($values[$key]))
                    $values[$key] = 1;
            // Update row
            $self->db()->query(
                "UPDATE `:prefix:mod_droplets` SET name=:name, active=:active, show_wysiwyg=:wysiwyg, " .
                "description=:description, code=:code, comments=:comment, modified_when=:time, " .
                "modified_by=:userid WHERE id=:id",
                array_merge(array('id'=>$id),$values)
            );
            if ( !$self->db()->isError() )
                return true;
            else
                return false;
        }   // end function updateDroplet()

        /**
         *
         * @access public
         * @return
         **/
        public static function updateDropletPerms($values)
        {
            $self = self::getInstance();
            $self->db()->query(
                'REPLACE INTO `:prefix:mod_droplets_permissions` VALUES( :id, :edit, :view );',
                $values
            );
            return $self->db()->isError() ? false : true;
        }   // end function updateDropletPerms()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function updateDropletSettings($attr,$newval)
        {
            $self = self::getInstance();
            $self->db()->query(
                'UPDATE `:prefix:mod_droplets_settings` SET `value`=:val WHERE `attribute`=:attr',
                array('val' => $newval, 'attr' => $key )
            );
            return $self->db()->isError() ? false : true;
        }   // end function updateDropletSettings()

        /**
         * Unsanitize a text variable and prepare it for output
         *
         * @param string $text
         * @return string
         */
        public static function unsanitizeText($text) {
            $text = stripcslashes($text);
            $text = str_replace(array("&lt;","&gt;","&quot;","&#039;"), array("<",">","\"","'"), $text);
            return $text;
        } // unsanitizeText()

        /**
         *
         **/
        public static function is_allowed( $perm, $gid )
        {
            global $settings;
            // admin is always allowed to do all
            if ( CAT_Users::is_root() )
                return true;

            if ( !array_key_exists( $perm, $settings ) )
            {
                return false;
            }
            else
            {
                $value = $settings[ $perm ];
                if ( !is_array( $value ) )
                {
                    $value = array(
                         $value
                    );
                }
                return is_in_array( $value, $gid );
            }
            return false;
        } // end function is_allowed()


        /**
         * this method takes the output and processes the included droplets;
         * as droplet code may contain other droplets, the max. loop depth is
         * restricted to avoid endless loops
         *
         * @access public
         * @param  string  $__CAT_Helper_Droplets_content
         * @param  integer $max_loops - default 3
         * @return string
         **/
        public static function process( &$__CAT_Helper_Droplets_content, $max_loops = 3 )
        {
            $max_loops = ( (int) $max_loops = 0 ? 3 : (int) $max_loops );
            while ( ( self::evaluate($__CAT_Helper_Droplets_content) === true ) && ( $max_loops > 0 ) )
            {
                $max_loops--;
            }
            return $__CAT_Helper_Droplets_content;
        }   // end function process()

        /**
         * Install a Droplet from a ZIP file (the ZIP may contain more than one
         * Droplet)
         *
         * @access public
         * @param  string  $temp_file - name of the ZIP file
         * @return array   see droplets_import() method
         *
         **/
        public static function installDroplet( $temp_file )
        {
            $self       = self::getInstance();
            $temp_unzip = CAT_PATH.'/temp/droplets_unzip/';
            CAT_Helper_Directory::createDirectory( $temp_unzip );

            $errors  = array();
            $imports = array();
            $count   = 0;

            // extract file
            $list = CAT_Helper_Zip::getInstance($temp_file)->config( 'Path', $temp_unzip )->extract();

            // get .php files
            $files = CAT_Helper_Directory::getPHPFiles($temp_unzip,$temp_unzip.'/');

            // now, open all *.php files and search for the header;
            // an exported droplet starts with "//:"
            foreach( $files as $file )
            {
                if ( pathinfo($file,PATHINFO_FILENAME) !== 'index' && pathinfo($file,PATHINFO_EXTENSION) == 'php' )
                {
                    $description = NULL;
                    $usage       = NULL;
                    $code        = NULL;
                    // Name of the Droplet = Filename
                    $name        = pathinfo($file,PATHINFO_FILENAME);
                    // Slurp file contents
                    $lines       = file( $temp_unzip.'/'.$file );
                    // First line: Description
                    if ( preg_match( '#^//\:(.*)$#', $lines[0], $match ) ) {
                        $description = addslashes( $match[1] );
                        array_shift($lines);
                    }
                    // Second line: Usage instructions
                    if ( preg_match( '#^//\:(.*)$#', $lines[0], $match ) ) {
                        $usage       = addslashes( $match[1] );
                        array_shift($lines);
                    }
                    // there may be more comment lines; they will be added to the usage instructions
                    while(preg_match('#^//(.*)$#', $lines[0], $match ) ) {
                        $usage       .= addslashes(trim($match[1]));
                        array_shift($lines);
                    }

                    if ( ! $description && ! $usage ) {
                        // invalid file
                        $errors[$file] = CAT_Helper_Directory::getInstance()->lang()->translate( 'No valid Droplet file (missing description and/or usage instructions)' );
                        continue;
                    }
                    // Remaining: Droplet code
                    $code = implode( '', $lines );
                            // replace 'evil' chars in code
                            $tags = array('<?php', '?>' , '<?');
                    //$code = addslashes(str_replace($tags, '', $code));
                    $code = str_replace($tags,'',$code);
                            // Already in the DB?
                            $stmt  = 'INSERT';
                            $id    = NULL;
                    $found = $self->db()->query(
                        "SELECT * FROM `:prefix:mod_droplets` WHERE name=:name", array('name'=>$name)
                    );
                    if ( $found->rowCount() )
                    {
                        $stmt = 'REPLACE';
                        $id   = $found->fetchColumn();
                    }
                    // execute
                    $q = "$stmt INTO `:prefix:mod_droplets` SET "
                        . ( ($id) ? 'id='.$id.', ' : '' )
                        . '`name`=:name, `code`=:code, `description`=:desc, '
                        . '`modified_when`=:when, `modified_by`=:userid, '
                        . '`active`=:active, `comments`=:usage'
                        ;
                    $params = array(
                        'name'   => $name,
                        'code'   => $code,
                        'desc'   => $description,
                        'when'   => time(),
                        'userid' => CAT_Users::get_user_id(),
                        'active' => 1,
                        'usage'  => $usage
                    );

                    $result = $self->db()->query($q,$params);
                    if( ! $self->db()->isError() ) {
                        $count++;
                        $imports[$name] = 1;
                    }
                    else {
                        $errors[$name] = $self->db()->getError();
                    }
                }

                // check for data directory
                if ( file_exists( $temp_unzip.'/data' ) ) {
                    // copy all files
                    CAT_Helper_Directory::copyRecursive( $temp_unzip.'/data', dirname(__FILE__).'/data/' );
                }

            }

            // cleanup; ignore errors here
            CAT_Helper_Directory::removeDirectory($temp_unzip);

            return array( 'count' => $count, 'errors' => $errors, 'imported' => $imports );

        }   // end function installDroplet()

        /**
         * get droplets of given type (example: 'header') from the DB
         * returns array 
         *
         * @access private
         * @param  integer $page_id
         * @param  string  $type
         * @return array
         **/
        private static function getDropletsFromDB($page_id,$type)
        {
            $SQL    = "SELECT `drop_module_dir`,`drop_droplet_name` "
                    . "FROM `:prefix:mod_droplets_extension` "
                    . "WHERE `drop_type`=:type AND `drop_page_id`=:id LIMIT 1"
                    ;
            $params = array('type'=>$type,'id'=>$page_id);
            if (null == ($query = self::getInstance()->db()->query($SQL,$params)))
                return false;
            if ($query->rowCount() > 0)
                return $query->fetchAll();
            else
                return array();
        }   // end function getDropletsFromDB()

        /**
         *
         * @access private
         * @return array
         **/
        private static function getCSSJS($page_id)
        {
            $SQL = "SELECT * FROM `:prefix:mod_droplets_extension` "
                 . "WHERE (`drop_type`=:type1 OR `drop_type`=:type2) AND `drop_page_id`=:id"
                 ;
            $params = array('type1'=>'css', 'type2'=>'javascript','id'=>$page_id);
            $query = self::getInstance()->db()->query($SQL,$params);
            if(self::getInstance()->db()->isError())
            {
                return array();
            }
            if ($query->rowCount() > 0)
                return $query->fetchAll();
            else
                return array();
        }   // end function getDropletsForUse()
        

        /**
         * evaluates the droplet code
         *
         * @access private
         * @param  string   $_x_codedata
         * @param  string   $_x_varlist
         * @param  string   $__CAT_Helper_Droplets_content
         * @return eval result
         **/
        private static function do_eval( $_x_codedata, $_x_varlist, &$__CAT_Helper_Droplets_content )
        {
            global $wb, $admin, $wb_page_data;
            $wb_page_data =& $__CAT_Helper_Droplets_content;
            self::getInstance()->log()->LogDebug('evaluating: '.$_x_codedata);
            extract( $_x_varlist, EXTR_SKIP );
            if(self::check_syntax($_x_codedata)===true)
            {
                return @eval($_x_codedata);
            }
        }   // end function do_eval()

        /**
         * evaluates the droplets contained in $__CAT_Helper_Droplets_content
         *
         * @access public
         * @param  string $__CAT_Helper_Droplets_content
         * @return string
         **/
        private static function evaluate(&$__CAT_Helper_Droplets_content)
        {

            $self = self::getInstance();

            $self->log()->LogDebug('> evaluate() - processing content:');
            $self->log()->LogDebug($__CAT_Helper_Droplets_content);

            // collect all droplets from document
            $droplet_tags         = array();
            $droplet_replacements = array();

            if ( preg_match_all( '/\[\[(.*?)\]\]/', $__CAT_Helper_Droplets_content, $found_droplets ) )
            {
                foreach ( $found_droplets[1] as $droplet )
                {
                    if ( array_key_exists('[['.$droplet.']]', $droplet_tags ) === false )
                    {
                        // go in if same droplet with same arguments is not processed already
                        $varlist = array();
                        // split each droplet command into droplet_name and request_string
                        $tmp            = preg_split( '/\?/', $droplet, 2 );
                        $droplet_name   = $tmp[0];
                        $request_string = ( isset($tmp[1]) ? $tmp[1] : '' );
                        if ( $request_string != '' )
                        {
                            // make sure we can parse the arguments correctly
                            $request_string = html_entity_decode( $request_string, ENT_COMPAT, DEFAULT_CHARSET );
                            // create array of arguments from query_string
                            $argv = preg_split( '/&(?!amp;)/', $request_string );
                            foreach ( $argv as $argument )
                            {
                                // split argument in pair of varname, value
                                list( $variable, $value ) = explode( '=', $argument, 2 );
                                if ( !empty( $value ) )
                                {
                                    // re-encode the value and push the var into varlist
                                    $varlist[$variable] = htmlentities( $value, ENT_COMPAT, DEFAULT_CHARSET );
                                }
                            }
                        }
                        else
                        {
                            // no arguments
                            $droplet_name = $droplet;
                        }

                        $self->log()->LogDebug('doing request: '.sprintf(
                            'SELECT `code` FROM `%smod_droplets` WHERE `name` LIKE "%s" AND `active` = 1',
                            CAT_TABLE_PREFIX, $droplet_name
                        ));

                        // request the droplet code from database
                        $codedata = $self->db()->query(
                            'SELECT `code` FROM `:prefix:mod_droplets` WHERE `name` LIKE :name AND `active` = 1',
                            array('name'=>$droplet_name)
                        )->fetchColumn();

                        $self->log()->LogDebug('code: '.$codedata);

                        if ( !is_null($codedata) )
                        {
                            $newvalue = self::do_eval( $codedata, $varlist, $__CAT_Helper_Droplets_content );
                            $self->log()->LogDebug('eval result (newvalue): '.$newvalue);

                            // check returnvalue (must be a string of 1 char at least or (bool)true
                            if ( $newvalue == '' && $newvalue !== true )
                            {
                                $self->log()->LogDebug('newvalue is empty and not true (this is an error!)');
                                if ( $self->_config['loglevel'] == 7 )
                                {
                                    $newvalue = sprintf(
                                        '<span class="mod_droplets_err">Error evaluating droplet [[%s]]: no valid returnvalue.</span>',
                                        $droplet
                                    );
                                }
                                else
                                {
                                    $newvalue = true;
                                }
                            }
                            if ( $newvalue === true )
                            {
                                $self->log()->LogDebug('newvalue is true, set to empty string');
                                $newvalue = "";
                            }
                            $self->log()->LogDebug('newvalue before removing styles:',$newvalue);
                            // remove any defined CSS section from code. For valid XHTML a CSS-section is allowed inside <head>...</head> only!
                            $newvalue = preg_replace( '/<style.*>.*<\/style>/siU', '', $newvalue );
                            $self->log()->LogDebug('newvalue after removing styles:',$newvalue);
                        }
                        else
                        {
                            $self->log()->LogDebug('no such droplet!');
                            // just remove droplet placeholder if no code was found
                            if ( $self->_config['loglevel'] == 7 )
                            {
                                $newvalue = '<span class="mod_droplets_err">No such droplet: ' . $droplet . '</span>';
                            }
                            else
                            {
                                $newvalue = NULL;
                            }
                        }
                        $droplet_tags[]         = '[[' . $droplet . ']]';
                        $droplet_replacements[] = $newvalue;
                    }
                }    // End foreach( $found_droplets[1] as $droplet )

                $self->log()->LogDebug('TAGS:',$droplet_tags);
                $self->log()->LogDebug('REPLACEMENTS:',$droplet_replacements);

                // replace each Droplet-Tag with coresponding $newvalue
                $__CAT_Helper_Droplets_content = str_replace( $droplet_tags, $droplet_replacements, $__CAT_Helper_Droplets_content );
            }

            $self->log()->LogDebug('returning:');
            $self->log()->LogDebug($__CAT_Helper_Droplets_content);
            $self->log()->LogDebug('< evaluate()');

            return $__CAT_Helper_Droplets_content;
        }   // end function evaluate()

    } // class CAT_Helper_Droplet

} // if class_exists()
