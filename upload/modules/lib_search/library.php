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
 * @link          http://blackcat-cms.org
 * @license       http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$lang = CAT_Helper_I18n::getInstance();

// use the parser
global $parser;
$parser->setPath(CAT_PATH. '/modules/'. basename(dirname(__FILE__)).'/templates/custom');
$parser->setFallbackPath(CAT_PATH. '/modules/'. basename(dirname(__FILE__)).'/templates/default');

// Include the functions file
require_once CAT_PATH. '/framework/functions.php';

// library functions
require_once CAT_PATH. '/modules/lib_search/search.constants.php';
require_once CAT_PATH. '/modules/lib_search/search.module.php';

class CATSearch {
    
    private $error = '';
    private $message = '';
    private $lang = NULL;
    private $prompt = false;
        
    // the $setting array hold all settings for the search
    protected $setting = array();
    
    // the $search_functions hold the module function for the search
    protected $search_functions = array();
    
    // list of all LEPTON users
    protected $users = array();
    
    // the search language
    protected $search_language = LANGUAGE;
    
    // the search path
    protected $search_path = '';
    
    // the search path, SQL
    protected $search_path_SQL = '';
    
    // search language SQL query
    protected $search_language_SQL = '';

    // search language SQL query with table
    protected $search_language_SQL_table = '';
    
    // search type
    protected $search_type = SEARCH_TYPE_ALL;
    
    // search string
    protected $search_string = '';
    
    // search words for regex
    protected $search_words = array();
    
    // search url array
    protected $search_url_array = array();
    
    // search entities array
    protected $search_entities_array = array();
    
    // search result array
    protected $search_result = array();
    
    /**
     * Constructor for CATSearch
     * 
     * @access public
     */
    public function __construct($prompt=true) {
        global $lang;       
        $this->lang = $lang;
        $this->setPrompt($prompt);
    } // __construct()
    
    /**
     * Return a message
     * 
     * @access protected
     * @return string $message
     */
    protected function getMessage() {
        return $this->message;
    }

	/**
	 * Set a message
	 * 
	 * @access protected
     * @param field_type $message
     */
    protected function setMessage($message) {
        $this->message = $message;
    }
    
    /**
     * Check if a message is set.
     * 
     * @access protected
     * @return boolean
     */
    protected function isMessage() {
        return (bool) !empty($this->message);
    } // isMessage()

	/**
	 * Return the active error string
	 * 
	 * @access public
     * @return string $error
     */
    public function getError() {
        return $this->error;
    }

	/**
	 * Set a error
	 * 
	 * @access protected
     * @param string $error
     */
    protected function setError($error) {
        $this->error = $error;
    }

    /**
     * Check if an error occured
     * 
     * @access public
     * @return boolean 
     */
    public function isError() {
        return (bool) !empty($this->error);
    } // isError()
    
    /**
     * Return if the class should prompt the results or not
     * 
     * @access public
     * @return boolean - $prompt
     */
    public function getPrompt() {
        return $this->prompt;
    } // getPrompt()
    
    /**
     * Set prompt for the class
     * 
     * @access public
     * @param boolean $prompt
     */
    public function setPrompt($prompt=true) {
        $this->prompt = $prompt;
    } // setPrompt()
    
    /**
     * Return if the class should prompt the results or not
     * Alias for getPrompt()
     * 
     * @access public
     * @return boolean $prompt
     */
    public function isPrompt() {
        return $this->prompt;
    } // isPrompt()
    
    /**
     * Load the desired template, execute the template engine and returns the
     * resulting template
     *
     * @access protected   
     * @param string $template - the file name of the template
     * @param array $template_data - the data for the template
     */
    protected function getTemplate($template, $template_data) {
        global $parser;
        $result = '';
        try {
            $result = $parser->get($template, $template_data);
        } catch (Exception $e) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('<p>Error executing template <b>{{ template }}</b>:</p><p>{{ error }}</p>',
                    array('template' => $template, 'error' => $e->getMessage()))));
            return false;
        }
        return $result;
    } // getTemplate()
    
    /**
     * Get the settings for the LEPTON Search
     * 
     * @access protected
     * @return boolean
     */
    protected function getSettings() {
        global $database;
        
        // set default values
        $this->setting = array(
            CFG_CONTENT_IMAGE => CONTENT_IMAGE_FIRST,
            CFG_SEARCH_DESCRIPTIONS => true,
            CFG_SEARCH_DROPLEP => true,
            CFG_SEARCH_IMAGES => true,
            CFG_SEARCH_KEYWORDS => true,
            CFG_SEARCH_LIBRARY => 'lib_search',
            CFG_SEARCH_LINK_NON_PUBLIC_CONTENT => '',
            CFG_SEARCH_MAX_EXCERPTS => 15,
            CFG_SEARCH_MODULE_ORDER => 'wysiwyg',
            CFG_SEARCH_NON_PUBLIC_CONTENT => false,
            CFG_SEARCH_SHOW_DESCRIPTIONS => true,
            CFG_SEARCH_TIME_LIMIT => 0,
            CFG_SEARCH_USE_PAGE_ID => -1,
            CFG_THUMBS_WIDTH => 100
        );
        
        $SQL = sprintf("SELECT * FROM %ssearch", CAT_TABLE_PREFIX);
        if (false ===($query = $database->query($SQL))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error())); 
            return  false;
        }
        while (false !== ($field = $query->fetchRow(MYSQL_ASSOC))) {
            if (isset($this->setting[$field['name']])) $this->setting[$field['name']] = $field['value'];
        }
        return true;
    } // getSettings()
    
    /**
     * Walk through the modules and gather all search functions which should 
     * included in the LEPTON search in the this->search_functions array
     * 
     * @access protected
     * @return boolean - true on success and false on error
     */
    protected function checkForModuleSearchFunctions() {
        global $database;
        
        $this->search_functions = array();
        $this->search_functions['__before'] = array();
        $this->search_functions['__after'] = array();
        
        // get all module directories
        $query = $database->query("SELECT DISTINCT directory FROM " . CAT_TABLE_PREFIX . "addons WHERE type = 'module'");
        if ($database->is_error()) { 
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error())); 
            return  false; 
        }
        if ($query->numRows() > 0) {
            while (false !== ($module = $query->fetchRow())) {
                $file = CAT_PATH . '/modules/' . $module['directory'] . '/search.php';
                if (file_exists($file)) {
                    include_once ($file);
                    // add standard search function
                    if (function_exists($module['directory'] . "_search")) {
                        $this->search_functions[$module['directory']] = $module['directory'] . "_search";
                    }
                    // add search function with high priority
                    if (function_exists($module['directory'] . "_search_before")) {
                        $this->search_functions['__before'][] = $module['directory'] . "_search_before";
                    }
                    // add search function with low priority
                    if (function_exists($module['directory'] . "_search_after")) {
                        $this->search_functions['__after'][] = $module['directory'] . "_search_after";
                    }
                }
            }
        }
        return true;
    } // checkForModuleSearchFunctions()
    
    /**
     * Create a list with all registered LEPTON users
     * 
     * @access protected
     * @return boolean - true on success
     */
    protected function getUsers() {
        global $database;
        
        // get all users
        $query = $database->query("SELECT user_id,username,display_name FROM " . CAT_TABLE_PREFIX . "users");
        if ($database->is_error()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return  false;
        }        
        // set a "unknown user"
        $this->users = array(
                '0' => array(
                        'display_name' => $this->lang->translate('- unknown user -'),
                        'username' => strtolower($this->lang->translate('- unknown user -'))
                        )
                );
        if ($query->numRows() > 0) {
            while (false !== ($user = $query->fetchRow())) {
                $this->users[$user['user_id']] = array(
                        'display_name' => $user['display_name'],
                        'username' => $user['username']);
            }
        }
        return true;        
    } // getUsers()
    
    /**
     * Get the path to search into. 
     * The search path is normally not set and blank, you can set the path using
     * $_REQUEST['search_path']. 
     * Use a '%' as wildcard at the BEGINNING of the path, the search adds 
     * automatically a wildcard at the END of the path (SQL like style).
     * 
     * Possible values:
     * 
     *   a single path: 
     *     "/en/" - search only pages whose link starts with "/en/" like 
     *     "/en/search-me.php"
     *   a single path NOT to search into: 
     *     "-/help" - search for all pages but not for pages whose link starts 
     *     with "/help"
     *   a bunch of alternative pathes:
     *     "/en/,%/machinery/,/docs/" - alternative search paths, separated
     *     by comma
     *   a bunch of paths to exclude:
     *     "-/about,%/info,/docs/" - search all paths buth exclude these!
     *     
     * The different styles can't be mixed.
     * 
     * @access protected
     */
    protected function getSearchPath() {
        global $wb;
                
        $this->search_path_SQL = '';
        $this->search_path = '';
        if (isset($_REQUEST[REQUEST_SEARCH_PATH])) {
            $this->search_path = addslashes(htmlspecialchars(strip_tags($wb->strip_slashes($_REQUEST[REQUEST_SEARCH_PATH])), ENT_QUOTES));
            if (!preg_match('~^%?[-a-zA-Z0-9_,/ ]+$~', $this->search_path)) $this->search_path = '';
            if ($this->search_path != '') {
                $this->search_path_SQL = 'AND ( ';
                $not = '';
                $op = 'OR';
                if ($this->search_path[0] == '-') {
                    $not = 'NOT';
                    $op = 'AND';
                    $paths = explode(',', substr($this->search_path, 1));
                } else {
                    $paths = explode(',', $this->search_path);
                }
                $i = 0;
                foreach ($paths as $p) {
                    if ($i ++ > 0) {
                        $this->search_path_SQL .= ' $op';
                    }
                    $this->search_path_SQL .= " link $not LIKE '" . $p . "%'";
                }
                $this->search_path_SQL .= ' )';
            }
        }   
    } // getSearchPath()
    
    /**
     * Get the type of the search to execute, possible values are
     * - SEARCH_TYPE_ANY  = 'any' - search matches any words
     * - SEARCH_TYPE_ALL = 'all' - search match all words
     * - SEARCH_TYPE_EXACT = 'exact' - search for exact match
     * and set $this->search_type
     * 
     * @access protected
     */
    protected function getSearchType() {
        if (isset($_REQUEST[REQUEST_SEARCH_TYPE])) {
            if ($_REQUEST[REQUEST_SEARCH_TYPE] == SEARCH_TYPE_ANY)
                $this->search_type = SEARCH_TYPE_ANY;
            elseif ($_REQUEST[REQUEST_SEARCH_TYPE] == SEARCH_TYPE_ALL)
                $this->search_type = SEARCH_TYPE_ALL;
            elseif ($_REQUEST[REQUEST_SEARCH_TYPE] == SEARCH_TYPE_EXACT)
                $this->search_type = SEARCH_TYPE_EXACT;
            elseif ($_REQUEST[REQUEST_SEARCH_TYPE] == SEARCH_TYPE_IMAGE)
                $this->search_type = SEARCH_TYPE_IMAGE;
            else
                $this->search_type = SEARCH_TYPE_ALL;
        } 
        else {
            $this->search_type = SEARCH_TYPE_ALL;
        }
    } // getSearchType()
    
    /**
     * Prepare the search before really executing
     * 
     * @access protected
     */
    protected function prepareSearch() {
        global $wb;
        
        $search_entities_string = ''; // for SQL's LIKE
        $search_display_string = ''; // for displaying
        $search_url_string = ''; // for $_GET -- ATTN: unquoted! Will become urldecoded later
        $string = '';
        if (isset($_REQUEST[REQUEST_SEARCH_STRING])) {
            if ($this->search_type != SEARCH_TYPE_EXACT) {
                // remove all comma's
                $string = str_replace(',', '', $_REQUEST[REQUEST_SEARCH_STRING]);
            } else {
                $string = $_REQUEST[REQUEST_SEARCH_STRING];
            }
            // redo possible magic quotes
            $string = $wb->strip_slashes($string);
            $string = preg_replace('/[ \r\n\t]+/', ' ', $string);
            $string = trim($string);
            // remove some bad chars
            $string = str_replace(array('[[', ']]'), '', $string);
            $string = preg_replace('/(^|\s+)[|.]+(?=\s+|$)/', '', $string);
            $search_display_string = htmlspecialchars($string);
            $search_entities_string = addslashes(umlauts_to_entities(htmlspecialchars($string)));
            // mySQL needs four backslashes to match one in LIKE comparisons)
            $search_entities_string = str_replace('\\\\', '\\\\\\\\', $search_entities_string);
            // convert string to utf-8
            $string = entities_to_umlauts($string, 'UTF-8');
            $search_url_string = $string;
            $search_entities_string = addslashes(htmlentities($string, ENT_COMPAT, 'UTF-8'));
            // mySQL needs four backslashes to match one in LIKE comparisons)
            $search_entities_string = str_replace('\\\\', '\\\\\\\\', $search_entities_string);
            $string = preg_quote($string);
            // quote ' " and /  -we need quoted / for regex
            $this->search_string = str_replace(array('\'', '"', '/'), array('\\\'', '\"', '\/'), $string);
        }
        // make arrays from the search_..._strings above
        if ($this->search_type == SEARCH_TYPE_EXACT) {
            $this->search_url_array[] = $search_url_string;
        }
        else {
            $this->search_url_array = explode(' ', $search_url_string);
        }
        $search_normal_array = array();
        $this->search_entities_array = array();
        
        if ($this->search_type == SEARCH_TYPE_EXACT) {
            $search_normal_array[] = $this->search_string;
            $this->search_entities_array[] = $search_entities_string;
        } 
        else {
            $exploded_string = explode(' ', $this->search_string);
            // Make sure there is no blank values in the array
            foreach ($exploded_string as $each_exploded_string) {
                if ($each_exploded_string != '') {
                    $search_normal_array[] = $each_exploded_string;
                }
            }
            $exploded_string = explode(' ', $search_entities_string);
            // Make sure there is no blank values in the array
            foreach ($exploded_string as $each_exploded_string) {
                if ($each_exploded_string != '') {
                    $this->search_entities_array[] = $each_exploded_string;
                }
            }
        }
        
        // make an extra copy of search_normal_array for use in regex
        $this->search_words = array();
        
        // include the translation tables for special chars
        $search_language = $this->search_language;
        include_once CAT_PATH.'/modules/'. basename(dirname(__FILE__)).'/search.convert.php';
        global $search_table_umlauts_local;
        
        include_once CAT_PATH.'/modules/'. basename(dirname(__FILE__)).'/search.convert.umlaute.php';
        global $search_table_ul_umlauts;
        
        foreach ($search_normal_array as $str) {
            $str = strtr($str, $search_table_umlauts_local);
            $str = strtr($str, $search_table_ul_umlauts);
            $this->search_words[] = $str;
        }    
            
    } // prepareSearch()
    
    protected function getSearchForm() {
        $data = array(
                'action' => CAT_URL.'/search/index.php',
                'search_path' => array(
                        'name' => REQUEST_SEARCH_PATH,
                        'value' => $this->search_path
                        ),
                'search_string' => array(
                        'name' => REQUEST_SEARCH_STRING,
                        'value' => $this->search_string
                        ),
                'search_type' => array(
                        'name' => REQUEST_SEARCH_TYPE,
                        'match_all' => array(
                                'value' => SEARCH_TYPE_ALL,
                                'checked' => ($this->search_type == SEARCH_TYPE_ALL) ? 1 : 0
                                ),
                        'match_any' => array(
                                'value' => SEARCH_TYPE_ANY,
                                'checked' => ($this->search_type == SEARCH_TYPE_ANY) ? 1 : 0
                                ),
                        'match_exact' => array(
                                'value' => SEARCH_TYPE_EXACT,
                                'checked' => ($this->search_type == SEARCH_TYPE_EXACT) ? 1 : 0
                                ),
                        'match_image' => array(
                                'value' => SEARCH_TYPE_IMAGE,
                                'checked' => ($this->search_type == SEARCH_TYPE_IMAGE) ? 1 : 0
                                ),
                        )
                );        
        return $data;
    } // getSearchForm
    
    protected function execSearch() {
        global $database;
        global $admin;
        
        $data = array();
        if ($this->search_string == '') {
            // empty search string - just return the dialog
            $this->search_result = array(
                'form' => $this->getSearchForm()
                );
            return true;
        }
        
        // Get the modules from module table
        $get_modules = $database->query(sprintf("SELECT DISTINCT module FROM %ssections WHERE module != '' ", CAT_TABLE_PREFIX));
        if ($database->is_error()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
        }
        $modules = array();
        if ($get_modules->numRows() > 0) {
            while (false !== ($module = $get_modules->fetchRow(MYSQL_ASSOC))) {
                $modules[] = $module['module'];
            }
        }
        
        // get the modules for the DropLEP search
        $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplep'", CAT_TABLE_PREFIX);
        if (false === ($get_dropleps = $database->query($SQL))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
        }
        $dropleps = array();
        $droplep_array = array();
        if ($get_dropleps->numRows() > 0) {
            while (false !== ($module = $get_dropleps->fetchRow(MYSQL_ASSOC))) {
                $value = unserialize($module['extra']);
                if (isset($value['page_id']) && isset($value['module_directory'])) {
                    $dropleps[] = array(
                        'module_directory' => $value['module_directory'],
                        'page_id' => $value['page_id'],
                        'droplep_name' => $module['value']);
                    if (!isset($droplep_array[$value['module_directory']])) {
                        $modules[] = $value['module_directory'];
                        $droplep_array[$value['module_directory']] = $value['module_directory'];
                    }
                }
            }
        }

        // sort module search order - first modules from CFG_MODULE_ORDER
        $sorted_modules = array();
        $search_modules = explode(',', $this->setting[CFG_SEARCH_MODULE_ORDER]);
        foreach ($search_modules as $item) {
            $item = trim($item);
            for ($i=0; $i < count($modules); $i++) {
                if (isset($modules[$i]) && $modules[$i] == $item) {
                    $sorted_modules[] = $modules[$i];
                    unset($modules[$i]);
                    break;
                }
            }
        }
        // ... then add the rest
        foreach ($modules as $item) {
            $sorted_modules[] = $item;
        }

        // Use the module's search-extensions.
        // This is somewhat slower than the orginial method.
        
        // init the $_SESSION for the search result items
        $_SESSION[SESSION_SEARCH_RESULT_ITEMS] = array();
                
        // call $search_funcs['__before'] first
        $search_func_vars = array(
                'database' => $database, // database-handle
                'page_id' => 0,
                'section_id' => 0, 
                'page_title' => '', 
                'page_menu_title' => '',
                'page_description' => '', 
                'page_keywords' => '', 
                'page_link' => '',
                'page_visibility' => 'public',
                'page_modified_when' => 0, 
                'page_modified_by' => 0, 
                'users' => $this->users,  // array of known user-id/user-name
                'search_words' => $this->search_words, // array of strings, prepared for regex
                'search_match' => $this->search_type,  // match-type
                'search_url_array' => $this->search_url_array,  // array of strings from the original search-string. ATTN: strings are not quoted!
                'search_entities_array' => $this->search_entities_array,  // entities
                'default_max_excerpt' => $this->setting[CFG_SEARCH_MAX_EXCERPTS],
                'time_limit' => $this->setting[CFG_SEARCH_TIME_LIMIT], // time-limit in secs
                'search_path' => $this->search_path,
                'settings' => $this->setting
                );// see docu
        
        foreach($this->search_functions['__before'] as $func) {
            $uf_res = call_user_func($func, $search_func_vars);
        }
        
        // now call module-based $search_funcs[]
        $seen_pages = array(); // seen pages per module.
        $pages_listed = array(); // seen pages.
        // skip this search if $search_max_excerpt == 0
        if ($this->setting[CFG_SEARCH_MAX_EXCERPTS] != 0) { 
            foreach ($sorted_modules as $module_name) {
                $start_time = time();	// get start-time to check time-limit; not very accurate, but ok
                $seen_pages[$module_name] = array();
                if (!isset($this->search_functions[$module_name])) {
                    // there is no search_func for this module
                    continue; 
                }
                if (isset($droplep_array[$module_name])) {
                    // don't look for sections - call DropLEPs search function
                    $pids = array();
                    foreach ($dropleps as $dl) {
                        if ($dl['module_directory'] == $module_name) $pids[] = $dl['page_id'];
                    }
                    foreach ($pids as $pid) {
                        $SQL = sprintf("SELECT * FROM %spages WHERE page_id='%s'", CAT_TABLE_PREFIX, $pid);
                        if (false === ($pages_query = $database->query($SQL))) {
                            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
                            return false;
                        }   
                        if ($pages_query->numRows() > 0) {
                            while(false !== ($res = $pages_query->fetchRow(MYSQL_ASSOC))) {
                                // check if time-limit is exceeded for this module
                                if ($this->setting[CFG_SEARCH_TIME_LIMIT] > 0 && (time()-$start_time > $this->setting[CFG_SEARCH_TIME_LIMIT])) {
                                    break;
                                }
                                $search_func_vars = array(
                                    'database' => $database,
                                    'page_id' => $res['page_id'],
                                    'section_id' => -1, // no section_id's for DropLEPs needed
                                    'page_title' => $res['page_title'],
                                    'page_menu_title' => $res['menu_title'],
                                    'page_description' => $this->setting[CFG_SEARCH_SHOW_DESCRIPTIONS] ? $res['description'] : "",
                                    'page_keywords' => $res['keywords'],
                                    'page_link' => $res['link'],
                                    'page_visibility' => $res['visibility'],
                                    'page_modified_when' => $res['modified_when'],
                                    'page_modified_by' => $res['modified_by'],
                                    'users' => $this->users,
                                    'search_words' => $this->search_words, // needed for preg_match
                                    'search_match' => $this->search_type,
                                    'search_url_array' => $this->search_url_array, // needed for url-string only
                                    'search_entities_array' => $this->search_entities_array, // entities
                                    'default_max_excerpt' => $this->setting[CFG_SEARCH_MAX_EXCERPTS],
                                    'time_limit' => $this->setting[CFG_SEARCH_TIME_LIMIT], // time-limit in secs
                                    'settings' => $this->setting
                                );
                                // Only show this page if we are allowed to see it
                                if ($admin->page_is_visible($res) == false) {
                                    if ($res['visibility'] == 'registered') {
                                        if (!$this->setting[CFG_SEARCH_NON_PUBLIC_CONTENT]) {
                                            // don't show excerpt
                                            $search_func_vars['default_max_excerpt'] = 0;
                                            $search_func_vars['page_description'] = $this->lang->translate('This content is reserved for registered users.');
                                        } else {
                                            // show non public content so set $_SESSIONs for print_excerpt2()
                                            $_SESSION[SESSION_SEARCH_NON_PUBLIC_CONTENT] = true;
                                            $_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT] = $this->setting[CFG_SEARCH_LINK_NON_PUBLIC_CONTENT];
                                        }
                                    } else { // private
                                        continue;
                                    }
                                }
                                // call the module search function
                                $uf_res = call_user_func($this->search_functions[$module_name], $search_func_vars);
                                if ($uf_res) {
                                    $pages_listed[$res['page_id']] = true;
                					$seen_pages[$module_name][$res['page_id']] = true;
                				} else {
                					$seen_pages[$module_name][$res['page_id']] = true;
                				}
                            } 
                        }
                    }
                }
                else {           
                    // get each section for $module_name
                    $table_s = CAT_TABLE_PREFIX."sections";
                    $table_p = CAT_TABLE_PREFIX."pages";
                    $SQL = 
                        "SELECT s.section_id, s.page_id, s.module, s.publ_start, 
                        s.publ_end, p.page_title, p.menu_title, p.link, p.description, 
                        p.keywords, p.modified_when, p.modified_by, p.visibility, 
                        p.viewing_groups, p.viewing_users FROM $table_s AS s 
                        INNER JOIN $table_p AS p ON s.page_id = p.page_id WHERE 
                        s.module = '$module_name' AND p.visibility NOT IN 
                        ('none','deleted') AND p.searching = '1' ".$this->search_path_SQL." ". 
                        $this->search_language_SQL." ORDER BY s.page_id, s.position ASC";
                    if (false === ($sections_query = $database->query($SQL))) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
                        return false;
                    }
                    if ($sections_query->numRows() > 0) {
                        while (false !== ($res = $sections_query->fetchRow())) {
                            // check if time-limit is exceeded for this module
                            if ($this->setting[CFG_SEARCH_TIME_LIMIT] > 0 && (time()-$start_time > $this->setting[CFG_SEARCH_TIME_LIMIT])) {
                                break;
                            }
                            // Only show this section if it is not "out of publication-date"
                            $now = time();
                            if (!($now < $res['publ_end'] && ($now > $res['publ_start'] || $res['publ_start'] == 0) ||
                                $now > $res['publ_start'] && $res['publ_end'] == 0)) {
                                continue;
                            }
                            $search_func_vars = array(
                                'database' => $database,
                                'page_id' => $res['page_id'],
                                'section_id' => $res['section_id'],
                                'page_title' => $res['page_title'],
                                'page_menu_title' => $res['menu_title'],
                                'page_description' => $this->setting[CFG_SEARCH_SHOW_DESCRIPTIONS] ? $res['description'] : "",
                                'page_keywords' => $res['keywords'],
                                'page_link' => $res['link'],
                                'page_visibility' => $res['visibility'],
                                'page_modified_when' => $res['modified_when'],
                                'page_modified_by' => $res['modified_by'],
                                'users' => $this->users,
                                'search_words' => $this->search_words, // needed for preg_match
                                'search_match' => $this->search_type,
                                'search_url_array' => $this->search_url_array, // needed for url-string only
                                'search_entities_array' => $this->search_entities_array, // entities
                                'default_max_excerpt' => $this->setting[CFG_SEARCH_MAX_EXCERPTS],
                                'time_limit' => $this->setting[CFG_SEARCH_TIME_LIMIT], // time-limit in secs
                                'settings' => $this->setting
                            );
                            // Only show this page if we are allowed to see it
                            if ($admin->page_is_visible($res) == false) {
                                if ($res['visibility'] == 'registered') {
                                    if (!$this->setting[CFG_SEARCH_NON_PUBLIC_CONTENT]) {
                                        // don't show excerpt
                                        $search_func_vars['default_max_excerpt'] = 0;
                                        $search_func_vars['page_description'] = $this->lang->translate('This content is reserved for registered users.');
                                    } else {
                                        // show non public content so set $_SESSIONs for print_excerpt2()
                                        $_SESSION[SESSION_SEARCH_NON_PUBLIC_CONTENT] = true;
                                        $_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT] = $this->setting[CFG_SEARCH_LINK_NON_PUBLIC_CONTENT];
                                    }
                                } else { // private
                                    continue;
                                }
                            }
                            // call the module search function
                            $uf_res = call_user_func($this->search_functions[$module_name], $search_func_vars);
                            if ($uf_res) {
                                $pages_listed[$res['page_id']] = true;
            					$seen_pages[$module_name][$res['page_id']] = true;
            				} else {
            					$seen_pages[$module_name][$res['page_id']] = true;
            				}
            			} // while
                    } // if
                }
        	} // foreach
        } // max_excerpts
        
        // call $search_funcs['__after']
        $search_func_vars = array(
            'database' => $database, // database-handle
            'page_id' => 0,
            'section_id' => 0,
            'page_title' => '',
            'page_menu_title' => '',
            'page_description' => '',
            'page_keywords' => '',
            'page_link' => '',
            'page_visibility' => 'public',
            'page_modified_when' => 0,
            'page_modified_by' => 0,
            'users' => $this->users,  // array of known user-id/user-name
            'search_words' => $this->search_words, // array of strings, prepared for regex
            'search_match' => $this->search_type,  // match-type
            'search_url_array' => $this->search_url_array,  // array of strings from the original search-string. ATTN: strings are not quoted!
            'search_entities_array' => $this->search_entities_array,  // entities
            'default_max_excerpt' => $this->setting[CFG_SEARCH_MAX_EXCERPTS],
            'time_limit' => $this->setting[CFG_SEARCH_TIME_LIMIT], // time-limit in secs
            'search_path' => $this->search_path,
            'settings' => $this->setting
        );// see docu
        
        foreach($this->search_functions['__after'] as $func) {
            $uf_res = call_user_func($func, $search_func_vars);
        }
        
        // Search page details only, such as description, keywords, etc, but only of unseen pages.
        $max_excerpt_num = 3; // we don't want excerpt here ???
        $divider = ".";
        $table = CAT_TABLE_PREFIX."pages";
        $SQL = "SELECT page_id, page_title, menu_title, link, description, 
            keywords, modified_when, modified_by, visibility, viewing_groups, 
            viewing_users FROM $table WHERE visibility NOT IN ('none','deleted') 
            AND searching = '1' ".$this->search_path_SQL." ".$this->search_language_SQL;
        if (false ===($query_pages = $database->query($SQL))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
        }
        if ($query_pages->numRows() > 0) {
            while(false !== ($page = $query_pages->fetchRow())) {
                if (isset($pages_listed[$page['page_id']])) continue;
                $func_vars = array(
                    'database' => $database,
                    'page_id' => $page['page_id'],
                    'page_title' => $page['page_title'],
                    'page_menu_title' => $page['menu_title'],
                    'page_description' => $this->setting[CFG_SEARCH_SHOW_DESCRIPTIONS] ? $page['description'] : '',
                    'page_keywords' => $page['keywords'],
                    'page_link' => $page['link'],
                    'page_visibility' => $page['visibility'],
                    'page_modified_when' => $page['modified_when'],
                    'page_modified_by' => $page['modified_by'],
                    'users' => $this->users,
                    'search_words' => $this->search_words, // needed for preg_match_all
                    'search_match' => $this->search_type,
                    'search_url_array' => $this->search_url_array, // needed for url-string only
                    'search_entities_array' => $this->search_entities_array, // entities
                    'default_max_excerpt' => $max_excerpt_num,
                    'settings' => $this->setting
                );
                // Only show this page if we are allowed to see it
                if ($admin->page_is_visible($page) == false) {
                    if($page['visibility'] != 'registered') {
                        continue;
                    } else { 
                        // page: registered, user: access denied
                        $func_vars['page_description'] = $this->lang->translate('This content is reserved for registered users.');
                    }
                }
                if($admin->page_is_active($page) == false) {
                    continue;
                }
                $text = $func_vars['page_title'].$divider
                    .$func_vars['page_menu_title'].$divider
                    .($this->setting[CFG_SEARCH_DESCRIPTIONS] ? $func_vars['page_description'] : '')
                    .$divider.($this->setting[CFG_SEARCH_DESCRIPTIONS] ? $func_vars['page_keywords'] : '').$divider;
                $mod_vars = array(
                    'page_link' => $func_vars['page_link'],
                    'page_link_target' => "",
                    'page_title' => $func_vars['page_title'],
                    'page_description' => $func_vars['page_description'],
                    'page_modified_when' => $func_vars['page_modified_when'],
                    'page_modified_by' => $func_vars['page_modified_by'],
                    'text' => $text,
                    'max_excerpt_num' => $func_vars['default_max_excerpt']
                );
                if (print_excerpt2($mod_vars, $func_vars)) {
                    $pages_listed[$page['page_id']] = true;
                }
            }
        }
        
        // ok - all done ...
        $src = CAT_PATH.'/modules/lib_search/images/content-locked.gif';
	    list($width, $height) = getimagesize($src);
	    
	    $this->search_result = array(
            'form' => $this->getSearchForm(),
            'result' => array(
                'count' => count($_SESSION[SESSION_SEARCH_RESULT_ITEMS]),
                'items' => $_SESSION[SESSION_SEARCH_RESULT_ITEMS],
                ),
            'images' => array(
                'locked' => array(
                    'src' => CAT_URL.'/modules/lib_search/images/content-locked.gif',
                    'width' => $width,
                    'height' => $height,
                )
            )            
        );
        
	    return true;
    } // execSearch()
    
    /**
     * Execute the LEPTON Search
     * 
     * @access protected
     * @return string result
     */
    public function exec() {
        if (!SHOW_SEARCH) {
            // the lepton search is not active
            $this->setMessage($this->lang->translate('The LEPTON Search is disabled!'));
            return $this->Output($this->getMessage);
        }
        
        // get the settings for the search
        if (!$this->getSettings()) return $this->Output();
                 
        // gather the modules search functions
        if (!$this->checkForModuleSearchFunctions()) return $this->Output();
        
        // get all users
        if (!$this->getUsers()) return $this->Output();
        
        // check if a search language is set, used for special umlaut handling
        if (isset($_REQUEST[REQUEST_SEARCH_LANG])) {
            $this->search_language = $_REQUEST[REQUEST_SEARCH_LANG];
            if (!preg_match('~^[A-Z]{2}$~', $this->search_language)) $this->search_language = LANGUAGE;
        } 
        
        // get the search path
        $this->getSearchPath();
        
        // use page languages?
        if (PAGE_LANGUAGES) {
            $table = CAT_TABLE_PREFIX . "pages";
            $this->search_language_SQL_table = "AND $table.`language` = '" . LANGUAGE . "'";
            $this->search_language_SQL = "AND `language` = '" . LANGUAGE . "'";
        }
        
        // get the search type
        $this->getSearchType();
        
        // prepare the search
        $this->prepareSearch();
        
        // create temporary directory for the search
        $tmp = CAT_PATH.'/temp/search';
        if (!file_exists($tmp)) {
            if (!mkdir($tmp, 0755, true)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                $this->lang->translate('Error creating the directory <b>{{ directory }}</b>.',
                array('directory' => '/temp/search'))));
                return $this->Output();
            }
        }
        // cleanup the temporary directory
        $oDir = dir($tmp);        
        while (false !== ($strFile = $oDir->read())) {
            if ($strFile != '.' && $strFile != '..'
                && !is_link($tmp.'/'.$strFile)
                && is_file($tmp.'/'.$strFile)) @unlink($tmp.'/'.$strFile);
        }        
        $oDir->close();

        // process the search
        if (!$this->execSearch()) return $this->Output();

        return $this->Output($this->search_result);
    } // exec()
    
    /**
     * Prompt or return the results of the LEPTON Search
     * 
     * @access protected
     * @param string $result - the string or dialog to output
     * @param array $data - template data
     * @return mixed - prompt the result or return the results
     */
	protected function Output($data=array()) {
	    global $parser;
        if ($this->isError()) {
            // prompt error
            $data = array(
                    'error' => array(
                            'header' => $this->lang->translate('LEPTON Search Error'),
                            'text' => $this->getError()
                            )
                    );
            $result = $this->getTemplate('error.lte', $data);            
        }
        elseif ($this->isMessage()) {
            // prompt a message
            $data = array(
                    'message' => array(
                            'header' => $this->lang->translate('LEPTON Search Message'),
                            'text' => $this->getMessage()
                            )
                    );
            $result = $this->getTemplate('message.lte', $data);
        }
        else {
            // return the search result
            $result = $this->getTemplate('search.results.lte', $data);
        } 
        return ($this->isPrompt()) ? print($result) : $result;
    }
} // class CATSearch