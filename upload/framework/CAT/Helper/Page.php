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
 *   @copyright       2013, 2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_Page'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Page extends CAT_Object
    {
        protected      $_config             = array(
            'loglevel'  => 8,
            'forbidden_l0' => array( // configurables will be added later
                'account',
                'framework',
                'include',
                'install',
                'languages',
                'modules',
                'search',
                'temp',
                'templates',
                'index.php'
            ),
            'forbidden_filenames_l0' => array(
                'index.php',
                'config.php',
                'upgrade-script.php'
            ),
        );
        private static $instance;
        private static $space               = '    '; // space before header items
        private static $pages               = array();
        private static $pages_by_visibility = array();
        private static $pages_by_parent     = array();
        private static $pages_by_id         = array();
        private static $pages_sections      = array();
        private static $pages_editable      = 0;

        // header components
        private static $css                 = array();
        private static $meta                = array();
        private static $js                  = array();
        private static $jquery              = array();
        private static $jquery_core         = false;
        private static $jquery_ui_core      = false;

        // scan dirs
        private static $css_search_path     = array();
        private static $js_search_path      = array();

        // footer components
        private static $script              = array();
        private static $f_jquery            = array();
        private static $f_js                = array();

        /**
         * the constructor loads the available pages from the DB and stores it
         * in internal arrays
         *
         * @access private
         * @return void
         **/
        public static function getInstance($skip_init=false)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                if(!$skip_init) self::init();
            }
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
         * reset class (reload pages from the DB)
         *
         * @access public
         * @return void
         **/
        public static function reset()
        {
            self::init(1);
        }   // end function reset()

        /**
         * initialize; fills the internal pages array
         *
         * @access private
         * @param  boolean $force - always reload
         * @return void
         **/
        private static function init($force=false)
        {

            global $page_id;

            if(CAT_Registry::exists('CAT_HELPER_PAGE_INITIALIZED') && !$force)
                return;

            if(!self::$instance) self::getInstance(true);

            // add configurable dirs to forbidden array (level 0)
            foreach( array(PAGES_DIRECTORY,MEDIA_DIRECTORY,CAT_BACKEND_FOLDER) as $dir )
            {
                $dir = preg_replace('~^/~','',$dir);
                if(!in_array($dir,self::$instance->_config['forbidden_l0']))
                    array_push(self::$instance->_config['forbidden_l0'],$dir);
            }
            // fill pages array
            if(count(self::$pages)==0 || $force)
            {
                $now = time();
                $result = self::$instance->db()->query(
                    'SELECT * FROM `:prefix:pages` ORDER BY `level` ASC, `position` ASC'
                );
                if( $result && $result->rowCount()>0 )
                {
                    $children_count = array();
                    $direct_parent  = 0;
                    while ( false !== ( $row = $result->fetch() ) )
                    {
                        $row['children']  = 0;
                        $row['is_parent'] = false;
                        $row['has_children']     = false; // same as is_parent!
                        $row['is_editable']      = false;
                        $row['is_in_trail']      = false;
                        $row['is_direct_parent'] = false;
                        $row['is_current']       = false;
                        $row['is_open']          = false;
                        $row['be_tree_is_open']  = isset( $_COOKIE[ session_name() . 'pageid_'.$row['page_id']] ) ? true : false; // for page tree
                        $row['href']             = CAT_URL . PAGES_DIRECTORY . $row['link'] . PAGE_EXTENSION;

                        // mark editable pages by checking user perms and page
                        // visibility
// --------------------- NOT READY YET! ----------------------------------------
        				if ( CAT_Users::ami_group_member($row['admin_groups']) )
        				{
                            if ( CAT_Registry::get('PAGE_TRASH') !== 'true' || $row['visibility'] !== 'deleted' )
                            {
                                $row['is_editable'] = true;
                                self::$pages_editable++;
                            }
        				}
// --------------------- NOT READY YET! ----------------------------------------

                        // mark current page
                        if (isset($page_id) && $row['page_id'] == $page_id )
                        {
                            $row['is_current'] = true;
                            $direct_parent = $row['parent'];
                        }

                        // count children; this lets us mark pages that have
                        // children later
                        if(!isset($children_count[$row['parent']]))
                            $children_count[$row['parent']] = 1;
                        else
                            $children_count[$row['parent']]++;

                        // add any other settings
                        $set = self::$instance->db()->query(
                            'SELECT * FROM `:prefix:pages_settings` WHERE page_id=:id',
                            array('id' => $row['page_id'])
                        );
                        if( $set && $set->rowCount()>0 )
                        {
                            while ( false !== ( $set_row = $set->fetch() ) )
                            {
                                if(!isset($row['settings']))
                                    $row['settings'] = array();
                                if(!isset($row['settings'][$set_row['set_type']]))
                                    $row[$set_row['set_type']] = array();
                                if(!isset($row['settings'][$set_row['set_type']][$set_row['set_name']]))
                                    $row[$set_row['set_type']][$set_row['set_name']] = array();
                                $row['settings'][$set_row['set_type']][$set_row['set_name']][] = $set_row['set_value'];
                            }
                        }

                        self::$pages[] = $row;

                        end(self::$pages);
                        self::$pages_by_id[$row['page_id']] = key(self::$pages);
                        reset(self::$pages);

                    }   // end while()

                    // mark pages that have children
                    foreach(self::$pages as $i => $page)
                    {
                        if(isset($children_count[$page['page_id']]))
                        {
                            self::$pages[$i]['children']  = $children_count[$page['page_id']];
                            self::$pages[$i]['is_parent'] = true;
                            self::$pages[$i]['has_children'] = true;
                        }
                        if($direct_parent && $page['page_id'] == $direct_parent)
                            self::$pages[$i]['is_direct_parent'] = true;
                    }

                    // resolve the trail
                    $trail = array();
                    if(isset($page_id) && isset(self::$pages_by_id[$page_id]))
                    {
                        // mark parents
                        $trail = explode(",", '0,'.self::$pages[self::$pages_by_id[$page_id]]['page_trail']);
                        array_pop($trail); // remove the current page
                        foreach($trail as $id)
                            if(isset(self::$pages_by_id[$id]) && isset(self::$pages[self::$pages_by_id[$id]]))
                                self::$pages[self::$pages_by_id[$id]]['is_in_trail'] = true;
                    }

                    // add 'virtual' page -1
                    if(!isset(self::$pages_by_id['-1']))
                    {
                        self::$pages_by_id['-1'] = 0;
                    }

                }       // end if($result)
            }
            CAT_Registry::register('CAT_HELPER_PAGE_INITIALIZED',true);
        }   // end function init()

        /**
         * allows to add a CSS file programmatically
         *
         * @access public
         * @param  string  $url
         * @param  string  $for   - 'frontend' (default) or 'backend'
         * @param  string  $media - default 'screen'
         * @return void
         **/
        public static function addCSS($url,$for='frontend',$media='screen')
        {
            CAT_Helper_Page::$css[] = array(
                'media' => $media,
                'file'  => $url
            );
        }   // end function addCSS()

        /**
         * allows to add a JS file programmatically
         *
         * @access public
         * @param  string  $url
         * @param  string  $for   - 'frontend' (default) or 'backend'
         * @param  string  $pos   - 'header' (default) or 'footer'
         * @return void
         **/
        public static function addJS($url,$for='frontend',$pos='header')
        {
            if ($pos == 'header')
                $static =& CAT_Helper_Page::$js;
            else
                $static =& CAT_Helper_Page::$f_js;
            $static[] = self::$space
                      . '<script type="text/javascript" src="'
                      . CAT_Helper_Validate::sanitize_url(CAT_URL.$url)
                      . '"></script>';
        }   // end function addJS()

        /**
         * creates a new page
         *
         * @access public
         * @param  array  $options
         * @return mixed  - new page ID or false on error
         **/
        public static function addPage($options)
        {
            $self = self::getInstance();
            // get mandatory fields
            $res = $self->db()->query(
                'DESCRIBE `:prefix:pages`'
            );
            $mandatory = array();
            while(false!==($row=$res->fetch()))
                if($row['Null']=='NO'&&$row['Key']!='PRI')
                    $mandatory[$row['Field']] = $row['Type'];
            // fill options
            $sql	 = 'INSERT INTO `:prefix:pages` SET ';
            $params  = array();
            foreach($options as $key => $value)
            {
                $sql .= '`'.$key.'` = :'.$key.', ';
                $params[$key] = $value;
                if(array_key_exists($key,$mandatory))
                    unset($mandatory[$key]);
            }
            // all mandatory fields filled?
            if(count($mandatory))
                foreach($mandatory as $key=>$type)
                    $sql .= '`'.$key.'`='
                         .  (
                              preg_match('~^int~i',$type)
                              ?  '0, '
                              :  '\'\', '
                            );

            $sql = preg_replace('~,\s*$~','',$sql);
            $self->db()->query($sql,$params);
            $page_id = $self->db()->lastInsertId();
            // reload pages list
            if(!$self->db()->isError()) self::init(1);
            return
                  $self->db()->isError()
                ? false
                : $page_id;
        }   // end function addPage()
        
        /**
         * update page options
         *
         * @access public
         * @param  integer $page_id
         * @param  array   $options
         * @return boolean
         **/
        public static function updatePage($page_id,$options)
        {
            if(!self::$instance) self::getInstance();
            $sql	 = 'UPDATE `:prefix:pages` SET ';
            $params  = array('id'=>$page_id);
            foreach($options as $key => $value)
            {
                if(is_array($value))
                    $value = implode(',',$value);
                $sql .= '`'.$key.'` = :'.$key.', ';
                $params[$key] = $value;

            }
            $sql = preg_replace('~,\s*$~','',$sql);
            $sql .= ' WHERE page_id=:id';
            self::$instance->db()->query($sql,$params);
            // reload pages list
            if(!self::$instance->db()->isError()) self::init(1);
            return
                  ( self::$instance->db()->isError() === true )
                ? false
                : true;
        }   // end function updatePage()

        /**
         * save page settings
         *
         * @access public
         * @param  integer $page_id
         * @param  array   $options
         * @return void
         **/
        public static function updatePageSettings($page_id,$options)
        {
            if(!self::$instance) self::getInstance();
            foreach($options as $key => $value)
            {
                if(is_array($value))
                    $value = implode(',',$value);
                self::$instance->db()->query(
                    'REPLACE INTO `:prefix:pages_settings` VALUES( :id, :type, :k, :v )',
                    array( 'id'=>$page_id,'type'=>'internal','k'=>$key,'v'=>$value)
                );
            }
        }   // end function updatePageSettings()
        
        /**
         * delete page; uses _trashPages() if trash is enabled, _deletePage()
         * otherwise
         *
         * @access public
         * @param  integer $page_id
         * @param  boolean $use_trash
         * @return boolean
         **/
        public static function deletePage($page_id,$use_trash=false)
        {
            if($use_trash)
            {
            	// Update the page visibility to 'deleted'
            	self::getInstance()->db()->query(
                    "UPDATE `:prefix:pages` SET `visibility` = :vis WHERE `page_id` = :id LIMIT 1",
                    array('vis'=>'deleted','id'=>$page_id)
                );
            	return self::_trashPages($page_id);
            }
            else
            {
                // remove sub pages
           	    $sub_pages = self::getSubPages($page_id);
                $sub_pages = array_reverse($sub_pages);
                $errors    = array();
            	foreach($sub_pages as $sub_page_id)
            	{
            		$err = self::_deletePage( $sub_page_id );
                    $errors = array_merge($errors,$err);
            	}
            	// remove the page itself
            	$err = self::_deletePage($page_id);
                $errors = array_merge($errors,$err);
                if(count($errors)) return false;
                return true;
            }
        }   // end function deletePage()

        /**
         *
         *
         **/
        public static function createAccessFile($filename, $page_id)
        {

            $filename = CAT_Helper_Directory::sanitizePath($filename);

            // check if $filename is a full path (may be 'link' db value)
            if(!preg_match('~^'.CAT_Helper_Directory::sanitizePath(CAT_PATH.PAGES_DIRECTORY).'~i',$filename))
                $filename = CAT_Helper_Directory::sanitizePath(CAT_PATH.PAGES_DIRECTORY.'/'.dirname($filename).'/'.self::getFilename(basename($filename)).PAGE_EXTENSION);

            $pages_path    = CAT_Helper_Directory::sanitizePath(CAT_PATH.PAGES_DIRECTORY);
            $rel_pages_dir = str_replace($pages_path, '', CAT_Helper_Directory::sanitizePath(dirname($filename)));
            $rel_filename  = str_replace($pages_path, '', CAT_Helper_Directory::sanitizePath($filename));
            // prevent system directories and files from being overwritten (level 0)
            $denied   = false;
            if (PAGES_DIRECTORY == '')
            {
                $forbidden_dirs  = self::$instance->_config['forbidden_l0'];
                $forbidden_files = self::$instance->_config['forbidden_filenames_l0'];
                $search          = explode('/', $rel_filename);
                $denied          = in_array($search[1], $forbidden_dirs);
                $denied          = in_array($search[1], $forbidden_files);
            }

            if ((true === is_writable($pages_path)) && (false == $denied))
            {
                // First make sure parent folder exists
                $parent_folders = explode('/', $rel_pages_dir);
                $parents        = '';
                foreach ($parent_folders as $parent_folder)
                {
                    if ($parent_folder != '/' && $parent_folder != '')
                    {
                        $parents .= '/' . $parent_folder;
                        if (!file_exists($pages_path . $parents))
                        {
                            // create dir; also creates index.php (last param = true)
                            CAT_Helper_Directory::createDirectory($pages_path . $parents, OCTAL_DIR_MODE, true);
                            CAT_Helper_Directory::setPerms($pages_path . $parents);
                        }
                    }
                }

                $step_back = str_repeat('../', substr_count($rel_pages_dir, '/') + (PAGES_DIRECTORY == "" ? 0 : 1));
                $content = '<?php' . "\n";
                $content .= "/**\n *\tThis file is autogenerated by BlackCat CMS Version " . CAT_VERSION . "\n";
                $content .= " *\tDo not modify this file!\n */\n";
                $content .= "\t" . '$page_id = ' . $page_id . ';' . "\n";
                $content .= "\t" . 'require_once \'' . $step_back . 'index.php\';' . "\n";
                $content .= '?>';
                /**
                 *  write the file
                 */
                $fp = fopen($filename, 'w');
                if ($fp)
                {
                    fwrite($fp, $content, strlen($content));
                    fclose($fp);
                    /**
                     *  Chmod the file
                     */
                    CAT_Helper_Directory::getInstance()->setPerms($filename);
                }
                else
                {
                    CAT_Backend::getInstance()->print_error('Error creating access file in the pages directory, cannot open file');
                    return false;
                }
                return true;
            }
            else
            {
                CAT_Backend::getInstance()->print_error('Error creating access file in the pages directory, path not writable or forbidden file / directory name');
                return false;
            }
        }   // end function createAccessFile()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function deleteAccessFile($page_id) {
            // Unlink the access file and directory
            $directory  = CAT_PATH . PAGES_DIRECTORY . self::properties($page_id,'link');
            $filename   = $directory . PAGE_EXTENSION;
            $directory .= '/';
            if (file_exists($filename))
            {
                if (!is_writable(CAT_PATH . PAGES_DIRECTORY . '/'))
                 {
                    $self     = self::getInstance(true);
                    $errors[] = $self->lang()->translate('Cannot delete access file!');
                }
                else
                {
                    unlink($filename);
                    if (
                           is_dir($directory)
                        && (rtrim($directory, '/') != CAT_PATH . PAGES_DIRECTORY)
                    ) {
                        CAT_Helper_Directory::removeDirectory($directory);
                    }
            	}
            }
        }   // end function deleteAccessFile()
        

        /**
         * delete language link (linked page)
         *
         * @access public
         * @param  integer $page_id
         * @param  string  $lang
         * @return boolean
         **/
        public static function deleteLanguageLink($page_id,$lang)
    	{
            if(!self::$instance) self::getInstance(true);
            self::$instance->db()->query(
                'DELETE FROM `:prefix:page_langs` WHERE link_page_id = :id AND lang = :lang',
                array('id'=>$page_id, 'lang'=>$lang)
            );
            return
                  self::$instance->db()->isError()
                ? false
                : true
                ;
        }   // end function deleteLanguageLink()

        /**
         * checks if a page exists; checks access file and database entry
         *
         * @access public
         * @return
         **/
        public static function exists($link)
        {
            // check database
            if(!self::$instance) self::getInstance(true);
            $get_same_page = self::$instance->db()->query(
                "SELECT `page_id` FROM `:prefix:pages` WHERE link=:link",
                array('link'=>$link)
            );
            if ($get_same_page->rowCount() > 0)
                return true;
            // check access file
            if(
                   file_exists(CAT_PATH.PAGES_DIRECTORY.$link.PAGE_EXTENSION)
                || file_exists(CAT_PATH.PAGES_DIRECTORY.$link.'/')
            ) {
                return true;
            }
        }   // end function exists()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getExtraHeaderFiles($page_id=NULL)
        {
            $data = array(); //'js'=>array(),'css'=>array(),'code'=>''
            $q    = 'SELECT * FROM `:prefix:pages_headers` WHERE `page_id`=:page_id';
            $r    = self::getInstance(1)->db()->query($q,array('page_id'=>$page_id));
            if($r->rowCount())
            {
                $row = $r->fetch();
                if(isset($row['page_js_files']) && $row['page_js_files']!='')
                    $data['js'] = unserialize($row['page_js_files']);
                if(isset($row['page_css_files']) && $row['page_css_files']!='')
                    $data['css'] = unserialize($row['page_css_files']);
                if(isset($row['page_js']) && $row['page_js']!='')
                    $data['code'] = $row['page_js'];
                $data['use_core'] = $row['use_core'];
                $data['use_ui']   = $row['use_ui'];
            }
            return $data;
        }   // end function getExtraHeaderFiles()

        /**
         * add header file to the database; returns an array with keys
         *     'success' (boolean)
         *         and
         *     'message' (some error text or 'ok')
         *
         * @access public
         * @param  string  $type
         * @param  string  $file
         * @param  integer $page_id
         * @return array
         **/
        public static function adminAddHeaderComponent($type,$file,$page_id=NULL)
        {
            $data = self::getExtraHeaderFiles($page_id);
            if(isset($data[$type]) && is_array($data[$type]) && count($data[$type]) && in_array($file,$data[$type]))
            {
                return array(
                    'success' => false,
                    'message' => $val->lang()->translate('The file is already listed')
                );
            }
            else
            {
                $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$file);
                if(file_exists($path))
                {
                    $new    = ( isset($data[$type]) && is_array($data[$type]) && count($data[$type]) )
                            ? $data[$type]
                            : array();
                    array_push($new,CAT_Helper_Directory::sanitizePath('/modules/lib_jquery/plugins/'.$file));
                    $new = array_unique($new);
                    $params = array(
                        'field'   => 'page_'.$type.'_files',
                        'value'   => serialize($new),
                        'page_id' => $page_id,
                    );
                    if(count($data))
                    {
                        $q = 'UPDATE `:prefix:pages_headers` SET :field:=:value WHERE `page_id`=:page_id';
                    }
                    else
                    {
                        $q = 'INSERT INTO `:prefix:pages_headers` ( `page_id`, :field: ) VALUES ( :page_id, :value )';
                    }
                    self::getInstance(1)->db()->query($q,$params);
                    return array(
                        'success' => ( self::getInstance(1)->isError() ? false                  : true ),
                        'message' => ( self::getInstance(1)->isError() ? self::getInstance(1)->getError() : 'ok' )
                    );
                }
            }
        }   // end function adminAddHeaderComponent()

        /**
         * remove header file from the database
         **/
        public static function adminDelHeaderComponent($type,$file,$page_id=NULL)
        {
            $data = self::getExtraHeaderFiles($page_id);
            if(!(is_array($data[$type]) && count($data[$type]) && in_array($file,$data[$type])))
                return array( 'success' => true, 'message' => 'ok' );
            if(($key = array_search($file, $data[$type])) !== false) {
                unset($data[$type][$key]);
            }
            $q = count($data)
               ? sprintf(
                     'UPDATE `:prefix:pages_headers` SET `page_%s_files`=\'%s\' WHERE `page_id`="%d"',
                     $type, serialize($data[$type]), $page_id
                 )
               : sprintf(
                     'REPLACE INTO `:prefix:pages_headers` ( `page_id`, `page_%s_files` ) VALUES ( "%d", \'%s\' )',
                     $type, $page_id, serialize($data[$type])
                 )
               ;
            self::getInstance(1)->db()->query($q);
            return array(
                'success' => ( self::getInstance(1)->isError() ? false                            : true ),
                'message' => ( self::getInstance(1)->isError() ? self::getInstance(1)->getError() : 'ok' )
            );
        }   // end function adminDelHeaderComponent()

        /**
         * prints the backend footers
         *
         * @access public
         * @return string
         **/
        public static function getBackendFooters()
        {
            // -----------------------------------------------------------------
            // -----                    backend theme                      -----
            // -----------------------------------------------------------------
            $tpl  = CAT_Registry::get('DEFAULT_THEME');
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.$tpl.'/footers.inc.php');
            if (file_exists($file))
            {
                self::getInstance()->log()->logDebug(sprintf('adding footer items for backend theme [%s]', $tpl));
                self::_load_footers_inc($file, 'backend', 'templates/'.$tpl);
            } // end loading theme

            // -----------------------------------------------------------------
            // -----                     admin tool                        -----
            // -----------------------------------------------------------------
            $tool = CAT_Helper_Validate::get('_REQUEST','tool','string');
            if ($tool)
            {
                $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$tool.'/tool.php');
                self::getInstance()->log()->logDebug(sprintf('handle admin tool [%s] - path [%s]', $tool, $path));
                if (file_exists($path))
                {
                    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH . '/modules/' . $tool . '/footers.inc.php');
                    if (file_exists($file))
                    {
                        self::getInstance()->log()->logDebug(sprintf('adding footer items for admin tool [%s]', $_REQUEST['tool']));
                        self::_load_footers_inc($file, 'backend', 'templates/' . $tpl);
                    }
                }
            }

            // -----------------------------------------------------------------
            // -----                scan for js files                      -----
            // -----------------------------------------------------------------
            if (count(self::$js_search_path))
            {
                $val = CAT_Helper_Validate::getInstance();
                foreach (self::$js_search_path as $directory)
                {
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/backend_body.js');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        self::$f_js[] = '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $file) . '"></script>' . "\n";
                    }
                }
            }

            return self::getJQuery('footer') . self::getJavaScripts('footer');

        } // end function getBackendFooters()

        /**
         *
         *
         *
         *
         **/
        public static function getBackendHeaders($section)
        {
            $self = self::$instance;
            $self->log()->logDebug('get backend headers for section:',$section);

            // -----------------------------------------------------------------
            // -----                    backend theme                      -----
            // -----------------------------------------------------------------
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH . '/templates/' . DEFAULT_THEME . '/headers.inc.php');
            if (file_exists($file))
            {
                self::$instance->log()->logDebug(sprintf('adding items for backend theme [%s]', DEFAULT_THEME));
                self::_load_headers_inc($file, 'backend', 'templates/' . DEFAULT_THEME, $section);
            } // end loading theme

            // -----------------------------------------------------------------
            // -----                     admin tool                        -----
            // -----------------------------------------------------------------
            if (isset($_REQUEST['tool']))
            {
                $path = CAT_Helper_Directory::sanitizePath(CAT_PATH . '/modules/' . $_REQUEST['tool'] . '/tool.php');
                self::$instance->log()->logDebug(sprintf('handle admin tool [%s] - path [%s]', $_REQUEST['tool'], $path));

                if (file_exists($path))
                {
                    array_push(CAT_Helper_Page::$css_search_path, '/modules/' . $_REQUEST['tool'], '/modules/' . $_REQUEST['tool'] . '/css');
                    array_push(CAT_Helper_Page::$js_search_path, '/modules/' . $_REQUEST['tool'], '/modules/' . $_REQUEST['tool'] . '/js');

                    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH . '/modules/' . $_REQUEST['tool'] . '/headers.inc.php');
                    if (file_exists($file))
                    {
                        self::$instance->log()->logDebug(sprintf('adding items for admin tool [%s]', $_REQUEST['tool']));
                        self::_load_headers_inc($file, 'backend', 'modules/' . $_REQUEST['tool'], $section);
                    }
                }
            }
            // -----------------------------------------------------------------
            // -----                  edit page                            -----
            // -----------------------------------------------------------------
            else
            {
                self::$instance->log()->logDebug('Loading sections');
                self::_load_sections('backend');
            }

            // -----------------------------------------------------------------
            // -----                scan for css files                     -----
            // -----------------------------------------------------------------
            self::_load_css('backend');

            // -----------------------------------------------------------------
            // -----                scan for js files                      -----
            // -----------------------------------------------------------------
            self::_load_js('backend');

            // return the results
            return self::getCSS('backend') . self::getJQuery('header') . self::getJavaScripts('header');

        } // end function getBackendHeaders()

        /**
         * returns the items of static array $css as HTML link markups
         *
         * @access public
         * @return HTML
         **/
        public static function getCSS($for='frontend',$as_array=false)
        {
            $output = array();
            if (count(CAT_Helper_Page::$css))
            {
                // check for template variants
/*
I think this is needed here, is it?
frontend.css and template.css are added in _get_css()
                $key    = 'DEFAULT_TEMPLATE_VARIANT';
                $subkey = 'DEFAULT_TEMPLATE';
                $file   = 'template';
                if ( $for == 'backend' )
                {
                    $key    = 'DEFAULT_THEME_VARIANT';
                    $subkey = 'DEFAULT_THEME';
                    $file   = 'theme';
                }
                $path   = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.CAT_Registry::get($subkey).'/css/'.CAT_Registry::get($key));
                if(CAT_Registry::get($key) != '' && file_exists($path) && file_exists($path.'/'.$file.'.css') )
                {
                    array_push(CAT_Helper_Page::$css, array('file'=>'templates/'.CAT_Registry::get($subkey).'/css/'.CAT_Registry::get($key).'/'.$file.'.css'));
                }
*/
                $val = CAT_Helper_Validate::getInstance();
                $seen = array();
                while( NULL !== ( $item = array_shift(CAT_Helper_Page::$css) ) )
                {
                    if ( ! isset($seen[$item['file']]) )
                    {
                        $seen[$item['file']]	= true;
                        // make sure we have an URI (CAT_URL included)
                        if(!preg_match('~^http(s)?://~i',$item['file']))
                        {
                            if ( ! preg_match( '~^/~', $item['file'] ) )
                                $item['file'] = '/' . $item['file'];
                            $file = (preg_match('#' . CAT_URL . '#i', $item['file']) ? $item['file'] : CAT_URL . '/' . $item['file']);
                            $file = $val->sanitize_url($file);
                        }
                        else
                        {
                            $file = $item['file'];
                        }
                        $line = '<link rel="stylesheet" type="text/css" href="'.$file.'" '
                              .  'media="' . (isset($item['media']) ? $item['media'] : 'all') . '" />'
                              . "\n"
                              ;
                        if(isset($item['conditional']) && $item['conditional'] != '')
                        {
                            $line = '<!--[if '.$item['conditional'].']>'."\n"
                                  . $line
                                  . '<![endif]-->'."\n"
                                  ;
                        }
                        $output[] = $line;
                    }
                    $seen[$item['file']] = $file;
                }
            }
            if($as_array) return array_values($seen);
            return implode('',$output);
        } // end function getCSS()


        /**
         * determine default page
         *
         * @access public
         * @return void
         **/
        public static function getDefaultPage()
        {
            if ( ! count(self::$pages) )
                self::init();
            // for all pages with level 0...
            $root = array();
            $now  = time();
            $ordered = CAT_Helper_Array::getInstance()->ArraySort ( self::$pages, 'position' );
            foreach( $ordered as $page )
            {
                if (
                       $page['level'] == 0
                    && $page['visibility'] == 'public'
                    && self::isActive($page['page_id'])
                ) {
                    if(!PAGE_LANGUAGES || $page['language'] == LANGUAGE)
                    {
                        return $page['page_id'];
                    }
                }
            }
        } // end function getDefaultPage()

        /**
         * returns the number of editable pages
         *
         * @access public
         * @return integer
         **/
        public static function getEditable()
        {
            if(!count(self::$pages)) self::init();
            return self::$pages_editable;
        }   // end function getEditable()
        

        /**
         * convert page title to a valid filename
         *
         * @access public
         * @param  string  $string - page title
         * @return string
         **/
        public static function getFilename($string)
        {
            require_once CAT_PATH . '/framework/functions-utf8.php';
            $string = entities_to_7bit($string);
            // Now remove all bad characters
            $bad = array('\'', '"', '`', '!', '@', '#', '$', '%', '^', '&', '*', '=', '+', '|', '/', '\\', ';', ':', ',', '?');
            $string = str_replace($bad, '', $string);
            // replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
            $string = preg_replace(array('/\.+/', '/\.+$/'), array('.', ''), $string);
            // Now replace spaces with page spcacer
            $string = trim($string);
            $string = preg_replace('/(\s)+/', PAGE_SPACER, $string);
            // Now convert to lower-case
            $string = strtolower($string);
            // If there are any weird language characters, this will protect us against possible problems they could cause
            $string = str_replace(array('%2F', '%'), array('/', ''), urlencode($string));
            // Finally, return the cleaned string
            return $string;
        }   // end function getFilename()

        /**
         * calls appropriate function for analyzing and printing page footers
         *
         * @access public
         * @param  string  $for - 'backend'/'frontend'
         * @return mixed
         **/
        public static function getFooters($for)
        {
            // what for?
            if (!$for || $for == '' || ($for != 'frontend' && $for != 'backend'))
                $for = 'frontend';

            if ($for == 'backend')
                return self::getBackendFooters();
            else
                return self::getFrontendFooters();
        } // end function getFooters()

        /**
         * prints the frontend footers
         *
         * @access public
         * @return string
         **/
        public static function getFrontendFooters()
        {
            // -----------------------------------------------------------------
            // -----                  frontend theme                       -----
            // -----------------------------------------------------------------
            $tpl  = CAT_Registry::get('TEMPLATE');
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.$tpl.'/footers.inc.php');
            if (file_exists($file))
            {
                self::$instance->log()->logDebug(sprintf('adding footer items for frontend template [%s]', $tpl));
                self::_load_footers_inc($file, 'frontend', 'templates/' . $tpl);
            } // end loading theme

            // -----------------------------------------------------------------
            // -----                  scan for js files                    -----
            // -----------------------------------------------------------------
            if (count(CAT_Helper_Page::$js_search_path))
            {
                $val = CAT_Helper_Validate::getInstance();
                foreach (CAT_Helper_Page::$js_search_path as $directory)
                {
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/frontend_body.js');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$f_js[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $file) . '"></script>' . "\n";
                    }
                }
            }

            return self::getJQuery('footer') . self::getJavaScripts('footer');

        } // end function getFrontendFooters()

        /**
         *
         *
         *
         *
         **/
        public static function getFrontendHeaders()
        {
            global $page_id;
            // -----------------------------------------------------------------
            // -----                  frontend theme                       -----
            // -----------------------------------------------------------------
            $tpl  = CAT_Registry::get('TEMPLATE');
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.$tpl.'/headers.inc.php');
            self::$instance->log()->logDebug(sprintf('searching for file [%s]', $file));
            if (file_exists($file))
            {
                self::$instance->log()->logDebug(sprintf('adding items for frontend template [%s]', $tpl));
                self::_load_headers_inc($file, 'frontend', 'templates/'.$tpl);
            }
            else
            {
                self::$instance->log()->logDebug('no headers.inc.php');
            }

            // add template path to CSS search path (frontend only)
            array_push(
                CAT_Helper_Page::$css_search_path,
                '/templates/'.$tpl,
                '/templates/'.$tpl.'/css',
                // for skinnables
                '/templates/'.$tpl.'/templates/default',
                '/templates/'.$tpl.'/templates/default/css',
                // page
                CAT_Registry::get('PAGES_DIRECTORY').'/css/',
                // search
                '/modules/'.CAT_Registry::get('SEARCH_LIBRARY').'/templates/custom/',
                '/modules/'.CAT_Registry::get('SEARCH_LIBRARY').'/templates/default/'
            );

            // Javascript search path
            array_push(
                CAT_Helper_Page::$js_search_path,
                '/templates/'.$tpl,
                '/templates/'.$tpl.'/js',
                // for skinnables
                '/templates/'.$tpl.'/templates/default',
                '/templates/'.$tpl.'/templates/default/js',
                // page
                CAT_Registry::get('PAGES_DIRECTORY').'/js/'
            );

            // -----------------------------------------------------------------
            // -----             get extra header files                    -----
            // -----------------------------------------------------------------
            $global_files = CAT_Helper_Page::getExtraHeaderFiles(0);
            $page_files   = CAT_Helper_Page::getExtraHeaderFiles($page_id);
            $all_files    = array_merge($global_files,$page_files);
            if(isset($all_files['css']) && is_array($all_files['css']))
                foreach($all_files['css'] as $file)
                    self::addCSS($file);
            if(isset($all_files['js']) && is_array($all_files['js']))
                foreach($all_files['js'] as $file)
                    self::addJS($file);

            // -----------------------------------------------------------------
            // -----                  sections (modules)                   -----
            // -----------------------------------------------------------------
            self::_load_sections('frontend');

            // -----------------------------------------------------------------
            // -----                  scan for css files                   -----
            // -----------------------------------------------------------------
            self::_load_css('frontend');

            // -----------------------------------------------------------------
            // -----                  scan for js files                    -----
            // -----------------------------------------------------------------
            self::_load_js('frontend');

            // called from backend?
            if(CAT_Helper_Validate::get('_REQUEST','preview') && CAT_Users::is_authenticated())
            {
                $file = CAT_PATH.'/templates/'.DEFAULT_THEME.'/css/visibility.css';
                if(file_exists($file))
                {
                    CAT_Helper_Page::$css[] = array(
                        'media' => 'screen,projection',
                        'file' => '/templates/'.DEFAULT_THEME.'/css/visibility.css'
                    );
                }
                $file = CAT_PATH.'/templates/'.DEFAULT_THEME.'/js/visibility.js';
                if(file_exists($file))
                {
                    global $page_id;
                    CAT_Helper_Page::$js[] = '<script type="text/javascript">'."\n"
                                           . '    var visibility = \''.self::getInstance(1)->lang()->translate(self::properties($page_id,'visibility')).'\';'."\n"
                                           . '    var visibility_text = \''.self::getInstance(1)->lang()->translate('Visibility of this page').'\';'."\n"
                                           . '    var visibility_title = \''.self::getInstance(1)->lang()->translate('Black Cat CMS Page Preview').'\';'."\n"
                                           . '</script>' . "\n"
                                           . '<script type="text/javascript" src="' . CAT_Helper_Validate::sanitize_url(CAT_URL.'/templates/'.DEFAULT_THEME.'/js/visibility.js') . '"></script>' . "\n"
                                           ;

                }
            }

            $droplets_config = CAT_Helper_Droplet::getDropletsForHeader($page_id);

            // return the results
            return self::getMeta($droplets_config)
                 . self::getCSS('frontend')
                 . ($droplets_config['css'] ? "<!-- dropletsExtension -->\n".$droplets_config['css']."\n<!-- /dropletsExtension -->\n" : NULL)
                 . self::getJQuery('header')
                 . self::getJavaScripts('header')
                 . ($droplets_config['js'] ? "<!-- dropletsExtension -->\n".$droplets_config['js']."\n<!-- /dropletsExtension -->\n" : NULL)
                 ;

        } // end function getFrontendHeaders()

        /**
         * calls appropriate function for analyzing and printing page headers
         *
         * @access public
         * @param  string  $for - 'backend'/'frontend'
         * @param  string  $section - backend section name to load JS for
         * @return mixed
         *
         **/
        public static function getHeaders($for = NULL, $section = false)
        {
            global $page_id;

            // don't do this twice
            if (defined('CAT_HEADERS_SENT'))
            {
                self::$instance->log()->logDebug('headers already sent, returning');
                return;
            }

            // what for?
            if (!$for || $for == '' || ($for != 'frontend' && $for != 'backend'))
            {
                $for = 'frontend';
            }
            self::$instance->log()->logDebug('creating headers for ['.$for.'], page id: ['.$page_id.']');

            // add default
            $default = "
		var WB_URL							  = '" . CAT_URL . "',
			LEPTON_URL						  = '" . CAT_URL . "',
            CAT_URL                           = '" . CAT_URL . "';
            ";

            // backend only
            if($for == 'backend')
            {
                $default .= "
        var THEME_URL                         = '" . CAT_THEME_URL . "',
			CAT_THEME_URL					  = '" . CAT_THEME_URL . "',
            ADMIN_URL						  = '" . CAT_ADMIN_URL . "',
			CAT_ADMIN_URL					  = '" . CAT_ADMIN_URL . "',
            DATE_FORMAT						  = '" . str_replace(
            	array( '%', 'Y', 'm', 'd' ),
            	array( '', 'yy', 'mm', 'dd' ),
            	CAT_DATE_FORMAT
            ) . "',
			TIME_FORMAT						  = '" . str_replace( '%', '', CAT_TIME_FORMAT ) . "',
			DEFAULT_LANGUAGE				  = '" . DEFAULT_LANGUAGE . "',
			SESSION							  =	'" . session_name() . "';
            ";
            }

            CAT_Helper_Page::$jquery[] = '<script type="text/javascript">'.$default.'</script>';

            if ($for == 'backend')
            {
                return self::getBackendHeaders($section);
            }
            else
            {
                return self::getFrontendHeaders($section);
            }

        } // end function getHeaders()

        /**
         * returns the items of static array $js
         *
         * @access public
         * @return HTML
         *
         **/
        public static function getJavaScripts($for = 'header')
        {
            if ($for == 'header')
                $static =& CAT_Helper_Page::$js;
            else
                $static =& CAT_Helper_Page::$f_js;

            // if there was some CSS added meanwhile...
            if($for == 'footer' && count(CAT_Helper_Page::$css))
            {
                $arr = CAT_Helper_Page::$css;
                CAT_Helper_Page::$css = array();
                self::_analyze_css($arr); // fixes paths
                $css = self::getCSS(NULL,true);
                $js  = '<script type="text/javascript">';
                foreach($css as $item)
                {
                    $js .= '$("head").append("<link rel=\"stylesheet\" href=\"'
                        .  $item . '\" type=\"text/css\" media=\"screen,projection\" />");';
                }
                $js .= '</script>';
                $static[] = $js;
            }

            if (is_array($static) && count($static))
                return implode("\n", $static) . "\n";

            return NULL;
        } // end function getJavaScripts()

        /**
         * returns the items of static array $jquery
         *
         * @access public
         * @return HTML
         **/
        public static function getJQuery($for = 'header')
        {
            if ($for == 'header')
                $static =& CAT_Helper_Page::$jquery;
            else
                $static =& CAT_Helper_Page::$f_jquery;

            if($for == 'footer' && count(CAT_Helper_Page::$css))
            {
                if(!CAT_Helper_Page::$jquery_core)
                {
                    array_unshift($static, CAT_Helper_Page::$space . '<script type="text/javascript" src="' . CAT_Helper_Validate::sanitize_url(CAT_URL . '/modules/lib_jquery/jquery-core/jquery-core.min.js') . '"></script>' . "\n");
                    CAT_Helper_Page::$jquery_core = true;
                }
            }

            if (count($static))
                return implode($static);

            return NULL;
        } // end function getJQuery()

        /**
         *
         * @access public
         * @return
         **/
        public static function getLastEdited($number=10)
        {
            $result = array();
            $pages  = self::getPages(1);
            // sort pages by when_changed
            $res = usort( $pages, create_function( '$a,$b', 'return ( ( $a["modified_when"] < $b["modified_when"] ) ? 1 : -1 );' ) );
            return array_slice($pages,0,$number);
        }   // end function getLastEdited()
        

        /**
         * counts the levels from given page_id to root
         *
         * taken from old functions.php, dunno why this is done this way, maybe
         * it's more 'secure' than just taking the level of the parent?
         *
         * @access public
         * @param  integer  $page_id
         * @return integer  level (>=0)
         **/
        public static function getLevel($page_id)
        {
            $parent = self::properties($page_id,'parent');
            if ($parent > 0)
            {
                $level = self::properties($parent,'level');
                return $level + 1;
            }
            else
            {
                return 0;
            }
        }   // end function getLevel()

        /**
         *
         *
         *
         *
         **/
        public static function getLink($page_id)
        {
            if(!is_numeric($page_id))
                $link = $page_id;
            else
                $link = self::properties($page_id,'link');

            if(!$link)
                return NULL;

            // Check for :// in the link (used in URL's) as well as mailto:
            if (strstr($link, '://') == '' && substr($link, 0, 7) != 'mailto:')
                return CAT_URL . PAGES_DIRECTORY . $link . PAGE_EXTENSION;
            else
                return $link;

        }   // end function getLink()

        /**
         *
         *
         *
         *
         **/
        public static function getLinkedByLanguage($page_id)
        {
            $sql     = 'SELECT * FROM `:prefix:page_langs` AS t1'
                     . ' RIGHT OUTER JOIN `:prefix:pages` AS t2'
                     . ' ON t1.link_page_id=t2.page_id'
                     . ' WHERE t1.page_id = :id'
                     ;

            $results = self::getInstance()->db()->query($sql,array('id'=>$page_id));
            if ($results->rowCount())
            {
                $items = array();
                while (($row = $results->fetch()) !== false)
                {
                    $row['href'] = self::getLink($row['link']) . (($row['lang'] != '') ? '?lang=' . $row['lang'] : NULL);
                    $items[]     = $row;
                }
                return $items;
            }
            return false;
        }   // end function getLinkedByLanguage()

        /**
         * returns META (default charset, keywords, ...) and TITLE
         *
         * @access public
         * @return HTML
         **/
        public static function getDefaultMeta($droplets_config=array())
        {
            global $page_id;

            $properties = self::properties($page_id);
            $output     = array();
            $title       = NULL;
            $keywords    = NULL;
            $description = NULL;

            // charset
            $output[] = CAT_Helper_Page::$space
                      . '<meta http-equiv="Content-Type" content="text/html; charset='
                      . (isset($properties['default_charset']) ? $properties['default_charset'] : 'utf-8')
                          . '" />'
                          ;

            // page title
            if(isset($droplets_config['page_title']))
                $title = $droplets_config['page_title'];
            elseif(isset($properties['page_title']))
                $title = $properties['page_title'];
            elseif(defined('WEBSITE_TITLE'))
                $title = WEBSITE_TITLE;
            else
                $title = '-';
            if($title)
                $output[] = CAT_Helper_Page::$space . '<title>' . $title . '</title>';

            // description
            if(isset($droplets_config['description']))
                $description = $droplets_config['description'];
            elseif(isset($properties['description']) && $properties['description'] != '' )
                $description = $properties['description'];
            else
                $description = CAT_Registry::get('WEBSITE_DESCRIPTION');
            if ($description!='')
                $output[] = CAT_Helper_Page::$space . '<meta name="description" content="' . $description . '" />';

            // keywords
            if(isset($droplets_config['keywords']))
                $keywords = $droplets_config['keywords'];
            elseif(isset($properties['keywords']) && $properties['keywords'] != '' )
                $keywords = $properties['keywords'];
            else
                $keywords = CAT_Registry::get('WEBSITE_KEYWORDS');
            if ($keywords!='')
                $output[] = CAT_Helper_Page::$space . '<meta name="keywords" content="' . $keywords . '" />';

            // other meta tags set by droplets
            if(isset($droplets_config['meta']))
                $output[] = $droplets_config['meta'];

            return $output;

        } // end function getDefaultMeta()

        /**
         *
         * @access public
         * @return
         **/
        public static function getMeta($droplets_config=array())
        {
            $meta = self::getDefaultMeta($droplets_config);
            self::$meta = array_merge(
                $meta,
                self::$meta
            );
            return implode("\n",array_unique(self::$meta));
        } // end function getMeta()

        /**
         * returns properties of given page (same as properties())
         *
         * @access public
         * @param  integer $page_id
         * @return array
         **/
        public static function getPage($page_id)
        {
            return self::properties($page_id);
        }   // end function getPage()

        /**
         * resolves the path to root and returns the list of parent IDs
         *
         * @access public
         * @return
         **/
        public static function getParentIDs($page_id)
        {
            $ids = array();
            while ( self::properties($page_id,'parent') !== NULL )
            {
                if ( self::properties($page_id,'level') == 0 )
                    break;
                $ids[]   = self::properties($page_id,'parent');
                $page_id = self::properties($page_id,'parent');
            }
            return $ids;
        }   // end function getParentIDs()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getParentTitles($page_id)
        {
            $page     = self::properties($page_id);
            $titles[] = ( isset($page['menu_title']) ? $page['menu_title'] : $page['page_title'] );
            if ($page['level']>0)
            {
                $parent_titles = self::getParentTitles($page['parent']);
                $titles = array_merge($titles, $parent_titles);
            }
            return $titles;
        }   // end function getParentTitles()

        /**
    	 * checks permission for a page
    	 *
    	 * @access public
    	 * @param  int    $page_id
    	 * @param  string $action - viewing|admin; default: admin
    	 * @return boolean
    	 */
    	public static function getPagePermission($page_id,$action='admin')
        {
    		if ($action!='viewing') $action='admin';
    		$action_groups = $action.'_groups';
    		$action_users  = $action.'_users';
            $page          = self::properties($page_id);
    		if (is_array($page))
            {
				$groups = ( isset($page[$action_groups]) )
                        ? explode(',',$page[$action_groups])
                        : array();
				$users  = ( isset($page[$action_users]) )
                        ? $page[$action_users]
                        : array();
    		}

            // check if user is in any admin group
    		$in_group = FALSE;
    		foreach(CAT_Users::getInstance()->get_groups_id() as $cur_gid)
            {
    		    if (in_array($cur_gid, $groups))
                {
    		        $in_group = true;
    		    }
    		}
    		if((!$in_group) && !is_numeric(array_search(CAT_Users::getInstance()->get_user_id(), $users)))
            {
    			return false;
    		}
    		return true;
    	}   // end function getPagePermission()

        /**
         * uses ListBuilder to create a dropdown list of pages
         *
         * @access public
         * @return HTML
         **/
        public static function getPageSelect($as_array=false)
        {
            $pages  = CAT_Helper_Page::getPages(CAT_Backend::isBackend());
            if($as_array)
            {
                $opt = array();
                foreach($pages as $pg)
                    $opt[$pg['page_id']] = $pg['menu_title'];
                return $opt;
            }
            else
            {
            return CAT_Helper_ListBuilder::getInstance()->config(array(
                '__li_level_css'       => true,
                '__li_id_prefix'       => 'pageid_',
                '__li_css_prefix'      => 'fc_page_',
                '__li_has_child_class' => 'fc_expandable',
                '__title_key'          => 'menu_title',
            ))->tree( $pages, 0 );
            }
        }   // end function getPageSelect()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPageSettings($page_id,$type='internal',$key=NULL)
        {
            $set = self::properties($page_id,'settings');
            if($type)
            {
                if($key)
                {
                    if( isset($set[$type][$key]) )
                    {
                        if(is_array($set[$type][$key]) && count($set[$type][$key]) == 1)
                            return $set[$type][$key][0];
                        return $set[$type][$key];
                    }
                }
                else
                {
                    return ( isset($set[$type]) ? $set[$type] : NULL );
                }
            }
            return $set;
        }   // end function getPageSettings()


        /**
         *
         * @access public
         * @return
         **/
        public static function getPageTemplate($page_id)
        {
            $tpl = self::properties($page_id,'template');
            return ( $tpl != '' ) ? $tpl : DEFAULT_TEMPLATE;
        }   // end function getPageTemplate()
        

        /**
         * returns complete pages array
         *
         * @access public
         * @param  boolean $all - show all page or only visible (default:false)
         * @return array
         **/
        public static function getPages($all=false)
        {
            if(!count(self::$pages)) self::getInstance();
            if ( $all )
                return self::$pages;
            // only visible for current lang
            $pages = array();
            foreach(self::$pages as $pg)
                if(self::isVisible($pg['page_id']))
                    $pages[] = $pg;
            return $pages;
        }   // end function getPages()

        /**
         * returns pages array for given menu number
         *
         * @access public
         * @param  integer  $id    - menu id
         * @return array
         **/
        public static function getPagesForMenu($id)
        {
            if(!count(self::$pages)) self::getInstance();
            $menu = array();
            foreach(self::$pages as $pg)
            {
                if( $pg['menu'] == $id && self::isVisible($pg['page_id']) )
                    $menu[] = $pg;
            }
            return $menu;
        }   // end function getPagesForMenu()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPagesForLevel($level,$menu_no=NULL)
        {
            if(!count(self::$pages)) self::getInstance();
            $pages = array();
            foreach(self::$pages as $pg)
            {
                // check level and visibility
                if ( $pg['level'] == $level  && self::isVisible($pg['page_id']) )
                {
                    // optional: check for given menu number
                    if(!$menu_no || $pg['menu'] == $menu_no)
                    {
                    $pages[] = $pg;
            }
                }
            }
            return $pages;
        }   // end function getPagesForLevel()

        /**
         *
         * @access public
         * @return mixed
         **/
        public static function getPageByPath($path)
        {
            if(!count(self::$pages)) self::getInstance();
            if(substr($path,0,1)!='/') $path = '/'.$path;
            foreach(self::$pages as $pg)
                if($pg['link']==$path)
                    return $pg['page_id'];
            return NULL;
        }   // end function getPageByPath()

        /**
         * returns a list of page_id's containing the children of given parent
         *
         * @access public
         * @param  integer  $parent (default:0)
         * @param  boolean  $add_sections (default:false)
         * @return array
         **/
    	public static function getPagesByParent($parent=0, $add_sections=false)
    	{
            if(!count(self::$pages_by_parent))
            {
                $pages = self::getPages(CAT_Backend::isBackend());
                foreach ( $pages as $page ) {
                    self::$pages_by_parent[$page['parent']][] = $page['page_id'];
                }
            }
    		return
                  isset(self::$pages_by_parent[$parent])
                ? self::$pages_by_parent[$parent]
                : array();
    	}   // end function getPagesByParent()
        
        /**
         * returns a list of page_id's by visibility
         *
         * @access public
         * @param  string  $visibility - optional
         * @return array
         **/
        public static function getPagesByVisibility($visibility=NULL)
        {
            if(!count(self::$pages)) self::getInstance();
            if(!count(self::$pages_by_visibility))
            {
                foreach(self::$pages as $page)
                {
                    self::$pages_by_visibility[$page['visibility']][] = $page['page_id'];
                }
            }
            if($visibility)
            {
                if(isset(self::$pages_by_visibility[$visibility]))
                return self::$pages_by_visibility[$visibility];
                else
                    return array();
            }
            return self::$pages_by_visibility;
        }   // end function getPagesByVisibility()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPageTrail($page_id,$skip_zero=false,$as_array=false)
        {
            $ids = array_reverse(self::getParentIDs($page_id));
            if($skip_zero) array_shift($ids);
            $ids[] = $page_id;
            return (
                $as_array ? $ids : implode(',',$ids)
            );
        }   // end function getPageTrail()
        
        /**
         * returns the root level page of a trail
         *
         * @access public
         * @return integer
         **/
        public static function getRootParent($page_id)
        {
            if(self::properties($page_id,'level')==0)
                return 0;
            $trail = self::getPageTrail($page_id,false,true);
            return $trail[0];
        }   // end function getRootParent()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getSection($page_id,$section_id)
        {
            $sections = self::getSections($page_id);
            if(count($sections))
            {
                foreach($sections as $section)
                {
                    if($section['section_id']==$section_id)
                        return $section;
                }
            }
            return false;
        }   // end function getSection()

        /**
         * returns the sections of a page
         *
         * to get all sections of all pages, leave param empty
         *
         * @access public
         * @param  integer  $page_id
         * @return array
         **/
        public static function getSections($page_id=NULL)
        {
            if(!count(self::$pages)) self::getInstance();
            if(!count(self::$pages_sections))
                self::$pages_sections = CAT_Sections::getActiveSections();

            if($page_id)
                return
                      isset(self::$pages_sections[$page_id])
                    ? self::$pages_sections[$page_id]
                    : array();
                else
                    return self::$pages_sections;
        }   // end function getSections()

        /**
         *
         * @access public
         * @return
         **/
        public static function getSubPages($page_id,$result=array())
        {
            $subs = self::getPagesByParent($page_id);
            if(!$subs || !is_array($subs)) return $result;
            foreach($subs as $pg)
            {
                $result[] = $pg;
                $result   = self::getSubPages($pg,$result);
            }
            return $result;
        }   // end function getSubPages()

        /**
         * virtual pages are used for something like
         *   - user preferences dialog
         *   - search results
         *   - ...
         * This methods sets some defaults for this case
         *
         * @access public
         * @return
         **/
        public static function getVirtualPage($title)
        {
            global $page_id, $page_description, $page_keywords;
            $page_id          = 0;
            $page_description = '';
            $page_keywords    = '';
            define( 'PAGE_ID'    , 0 );
            define( 'ROOT_PARENT', 0 );
            define( 'PARENT'     , 0 );
            define( 'LEVEL'      , 0 );
            define( 'PAGE_TITLE' , CAT_Helper_I18n::getInstance()->translate($title) );
            define( 'MENU_TITLE' , CAT_Helper_I18n::getInstance()->translate($title) );
            define( 'MODULE'     , '' );
            define( 'VISIBILITY' , 'public' );
        }   // end function getVirtualPage()
        

        /**
         * Work-out if the page parent (if selected) has a seperate language
         *
         * @access public
         * @return
         **/
        public static function sanitizeLanguage(&$page)
        {
            if($page['language'] == '')
            {
                // root level
                if ( !$page['parent'] || $page['parent'] == '0' )
                {
                    $page['language'] = ( $page['language'] == '' ) ? DEFAULT_LANGUAGE : $page['language'];
                }
                else
                {
                    $parent_lang = self::properties($page['parent'],'language');
                    $page['language'] = ( $page['language'] == '' ) ? $parent_lang : $page['language'];
                }
            }
        }   // end function sanitizeLanguage()

        /**
         * Work-out what the link and page filename should be
         *
         * @access public
         * @return
         **/
        public static function sanitizeLink(&$page)
        {
            if($page['link']=='/')
                $page['link'] .= $page['menu_title'];
            // root level
            if ( !$page['parent'] || $page['parent'] == '0' )
            {
                $page['link']
                    = ( $page['link'] !== '' )
                      ? self::getFilename($page['link'])
                      : self::getFilename($page['menu_title'])
                      ;
                $page['link'] = '/'.$page['link'];
                // 'intro' and 'index' are not allowed in root level
                if( $page['link'] == '/index' || $page['link'] == '/intro' )
                    $page['link'] .= '_0';
            }
            // sub level
            else
            {
                // get the titles of the parent pages to create the subdirectory
                $parent_section = '';
                $parent_titles  = array_reverse(self::getParentTitles($page['parent']));

                foreach( $parent_titles as $parent_title )
                    $parent_section .= self::getFilename($parent_title).'/';

                if ($parent_section == '/')
                    $parent_section = '';

                $page['link']  = '/'.$parent_section.self::getFilename($page['link']);
                $page['level'] = count($parent_titles);
            }
        }   // end function sanitizeLink()

        /**
         * Work-out if the page parent (if selected) has a seperate template
         *
         * @access public
         * @return
         **/
        public static function sanitizeTemplate(&$page)
        {
            if($page['template'] == '')
            {
                // root level
                if ( !$page['parent'] || $page['parent'] == '0' )
                {
                    //$page['template'] = ( $page['template'] == '' ) ? DEFAULT_TEMPLATE : $page['template'];
                    $page['template'] = ( $page['template'] == '' ) ? '' : $page['template'];
                }
                else
                {
                    $parent_tpl = self::properties($page['parent'],'template');
                    $page['template'] = ( $page['template'] == '' ) ? $parent_tpl : $page['template'];
                }
            }
        }   // end function sanitizeTemplate()

        /**
         *
         * @access public
         * @return
         **/
        public static function sanitizeTitles(&$page)
        {
            // =======================================
            // ! Validate menu_title (mandatory field)
            // =======================================
            if ($page['menu_title'] == '' || substr($page['menu_title'],0,1)=='.')
                return false;

            // check page_title
            $page['page_title']
                = ( $page['page_title'] == '' )
                ? $page['menu_title']
                : $page['page_title']
                ;
        }   // end function sanitizeTitles()
        

        /**
         * identify the page to show
         *
         * @access public
         * @param  boolean  $no_intro
         * @return boolean
         **/
        public static function selectPage( $no_intro = false )
        {
            global $page_id; // may be set by accessor file

            // check if the system is in maintenance mode
            if ( self::isMaintenance() )
            {
                // admin can still see any page
                if(!CAT_Users::getInstance()->is_root() )
                {
                    if(!CAT_Registry::exists('MAINTENANCE_PAGE'))
                    {
                        $result = CAT_Registry::getInstance()->db()->query(
                            'SELECT `value` FROM `:prefix:settings` WHERE `name`="maintenance_page"'
                        );
                        if(is_resource($result) && $result->rowCount()==1)
                        {
                            $row = $result->fetch();
                            CAT_Registry::register('MAINTENANCE_PAGE',$row['maintenance_page'],true);
                        }
                    }
                    $page_id = MAINTENANCE_PAGE;
                }
            }

            // check if intro page to show
            if ( (INTRO_PAGE && ! $no_intro) && (!isset($page_id) || !is_numeric($page_id)))
            {
                // Get intro page content
                $filename = CAT_PATH . PAGES_DIRECTORY . '/intro' . PAGE_EXTENSION;
                if (file_exists($filename))
                {
                    $handle  = @fopen($filename, "r");
                    $content = @fread($handle, filesize($filename));
                    @fclose($handle);
                    CAT_Helper_Page::preprocess($content);
                    header("Location: " . CAT_URL . PAGES_DIRECTORY . "/intro" . PAGE_EXTENSION . ""); // send intro.php as header to allow parsing of php statements
                    echo ($content);
                    return false;
                }
            }

            // search
            if ( ! $page_id )
            {
                if(CAT_Registry::get('USE_SHORT_URLS')&&isset($_SERVER['REDIRECT_QUERY_STRING']))
                     $page_id = CAT_Helper_Page::getPageByPath('/'.$_SERVER['REDIRECT_QUERY_STRING']);
                else
                    $page_id = self::getDefaultPage();
            }

            if(!defined('PAGE_ID'))
                define('PAGE_ID',$page_id);

            return $page_id;
        } // end function selectPage()

        /**
         * returns the properties for the given page ID
         *
         * @access public
         * @param  integer $page_id
         * @param  string  $key      - optional property name
         * @return mixed
         **/
        public static function properties($page_id,$key=NULL)
        {
            if(!$page_id)
                return NULL;
            if(!count(self::$pages)&&!CAT_Registry::exists('CAT_HELPER_PAGE_INITIALIZED'))
                self::init();
            // get page data
            $page = isset(self::$pages_by_id[$page_id])
                  ? self::$pages[self::$pages_by_id[$page_id]]
                  : array();
            if(count($page))
            {
                if($key)
                {
                    if(isset($page[$key]))
                    return $page[$key];
                    else
                        return NULL;
                }
                else
                {
                    return $page;
                }
            }
            return array();
        }   // end function properties()

        /**
         * replaces internal links; should be exported into an output filter
         **/
        public static function preprocess(&$content)
        {
            if( ! function_exists('cmsplink') )
                @include_once CAT_PATH.'/modules/blackcatFilter/filter/cmsplink.php';
            cmsplink($content);
        }   // end static function preprocess()

        public static function printUnderConstruction()
        {
            // try to find a template
            $file      = CAT_Helper_Directory::getInstance()->findFile('under_construction', CAT_PATH."/templates/".DEFAULT_TEMPLATE, true );
            $image_url = CAT_URL.'/templates/'.DEFAULT_TEMPLATE.'/images';
            if(!$file)
            {
                $file = CAT_Helper_Directory::getInstance()->findFile('under_construction', CAT_PATH."/templates/".DEFAULT_THEME, true );
                $image_url = CAT_URL.'/templates/'.DEFAULT_THEME.'/images';
            }
            if(!$file)
            {
                self::getInstance()->printFatalError('Website Under Construction'.'<br />'.'Please check back soon...');
    		}
            else
            {
                global $parser;
                $parser->setPath(pathinfo($file,PATHINFO_DIRNAME));
                $parser->output(pathinfo($file,PATHINFO_FILENAME),array('IMAGE_URL'=>$image_url));
    		}
    	}

        /**
         * recursivly update page trail of subs
         *
         * @access public
         * @param  integer $parent
         * @param  integer $root_parent
         **/
        public static function updatePageTrail($parent,$root_parent)
        {
#echo "updatePageTrail($parent,$root_parent)\n";
            $page_id = self::properties($parent,'page_id');
#echo "page_id $page_id\n";
            if($page_id)
            {
                self::$instance->db()->query(
                    'UPDATE `:prefix:pages` SET root_parent=:root, page_trail=:trail WHERE page_id=:id',
                    array('root'=>$root_parent,'trail'=>self::getPageTrail($page_id,true),'id'=>$page_id)
                );
                if( $page_id !== $parent )
                // recurse
        		    self::updatePageTrail($page_id,$root_parent);
        	}
        }   // end function updatePageTrail()

        /**
         * checks if page is active (=has active sections and is between
         * publ_start and publ_end)
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isActive($page_id)
        {
            self::getSections($page_id);
            if(isset(self::$pages_sections[$page_id]) && count(self::$pages_sections[$page_id]))
                return true;
            return false;
        } // end function isActive()

        /**
         * checks if page is deleted
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isDeleted($page_id)
        {
            $page    = self::properties($page_id);
            if($page['visibility']=='deleted')
                return true;
            return false;
        } // end function isDeleted()

        /**
         * check if system is in maintenance mode
         *
         * @access public
         * @return boolean
         **/
        public static function isMaintenance()
        {
            if(!CAT_Registry::exists('MAINTENANCE_MODE'))
            {
                $result = $this->db()->query(
                    'SELECT `value` FROM `:prefix:settings` WHERE `name`="maintenance_mode"'
                );
                if(is_resource($result)&&$result->rowCount()==1)
                {
                    $row = $result->fetch();
                    CAT_Registry::register('MAINTENANCE_MODE',$row['maintenance_mode'],true);
                }
            }
            return
                ( CAT_Registry::get('MAINTENANCE_MODE') == 'on' )
                ? true
                : false;
        }   // end function isMaintenance()

        /**
         * Check whether a page is visible or not
         * This will check page-visibility, user- and group permissions
         *
         * @access public
         * @param  integer  $page_id
         * @return boolean
         **/
        public static function isVisible($page_id)
        {
            $show_it = false;
            $page    = self::properties($page_id);
            switch ($page['visibility'])
            {
                // never shown in FE
                case 'none':
                case 'deleted':
                    $show_it = false;
                    break;
                // shown if called, but not in menu
                case 'hidden':
                    if(self::selectPage()==$page_id)
                        $show_it = true;
                    break;
                // always visible
                case 'public':
                    // check language
                    if(CAT_Registry::get('PAGE_LANGUAGES')===false || self::properties($page_id,'language')==''||self::properties($page_id,'language')==LANGUAGE)
                    $show_it = true;
                    break;
                // shown if user is allowed
                case 'private':
                case 'registered':
                    if (CAT_Users::is_authenticated() == true)
                    {
                        // check language
                        if(CAT_Registry::get('PAGE_LANGUAGES')=='false'||(self::properties($page_id,'language')==''||self::properties($page_id,'language')==LANGUAGE))
                        $show_it = (
                               CAT_Users::is_group_match(CAT_Users::get_groups_id(), $page['viewing_groups'])
                            || CAT_Users::is_group_match(CAT_Users::get_user_id(), $page['viewing_users'])
                            || CAT_Users::is_root()
                        );
                    }
                    else
                    {
                        $show_it = false;
                    }
                    break;
            }
            return $show_it;
        } // end function isVisible()

// *****************************************************************************
//                   PRIVATE FUNCTIONS
// *****************************************************************************

        /**
         * analyzes CSS files to load and fills the static array $css
         *
         * @access private
         * @param  array   $arr
         * @param  string  $path_prefix (optional)
         * @return void
         **/
        private static function _analyze_css(&$arr,$path_prefix=NULL)
        {
            if (is_array($arr))
            {
                $check_paths = array();
                if ($path_prefix != '')
                {
                    $check_paths = explode('/', $path_prefix);
                    $check_paths = array_reverse($check_paths);
                }
                foreach ($arr as $css)
                {
                    // no file - no good
                    if (!isset($css['file']))
                        continue;
                    // relative path?
                    if (!preg_match('#/modules/#i', $css['file']) && ! !preg_match('#/templates/#i', $css['file']))
                    {
                        foreach ($check_paths as $subdir)
                        {
                            if (!preg_match('#' . $subdir . '/#', $css['file']))
                            {
                                $css['file'] = CAT_Helper_Directory::sanitizePath($subdir.'/'.$css['file']);
                            }
                        }
                    }
                    CAT_Helper_Page::$css[] = $css;
                }
            }
            self::$instance->log()->logDebug('CSS',CAT_Helper_Page::$css);
        } // end function _analyze_css()

        /**
         * analyzes javascripts array and fills static array $js
         *
         * The components of given $path_prefix are checked to be included in
         * the file name (and added if not)
         *
         * @access private
         * @param  array    $arr         - items to add
         * @param  string   $path_prefix - example: templates/mojito/js
         * @return void
         **/
        private static function _analyze_javascripts(&$arr, $for = 'header', $path_prefix = NULL, $section = false)
        {
            $self = self::$instance;

            // INTERNAL NOTE: $section should be a string, but there were
            // cases it wasn't, so we check here
            $self->log()->logDebug(
                sprintf(
                    'analyzing javascripts for [%s], path_prefix [%s], section [%s]',
                    $for,$path_prefix,(is_array($section) ? var_export($section,1) : $section )
                ),
                $arr
            );

            if (!is_array($arr)) return;

            // reference array (header or footer)
            if ($for == 'header') $ref =& CAT_Helper_Page::$js;
            else                  $ref =& CAT_Helper_Page::$f_js;

            // $check_paths is a reversed array of $path_prefix parts; these
            // parts will be added to every $arr item until the file is found
                $check_paths = array();
                if ($path_prefix != '')
                {
                    $check_paths = explode('/', $path_prefix);
                    $check_paths = array_reverse($check_paths);
                }

            // validator is needed to sanitize URL
                    $val = CAT_Helper_Validate::getInstance();

            foreach ($arr as $index => $item)
                    {
#echo "INDEX $index ITEM ", var_export($item,1), "<br />";

                if(is_array($item))
                    continue;

                // if the path contains 'modules' or 'templates', we presume
                // that it's a complete path
                // same for entries starting with 'http(s)'
                if (
                       preg_match('/^http(s)?:/', $item, $m1) // abs. URL
                    || preg_match('#/(modules|templates)/#i', $item, $m2)
                                        ) {
                    $self->log()->logDebug('m1',$m1);
                    $self->log()->logDebug('m2',$m2);
                    $self->log()->logDebug('abs. URL');
                    $ref[] = CAT_Helper_Page::$space
                           . '<script type="text/javascript" src="'
                           . $val->sanitize_url( (isset($m2[0]) ? CAT_URL : '' ) .'/'.$item)
                           . '"></script>';
                    continue;
                }
                // try to combine $item with $path_prefix
                if ($path_prefix != '' && file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$path_prefix.'/'.$item)))
                {
                    $self->log()->logDebug('matched by path_prefix');
                    $ref[] = CAT_Helper_Page::$space
                           . '<script type="text/javascript" src="'
                           . $val->sanitize_url(CAT_URL.'/'.$path_prefix.'/'.$item)
                           . '"></script>';
                            continue;
                        }
                // we iterate over $check_paths, adding the path parts and
                // trying to find the file
                $add_to_path = '';
                            foreach ($check_paths as $subdir)
                            {
                    if (!preg_match('#/'.$subdir.'/#', $item))
                        $add_to_path = $subdir.'/'.$add_to_path;
                    if(file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$add_to_path.'/'.$item)))
                                {
                        $self->log()->logDebug('matched by check_paths');
                        $ref[] = CAT_Helper_Page::$space
                               . '<script type="text/javascript" src="'
                               . $val->sanitize_url(CAT_URL.'/'.$add_to_path.'/'.$item)
                               . '"></script>';
                        continue;
                    }
                }
                $self->log()->logDebug('NO MATCH!');
            }
            $self->log()->logDebug('complete result:',$ref);
        } // end function _analyze_javascripts()

        /**
         * analyzes jQuery components to load; fills static array $jquery
         *
         * @access private
         * @param  array    $arr
         * @param  string   $for
         * @param  string   $section
         * @return void
         **/
        private static function _analyze_jquery_components(&$arr, $for = 'frontend', $section = NULL)
        {
            global $page_id;

            $static =& CAT_Helper_Page::$jquery;
            $val    =  CAT_Helper_Validate::getInstance();

            $set    = self::getInstance()->db()->query(
                'SELECT `use_core`, `use_ui` FROM `:prefix:pages_headers` WHERE `page_id`=:id OR `page_id`=0',
                array('id' => $page_id)
            );
            if($set->rowCount())
            {
                while(false!==($row = $set->fetch()))
                {
                    if($row['use_ui'] == 'Y') $arr['ui']     = true;
                    if($row['use_core'] == 'Y') $arr['core'] = true;
                }
            }

            // make sure that we load the core if needed, even if the
            // author forgot to set the flags
            if ( isset($arr['ui']) && $arr['ui'] === true )
                $arr['core'] = true;

            // load the components
            if (isset($arr['ui-theme']) && file_exists(CAT_PATH . '/modules/lib_jquery/jquery-ui/themes/' . $arr['ui-theme']))
            {
                $static[] = CAT_Helper_Page::$space . '<link rel="stylesheet" type="text/css" href="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/jquery-ui/themes/' . $arr['ui-theme'] . '/jquery-ui.css') . '" media="all" />' . "\n";
            }

            // core is always added to header
            if (!CAT_Helper_Page::$jquery_core && isset($arr['core']) && $arr['core'] === true)
            {
                CAT_Helper_Page::$jquery[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/jquery-core/jquery-core.min.js') . '"></script>' . "\n";
                CAT_Helper_Page::$jquery_core = true;
            }

            // ui is always added to header
            if (!CAT_Helper_Page::$jquery_ui_core && isset($arr['ui']) && $arr['ui'] === true)
            {
                CAT_Helper_Page::$jquery[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/jquery-ui/ui/jquery-ui.min.js') . '"></script>' . "\n";
                CAT_Helper_Page::$jquery[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/jquery-ui/ui/i18n/jquery-ui-i18n.min.js') . '"></script>' . "\n";
                CAT_Helper_Page::$jquery_ui_core = true;
            }

            // components to load on all pages (backend only)
            if (isset($arr['all']) && is_array($arr['all']))
            {
                foreach ($arr['all'] as $item)
                {
                    $resolved = self::_find_item($item);
                    if($resolved) {
                    $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/plugins/' . $resolved ) . '"></script>' . "\n";
                }
            }
            }

            // components to load on individual pages only (backend only)
            if (isset($arr['individual']) && is_array($arr['individual']) && isset($section) && $section != '')
            {
                foreach ($arr['individual'] as $section_name => $item)
                {
                    if ($section_name == strtolower($section))
                    {
                        $resolved = self::_find_item($item);
                        $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/plugins/' . $item) . '"></script>' . "\n";
                    }
                }
            }
            self::$instance->log()->logDebug('jQuery',$static);
        } // end function _analyze_jquery_components()

        /**
         *
         * @access private
         * @return
         **/
        private static function _analyze_meta(&$arr)
        {
            if (is_array($arr))
            {
                foreach($arr as $el)
                {
                    $str = '<meta ';
                    foreach($el as $key => $val)
                        $str .= $key.'="'.$val.'" ';
                    $str .= '/>';
                    self::$meta[] = $str;
                }
            }
        }   // end function _analyze_meta()

        /**
         * really deletes a page
         *
         * @access private
         * @return
         **/
        private static function _deletePage($page_id)
        {

            global $wb, $admin, $backend;
            $admin =& $backend;

            $self   = self::getInstance();
            $errors = array();
            // delete sections (call delete.php for each)
            $sections = self::getSections($page_id);

            // $sections array: <blockid> => array( <sections> )
            if(count($sections))
            {
                foreach($sections as $blockid => $sec)
                {
                    foreach($sec as $section)
                {
                        // we don't need this here, but the delete.php may
                        // use the $section_id global
                    $section_id = $section['section_id'];
                    if (file_exists(CAT_PATH.'/modules/'.$section['module'].'/delete.php'))
                    {
                        include(CAT_PATH.'/modules/'.$section['module'].'/delete.php');
                    }
                }
            }
            }
            // delete access file
            self::deleteAccessFile($page_id);
            // delete settings
            self::getInstance()->db()->query(
                'DELETE FROM `:prefix:pages_settings` WHERE `page_id`=:id',
                array('id'=>$page_id)
            );
            // remove page from DB
            $self->db()->query(
                'DELETE FROM `:prefix:pages` WHERE `page_id` = :id',
                array('id'=>$page_id)
            );
            if ($self->db()->isError())
            {
                $errors[] = $self->db()->getError();
            }
            // Update the sections table
            $self->db()->query(
                'DELETE FROM `:prefix:sections` WHERE `page_id` = :id',
                array('id'=>$page_id)
            );
            if ($self->db()->isError())
            {
                $errors[] = $self->db()->getError();
            }
            // clean-up ordering
            include_once(CAT_PATH . '/framework/class.order.php');
            $order = new order(CAT_TABLE_PREFIX . 'pages', 'position', 'page_id', 'parent');
            $order->clean($page_id);
            return $errors;
        }   // end function _deletePage()

        /**
         * marks pages as 'deleted' if trash is enabled
         * this method works recursively for sub pages
         *
         * @access private
         * @param  integer $parent
         * @return void
         **/
       	private static function _trashPages($parent = 0)
        {
            // get pages for current parent
            $pages = self::getPagesByParent($parent);
            if(count($pages))
            {
    			foreach($pages as $page_id)
                {
                    $page = self::getPage($page_id);
    				// Update the page visibility to 'deleted'
    				self::getInstance()->db()->query(
                        "UPDATE `:prefix:pages` SET visibility = :vis WHERE page_id = :id LIMIT 1",
                        array('vis'=>'deleted','id'=>$page['page_id'])
                    );
    				// Run this function again for all sub-pages
    				if( !self::_trashPages( $page['page_id'] ) )
                        return false;
                    if(self::getInstance()->db()->isError())
                        return false;
                }
            }
            return true;
    	}   // end function _trashPages()

        /**
         * evaluate correct item path
         *
         * @access private
         * @param  string  $item
         * @return mixed
         **/
        private static function _find_item($item)
        {
            // check suffix
            if ( pathinfo($item,PATHINFO_EXTENSION) != 'js' )
                $item .= '.js';
            // just there?
            if (!file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$item)))
            {
                $dir = pathinfo($item,PATHINFO_FILENAME);
                if (file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$dir.'/'.$item)))
                {
                    $item = $dir.'/'.$item;
                    return $item;
                }
            }
            else
            {
                return $item;
            }
            return NULL;
        }   // end function _find_item()

        /**
         * load all CSS files
         *
         * @access private
         * @param  string  $for - frontend | backend
         * @return void
         **/
        private static function _load_css($for = 'frontend')
        {
            if (count(CAT_Helper_Page::$css_search_path))
            {
                // automatically add CSS files
                foreach (CAT_Helper_Page::$css_search_path as $directory)
                {
                    // template.css
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/template.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'screen,projection',
                            'file' => $file
                        );
                    }
                    // print.css
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/print.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'print',
                            'file' => $file
                        );
                    }
                    // frontend.css / backend.css
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/' . $for . '.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'all',
                            'file' => $file
                        );
                    }
                    // frontend.css / backend_print.css
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/' . $for . '_print.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'print',
                            'file' => $file
                        );
                    }
                    // PAGE_ID.css (frontend only)
                    if ($for == 'frontend' && defined('PAGE_ID') )
                    {
                        $file = CAT_Helper_Directory::sanitizePath($directory . '/' . PAGE_ID . '.css');
                        if (file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH . '/' . $file)))
                        {
                            CAT_Helper_Page::$css[] = array(
                                'media' => 'screen,projection',
                                'file' => $file
                            );
                        }
                        $file = CAT_Helper_Directory::sanitizePath($directory . '/' . PAGE_ID . '_print.css');
                        if (file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH . '/' . $file)))
                        {
                            CAT_Helper_Page::$css[] = array(
                                'media' => 'print',
                                'file' => $file
                            );
                        }
                    }
                }
            }
            self::$instance->log()->logDebug('CSS',CAT_Helper_Page::$css);
        } // end function _load_css()

        /**
         * analyzes the contents of the footers.inc.php
         *
         * @access private
         * @param  string  $file - path to headers.inc.php
         * @param  string  $for  - frontend | backend
         * @param  string  $path_prefix
         * @param  string  $section
         * @return void
         **/
        private static function _load_footers_inc($file, $for, $path_prefix, $section=NULL)
        {
            // reset array
            $mod_footers = array();
            // load file
            require $file;
            // analyze
            if (isset($mod_footers[$for]) && is_array($mod_footers[$for]) && count($mod_footers[$for]))
            {
                if (isset($mod_footers[$for]['jquery']) && is_array($mod_footers[$for]['jquery']) && count($mod_footers[$for]['jquery']))
                {
                    self::_analyze_jquery_components($mod_footers[$for]['jquery'][0], $for, $section);
                }
                // ----- other JS -----
                if (isset($mod_footers[$for]['js']) && is_array($mod_footers[$for]['js']) && count($mod_footers[$for]['js']))
                {
                    $temp_arr = ( is_array($mod_footers[$for]['js'][0]) ? $mod_footers[$for]['js'][0] : $mod_footers[$for]['js'] );
                    self::_analyze_javascripts($mod_footers[$for]['js'], 'footer', $path_prefix . '/js', $section);
                }
            }
            else
            {
                self::$instance->log()->logDebug(sprintf('no $mod_footers for [%s]',$for));
            }
        } // end function _load_footers_inc()

        /**
         * analyzes the contents of the headers.inc.php
         *
         * @access private
         * @param  string  $file - path to headers.inc.php
         * @param  string  $for  - frontend | backend
         * @param  string  $path_prefix
         * @param  string  $section
         * @return void
         **/
        private static function _load_headers_inc($file, $for, $path_prefix, $section = NULL)
        {
            // reset array
            $mod_headers = array();
            // load file
            require $file;
            // analyze
            if (isset($mod_headers[$for]) && is_array($mod_headers[$for]) && count($mod_headers[$for]))
            {
                // ----- META -----
                if (isset($mod_headers[$for]['meta']) && is_array($mod_headers[$for]['meta']) && count($mod_headers[$for]['meta']))
                {
                    self::_analyze_meta($mod_headers[$for]['meta']);
                }
                // ----- CSS -----
                if (isset($mod_headers[$for]['css']) && is_array($mod_headers[$for]['css']) && count($mod_headers[$for]['css']))
                {
                    self::_analyze_css($mod_headers[$for]['css'], $path_prefix);
                }
                // ----- jQuery -----
                if (isset($mod_headers[$for]['jquery']) && is_array($mod_headers[$for]['jquery']) && count($mod_headers[$for]['jquery']))
                {
                    self::_analyze_jquery_components($mod_headers[$for]['jquery'][0], $for, $section);
                }
                // ----- other JS -----
                if (isset($mod_headers[$for]['js']) && is_array($mod_headers[$for]['js']) && count($mod_headers[$for]['js']))
                {
                    $temp_arr = ( is_array($mod_headers[$for]['js'][0]) ? $mod_headers[$for]['js'][0] : $mod_headers[$for]['js'] );
                    self::_analyze_javascripts($temp_arr, 'header', $path_prefix . '/js', $section);
                }
            }
            else
            {
                self::$instance->log()->logDebug(sprintf('no $mod_headers for [%s]',$for));
            }
        }   // end function _load_headers_inc()

        /**
         * load JS
         *
         * @access private
         * @param  string  $for - frontend | backend
         * @return void
         **/
        private static function _load_js($for = 'frontend')
        {
            global $page_id;
            if (count(CAT_Helper_Page::$js_search_path))
            {
                $val = CAT_Helper_Validate::getInstance();
                $seen = array();
                foreach (CAT_Helper_Page::$js_search_path as $directory)
                {
                    $file = CAT_Helper_Directory::sanitizePath($directory . '/' . $for . '.js');
                    if ( ! isset($seen[$file]) )
                    if (file_exists(CAT_PATH . '/' . $file))
                            CAT_Helper_Page::$js[]
                                = '<script type="text/javascript" src="'
                                . $val->sanitize_url(CAT_URL . $file)
                                . '"></script>' . "\n";
                    $seen[$file] = 1;
                }
                if ($for == 'frontend')
                {
                    $file = CAT_Helper_Directory::sanitizePath(CAT_Registry::get('PAGES_DIRECTORY').'/js/'.$page_id.'.js');
                    if ( ! isset($seen[$file]) && file_exists(CAT_PATH . '/' . $file) )
                    {
                        CAT_Helper_Page::$js[]
                            = '<script type="text/javascript" src="'
                            . $val->sanitize_url(CAT_URL . $file)
                            . '"></script>' . "\n";
                        $seen[$file] = 1;
                    }
                }
            }
            self::$instance->log()->logDebug('JS',CAT_Helper_Page::$js);
        } // end function _load_js()

        /**
         * load headers.inc.php for sections
         *
         * @access private
         * @param  string  $for - frontend | backend
         * @return void
         **/
        private static function _load_sections($for = 'frontend')
        {
            global $page_id;
            // make sure we have a page_id
            if(!$page_id)
                $page_id = CAT_Helper_Validate::get('_REQUEST','page_id','numeric');
            if ($page_id && is_numeric($page_id))
            {
                $sections     = self::getSections($page_id);
                $wysiwyg_seen = false;
                self::$instance->log()->logDebug('sections:',$sections);
                if (is_array($sections) && count($sections))
                {
                    global $current_section;
                    global $wysiwyg_seen;
                    foreach ($sections as $block_id => $item)
                    {
                        foreach($item as $section)
                        {
                            $module = $section['module'];
                            $file   = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$module.'/headers.inc.php');
                            // find header definition file
                            if (file_exists($file))
                            {
                                self::$instance->log()->logDebug(sprintf('loading headers.inc.php for module [%s]',$module));
                                $current_section = $section['section_id'];
                                self::_load_headers_inc($file, $for, 'modules/' . $module, $current_section);
                            }
                            array_push(CAT_Helper_Page::$css_search_path, '/modules/' . $module, '/modules/' . $module . '/css');
                            array_push(CAT_Helper_Page::$js_search_path, '/modules/' . $module, '/modules/' . $module . '/js');
                        } // foreach ($sections as $section)
                    }
                } // if (count($sections))

                // always add WYSIWYG headers, some modules may use show_wysiwyg_editor() later on
                if ( ! $wysiwyg_seen )
                        {
                            if ( file_exists(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/headers.inc.php') )
                            {
                                self::$instance->log()->logDebug('adding headers.inc.php for wysiwyg');
                        self::_load_headers_inc(
                            CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.WYSIWYG_EDITOR.'/headers.inc.php'),
                            $for,
                            CAT_PATH.'/modules/'.WYSIWYG_EDITOR
                        );
                            }
                            $wysiwyg_seen = true;
                        }

                // search
                if($for == 'frontend' && CAT_Registry::get('SHOW_SEARCH') === true)
                {
                    array_push(
                        CAT_Helper_Page::$js_search_path,
                        '/modules/'.CAT_Registry::get('SEARCH_LIBRARY').'/templates/custom/',
                        '/modules/'.CAT_Registry::get('SEARCH_LIBRARY').'/templates/default/'
                    );
                }

            }
        }   // end function _load_sections()

        /**
         *
         * @access private
         * @return
         **/
        private static function _set_current($page_id)
        {
        
        }   // end function _set_current()
    }
}
