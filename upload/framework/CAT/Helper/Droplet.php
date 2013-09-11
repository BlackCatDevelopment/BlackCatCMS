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

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

/*******************************************************************************
 * Droplets extensions
 ******************************************************************************/

        /**
         * Bereinigt den angegebenen Droplet Namen
         *
         * @param  string $droplet_name
         * @return STR $droplet_name
         */
        public static function clear_droplet_name($droplet_name) {
        	$droplet_name = strtolower($droplet_name);
        	$droplet_name = str_replace('[', '', $droplet_name);
        	$droplet_name = str_replace(']', '', $droplet_name);
        	$droplet_name = trim($droplet_name);
        	return $droplet_name;
        }  // end function clear_droplet_name()

        /**
         * Ueberprueft ob das angegebene Droplet auf der Seite mit der PAGE_ID
         * verwendet wird; untersucht nur WYSIWYG-Sektionen!
         *
         * @param  string $droplet_name
         * @param  int    $page_id
         * @return boolean
         */
        public static function droplet_exists($droplet_name, $page_id)
        {

        	$droplet_name = self::clear_droplet_name($droplet_name);
        	$SQL = sprintf(
                "SELECT * FROM %smod_wysiwyg WHERE `%s`='%s' AND ((`%s` LIKE '%%[[%s?%%') OR (`%s` LIKE '%%[[%s]]%%'))",
				CAT_TABLE_PREFIX, 'page_id', $page_id, 'text', $droplet_name, 'text', $droplet_name
            );
            $result = self::getInstance()->db()->query($SQL);
        	if ($result->numRows() > 0)
            {
        		return true;
        	}
        	return false;
        }  // end function droplet_exists()

        /**
         * Ueberprueft ob das angegebene Droplet registriert ist
         *
         * @param  string  $droplet_name
         * @param  string  $register_type
         * @param  int     $page_id - die PAGE_ID fuer die das Droplet registriert ist
         * @return boolean
         */
        public static function is_registered_droplet($droplet_name, $register_type, $page_id)
        {
        	$droplet_name = self::clear_droplet_name($droplet_name);
            $SQL = sprintf(
                "SELECT * FROM `%smod_droplets_extension` where `%s`='%s' AND `%s`='%s' AND `%s`='%s'",
                CAT_TABLE_PREFIX, self::field_droplet_name, $droplet_name,
                self::field_type, $register_type, self::field_page_id, $page_id
            );
        	$result  = self::getInstance()->db()->query($SQL);
            if(!$result->numRows()) return false;
            return true;
        } // is_registered_droplet()
        /**
         * Check wether the Droplet $droplet_name is registered for setting CSS Headers
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory
         * @return boolean true if the Droplet is registered
         */
        public static function is_registered_css($page_id, $droplet_name, $module_directory) {
            return is_registered_droplet($page_id, $droplet_name, $module_directory, 'css');
        }   // end function is_registered_css()

        /**
         * Check wether the Droplet $droplet_name is registered for setting JS Headers
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory
         * @return boolean true if the Droplet is registered
         */
        public static function is_registered_js($page_id, $droplet_name, $module_directory) {
            return is_registered_droplet($page_id, $droplet_name, $module_directory, 'js');
        }   // end function is_registered_js()

        /**
         * Check if the Droplet $droplet_name is registered for search
         *
         * @param string $droplet_name
         * @return boolean true on success
         */
        public static function is_registered_for_search($droplet_name) {
            return is_droplet_registered_for_search($droplet_name);
        }   // end function is_registered_for_search()

        /**
         * Register the Droplet $droplet_name for the $page_id for loading a CSS
         * file with the specified $file_name.
         * If $file_path is specified the file will be loaded from $file_path,
         * otherwise the file will be loaded from the desired $module_directory.
         * If $page_id is set to -1 the CSS file will be loaded at every page
         * (this option is intended for usage in templates)
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory - only the directory name
         * @param string $file_name - the filename with extension
         * @param string $file_path - relative to the root
         * @return boolean on success
         */
        public static function register_css($page_id, $droplet_name, $module_directory, $file_name, $file_path='') {
            return register_droplet($page_id, $droplet_name, $module_directory, 'css', $file_name, $file_path);
        }   // end function register_css()

        /**
         * Register the Droplet $droplet_name for the $page_id for loading a JS
         * JavaScript file with the specified $file_name.
         * If $file_path is specified the file will be loaded from $file_path,
         * otherwise the file will be loaded from the desired $module_directory.
         * If $page_id is set to -1 the JS file will be loaded at every page
         * (this option is intended for usage in templates)
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory - only the directory name
         * @param string $file_name - the filename with extension
         * @param string $file_path - relative to the root
         * @return boolean on success
         */
        public static function register_js($page_id, $droplet_name, $module_directory, $file_name, $file_path='') {
            return register_droplet($page_id, $droplet_name, $module_directory, 'js', $file_name, $file_path);
        }   // end function register_js()

        /**
         * Register the Droplet $droplet_name in $module_directory for the
         * search of $page_id
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory
         * @return boolean true on success
         */
        public static function register_for_search($page_id, $droplet_name, $module_directory) {
            return register_droplet_for_search($droplet_name, $page_id, $module_directory);
        }   // end function register_for_search()

        /**
         * Unregister the Droplet $droplet_name from the $page_id with the settings
         * $module_directory and $file_name
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param sring $module_directory
         * @param string $file_name
         */
        public static function unregister_css($page_id, $droplet_name, $module_directory, $file_name) {
            return unregister_droplet($page_id, $droplet_name, $module_directory, 'css', $file_name);
        }   // end function unregister_css()

        /**
         * Unregister the Droplet $droplet_name from the $page_id with the settings
         * $module_directory and $file_name
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @param sring $module_directory
         * @param string $file_name
         */
        public static function unregister_js($page_id, $droplet_name, $module_directory, $file_name) {
            return unregister_droplet($page_id, $droplet_name, $module_directory, 'js', $file_name);
        }   // end function unregister_js()

        /**
         * Unregister the Droplet $droplet_name in $module_directory for the
         * search of $page_id
         *
         * @param integer $page_id
         * @param string $droplet_name
         * @return boolean true on success
         */
        public static function unregister_for_search($page_id, $droplet_name) {
            return unregister_droplet_for_search($droplet_name, $page_id);
        }   // end function unregister_for_search()

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
            // zuerst pruefen, ob eine droplet_search section existiert
            //if ($register_type == self::type_search) check_droplet_search_section($page_id);
            $droplet_name = self::clear_droplet_name($droplet_name);
            if (!self::droplet_exists($droplet_name, $page_id)) {
                return false;
            }
            // already registered?
            if (self::is_registered_droplet($droplet_name, $register_type, $page_id))
            {
                return true;
            }
            $module_directory = CAT_Helper_Directory::sanitizePath($module_directory);
            $SQL = sprintf(
                "INSERT INTO `%smod_droplets_extension` VALUES ( '', '%s', '%s', '%s', '%s', '%s' )",
                CAT_TABLE_PREFIX, $droplet_name, $page_id, $module_directory, $register_type, $file
            );
            self::getInstance()->db()->query($SQL);
            return self::getInstance()->db()->is_error();
        } // register_droplet()


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
            {
                $fields .= ', `code`';
            }
            $query   = $self->db()->query(sprintf(
                  "SELECT $fields FROM `%smod_droplets` AS t1 LEFT OUTER JOIN `%smod_droplets_permissions` AS t2 "
                . "ON t1.id=t2.id ORDER BY name ASC",
                CAT_TABLE_PREFIX,CAT_TABLE_PREFIX
            ));

            if ( $query->numRows() )
            {
                while ( $droplet = $query->fetchRow(MYSQL_ASSOC) )
                {
                    // the current user needs global edit permissions, or specific edit permissions to see this droplet
                    if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
                    {
                        // get edit groups for this drople
                        if ( $droplet['edit_groups'] )
                        {
                            if ( CAT_Users::get_user_id() != 1 && !is_in_array( $droplet['edit_groups'], $groups ) )
                            {
                                continue;
                            }
                            else
                            {
                                $droplet['user_can_modify_this'] = true;
                            }
                        }
                    }
                    $comments = str_replace( array("\r\n", "\n", "\r"), '<br />', $droplet['comments'] );
                    if ( !strpos( $comments, "[[" ) ) //
                    {
                        $comments = '<span class="usage">' . $self->lang()->translate( 'Use' ) . ": [[" . $droplet['name'] . "]]</span><br />" . $comments;
                    }
                    $comments = str_replace( array("[[","]]"), array('<b>[[',']]</b>'), $comments );
                    if ( $with_code )
                    {
                        $droplet['valid_code']   = check_syntax( $droplet['code'] );
                    }
                    $droplet['comments']     = $comments;
                    // droplet included in search?
                    $droplet['is_in_search']   = CAT_Helper_Droplet::is_registered_for_search($droplet['name']);
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
         *
         **/
        public static function print_page_head( $page_id, $open_graph=false )
        {
            $page        = CAT_Helper_Page::properties($page_id);
            $title       = trim($page['page_title']);
            $description = trim($page['description']);
            $keywords    = trim($page['keywords']);

            if (defined('TOPIC_ID')) {
            }
            elseif (defined('POST_ID')) {
            }

          $SQL = "SELECT `drop_module_dir`,`drop_droplet_name` FROM `".TABLE_PREFIX."mod_droplets_extension` WHERE `drop_type`='header' AND `drop_page_id`='$page_id' LIMIT 1";
          if (null == ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
            return false;
          }
          if ($query->numRows() > 0) {
            $droplet = $query->fetchRow(MYSQL_ASSOC);
            if (droplet_exists($droplet['drop_droplet_name'], $page_id)) {
              // the droplet exists
              if (file_exists(WB_PATH.'/modules/'.$droplet['drop_module_dir'].'/droplet.extension.php')) {
                // we have to use the header informations from the droplet!
                include(WB_PATH.'/modules/'.$droplet['drop_module_dir'].'/droplet.extension.php');
                $user_func = $droplet['drop_module_dir'].'_droplet_header';
                if (function_exists($user_func)) {
                  $header = call_user_func($user_func, $page_id);
                  if (is_array($header)) {
                    if (isset($header['title']) && !empty($header['title']))
                      $title = $header['title'];
                    if (isset($header['description']) && !empty($header['description']))
                      $description = $header['description'];
                    if (isset($header['keywords']) && !empty($header['keywords']))
                      $keywords = $header['keywords'];
                  }
                }
              }
            }
            else {
              // the droplet does not exists, so unregister it to avoid an overhead
              unregister_droplet_header($droplet['drop_droplet_name'], $page_id);
            }
          }

          // check if we have to load css or javascript files
          $load_css = '';
          $load_js = '';

          $SQL = "SELECT * FROM `".TABLE_PREFIX."mod_droplets_extension` WHERE (`drop_type`='css' OR `drop_type`='javascript') AND `drop_page_id`='$page_id'";
          if (null == ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
            return false;
          }

          while (false !== ($droplet = $query->fetchRow(MYSQL_ASSOC))) {
            // go only ahead if the droplet exists
            if (droplet_exists($droplet['drop_droplet_name'], $droplet['drop_page_id'])) {
              // check if this droplet is loaded by a TOPICS article
              $topics_array = explode(',',$droplet['drop_topics_array']);
              if (defined('TOPIC_ID')) {
                if (!in_array(TOPIC_ID, $topics_array))
                  // the droplet is not registered for this TOPIC_ID so continue
                  continue;
              }
              elseif (!empty($droplet['drop_topics_array']) && !defined('TOPIC_ID')) {
                // the droplet is not registered for this TOPIC_ID so continue
                continue;
              }
              $checked = false;
              // first check if there exists a custom.* file ...
              $file = WB_PATH.'/modules/'.$droplet['drop_module_dir'].'/custom.'.$droplet['drop_file'];
              if (file_exists($file))
                $checked = true;
              else {
                // check for the regular file ...
                $file = WB_PATH.'/modules/'.$droplet['drop_module_dir'].'/'.$droplet['drop_file'];
                if (file_exists($file))
                  $checked = true;
              }
              if ($checked) {
                // load the file
                $file = str_replace(WB_PATH, WB_URL, $file);
                if ($droplet['drop_type'] == 'css') {
                  // CSS
                  $load_css .= sprintf(' <link rel="stylesheet" type="text/css" href="%s" media="screen" />', $file);
                }
                else {
                  // JavaScript
                  $load_js .= sprintf(' <script type="text/javascript" src="%s"></script>', $file);
                }
              }
            }
            else {
              // unregister the droplet to prevent overhead
              unregister_droplet($droplet['drop_droplet_name'], $droplet['drop_type'], $page_id);
              // also unregister droplet search
              unregister_droplet_search($droplet['drop_droplet_name'], $page_id);
            }
          }

          // set the default values for the Open Graph support
          $image = '';
          $site_name = WEBSITE_TITLE;
          $og_type = 'article';
          $exec_droplets = true;

          // check if a configuration file exists
          if (file_exists(WB_PATH.'/modules/droplets_extension/config.json'))
            $config = json_decode(file_get_contents(WB_PATH.'/modules/droplets_extension/config.json'), true);

          if (isset($config['og:website_title']))
            $site_name = $config['og:website_title'];

          if (!empty($keywords)) {
            // process the keywords
            $ka = explode(',', $keywords);
            $keyword_array = array();
            foreach ($ka as $keyword) {
              $keyword = trim($keyword);
              if (false !== strpos($keyword, '=')) {
                list($command, $value) = explode('=', $keyword);
                $command = trim(strtolower($command));
                $value = trim($value);
                switch ($command) {
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

          if (isset($config['og:droplets'])) {
            if (trim(strtolower($config['og:droplets'])) == 'false')
              $exec_droplets = false;
          }

          if (empty($image)) {
            // try to get the first image from the content
            if (false !== ($test = getFirstImageFromContent($page_id, $exec_droplets)))
              $image = $test;
          }

          if (empty($image)) {
            // if no image is available look if a image is defined in config.json
            if (isset($config['og:image']))
              $image = $config['og:image'];
          }

          if (!empty($image)) {
            $ext = pathinfo(WB_PATH.substr($image, strlen(WB_URL)), PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), array('png','gif','jpg','jpeg')))
              $image = '';
          }

          if ($open_graph && !empty($image)) {
            $url = getURLbyPageID($page_id);
            $image_dimensions = '';
            if (substr($image, 0, strlen(WB_URL)) == WB_URL) {
              list($width,$height) = getimagesize(WB_PATH.substr($image, strlen(WB_URL)));
$image_dimensions = <<<EOD
<meta property="og:image:width" content="$width" />
<meta property="og:image:height" content="$height" />
EOD;

$head = <<<EOD
<!-- dropletsExtension -->
<meta name="description" content="$description" />
<meta name="keywords" content="$keywords" />
<title>$title</title>
<meta property="og:image" content="$image" />
$image_dimensions
<meta property="og:type" content="$og_type" />
<meta property="og:title" content="$title" />
<meta property="og:description" content="$description" />
<meta property="og:url" content="$url" />
<meta property="og:site_name" content="$site_name" />
<link rel="image_src" href="$image" />
$load_css
$load_js
<!-- /dropletsExtension -->
EOD;
    }
    else {
$head = <<<EOD
<!-- dropletsExtension -->
<meta name="description" content="$description" />
<meta name="keywords" content="$keywords" />
<title>$title</title>
<meta property="og:type" content="$og_type" />
<meta property="og:title" content="$title" />
<meta property="og:description" content="$description" />
<meta property="og:url" content="$url" />
<meta property="og:site_name" content="$site_name">
$load_css
$load_js
<!-- /dropletsExtension -->
EOD;
    }
  }
  else {
$head = <<<EOD
<!-- dropletsExtension -->
<meta name="description" content="$description" />
<meta name="keywords" content="$keywords" />
<title>$title</title>$load_css$load_js
<!-- /dropletsExtension -->
EOD;
          }
          echo $head;
        } // print_page_head()

        /**
         *
         **/
        public static function is_allowed( $perm, $gid )
        {
            global $settings;
            // admin is always allowed to do all
            if ( CAT_Users::is_root() )
            {
                return true;
            }
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
         * @param  string  $content
         * @param  integer $max_loops - default 3
         * @return string
         **/
        public static function process( &$content, $max_loops = 3 )
        {
            $max_loops = ( (int) $max_loops = 0 ? 3 : (int) $max_loops );
            while ( ( self::evaluate($content) === true ) && ( $max_loops > 0 ) )
            {
                $max_loops--;
            }
            return $content;
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
                            $code = addslashes(str_replace($tags, '', $code));
                            // Already in the DB?
                            $stmt  = 'INSERT';
                            $id    = NULL;
                    $found = CAT_Helper_Directory::getInstance()->db()->get_one("SELECT * FROM ".CAT_TABLE_PREFIX."mod_droplets WHERE name='$name'");
                    if ( $found && $found > 0 ) {
                        $stmt = 'REPLACE';
                        $id   = $found;
                    }
                    // execute
                    $result = CAT_Helper_Directory::getInstance()->db()->query(sprintf(
                        "$stmt INTO `%smod_droplets` SET "
                        . ( ($id) ? 'id='.$id.', ' : '' )
                        . '`name`=\'%s\', `code`=\'%s\', `description`=\'%s\', '
                        . '`modified_when`=%d, `modified_by`=\'%s\', '
                        . '`active`=\'%s\', `comments`=\'%s\'',
                        CAT_TABLE_PREFIX, $name, $code, $description, time(), CAT_Users::get_user_id(), 1, $usage
                    ));
                    if( ! CAT_Helper_Directory::getInstance()->db()->is_error() ) {
                        $count++;
                        $imports[$name] = 1;
                    }
                    else {
                        $errors[$name] = CAT_Helper_Directory::getInstance()->db()->get_error();
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
         * evaluates the droplet code
         *
         * @access private
         * @param  string   $_x_codedata
         * @param  string   $_x_varlist
         * @param  string   $content
         * @return eval result
         **/
        private static function do_eval( $_x_codedata, $_x_varlist, $content )
        {
            global $wb, $admin, $wb_page_data;
            self::getInstance()->log()->LogDebug('evaluating: '.$_x_codedata);
            extract( $_x_varlist, EXTR_SKIP );
            return ( eval( $_x_codedata ) );
        }   // end function do_eval()

        /**
         * evaluates the droplets contained in $content
         *
         * @access public
         * @param  string $content
         * @return string
         **/
        private static function evaluate(&$content)
        {

            $self = self::getInstance();

            $self->log()->LogDebug('processing content:');
            $self->log()->LogDebug($content);

            // collect all droplets from document
            $droplet_tags         = array();
            $droplet_replacements = array();

            if ( preg_match_all( '/\[\[(.*?)\]\]/', $content, $found_droplets ) )
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
                        $codedata = $self->db()->get_one(sprintf(
                            'SELECT `code` FROM `%smod_droplets` WHERE `name` LIKE "%s" AND `active` = 1',
                            CAT_TABLE_PREFIX, $droplet_name
                        ));

                        $self->log()->LogDebug('code: '.$codedata);

                        if ( !is_null($codedata) )
                        {
                            $newvalue = self::do_eval( $codedata, $varlist, $content );
                            $self->log()->LogDebug('eval result: '.$newvalue);

                            // check returnvalue (must be a string of 1 char at least or (bool)true
                            if ( $newvalue == '' && $newvalue !== true )
                            {
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
                                $newvalue = "";
                            }
                            // remove any defined CSS section from code. For valid XHTML a CSS-section is allowed inside <head>...</head> only!
                            $newvalue = preg_replace( '/<style.*>.*<\/style>/siU', '', $newvalue );
                        }
                        else
                        {
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
                // replace each Droplet-Tag with coresponding $newvalue
                $content = str_replace( $droplet_tags, $droplet_replacements, $content );
            }

            $self->log()->LogDebug('returning:');
            $self->log()->LogDebug($content);

            return $content;
        }   // end function evaluate()

    } // class CAT_Helper_Droplet
    
} // if class_exists()    
