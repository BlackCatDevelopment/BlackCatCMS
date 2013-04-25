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

if (!class_exists('CAT_Helper_Page'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Page extends CAT_Object
    {
        protected      $_config             = array( 'loglevel' => 7 );
        private static $instance;
        private static $space               = '    '; // space before header items
        private static $pages               = array();
        private static $pages_by_visibility = array();
        private static $pages_by_id         = array();
        private static $pages_sections      = array();

        // header components
        private static $css             = array();
        private static $meta            = array();
        private static $js              = array();
        private static $jquery          = array();
        private static $jquery_core     = false;
        private static $jquery_ui_core  = false;

        // scan dirs
        private static $css_search_path = array();
        private static $js_search_path  = array();

        // footer components
        private static $script          = array();
        private static $f_jquery        = array();
        private static $f_js            = array();


        /**
         * the constructor loads the available pages from the DB and stores it
         * in internal arrays
         *
         * @access private
         * @return void
         **/
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                self::init();
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

        /**
         * initialize
         *
         * @access private
         * @return void
         **/
        private static function init()
        {
            // get complete list
            if(!count(self::$pages))
            {
                $now = time();
                $result = self::$instance->db()->query(sprintf(
                    'SELECT * FROM %spages ORDER BY `level` ASC, `position` ASC',
                    CAT_TABLE_PREFIX
                ));
                if( $result && $result->numRows()>0 )
                {
                    while ( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
                    {
                        self::$pages[] = $row;
                        end(self::$pages);
                        $key = key(self::$pages);
                        reset(self::$pages);

                        self::$pages_by_visibility[$row['visibility']][]
                            =& self::$pages[$key];
                        self::$pages_by_id[$row['page_id']]
                            =& self::$pages[$key];
                        // get active sections
                        $sec = self::getInstance()->db()->query(sprintf(
                              'SELECT COUNT(*) FROM `%ssections` '
                            . 'WHERE ( "%s" BETWEEN `publ_start` AND `publ_end`) OR '
                            . '("%s" > `publ_start` AND `publ_end`=0) '
                            . 'AND `page_id`=' . (int)$row['page_id'],
                            CAT_TABLE_PREFIX, $now, $now
                        ));
                        self::$pages_sections[$row['page_id']]
                            = ( $sec ? $sec->numRows() : 0 );
                    }
                }
            }
        }   // end function init()

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
            $file = sanitize_path(CAT_PATH.'/templates/'.$tpl.'/footers.inc.php');
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
                $path = sanitize_path(CAT_PATH.'/modules/'.$tool.'/tool.php');
                self::getInstance()->log()->logDebug(sprintf('handle admin tool [%s] - path [%s]', $tool, $path));
                if (file_exists($path))
                {
                    $file = sanitize_path(CAT_PATH . '/modules/' . $tool . '/footers.inc.php');
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
                    $file = sanitize_path($directory . '/backend_body.js');
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
            // -----------------------------------------------------------------
            // -----                    backend theme                      -----
            // -----------------------------------------------------------------
            $file = sanitize_path(CAT_PATH . '/templates/' . DEFAULT_THEME . '/headers.inc.php');
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
                $path = sanitize_path(CAT_PATH . '/modules/' . $_REQUEST['tool'] . '/tool.php');
                self::$instance->log()->logDebug(sprintf('handle admin tool [%s] - path [%s]', $_REQUEST['tool'], $path));

                if (file_exists($path))
                {
                    array_push(CAT_Pages::$css_search_path, '/modules/' . $_REQUEST['tool'], '/modules/' . $_REQUEST['tool'] . '/css');
                    array_push(CAT_Pages::$js_search_path, '/modules/' . $_REQUEST['tool'], '/modules/' . $_REQUEST['tool'] . '/js');

                    $file = sanitize_path(CAT_PATH . '/modules/' . $_REQUEST['tool'] . '/headers.inc.php');
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
            return self::getCSS() . self::getJQuery('header') . self::getJavaScripts('header');

        } // end function getBackendHeaders()

        /**
         * returns the items of static array $css as HTML link markups
         *
         * @access public
         * @return HTML
         **/
        public static function getCSS()
        {
            $output = NULL;
            if (count(CAT_Helper_Page::$css))
            {
                $val = CAT_Helper_Validate::getInstance();
                foreach (CAT_Helper_Page::$css as $item)
                {
                    // make sure we have an URI (CAT_URL included)
                    $file = (preg_match('#' . CAT_URL . '#i', $item['file']) ? $item['file'] : CAT_URL . '/' . $item['file']);
                    $output .= '<link rel="stylesheet" type="text/css" href="' . $val->sanitize_url($file) . '" media="' . (isset($item['media']) ? $item['media'] : 'all') . '" />' . "\n";
                }
            }
            return $output;
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
                    && self::$pages_sections[$page['page_id']] > 0
                ) {
                    if(!PAGE_LANGUAGES || $page['language'] == LANGUAGE)
                    {
                        return $page['page_id'];
                    }
                }
            }
        } // end function getDefaultPage()

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
            {
                $for = 'frontend';
            }

            if ($for == 'backend')
            {
                return self::getBackendFooters();
            }
            else
            {
                return self::getFrontendFooters();
            }
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
            $file = sanitize_path(CAT_PATH.'/templates/'.$tpl.'/footers.inc.php');
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
                    $file = sanitize_path($directory . '/frontend_body.js');
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
            // -----------------------------------------------------------------
            // -----                  frontend theme                       -----
            // -----------------------------------------------------------------
            $tpl  = CAT_Registry::get('TEMPLATE');
            $file = sanitize_path(CAT_PATH.'/templates/'.$tpl.'/headers.inc.php');
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
                '/templates/'.$tpl.'/templates/default/css'
            );

            global $page_id;
            #if($page_id && $this->link )
            #{
                #$dir = preg_replace( '~^'.CAT_Helper_Validate::getInstance()->sanitize_url(CAT_URL.'/'.PAGES_DIRECTORY).'~i', '', pathinfo($this->link,PATHINFO_DIRNAME) );
                #array_push( CAT_Helper_Page::$css_search_path, sanitize_path(PAGES_DIRECTORY.$dir) );
            #}

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

            // return the results
            return self::getMeta() . self::getCSS() . self::getJQuery('header') . self::getJavaScripts('header');

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
            {
                $static =& CAT_Helper_Page::$js;
            }
            else
            {
                $static =& CAT_Helper_Page::$f_js;
            }
            if (is_array($static) && count($static))
            {
                return implode("\n", $static) . "\n";
            }
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
            {
                $static =& CAT_Helper_Page::$jquery;
            }
            else
            {
                $static =& CAT_Helper_Page::$f_jquery;
            }
            if (count($static))
            {
                return implode($static);
            }
            return NULL;
        } // end function getJQuery()

        /**
         *
         *
         *
         *
         **/
        public static function getLink($page_id)
        {
            if(!is_numeric($page_id))
            {
                $link = $page_id;
            }
            else
            {
                $link = self::properties($page_id,'link');
            }
            // Check for :// in the link (used in URL's) as well as mailto:
            if (strstr($link, '://') == '' && substr($link, 0, 7) != 'mailto:')
            {
                return CAT_URL . PAGES_DIRECTORY . $link . PAGE_EXTENSION;
            }
            else
            {
                return $link;
            }
        }   // end function getLink()

        /**
         *
         *
         *
         *
         **/
        public static function getLinkedByLanguage($page_id)
        {
            $sql     = 'SELECT * FROM `%spage_langs` AS t1'
                     . ' RIGHT OUTER JOIN `%spages` AS t2'
                     . ' ON t1.link_page_id=t2.page_id'
                     . ' WHERE t1.page_id = %d'
                     ;

            $results = self::getInstance()->db()->query(sprintf($sql,CAT_TABLE_PREFIX,CAT_TABLE_PREFIX,$page_id));
            if ($results->numRows())
            {
                $items = array();
                while (($row = $results->fetchRow(MYSQL_ASSOC)) !== false)
                {
                    $row['href'] = $this->getLink($row['link']) . (($row['lang'] != '') ? '?lang=' . $row['lang'] : NULL);
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
        public static function getMeta()
        {
            global $page_id;

            $properties = self::properties($page_id);
            $output     = array();

            // charset
            if (isset($properties['default_charset']))
            {
                $output[] = CAT_Helper_Page::$space . '<meta http-equiv="Content-Type" content="text/html; charset=' . $properties['default_charset'] . '" />';
            }

            // page title
            if (isset($properties['title']))
            {
                $output[] = CAT_Helper_Page::$space . '<title>' . $properties['title'] . '</title>';
            }

            // description
            if (isset($properties['description']))
            {
                $output[] = CAT_Helper_Page::$space . '<meta name="description" content="' . $properties['description'] . '" />';
            }

            // keywords
            if (isset($properties['keywords']))
            {
                $output[] = CAT_Helper_Page::$space . '<meta name="keywords" content="' . $properties['keywords'] . '" />';
            }

            return implode("\n", $output) . "\n";

        } // end function getMeta()

        /**
         * returns complete pages array
         *
         * @access public
         * @return array
         **/
        public static function getPages()
        {
            if(!count(self::$pages)) self::getInstance();
            return self::$pages;
        }   // end function getPages()
        
        /**
         * returns pages by visibility array
         *
         * @access public
         * @param  string  $visibility - optional
         * @return array
         **/
        public static function getPagesByVisibility($visibility=NULL)
        {
            if(!count(self::$pages_by_visibility)) self::getInstance();
            if($visibility)
                return self::$pages_by_visibility[$visibility];
            return self::$pages_by_visibility;
        }   // end function getPagesByVisibility()

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
                        $result = CAT_Registry::getInstance()->db()->query(sprintf('SELECT `value` FROM %ssettings WHERE `name`="maintenance_page"',CAT_TABLE_PREFIX));
                        if(is_resource($result) && $result->numRows()==1)
                        {
                            $row = $result->fetchRow(MYSQL_ASSOC);
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
            if ( ! $page_id )
                $page_id = self::getDefaultPage();

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
            if(!count(self::$pages)) self::init();
            if($key && isset(self::$pages_by_id[$page_id]) && isset(self::$pages_by_id[$page_id][$key]))
            {
                return self::$pages_by_id[$page_id][$key];
            }
            return (
                isset(self::$pages_by_id[$page_id])
                ? self::$pages_by_id[$page_id]
                : array()
            );
        }   // end function properties()

        /**
         * replaces internal links; should be exported into an output filter
         **/
        public static function preprocess(&$content)
        {
    		$regexp = array( '/\[cmsplink([0-9]+)\]/isU' );
            // for backward compatibility with WB
            if(defined('WB_PREPROCESS_PREG')) $regexp[] = WB_PREPROCESS_PREG;
            foreach($regexp as $preg) {
        		if(preg_match_all( $preg, $content, $ids ) ) {
        			$new_ids = array_unique($ids[1]);
        			foreach($new_ids as $key => &$page_id) {
        				$link = self::$pages_by_id[$page_id]['link'];
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
         * checks if page is active (=has active sections and is between
         * publ_start and publ_end)
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isActive($page_id)
        {
            if(self::$pages_sections[$page_id]>0)
                return true;
            return false;
        } // end function isActive()

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
                $result = $this->db()->query(sprintf('SELECT `value` FROM %ssettings WHERE `name`="maintenance_mode"',CAT_TABLE_PREFIX));
                if(is_resource($result)&&$result->numRows()==1)
                {
                    $row = $result->fetchRow(MYSQL_ASSOC);
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
                // shown if called
                case 'hidden':
                case 'public':
                    $show_it = true;
                    break;
                // shown if user is allowed
                case 'private':
                case 'registered':
                    $u = CAT_Users::getInstance();
                    if ($u->is_authenticated() == true)
                    {
                        $show_it = ($u->is_group_match($u->get_groups_id(), $page['viewing_groups']) || $u->is_group_match($u->get_user_id(), $page['viewing_users']));
                    }
                    #$show_it = true;
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
                                $css['file'] = sanitize_path($subdir.'/'.$css['file']);
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
         * @param  array    $arr
         * @param  string   $path_prefix
         * @return void
         **/
        private static function _analyze_javascripts(&$arr, $for = 'header', $path_prefix = NULL, $section = false)
        {
            if ($for == 'header')
            {
                $static =& CAT_Helper_Page::$js;
            }
            else
            {
                $static =& CAT_Helper_Page::$f_js;
            }

            if (is_array($arr))
            {
                $check_paths = array();
                if ($path_prefix != '')
                {
                    $check_paths = explode('/', $path_prefix);
                    $check_paths = array_reverse($check_paths);
                }

                if (isset($arr['all']))
                {
                    $val = CAT_Helper_Validate::getInstance();
                    foreach ($arr['all'] as $item)
                    {
                        if (!preg_match('#/modules/#i', $item))
                        {
                            foreach ($check_paths as $subdir)
                            {
                                if (!preg_match('#' . $subdir . '/#', $item))
                                {
                                    $item = sanitize_path($subdir . '/' . $item);
                                }
                            }
                        }
                        $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $item) . '"></script>';
                    }
                    unset($arr['all']);
                }

                if (isset($arr['individual']))
                {
                    if (is_array($arr['individual']))
                    {
                        $val = CAT_Helper_Validate::getInstance();
                        foreach ($arr['individual'] as $section_name => $item)
                        {
                            if ($section_name == strtolower($section))
                            {
                                foreach ($check_paths as $subdir)
                                {
                                    if (!preg_match('#' . $subdir . '/#', $item))
                                    {
                                        $item = sanitize_path($subdir . '/' . $item);
                                    }
                                }
                                $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $item) . '"></script>';
                            }
                        }
                    }
                    unset($arr['individual']);
                }

                #remaining
                if(is_array($arr) && count($arr))
                {
                    $val = CAT_Helper_Validate::getInstance();
                    foreach ($arr as $item)
                    {
                        if ( preg_match('/^http(s)?:/', $item))
                        {
                            $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url($item) . '"></script>';
                            continue;
                        }
                        if (!preg_match('#/modules/#i', $item))
                        {
                            foreach ($check_paths as $subdir)
                            {
                                if (!preg_match('#' . $subdir . '/#', $item))
                                {
                                    $item = sanitize_path($subdir . '/' . $item);
                                }
                            }
                        }
                        $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $item) . '"></script>';
                    }
                }

            }
            else
            {
                $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . CAT_Helper_Validate::getInstance()->sanitize_url(CAT_URL . '/' . $arr) . '"></script>';
            }
            self::$instance->log()->logDebug('JavaScripts',$static);
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
            $static =& CAT_Helper_Page::$jquery;
            $val    =  CAT_Helper_Validate::getInstance();

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
                CAT_Helper_Page::$jquery_ui_core = true;
            }

            // components to load on all pages (backend only)
            if (isset($arr['all']) && is_array($arr['all']))
            {
                foreach ($arr['all'] as $item)
                {
                    $resolved = self::_find_item($item);
                    $static[] = CAT_Helper_Page::$space . '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . '/modules/lib_jquery/plugins/' . $resolved ) . '"></script>' . "\n";
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
            if (!file_exists(sanitize_path(CAT_PATH.'/modules/lib_jquery/plugins/'.$item)))
            {
                $dir = pathinfo($item,PATHINFO_FILENAME);
                if (file_exists(sanitize_path(CAT_PATH.'/modules/lib_jquery/plugins/'.$dir.'/'.$item)))
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
                    $file = sanitize_path($directory . '/template.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'screen,projection',
                            'file' => $file
                        );
                    }
                    // print.css
                    $file = sanitize_path($directory . '/print.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'print',
                            'file' => $file
                        );
                    }
                    // frontend.css / backend.css
                    $file = sanitize_path($directory . '/' . $for . '.css');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$css[] = array(
                            'media' => 'all',
                            'file' => $file
                        );
                    }
                    // frontend.css / backend_print.css
                    $file = sanitize_path($directory . '/' . $for . '_print.css');
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
                        $file = sanitize_path($directory . '/' . PAGE_ID . '.css');
                        if (file_exists(sanitize_path(CAT_PATH . '/' . $file)))
                        {
                            CAT_Helper_Page::$css[] = array(
                                'media' => 'screen,projection',
                                'file' => $file
                            );
                        }
                        $file = sanitize_path($directory . '/' . PAGE_ID . '_print.css');
                        if (file_exists(sanitize_path(CAT_PATH . '/' . $file)))
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
        private static function _load_footers_inc($file, $for, $path_prefix, $section)
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
            if (count(CAT_Helper_Page::$js_search_path))
            {
                $val = CAT_Helper_Validate::getInstance();
                foreach (CAT_Helper_Page::$js_search_path as $directory)
                {
                    $file = sanitize_path($directory . '/' . $for . '.js');
                    if (file_exists(CAT_PATH . '/' . $file))
                    {
                        CAT_Helper_Page::$js[] = '<script type="text/javascript" src="' . $val->sanitize_url(CAT_URL . $file) . '"></script>' . "\n";
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
            if ($page_id && is_numeric($page_id))
            {
                $sections = self::$pages_sections[$page_id];
                if (is_array($sections) && count($sections))
                {
                    global $current_section;
                    foreach ($sections as $section)
                    {
                        $module = $section['module'];
                        $file   = sanitize_path(CAT_PATH.'/modules/'.$module.'/headers.inc.php');
                        // find header definition file
                        if (file_exists($file))
                        {
                            $current_section = $section['section_id'];
                            self::_load_headers_inc($file, $for, 'modules/' . $module, $section);
                        }
                        array_push(CAT_Helper_Page::$css_search_path, '/modules/' . $module, '/modules/' . $module . '/css');
                        array_push(CAT_Helper_Page::$js_search_path, '/modules/' . $module, '/modules/' . $module . '/js');
                    } // foreach ($sections as $section)
                } // if (count($sections))
            }
        }   // end function _load_sections()


    }
}
