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
 *   @category        CAT_Modules
 *   @package         blackcatFilter
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

global $_bc_filter_js, $_bc_filter_onload;
$_bc_filter_js     = array();
$_bc_filter_onload = array();

/**
 * execute registered filters
 *
 * @param  reference $content
 * @return void
 **/
function executeFilters(&$content)
{
    // get active filters
    $res = CAT_Helper_Page::getInstance()->db()->query(sprintf(
        'SELECT * FROM %smod_filter WHERE filter_active="Y"',
        CAT_TABLE_PREFIX
    ));

    if(is_object($res) && $res->numRows())
    {
        $filter = array();
        while( false !== ( $row = $res->fetchRow(MYSQL_ASSOC) ) )
        {
            $filter[] = $row;
        }
        foreach($filter as $f)
        {
            if($f['filter_code']=='' && $f['module_name']!='')
            {
                $inc_file = sanitize_path(CAT_PATH.'/modules/'.$f['module_name'].'/filter/'.$f['filter_name'].'.php');
                if(file_exists($inc_file))
                {
                    include_once $inc_file;
                    $f['filter_name']($content);
                }
            }
        }
    }

    // if we have some JS registered...
    global $_bc_filter_js;
    if(count($_bc_filter_js))
    {
        $js  = array();
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($content);
        $h   = $dom->getElementsByTagName('head')->item(0);
        if($h)
        {
        foreach($_bc_filter_js as $file)
        {
            $element = $dom->createElement('script');
            // Creating an empty text node forces <script></script>
            $element->appendChild ($dom->createTextNode (''));
            $element->setAttribute( 'type', 'text/javascript' );
            $element->setAttribute( 'src', $file );
            $h->appendChild($element);
        }
        $content = $dom->saveHTML();
    }
    }

    // onload events
    global $_bc_filter_onload;
    if(count($_bc_filter_onload))
    {
        $attach   = NULL;
        $listener = NULL;
        foreach($_bc_filter_onload as $item)
        {
             $attach   .= "    window.attachEvent('onload','$item');\n";
             $listener .= "    window.addEventListener('DOMContentLoaded',$item,false);\n";
        }
        $h   = $dom->getElementsByTagName('body')->item(0);
        $element = $dom->createElement('script');
        $element->appendChild ($dom->createTextNode("\nif(window.attachEvent) {\n".$attach."\n} else {\n".$listener."\n}\n"));
        $element->setAttribute( 'type', 'text/javascript' );
        $h->appendChild($element);
        $content = $dom->saveHTML();
    }
}   // end function executeFilters()

/**
 * register a JS file
 *
 * @access public
 * @param  string  $file     - file URI
 * @param  string  $position - OPTIONAL 'body'|'head' (default 'head')
 * @return void
 **/
function register_filter_js($file,$position='head')
{
    global $_bc_filter_js;
    if ( ! in_array($file,$_bc_filter_js) )
        $_bc_filter_js[] = $file;
}   // end function register_filter_js()

/**
 * register an onload event (will be added to <body>)
 *
 * @access public
 * @param  string  $code - onload content
 * @return void
 **/
function register_filter_onload($code)
{
    global $_bc_filter_onload;
    if ( ! in_array($code,$_bc_filter_onload) )
        $_bc_filter_onload[] = $code;
}   // end function register_filter_onload()


/**
 * allows modules to register output filters
 *
 * This method can only be called in BACKEND context!
 * It needs 'modules_install' permissions!
 *
 * @param  string  $filter_name
 * @param  string  $module_directory
 * @param  string  $filter_description - optional
 * @param  string  $filter_code        - optional
 * @return boolean
 **/
function register_filter($filter_name,$module_directory,$filter_description=NULL,$filter_code=NULL)
{
    $backend = CAT_Backend::getInstance('addons','modules_install');
    $SQL     = sprintf("SELECT * FROM `%smod_output_filter` WHERE module_name='%s'", CAT_TABLE_PREFIX, $module_directory);
    if (false !== ($data = $backend->db()->get_one($SQL, MYSQL_ASSOC)))
    {
        if (empty($data))
        {
            $SQL = sprintf(
                "INSERT INTO `%smod_filter` SET
                filter_name='%s', module_name='%s', filter_description='%s',
                filter_code='%s', filter_active='Y'",
            CAT_TABLE_PREFIX, $filter_name, $module_directory, $filter_description, $filter_code
            );
            if (!$backend->db()->query($SQL))
            {
                trigger_error(sprintf("[%s] %s", __FUNCTION__, $backend->db()->get_error()));
                return false;
            }
        }
    }
    else {
        trigger_error(sprintf("[%s] %s", __FUNCTION__, $backend->db()->get_error()));
        return false;
    }
    return true;
}   // end function register_filter()

/**
 * Unregister an output filter
 *
 * @param  string  $filter_name
 * @param  string  $module_directory
 * @return boolean
 */
function unregister_filter($filter_name, $module_directory)
{
    $backend = CAT_Backend::getInstance('addons','modules_uninstall');
    $SQL     = sprintf(
        "DELETE FROM `%smod_filter` WHERE filter_name='%s' AND module_name='%s'",
        CAT_TABLE_PREFIX, $filter_name, $module_directory
    );
    if (!$backend->db()->query($SQL)) {
        trigger_error(sprintf('[%s] %s', __FUNCTION__, $backend->db()->get_error()));
        return false;
    }
    return true;
}   // end function unregister_filter()

/**
 * Check if a output filter is already registered
 *
 * @param string $filter_name
 * @param string $module_directory
 * @return boolean
 */
function is_filter_registered($filter_name, $module_directory)
{
    $backend = CAT_Backend::getInstance('addons', 'modules_install');
    $SQL = "SELECT `filter_name` FROM `".CAT_TABLE_PREFIX."mod_filter` WHERE ".
        "`filter_name`='$filter_name' AND `module_name`='$module_directory'";
    if (false === ($name = $backend->db()->get_one($SQL, MYSQL_ASSOC))) {
        trigger_error(sprintf('[%s] %s', __FUNCTION__, $backend->db()->get_error()));
        return false;
    }
    return ($name == $filter_name);
}
