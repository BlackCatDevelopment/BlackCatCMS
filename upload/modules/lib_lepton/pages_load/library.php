<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          lib_lepton
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.lepton-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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
// end include class.secure.php

/**
 * Check wether the DropLEP $droplep_name is registered for setting CSS/JS Headers
 * 
 * @param integer $page_id
 * @param string $droplep_name
 * @param string $module_directory
 * @param string $file_type - may be 'css' or 'js'
 * @return boolean true if the DropLEP is registered
 */
function is_registered_droplep($page_id, $droplep_name, $module_directory, $file_type, $droplep_option=array()) {
    global $database;
    
    $table = TABLE_PREFIX.'pages_load';
    $SQL = "SELECT `id`, `options` FROM `$table` WHERE `page_id`='$page_id' AND `register_name`='$droplep_name' ".
        "AND `file_type`='$file_type' AND `module_directory`='$module_directory'";
    if (false === ($query = $database->query($SQL))) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    while (false !== ($droplep = $query->fetchRow(MYSQL_ASSOC))) {
        $option = unserialize($droplep['options']);
        if (isset($droplep_option['POST_ID'])) {
            if (isset($option['POST_ID']) && ($droplep_option['POST_ID'] == $option['POST_ID'])) return true;
        }
        elseif (isset($droplep_option['TOPIC_ID'])) {
            if (isset($option['TOPIC_ID']) && ($droplep_option['TOPIC_ID'] == $option['TOPIC_ID'])) return true;
        }
        else {
            return true;
        }
    } // while
    return false;
} // is_registered_droplep()

/**
 * Register the DropLEP $droplep_name for the $page_id for the $file_type 'css' or 'js'
 * with the specified $file_name.
 * If $file_path is specified the file will be loaded from $file_path, otherwise the
 * file will be loaded from the desired $module_directory.
 * If $page_id is set to -1 the CSS/JS file will be loaded at every page (for usage 
 * in templates)
 * 
 * @param integer $page_id
 * @param string $droplep_name
 * @param string $module_directory - only the directory name
 * @param string $file_type - may be 'css' or 'js'
 * @param string $file_name - the filename with extension
 * @param string $file_path - relative to the root
 * @return boolean on success
 */
function register_droplep($page_id, $droplep_name, $module_directory, $file_type, $file_name='frontend.css', $file_path='') {
    global $database;
    
    $option = array();
    if (defined('POST_ID')) $option['POST_ID'] = POST_ID;
    if (defined('TOPIC_ID')) $option['TOPIC_ID'] = TOPIC_ID;
    $option_str = serialize($option);
    
    if (is_registered_droplep($page_id, $droplep_name, $module_directory, $file_type)) return true;
    
    $table = TABLE_PREFIX.'pages_load';
    $SQL = "INSERT INTO `$table` (page_id, register_name, register_type, file_type, module_directory, file_name, file_path, options) ".
        "VALUES ('$page_id', '$droplep_name', 'droplep', '$file_type', '$module_directory', '$file_name', '$file_path', '$option_str')";
    if (!$database->query($SQL)) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    return true;    
} // register_css

/**
 * Unregister the DropLEP $droplep_name from the $page_id with the settings
 * $module_directory, $file_type and $file_name
 * 
 * @param integer $page_id
 * @param string $droplep_name
 * @param sring $module_directory
 * @param string $file_type - 'css' or 'js'
 * @param string $file_name
 */
function unregister_droplep($page_id, $droplep_name, $module_directory, $file_type, $file_name) {
    global $database;    
    if (is_registered_droplep($page_id, $droplep_name, $module_directory, $file_type)) {
        $table = TABLE_PREFIX.'pages_load';
        $SQL = "DELETE FROM `$table` WHERE `page_id`='$page_id' AND `register_name`='$droplep_name' AND ".
            "`module_directory`='$module_directory' AND `file_type`='$file_type' AND `file_name`='$file_name'";
        if (!$database->query($SQL)) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
    }
    return true;
} // unregister_droplep()

/**
 * Check if the DropLEP $droplep_name exists in a WYSIWYG section of $page_id or
 * if the DropLEP is placed in a NEWs or TOPICs article.
 * 
 * @param string $droplep_name
 * @param integer $page_id
 * @param array $option
 * @return boolean true on success
 */
function droplep_exists($droplep_name, $page_id, &$option=array()) {
    global $database;
    
    if (isset($option['POST_ID']) || defined('POST_ID')) {
        // DropLEP may be placed at a NEWs article
        $post_id = defined('POST_ID') ? POST_ID : $option['POST_ID'];
        $table = TABLE_PREFIX.'mod_news_posts';
        $SQL = "SELECT `page_id` FROM `$table` WHERE `post_id`='$post_id' AND ((`content_long` LIKE '%[[$droplep_name?%') OR (`content_long` LIKE '%[[$droplep_name]]%'))";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) return true;
    }
    
    if (isset($option['TOPIC_ID']) || defined('TOPIC_ID')) {
        // DropLEP may be placed at a TOPICs article
        $topic_id = defined('TOPIC_ID') ? TOPIC_ID : $option['TOPIC_ID'];
        $table = TABLE_PREFIX.'mod_topics';
        $SQL = "SELECT `page_id` FROM `$table` WHERE `topic_id`='$topic_id' AND ((`content_long` LIKE '%[[$droplep_name?%') OR (`content_long` LIKE '%[[$droplep_name]]%'))";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) return true;
    }

    $table = TABLE_PREFIX.'mod_wysiwyg';
    $SQL = "SELECT `section_id` FROM `$table` WHERE `page_id`='$page_id' AND ((`text` LIKE '%[[$droplep_name?%') OR (`text` LIKE '%[[$droplep_name]]%'))";
    if (false === ($query = $database->query($SQL))) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    if ($query->numRows() > 0) return true;
    
    return false;
} // droplep_exists()

/**
 * Check for entries for the desired $page_id or for entries which should be loaded
 * at every page, load the specified CSS and JS files in the global $HEADER array
 * 
 * @param integer $page_id
 * @return boolean true on success
 */
function get_droplep_headers($page_id) {
    global $HEADERS, $lhd, $database;

    $table = TABLE_PREFIX.'pages_load';
    
    $SQL = "SELECT * FROM `$table` WHERE (`page_id`='$page_id' OR `page_id`='-1') AND (`file_type`='css' OR `file_type`='js')";
    if (false === ($query = $database->query($SQL))) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    if ($query->numRows() > 0) {
        while (false !== ($droplep = $query->fetchRow(MYSQL_ASSOC))) {
            // use the module_directory if no path is set ...
            $directory = (!empty($droplep['file_path'])) ? $droplep['file_path'] : 'modules/'.$droplep['module_directory'];
            $file = $lhd->sanitizePath($directory.'/'.$droplep['file_name']);
            if (file_exists(WB_PATH.'/'.$file)) {
                $options = unserialize($droplep['options']);
                
                if (isset($options['POST_ID']) && !defined('POST_ID')) continue;
                if (isset($options['TOPIC_ID']) && !defined('TOPIC_ID')) continue;
                
                if (!droplep_exists($droplep['register_name'], $page_id, $options)) {
                    // the DropLEP does no longer exists at the $page_id, so remove it!
                    if (!$database->query("DELETE FROM `$table` WHERE `id`='".$droplep['id']."'")) {
                        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
                    }
                }
                if ($droplep['file_type'] == 'css') {
                    // add the CSS file to the global $HEADERS
                    $HEADERS['frontend']['css'][] = array(    
                        'media' => 'all',
                        'file' => $file
                    );
                }
                else {
                    // add the JS file to the global $HEADERS
                    $HEADERS['frontend']['js'][] = $file;
                }
            }
            else {
                // if the file does not exists unregister the DropLEP to avoid overhead!
                if (!$database->query("DELETE FROM `$table` WHERE `id`='".$droplep['id']."'")) {
                    trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
                }
            }
        }
    }
    return true;
} // get_droplep_headers()

/**
 * Check for individual page titles from the addons NEWS, TOPICs and for registered
 * Addons - return an empty string or individual title
 * 
 * @param integer $page_id
 * @return string - title on success or empty string if search fail
 */
function get_addon_page_title($page_id) {
    global $database;
    
    if (defined('POST_ID')) {
        // special handling for the NEWS module
        $table = TABLE_PREFIX.'mod_news_posts';
        $SQL = "SELECT `title` FROM `$table` WHERE `post_id`='".POST_ID."'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $result = $query->fetchRow(MYSQL_ASSOC);
            return $result['title'];
        }
    }
    elseif (defined('TOPIC_ID')) {
        // special handling for the TOPICS module
        $table = TABLE_PREFIX.'mod_topics';
        $SQL = "SELECT `title` FROM `$table` WHERE `topic_id`='".TOPIC_ID."'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $result = $query->fetchRow(MYSQL_ASSOC);
            return $result['title'];
        }
    }
    else {
        // check for addons which will set the page title
        $table = TABLE_PREFIX.'pages_load';
        $SQL = "SELECT `id`, `module_directory` FROM `$table` WHERE `register_type`='addon' AND `file_type`='title'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $addon = $query->fetchRow(MYSQL_ASSOC);
            $file = WB_PATH.'/modules/'.$addon['module_directory'].'/headers.load.php';
            if (file_exists($file)) {
                include_once $file;
                $function = $addon['module_directory'].'_get_page_title';
                if (function_exists($function)) {
                    // return individual page title
                    return call_user_func($function, $page_id);
                }
                else {
                    // function does not exists - unregister the addon!
                    unregister_addon_header($page_id, $addon['module_directory'], 'title');
                    return '';
                }
            }
            else {
                // function does not exists - unregister the addon!
                unregister_addon_header($page_id, $addon['module_directory'], 'title');
                return '';
            }
        }
    }    
    return '';    
} // get_addon_page_title()


/**
 * Check for individual page description from the addons NEWS, TOPICs and for 
 * registered Addons - return an empty string or individual description
 * 
 * @param integer $page_id
 * @return string - title on success or empty string if search fail
 */
function get_addon_page_description($page_id) {
    global $database;
    
    if (defined('POST_ID')) {
        $table = TABLE_PREFIX.'mod_news_posts';
        $SQL = "SELECT `content_short` FROM `$table` WHERE `post_id`='".POST_ID."'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $result = $query->fetchRow(MYSQL_ASSOC);
            return strip_tags($result['content_short']);
        }
    }
    elseif (defined('TOPIC_ID')) {
        $table = TABLE_PREFIX.'mod_topics';
        $SQL = "SELECT `description` FROM `$table` WHERE `topic_id`='".TOPIC_ID."'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $result = $query->fetchRow(MYSQL_ASSOC);
            return $result['description'];
        }
    }
    else {
        // check for addons which will set the page description
        $table = TABLE_PREFIX.'pages_load';
        $SQL = "SELECT `id`, `module_directory` FROM `$table` WHERE `register_type`='addon' AND `file_type`='description'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $addon = $query->fetchRow(MYSQL_ASSOC);
            $file = WB_PATH.'/modules/'.$addon['module_directory'].'/headers.load.php';
            if (file_exists($file)) {
                include_once $file;
                $function = $addon['module_directory'].'_get_page_description';
                if (function_exists($function)) {
                    // return individual page description
                    return call_user_func($function, $page_id);
                }
                else {
                    // function does not exists - unregister the addon!
                    unregister_addon_header($page_id, $addon['module_directory'], 'description');
                    return '';
                }
            }
            else {
                // function does not exists - unregister the addon!
                unregister_addon_header($page_id, $addon['module_directory'], 'description');
                return '';
            }
        }
    }
    return '';
} // get_addon_page_description()

/**
 * Check for individual page keywords from the addon TOPICs and for
 * registered Addons - return an empty string or individual keywords
 *
 * @param integer $page_id
 * @return string - keywords on success or empty string if search fail
 */
function get_addon_page_keywords($page_id) {
    global $database;

    if (defined('TOPIC_ID')) {
        $table = TABLE_PREFIX.'mod_topics';
        $SQL = "SELECT `keywords` FROM `$table` WHERE `topic_id`='".TOPIC_ID."'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $result = $query->fetchRow(MYSQL_ASSOC);
            return $result['keywords'];
        }
    }
    else {
        // check for addons which will set the page description
        $table = TABLE_PREFIX.'pages_load';
        $SQL = "SELECT `id`, `module_directory` FROM `$table` WHERE `register_type`='addon' AND `file_type`='keywords'";
        if (false === ($query = $database->query($SQL))) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
        if ($query->numRows() > 0) {
            $addon = $query->fetchRow(MYSQL_ASSOC);
            $file = WB_PATH.'/modules/'.$addon['module_directory'].'/headers.load.php';
            if (file_exists($file)) {
                include_once $file;
                $function = $addon['module_directory'].'_get_page_keywords';
                if (function_exists($function)) {
                    // return individual page keywords
                    return call_user_func($function, $page_id);
                }
                else {
                    // function does not exists - unregister the addon!
                    unregister_addon_header($page_id, $addon['module_directory'], 'keywords');
                    return '';
                }
            }
            else {
                // function does not exists - unregister the addon!
                unregister_addon_header($page_id, $addon['module_directory'], 'keywords');
                return '';
            }
        }
    }
    return '';
} // get_addon_page_keywords()


/**
 * Register a addon in $module_directory for sending 'title', 'description' or
 * 'keywords'
 * 
 * @param integer $page_id
 * @param string $module_name
 * @param string $module_directory
 * @param string $header_type - 'title', 'description', 'keywords'
 */
function register_addon_header($page_id, $module_name, $module_directory, $header_type) {
    global $database;
    
    if (is_registered_addon_header($page_id, $module_directory, $header_type)) return true;
    
    $table = TABLE_PREFIX.'pages_load';
    $SQL = "INSERT INTO `$table` (page_id, file_type, module_directory, register_name, register_type) ".
    "VALUES ('$page_id', '$header_type', '$module_directory', '$module_name', 'addon')";
    if (!$database->query($SQL)) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    return true;
} // register_addon_header()

/**
 * Unregister the addon from $module_directory from sending 'title', 'description'
 * or 'keywords'
 * 
 * @param integer $page_id
 * @param string $module_directory
 * @param string $header_type - 'title', 'description', 'keywords'
 */
function unregister_addon_header($page_id, $module_directory, $header_type) {
    global $database;
    if (is_registered_addon_header($page_id, $module_directory, $header_type)) {
        $table = TABLE_PREFIX.'pages_load';
        $SQL = "DELETE FROM `$table` WHERE `page_id`='$page_id' AND ".
        "`module_directory`='$module_directory' AND `file_type`='$header_type'";
        if (!$database->query($SQL)) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
        }
    }
    return true;
}

/**
 * Check if the addon in $module_directory is registered for sending title, 
 * description or keywords
 * 
 * @param integer $page_id
 * @param string $module_directory
 * @param string $header_type - may be 'title', 'description', 'keywords'
 */
function is_registered_addon_header($page_id, $module_directory, $header_type) {
    global $database;
    
    $table = TABLE_PREFIX.'pages_load';
    $SQL = "SELECT `id` FROM `$table` WHERE `page_id`='$page_id' AND ".
        "`file_type`='$header_type' AND `module_directory`='$module_directory'";
    if (false === ($query = $database->query($SQL))) {
        trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()));
    }
    if ($query->numRows() > 0) return true;
    return false;
} // is_registered_addon_header()