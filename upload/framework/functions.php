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
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
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

/**
 *  Define that this file has been loaded
 *
 *  To avoid double function-declarations (inside LEPTON) and to avoid a massiv use
 *  of "if(!function_exists('any_function_name_here_since_wb_2.5.0')) {" we've to place it
 *  inside this condition-body!
 *
 */
if (!defined('FUNCTIONS_FILE_LOADED'))
{
    define('FUNCTIONS_FILE_LOADED', true);
    
    // set debug level here; see CAT_Helper_KLogger for available levels
    // 7 = debug, 8 = off
	$debug_level  = 8;

    // include helpers
	global $lhd, $arrayh, $logger;
    include dirname(__FILE__).'/CAT/Helper/Directory.php';
	$lhd   = new CAT_Helper_Directory();
	$logger = new CAT_Helper_KLogger( CAT_PATH.'/temp', $debug_level );

    
    
    /**
     * Scan a given directory for dirs and files.
     *
     * usage: scan_current_dir ($root = '' )
     *
     * Used by admins/reload.php, for example
     *
     * @access    public
     * @param     $root    (optional) path to be scanned; defaults to current working directory (getcwd())
     * @return    array    returns a natsort-ed array with keys 'path' and 'filename'
     *
     */
    function scan_current_dir($root = '')
    {
        $FILE = array();
        clearstatcache();
        $root = empty($root) ? getcwd() : $root;
        if (false !== ($handle = opendir($root)))
        {
            // Loop through the files and dirs an add to list  DIRECTORY_SEPARATOR
            while (false !== ($file = readdir($handle)))
            {
                if (substr($file, 0, 1) != '.' && $file != 'index.php')
                {
                    if (is_dir($root . '/' . $file))
                    {
                        $FILE['path'][] = $file;
                    }
                    else
                    {
                        $FILE['filename'][] = $file;
                    }
                }
            }
            $close_verz = closedir($handle);
        }
        if (isset($FILE['path']) && natcasesort($FILE['path']))
        {
            $tmp = array();
            $FILE['path'] = array_merge($tmp, $FILE['path']);
        }
        if (isset($FILE['filename']) && natcasesort($FILE['filename']))
        {
            $tmp = array();
            $FILE['filename'] = array_merge($tmp, $FILE['filename']);
        }
        return $FILE;
    }   // end function scan_current_dir()
    
    /**
     *  Function to list all files in a given directory.
     *
     *  @param  string  $directory   - directory to list
     *  @param  array   $skip        - An array with directories to skip, e.g. '.svn' or '.git'
     *  @param  bool    $show_hidden - Show also hidden files, e.g. ".htaccess".
     *
     *  @retrun  array  Natsorted array within the files.
     *
     */
    function file_list($directory, $skip = array(), $show_hidden = false)
    {
        $result_list = array();
        if (is_dir($directory))
        {
            $use_skip = (count($skip) > 0);
            // Open the directory
            $dir = dir($directory);
            while (false !== ($entry = $dir->read()))
            {
                // loop through the directory
                // Skip hidden files
                if (($entry[0] == '.') && (false == $show_hidden))
                {
                    continue;
                }
                // Check if we to skip anything else
                if ((true === $use_skip) && (in_array($entry, $skip)))
                {
                    continue;
                }
                if (is_file($directory . '/' . $entry))
                {
                    // Add files to list
                    $result_list[] = $directory . '/' . $entry;
                }
            }
            // closing the folder-object
            $dir->close();
        }
        natcasesort($result_list);
        return $result_list;
    }   // end function file_list()

    // Function to get a list of home folders not to show
    /**
     *  M.f.i.!  Dietrich Roland Pehlke
     *      I would like to keep the original comment unless i understand this one!
     *      E.g. 'ami' is for me nothing more and nothing less than an 'admim'!
     *
     *      I'm also not acceppt the declaration of a function inside a function at all!
     *      E.g. what happend if the function "get_home_folders" twice? Bang!
     *
     * 2011-08-22
     *      Bianka Martinovic
     *      The only file where this is used seems to be admins/media/index.php,
     *      so in my opinion, it should be moved there
     *
     */
    function get_home_folders()
    {
        global $database, $admin;
        $home_folders = array();
        // Only return home folders is this feature is enabled
        // and user is not admin
        //if(HOME_FOLDERS AND ($_SESSION['GROUP_ID']!='1'))
        if (HOME_FOLDERS && (!CAT_Users::getInstance()->ami_group_member('1')))
        {
            $sql = 'SELECT `home_folder` FROM `' . CAT_TABLE_PREFIX . 'users` WHERE `home_folder` != \'' . $admin->get_home_folder() . '\'';
            $query_home_folders = $database->query($sql);
            if ($query_home_folders->numRows() > 0)
            {
                while (false !== ($folder = $query_home_folders->fetchRow()))
                {
                    $home_folders[$folder['home_folder']] = $folder['home_folder'];
                }
            }
            function remove_home_subs($directory = '/', $home_folders = '')
            {
                if (false !== ($handle = opendir(CAT_PATH . MEDIA_DIRECTORY . $directory)))
                {
                    // Loop through the dirs to check the home folders sub-dirs are not shown
                    while (false !== ($file = readdir($handle)))
                    {
                        if ($file[0] != '.' && $file != 'index.php')
                        {
                            if (is_dir(CAT_PATH . MEDIA_DIRECTORY . $directory . '/' . $file))
                            {
                                if ($directory != '/')
                                {
                                    $file = $directory . '/' . $file;
                                }
                                else
                                {
                                    $file = '/' . $file;
                                }
                                foreach ($home_folders as $hf)
                                {
                                    $hf_length = strlen($hf);
                                    if ($hf_length > 0)
                                    {
                                        if (substr($file, 0, $hf_length + 1) == $hf)
                                        {
                                            $home_folders[$file] = $file;
                                        }
                                    }
                                }
                                $home_folders = remove_home_subs($file, $home_folders);
                            }
                        }
                    }
                }
                return $home_folders;
            }
            $home_folders = remove_home_subs('/', $home_folders);
        }
        return $home_folders;
    }   // end function get_home_folders()

    /*
     * @param object &$wb: $wb from frontend or $admin from backend
     * @return array: list of new entries
     * @description: callback remove path in files/dirs stored in array
     * @example: array_walk($array,'remove_path',PATH);
     */
    /**
     *  M.f.o.!  MARKED FOR OBSOLETE
     *      As this one belongs to the results of the function 'directory_list'
     *
     */
    function remove_path(&$path, $key, $vars = '')
    {
        $path = str_replace($vars, '', $path);
    }

    /*
     * @param object &$wb: $wb from frontend or $admin from backend
     * @return array: list of ro-dirs
     * @description: returns a list of directories beyound /wb/media which are ReadOnly for current user
     *
     *  M.f.i.!  Copy and paste crap
     *
     */
    function media_dirs_ro(&$wb)
    {
        /**
    		 * @deprecated media_dirs_ro() is deprecated and will be removed in LEPTON 1.2
    		 */
    		trigger_error('The function media_dirs_ro() is deprecated and will be removed in LEPTON 1.3.', E_USER_NOTICE);
        global $database;
        // if user is admin or home-folders not activated then there are no restrictions
        $allow_list = array();
        if ($wb->get_user_id() == 1 || !HOME_FOLDERS)
        {
            return array();
        }
        // at first read any dir and subdir from /media
        $full_list = directory_list(CAT_PATH . MEDIA_DIRECTORY);
        // add own home_folder to allow-list
        if ($wb->get_home_folder())
        {
            // old: $allow_list[] = get_home_folder();
            $allow_list[] = $wb->get_home_folder();
        }
        // get groups of current user
        $curr_groups = $wb->get_groups_id();
        // if current user is in admin-group
        if (($admin_key = array_search('1', $curr_groups)) !== false)
        {
            // remove admin-group from list
            unset($curr_groups[$admin_key]);
            // search for all users where the current user is admin from
            foreach ($curr_groups as $group)
            {
                $sql = 'SELECT `home_folder` FROM `' . CAT_TABLE_PREFIX . 'users` ';
                $sql .= 'WHERE (FIND_IN_SET(\'' . $group . '\', `groups_id`) > 0) AND `home_folder` <> \'\' AND `user_id` <> ' . $wb->get_user_id();
                if (($res_hf = $database->query($sql)) != null)
                {
                    while (false !== ($rec_hf = $res_hf->fetchrow(MYSQL_ASSOC)))
                    {
                        $allow_list[] = $rec_hf['home_folder'];
                    }
                }
            }
        }
        $tmp_array = $full_list;
        // create a list for readonly dir
        $array = array();
        while (sizeof($tmp_array) > 0)
        {
            $tmp = array_shift($tmp_array);
            $x = 0;
            while ($x < sizeof($allow_list))
            {
                if (strpos($tmp, $allow_list[$x]))
                {
                    $array[] = $tmp;
                }
                $x++;
            }
        }
        $full_list = array_diff($full_list, $array);
        $tmp = array();
        $full_list = array_merge($tmp, $full_list);
        return $full_list;
    }   // end function media_dirs_ro()

    /*
     * @param object &$wb: $wb from frontend or $admin from backend
     * @return array: list of rw-dirs
     * @description: returns a list of directories beyound /wb/media which are ReadWrite for current user
     *
     *  M.f.i.!  Copy and paste crap!
     *
     *  2011-08-22 Bianka Martinovic
     *      used only in admins/media/index.php, should be moved there
     */
    function media_dirs_rw(&$wb)
    {
        global $database;
        // if user is admin or home-folders not activated then there are no restrictions
        // at first read any dir and subdir from /media
        $full_list = directory_list(CAT_PATH . MEDIA_DIRECTORY);
        $allow_list = array();
        if (($wb->get_user_id() == 1) || !HOME_FOLDERS)
        {
            return $full_list;
        }
        // add own home_folder to allow-list
        if ($wb->get_home_folder())
        {
            $allow_list[] = $wb->get_home_folder();
        }
        // get groups of current user
        $curr_groups = $wb->get_groups_id();
        // if current user is in admin-group
        if (($admin_key = array_search('1', $curr_groups)) !== false)
        {
            // remove admin-group from list
            unset($curr_groups[$admin_key]);
            // search for all users where the current user is admin from
            foreach ($curr_groups as $group)
            {
                $sql = 'SELECT `home_folder` FROM `' . CAT_TABLE_PREFIX . 'users` ';
                $sql .= 'WHERE (FIND_IN_SET(\'' . $group . '\', `groups_id`) > 0) AND `home_folder` <> \'\' AND `user_id` <> ' . $wb->get_user_id();
                if (($res_hf = $database->query($sql)) != null)
                {
                    while (false !== ($rec_hf = $res_hf->fetchrow()))
                    {
                        $allow_list[] = $rec_hf['home_folder'];
                    }
                }
            }
        }
        $tmp_array = $full_list;
        // create a list for readwrite dir
        $array = array();
        while (sizeof($tmp_array) > 0)
        {
            $tmp = array_shift($tmp_array);
            $x = 0;
            while ($x < sizeof($allow_list))
            {
                if (strpos($tmp, $allow_list[$x]))
                {
                    $array[] = $tmp;
                }
                $x++;
            }
        }
        $tmp = array();
        $full_list = array_merge($tmp, $array);
        return $full_list;
    }   // end function media_dirs_rw()

    /**
     *  Create directories recursive
     *
     * @param string   $dir_name - directory to create
     * @param ocatal   $dir_mode - access mode
     * @return boolean result of operation
     *
     *  THIS METHOD WAS MOVED TO CAT_Helper_Addons!
     *
     */
    function make_dir( $dir_name, $dir_mode = OCTAL_DIR_MODE )
    {
        if ( ! class_exists( 'CAT_Helper_Directory' ) )
                {
	        @require_once dirname(__FILE__).'/CAT/Helper/Directory.php';
        }
		$addons_helper = new CAT_Helper_Directory();
		return $addons_helper->createDirectory( $dir_name, $dir_mode );
    }   // end function make_dir()

    /**
     * check if the page with the given id has children
     *
     * @access public
     * @param  integer $page_id - page ID
     * @return mixed   (false if page hasn't children, parent id if not)
     *
     * 2011-08-22 Bianka Martinovic
     *    Should be moved to new page object when ready
     *    I don't understand why this returns the parent page, as methods
     *    beginning with is* should always return boolean only IMHO
     **/
    function is_parent($page_id)
    {
        global $database;
        // Get parent
        $sql = 'SELECT `parent` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id;
        $parent = $database->get_one($sql);
        // If parent isnt 0 return its ID
        if (is_null($parent))
        {
            return false;
        }
        else
        {
            return $parent;
        }
    }   // end function is_parent()

    /**
     * counts the levels from given page_id to root
     *
     * @access public
     * @param  integer  $page_id
     * @return integer  level (>=0)
     *
     **/
    function level_count($page_id)
    {
        global $database;
        // Get page parent
        $sql = 'SELECT `parent` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id;
        $parent = $database->get_one($sql);
        if ($parent > 0)
        {
            // Get the level of the parent
            $sql = 'SELECT `level` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $parent;
            $level = $database->get_one($sql);
            return $level + 1;
        }
        else
        {
            return 0;
        }
    }   // function level_count()

    /**
     * Function to work out root parent
     *
     * @access public
     * @param  integer $page_id
     * @return integer ID of the root page
     *
     **/
    function root_parent($page_id)
    {
        global $database;
        // Get page details
        $sql = 'SELECT `parent`, `level` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id;
        $query_page = $database->query($sql);
        $fetch_page = $query_page->fetchRow();
        $parent = $fetch_page['parent'];
        $level = $fetch_page['level'];
        if ($level == 1)
        {
            return $parent;
        }
        elseif ($parent == 0)
        {
            return $page_id;
        }
        else
        {
            // Figure out what the root parents id is
            $parent_ids = array_reverse(get_parent_ids($page_id));
            return $parent_ids[0];
        }
    }   // end root_parent()

    /**
     * get additions for page header (css, js, meta)
     *
     * This is a wrapper for CAT_Pages->getHeaders()
     *
     * @access public
     * @param  string  $for - 'frontend' (default) / 'backend'
     * @param  boolean $print_output - echo result (default) or return
     * @param  boolean $individual   - JS for individual section (BE only)
     * @return mixed
     *
     **/
	function get_page_headers( $for = 'frontend', $print_output = true, $individual = false )
	{
        if ( defined('CAT_HEADERS_SENT') ) return false;
	    if ( ! class_exists( 'CAT_Pages', false ) )
							{
	        include sanitize_path( dirname(__FILE__).'/CAT/Pages.php' );
							}
	    $pg     = CAT_Pages::getInstance();
	    $output = $pg->getHeaders( $for, $individual );
		if ( $print_output )
		{
			echo $output;
			define('CAT_HEADERS_SENT', true);
		}
		else
		{
			return $output;
		}

    }   // end function get_page_headers()
    
    /**
     * get additions for page footer (js, script)
     *
     * + gets all active sections for a page;
     * + scans module directories for file footers.inc.php;
     * + includes that file if it is available
     * + includes automatically if exists:
     *   + module dirs:
     *     + frontend.css / backend.css              (media: all)
     *     + frontend_print.css / backend_print.css  (media: print)
     *   + template dir:
     *     + <PAGE_ID>.css in template dir           (media: all)
     *
     * @access public
     * @param  string  $for - 'frontend' (default) / 'backend'
     * @return void (echo's result)
     *
     **/
    function get_page_footers( $for = 'frontend', $print_output = true )
            {
        if ( ! class_exists( 'CAT_Pages', false ) )
                {
	        include sanitize_path( dirname(__FILE__).'/CAT/Pages.php' );
                }
	    $pg     = CAT_Pages::getInstance();
	    $output = $pg->getFooters( $for );
        if ( $print_output )
                    {
			echo $output;
			define('LEP_FOOTERS_SENT', true);
                    }
		else
                        {
			return $output;
                }
    }   // end function get_page_footers()

    // Function to get page title
    function get_page_title($id)
    {
        global $database;
        // Get title
        $sql = 'SELECT `page_title` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $id;
        $page_title = $database->get_one($sql);
        return $page_title;
    }
    // Function to get a pages menu title
    function get_menu_title($id)
    {
        global $database;
        // Get title
        $sql = 'SELECT `menu_title` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $id;
        $menu_title = $database->get_one($sql);
        return $menu_title;
    }
    // Function to get all parent page titles
    function get_parent_titles($parent_id)
    {
        $titles[] = get_menu_title($parent_id);
        if (is_parent($parent_id) != false)
        {
            $parent_titles = get_parent_titles(is_parent($parent_id));
            $titles = array_merge($titles, $parent_titles);
        }
        return $titles;
    }
    // Function to get all parent page id's
    function get_parent_ids($parent_id)
    {
        $ids[] = $parent_id;
        if (is_parent($parent_id) != false)
        {
            $parent_ids = get_parent_ids(is_parent($parent_id));
            $ids = array_merge($ids, $parent_ids);
        }
        return $ids;
    }
    // Function to generate page trail
    function get_page_trail($page_id)
    {
        return implode(',', array_reverse(get_parent_ids($page_id)));
    }
    
    // Function to get all sub pages id's
    function get_subs($parent, $subs)
    {
        // Connect to the database
        global $database;
        // Get id's
        $sql = 'SELECT `page_id` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `parent` = ' . $parent;
        $query = $database->query($sql);
        if ($query->numRows() > 0)
        {
            while (false !== ($fetch = $query->fetchRow()))
            {
                $subs[] = $fetch['page_id'];
                // Get subs of this sub
                $subs = get_subs($fetch['page_id'], $subs);
            }
        }
        // Return subs array
        return $subs;
    }
    // Convert a string from mixed html-entities/umlauts to pure $charset_out-umlauts
    // Will replace all numeric and named entities except &gt; &lt; &apos; &quot; &#039; &nbsp;
    // In case of error the returned string is unchanged, and a message is emitted.
    function entities_to_umlauts($string, $charset_out = DEFAULT_CHARSET)
    {
        require_once(CAT_PATH . '/framework/functions-utf8.php');
        return entities_to_umlauts2($string, $charset_out);
    }
    // Will convert a string in $charset_in encoding to a pure ASCII string with HTML-entities.
    // In case of error the returned string is unchanged, and a message is emitted.
    function umlauts_to_entities($string, $charset_in = DEFAULT_CHARSET)
    {
        require_once(CAT_PATH . '/framework/functions-utf8.php');
        return umlauts_to_entities2($string, $charset_in);
    }
    
    // @internal webbird - moved this function from admins/modules/uninstall.php and admins/templates/uninstall.php
    function replace_all($aStr = "", &$aArray)
    {
        foreach ($aArray as $k => $v)
        {
            $aStr = str_replace("{{" . $k . "}}", $v, $aStr);
        }
        return $aStr;
    }   // end function replace_all()

    // Function to convert a page title to a page filename
    function page_filename($string)
    {
        require_once(CAT_PATH . '/framework/functions-utf8.php');
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
    }
    // Function to convert a desired media filename to a clean filename
    function media_filename($string)
    {
        require_once(CAT_PATH . '/framework/functions-utf8.php');
        $string = entities_to_7bit($string);
        // Now remove all bad characters
        $bad = array('\'', '"', '`', '!', '@', '#', '$', '%', '^', '&', '*', '=', '+', '|', '/', '\\', ';', ':', ',', '?');
        $string = str_replace($bad, '', $string);
        // replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
        $string = preg_replace(array('/\.+/', '/\.+$/', '/\s/'), array('.', '', '_'), $string);
        // Clean any page spacers at the end of string
        $string = trim($string);
        // Finally, return the cleaned string
        return $string;
    }

    /**
     * wrapper to $admin->page_link()
     **/
    function page_link($link)
    {
        global $admin;
        return $admin->page_link($link);
    }   // end function page_link()

    /*
     * create_access_file
     * @param string $filename: full path and filename to the new access-file
     * @param int $page_id: ID of the page for which the file should created
     * @param int $level: never needed argument, for compatibility only
     * @param mixed $opt_cmds: a string or an array with one or more additional statements
     *                         to include in accessfile.
     *                         Example: $opt_cmds = "$section_id = '.$section_id"
     *                                  $opt_cmds = array(
     *                                       '$section_id = '.$section_id,
     *                                       '$mod_var_txt = \''.$mod_var_txt.'\'',
     *                                       '$mod_var_int = '.$mod_var_int
     *                                  );
     * @description: Create a new access file in the pages directory ans subdirectory also if needed
     * @warning: the params $level and $opt_cmds should NOT be used for new developments!! It will
     *           be removed in one of the next releases !!!!!!!!!!!!!
     */
    // M.f.i.   2011-02-17  drp: this one is worse ...
    //        a) test where call from
    //        b) test the circumstances
    //        c) optimize the code as it is .. even the two params at the end!
    function create_access_file($filename, $page_id, $level, $opt_cmds = null)
    {
        global $admin, $MESSAGE;
        $pages_path = CAT_PATH . PAGES_DIRECTORY;
        $rel_pages_dir = str_replace($pages_path, '', dirname($filename));
        $rel_filename = str_replace($pages_path, '', $filename);
        // root_check prevent system directories and importent files from being overwritten if PAGES_DIR = '/'
        $denied = false;
        if (PAGES_DIRECTORY == '')
        {
            $forbidden = array('account', 'admin', 'framework', 'include', 'install', 'languages', 'media', 'modules', 'pages', 'search', 'temp', 'templates', 'index.php', 'config.php', 'upgrade-script.php');
            $search = explode('/', $rel_filename);
            //! 6 -- why only the first level?
            $denied = in_array($search[1], $forbidden);
        }
        if ((true === is_writable($pages_path)) && (false == $denied))
        {
            // First make sure parent folder exists
            $parent_folders = explode('/', $rel_pages_dir);
            $parents = '';
            foreach ($parent_folders as $parent_folder)
            {
                if ($parent_folder != '/' && $parent_folder != '')
                {
                    $parents .= '/' . $parent_folder;
                    if (!file_exists($pages_path . $parents))
                    {
                        make_dir($pages_path . $parents);
                        change_mode($pages_path . $parents);
                    }
                }
            }
            $step_back = str_repeat('../', substr_count($rel_pages_dir, '/') + (PAGES_DIRECTORY == "" ? 0 : 1));
            $content = '<?php' . "\n";
            $content .= "/**\n *\tThis file is autogenerated by Black Cat CMS Version " . CAT_VERSION . "\n";
            $content .= " *\tDo not modify this file!\n */\n";
            $content .= "\t" . '$page_id = ' . $page_id . ';' . "\n";
            if ($opt_cmds)
            {
                // #! 3 -- not clear what this meeans at all! and in witch circumstances this 'param' will be make sence?
                if (!is_array($opt_cmds))
                {
                    $opt_cmds = explode('!', $opt_cmds);
                }
                foreach ($opt_cmds as $command)
                {
                    $new_cmd = rtrim(trim($command), ';');
                    $content .= (preg_match('/include|require/i', $new_cmd) ? '// *not allowed command >> * ' : "\t");
                    $content .= $new_cmd . ';' . "\n";
                }
            }
            //! 4 -- should be require once ...
            $content .= "\t" . 'require(\'' . $step_back . 'index.php\');' . "\n";
            $content .= '?>';
            /**
             *  write the file
             *
             */
            $fp = fopen($filename, 'w');
            if ($fp)
            {
                fwrite($fp, $content, strlen($content));
                fclose($fp);
                /**
                 *  Chmod the file
                 *
                 */
                change_mode($filename);
                /**
				 *	Looking for the index.php inside the current directory.
				 *	If not found - we're just copy the master_index.php from the admin/pages
				 *
				 */
				$temp_index_path = dirname($filename)."/index.php";
				if (!file_exists($temp_index_path))
				{
					$origin = CAT_ADMIN_PATH."/pages/master_index.php";
					if (file_exists($origin)) copy( $origin, $temp_index_path);
				}

            }
            else
            {
                /**
                 *  M.f.i  drp:  as long as we've got no key for this situation inside the languagefiles
                 *          we're in the need to make a little addition to the given one, to get it unique for trouble-shooting.
                 */
                $admin->print_error($MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE'] . "<br />Problems while trying to open the file!");
                return false;
            }
            return true;
        }
        else
        {
            $admin->print_error($MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE']);
            return false;
        }
    }
    
    /**
     *	Get the mime-type of a given file.
     *
     *	@param	string	A filename within the complete local path.
     *	@return	string	Returns the content type in MIME format, e.g. 'image/gif', 'text/plain', etc.
     *	@notice			If nothing match, the function will return 'application/octet-stream'.
     *
     *	2011-10-04	Aldus:	The function has been marked as 'deprecated' by PHP/Zend.
     *						For details please take a look at:
     *						http://php.net/manual/de/function.mime-content-type.php
     *
     */
    if (!function_exists("mime_content_type"))
    {
		function mime_content_type($filename)
		{
			$mime_types = array('txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv', // images
			'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml', // archives
			'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed', // audio/video
			'mp3' => 'audio/mpeg', 'mp4' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime', // adobe
			'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', // ms office
			'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint', // open office
			'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet', );
			$temp = explode('.', $filename);
			$ext = strtolower(array_pop($temp));
			if (array_key_exists($ext, $mime_types))
			{
				return $mime_types[$ext];
			}
			elseif (function_exists('finfo_open'))
			{
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $mimetype;
			}
			else
			{
				return 'application/octet-stream';
			}
		}   // end function mime_content_type()
	}
    // Generate a thumbnail from an image
    function make_thumb($source, $destination, $size)
    {
        return $this->get_helper('Image')->make_thumb( $source, $destination, $size );
    }   // end make_thumb()
    
    /*
     * Function to work-out a single part of an octal permission value
     *
     * @param mixed $octal_value: an octal value as string (i.e. '0777') or real octal integer (i.e. 0777 | 777)
     * @param string $who: char or string for whom the permission is asked( U[ser] / G[roup] / O[thers] )
     * @param string $action: char or string with the requested action( r[ead..] / w[rite..] / e|x[ecute..] )
     * @return boolean
     */
    function extract_permission($octal_value, $who, $action)
    {
        // Make sure that all arguments are set and $octal_value is a real octal-integer
        if (($who == '') || ($action == '') || (preg_match('/[^0-7]/', (string)$octal_value)))
        {
            // invalid argument, so return false
            return false;
        }
        // convert into a decimal-integer to be sure having a valid value
        $right_mask = octdec($octal_value);
        switch (strtolower($action[0]))
        {
            // get action from first char of $action
            // set the $action related bit in $action_mask
            case 'r':
                // set read-bit only (2^2)
                $action_mask = 4;
                break;
            case 'w':
                // set write-bit only (2^1)
                $action_mask = 2;
                break;
            case 'e':
            case 'x':
                // set execute-bit only (2^0)
                $action_mask = 1;
                break;
            default:
                // undefined action name, so return false
                return false;
        }
        switch (strtolower($who[0]))
        {
            // get who from first char of $who
            // shift action-mask into the right position
            case 'u':
                // shift left 3 bits
                $action_mask <<= 3;
            case 'g':
                // shift left 3 bits
                $action_mask <<= 3;
            case 'o':
                /* NOP */
                break;
            default:
                // undefined who, so return false
                return false;
        }
        // return result of binary-AND
        return(($right_mask & $action_mask) != 0);
    }
    
    /**
     * delete a page
     *
     * @access public
     * @param  integer $page_id
     * @return void
     *
     **/
    function delete_page($page_id)
    {
        global $admin, $database, $MESSAGE;
        // Find out more about the page
        // $database = new database();
        $sql = 'SELECT `page_id`, `menu_title`, `page_title`, `level`, `link`, `parent`, `modified_by`, `modified_when` ';
        $sql .= 'FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id;
        $results = $database->query($sql);
        if ($database->is_error())
        {
            $admin->print_error($database->get_error());
        }
        if ($results->numRows() == 0)
        {
            $admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
        }
        $results_array = $results->fetchRow();
        $parent = $results_array['parent'];
        $level = $results_array['level'];
        $link = $results_array['link'];
        $page_title = $results_array['page_title'];
        $menu_title = $results_array['menu_title'];
        // Get the sections that belong to the page
        $sql = 'SELECT `section_id`, `module` FROM `' . CAT_TABLE_PREFIX . 'sections` WHERE `page_id` = ' . $page_id;
        $query_sections = $database->query($sql);
        if ($query_sections->numRows() > 0)
        {
            while (false !== ($section = $query_sections->fetchRow()))
            {
                // Set section id
                $section_id = $section['section_id'];
                // Include the modules delete file if it exists
                if (file_exists(CAT_PATH . '/modules/' . $section['module'] . '/delete.php'))
                {
                    include(CAT_PATH . '/modules/' . $section['module'] . '/delete.php');
                }
            }
        }
        // Update the pages table
        $sql = 'DELETE FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id;
        $database->query($sql);
        if ($database->is_error())
        {
            $admin->print_error($database->get_error());
        }
        // Update the sections table
        $sql = 'DELETE FROM `' . CAT_TABLE_PREFIX . 'sections` WHERE `page_id` = ' . $page_id;
        $database->query($sql);
        if ($database->is_error())
        {
            $admin->print_error($database->get_error());
        }
        // Include the ordering class or clean-up ordering
        include_once(CAT_PATH . '/framework/class.order.php');
        $order = new order(CAT_TABLE_PREFIX . 'pages', 'position', 'page_id', 'parent');
        $order->clean($parent);
        // Unlink the page access file and directory
        $directory = CAT_PATH . PAGES_DIRECTORY . $link;
        $filename = $directory . PAGE_EXTENSION;
        $directory .= '/';
        if (file_exists($filename))
        {
            if (!is_writable(CAT_PATH . PAGES_DIRECTORY . '/'))
            {
                $admin->print_error($MESSAGE['PAGES_CANNOT_DELETE_ACCESS_FILE']);
            }
            else
            {
                unlink($filename);
                if (file_exists($directory) && (rtrim($directory, '/') != CAT_PATH . PAGES_DIRECTORY) && (substr($link, 0, 1) != '.'))
                {
                    rm_full_dir($directory);
                }
            }
        }
    }   // end function delete_page()
    
    /**
     *  Load module-info into the current DB
     *
     *  @param  string  Any valid directory(-path)
     *  @param  boolean Call the install-script of the module? Default: false
     *
     *  THIS METHOD WAS MOVED TO CAT_Helper_Addons!
     *
     */
    function load_module($directory, $install = false)
    {
        if ( ! class_exists( 'CAT_Helper_Addons' ) )
                {
	        @require_once dirname(__FILE__).'/CAT/Helper/Addons.php';
        }
		$addons_helper = new CAT_Helper_Addons();
		return $addons_helper->installModule($directory, $install);
    }   // end function load_module()

    /**
     *  Load template-info into the DB.
     *
     *  @param  string  Any valid directory(-path)
     *
     *  THIS METHOD WAS MOVED TO CAT_Helper_Addons!
     *
     */
    function load_template( $directory )
    {
        if ( ! class_exists( 'CAT_Helper_Addons' ) )
                {
	        @require_once dirname(__FILE__).'/CAT/Helper/Addons.php';
        }
		$addons_helper = new CAT_Helper_Addons();
		return $addons_helper->installTemplate( $directory );
    }   // end function load_template()

    /**
     *  Load language-info into the DB.
     *
     *  @param  string  Any valid file(-path)
     *
     *  THIS METHOD WAS MOVED TO CAT_Helper_Addons!
     *
     */
    function load_language( $file )
    {
        if ( ! class_exists( 'CAT_Helper_Addons' ) )
                {
	        @require_once dirname(__FILE__).'/CAT/Helper/Addons.php';
        }
		$addons_helper = new CAT_Helper_Addons();
		return $addons_helper->installLanguage( $file );
    }   // end function load_language()

    /**
     *  Update the module informations in the DB
     *
     *  @param  string  Name of the modul-directory
     *  @param  bool  Optional boolean to run the upgrade-script of the module.
     *
     *  THIS METHOD WAS MOVED TO CAT_Helper_Addons!
     *
     */
    function upgrade_module( $directory, $upgrade = false )
    {
        if ( ! class_exists( 'CAT_Helper_Addons' ) )
                {
	        @require_once dirname(__FILE__).'/CAT/Helper/Addons.php';
        }
		$addons_helper = new CAT_Helper_Addons();
		return $addons_helper->upgradeModule( $directory, $upgrade );
    }   // end function upgrade_module()


    function get_variable_content($search, $data, $striptags = true, $convert_to_entities = true)
    {
        $match = '';
        // search for $variable followed by 0-n whitespace then by = then by 0-n whitespace
        // then either " or ' then 0-n characters then either " or ' followed by 0-n whitespace and ;
        // the variable name is returned in $match[1], the content in $match[3]
        if (preg_match('/(\$' . $search . ')\s*=\s*("|\')(.*)\2\s*;/', $data, $match))
        {
            if (strip_tags(trim($match[1])) == '$' . $search)
            {
                // variable name matches, return it's value
                $match[3] = ($striptags == true) ? strip_tags($match[3]) : $match[3];
                $match[3] = ($convert_to_entities == true) ? htmlentities($match[3]) : $match[3];
                return $match[3];
            }
        }
        return false;
    }   // end function get_variable_content()

    /**
     *  Try to get the current version of a given Modul.
     *
     *  @param  string  $modulname: like saved in addons directory
     *  @param  boolean  $source: true reads from database, false from info.php
     *  @return  string  the version as string, if not found returns null
     *
     */
    function get_modul_version($modulname, $source = true)
    {
        global $database;
        $version = null;
        if ($source != true)
        {
            $sql = "SELECT `version` FROM `" . CAT_TABLE_PREFIX . "addons` WHERE `directory`='" . $modulname . "'";
            $version = $database->get_one($sql);
        }
        else
        {
            $info_file = CAT_PATH . '/modules/' . $modulname . '/info.php';
            if (file_exists($info_file))
            {
                $module_version = null;
                require($info_file);
                $version = &$module_version;
            }
        }
        return $version;
    }
    
    /**
     *
     *
     *
     *
     **/
	function valid_lepton_template($file)
	{
		if ( ! file_exists( $file ) )
		{
			return false;
		}
		$suffix = pathinfo( $file, PATHINFO_EXTENSION );
		if ( $suffix == 'php' )
		{
			$string = implode( '', file($file) );
			if ( $string )
			{
				$tokens  = token_get_all($string);
				foreach( $tokens as $i => $token )
				{
					if ( is_array($token) )
					{
						if ( strcasecmp( $token[1], 'register_frontend_modfiles' ) == 0 )
						{
							return false;
						}
					}
				}
				return true;
			}
		}
		return false;
	}	// end function valid_lepton_template()

    /**
     * Generate a globally unique identifier (GUID)
     * Uses COM extension under Windows otherwise
     * create a random GUID in the same style
     * @return STR GUID
     */
    function createGUID()
    {
        if (function_exists('com_create_guid'))
        {
            $guid = com_create_guid();
            $guid = strtolower($guid);
            if (strpos($guid, '{') == 0)
            {
                $guid = substr($guid, 1);
            }
            if (strpos($guid, '}') == strlen($guid) - 1)
            {
                $guid = substr($guid, 0, strlen($guid) - 1);
            }
            return $guid;
        }
        else
        {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        }
    }   // end function createGUID()
    
    function checkIPv4address($ip_addr)
    {
        if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip_addr))
        {
            $parts = explode(".", $ip_addr);
            foreach ($parts as $ip_parts)
            {
                if (intval($ip_parts) > 255 || intval($ip_parts) < 0)
                    return false;
            }
            return true;
        }
        else
            return false;
    }   // end function checkIPv4address()
    
    /**
     *  As for some special chars, e.g. german-umlauts, inside js-alerts we are in the need to escape them.
     *  Keep in mind, that you will to have unescape them befor you use them inside a js!
     *
     */
    function js_alert_encode($string)
    {
        $entities = array('&auml;' => "%E4", '&Auml;' => "%C4", '&ouml;' => "%F6", '&Ouml;' => "%D6", '&uuml;' => "%FC", '&Uuml;' => "%DC", '&szlig;' => "%DF", '&euro;' => "%u20AC", '$' => "%24");
        return str_replace(array_keys($entities), array_values($entities), $string);
    }

    //**************************************************************************
    // wrappers to external functions (for convenience or backward compatiblity)
    //**************************************************************************

    /**
     * found no file where this is really used, but left it just in case...
     **/
    function chmod_directory_contents($directory, $file_mode) {
        global $lhd;
        return $lhd->setPerms($directory,$file_mode);
    }   // end function chmod_directory_contents()

    /**
     *    This function returns a recursive list of all subdirectories from a given directory
     *
     *    @access  public
     *    @param   string  $directory: from this dir the recursion will start.
     *    @param   bool    $show_hidden (optional): if set to TRUE also hidden dirs (.dir) will be shown.
     *    @param   int     $recursion_deep (optional): An optional integer to test the recursions-deep at all.
     *    @param   array   $aList (optional): A simple storage list for the recursion.
     *    @param   string  $ignore (optional): This is the part of the "path" to be "ignored"
     *
     *    @return  array
     *
     *    example:
     *        /srv/www/httpdocs/wb/media/a/b/c/
     *        /srv/www/httpdocs/wb/media/a/b/d/
     *
     *        if &ignore is set - directory_list('/srv/www/httpdocs/wb/media/') will return:
     *        /a
     *        /a/b
     *        /a/b/c
     *        /a/b/d
     */
    function directory_list($directory, $show_hidden = false, $recursion_deep = 0, &$aList = null, &$ignore = "")
    {
        global $lhd;
        if ($aList == null)
        {
            $aList = array();
        }
        $dirs = $lhd->scanDirectory( $directory, false, false, $ignore );
        return $aList;
    }   // end function directory_list()

    /**
     *  Function to remove a non-empty directory
     *  The function was moved to Directory helper class
     *
     *  @param  string $directory
     *  @return boolean
     */
    function rm_full_dir($directory) {
        global $lhd;
        return $lhd->removeDirectory($directory);
    }   // end function rm_full_dir()

    /**
     * sanitize path (remove '/./', '/../', '//')
     **/
    function sanitize_path( $path )
    {
        global $lhd;
		return $lhd->sanitizePath($path);
    }   // end function sanitize_path()

    /**
     * sanitize URL (remove '/./', '/../', '//')
     **/
    function sanitize_url( $href )
    {
        return CAT_Helper_Protect::getInstance()->sanitize_url($href);
    }   // end function sanitize_url()

}
// end .. if functions is loaded 
?>