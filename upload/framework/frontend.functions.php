<?php
/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if ( defined( 'CAT_PATH' ) )
{
    include( CAT_PATH . '/framework/class.secure.php' );
}
else
{
    $oneback = "../";
    $root    = $oneback;
    $level = 1;
    while ( ( $level < 10 ) && ( !file_exists( $root . '/framework/class.secure.php' ) ) )
    {
        $root .= $oneback;
        $level += 1;
    }
    if ( file_exists( $root . '/framework/class.secure.php' ) )
    {
        include( $root . '/framework/class.secure.php' );
    }
    else
    {
        trigger_error( sprintf( "[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER[ 'SCRIPT_NAME' ] ), E_USER_ERROR );
    }
}
// end include class.secure.php

// set debug level here; see CAT_Helper_KLogger for available levels
$debug_level  = 8;

// references to objects and variables that changed their names

$admin =& $wb;

$default_link =& $wb->default_link;

if ( ! class_exists( 'LEPTON_Sections' ) )
{
    @require_once dirname(__FILE__).'/LEPTON/Sections.php';
}
global $sec_h;
$sec_h = new LEPTON_Sections();

$logger = $wb->get_helper('KLogger', CAT_PATH.'/temp', $debug_level );

/**
 *  2011-08-17  Aldus  It's absolutly not clear why on earth thees following vars are double here.
 *            As we can use them directly within the $wb instance at all!
 */
$page_trail =& $wb->page_trail;
$page_description =& $wb->page_description;
$page_keywords =& $wb->page_keywords;
$page_link =& $wb->link;

$include_head_link_css = '';
$include_body_links    = '';
$include_head_links    = '';
// workout to included frontend.css, fronten.js and frontend_body.js in snippets
$query                 = "SELECT directory FROM " . CAT_TABLE_PREFIX . "addons WHERE type = 'module' AND function = 'snippet'";
$query_result          = $database->query( $query );
if ( $query_result->numRows() > 0 )
{
    while ( $row = $query_result->fetchRow() )
    {
        $module_dir = $row[ 'directory' ];
        if ( file_exists( CAT_PATH . '/modules/' . $module_dir . '/include.php' ) )
        {
            include( CAT_PATH . '/modules/' . $module_dir . '/include.php' );
            /* check if frontend.css file needs to be included into the <head></head> of index.php
             */
            if ( file_exists( CAT_PATH . '/modules/' . $module_dir . '/frontend.css' ) )
            {
                $include_head_link_css .= '<link href="' . LEPTON_URL . '/modules/' . $module_dir . '/frontend.css"';
                $include_head_link_css .= ' rel="stylesheet" type="text/css" media="screen" />' . "\n";
                $include_head_file = 'frontend.css';
            }
            // check if frontend.js file needs to be included into the <body></body> of index.php
            if ( file_exists( CAT_PATH . '/modules/' . $module_dir . '/frontend.js' ) )
            {
                $include_head_links .= '<script src="' . LEPTON_URL . '/modules/' . $module_dir . '/frontend.js" type="text/javascript"></script>' . "\n";
                $include_head_file = 'frontend.js';
            }
            // check if frontend_body.js file needs to be included into the <body></body> of index.php
            if ( file_exists( CAT_PATH . '/modules/' . $module_dir . '/frontend_body.js' ) )
            {
                $include_body_links .= '<script src="' . LEPTON_URL . '/modules/' . $module_dir . '/frontend_body.js" type="text/javascript"></script>' . "\n";
                $include_body_file = 'frontend_body.js';
            }
        }
    }
}

// Frontend functions
if ( !function_exists( 'page_link' ) )
{
    function page_link( $link )
    {
        global $wb;
        return $wb->page_link( $link );
    }
}

if ( !function_exists( 'get_page_link' ) )
{
    function get_page_link( $id )
    {
        global $database;
        // Get link
        $sql  = 'SELECT `link` FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $id;
        $link = $database->get_one( $sql );
        return $link;
    }
}

//function to highlight search results
if ( !function_exists( 'search_highlight' ) )
{
    function search_highlight($foo = '', $arr_string = array()) {
        global $database;
        
        require_once( CAT_PATH . '/framework/functions.php' );
        
        static $string_ul_umlaut = false;
        static $string_ul_regex = false;
        if ($string_ul_umlaut === false || $string_ul_regex === false) {
            require_once CAT_PATH. '/modules/'.SEARCH_LIBRARY.'/search.convert.php';        
        }
        $foo = entities_to_umlauts( $foo, 'UTF-8' );
        array_walk( $arr_string, create_function( '&$v,$k', '$v = preg_quote($v, \'~\');' ) );
        $search_string = implode( "|", $arr_string );
        $string        = str_replace( $string_ul_umlaut, $string_ul_regex, $search_string );
        // the highlighting
        // match $string, but not inside <style>...</style>, <script>...</script>, <!--...--> or HTML-Tags
        // Also droplet tags are now excluded from highlighting.
        // split $string into pieces - "cut away" styles, scripts, comments, HTML-tags and eMail-addresses
        // we have to cut <pre> and <code> as well.
        // for HTML-Tags use <(?:[^<]|<.*>)*> which will match strings like <input ... value="<b>value</b>" >
        $matches       = preg_split( "~(\[\[.*\]\]|<style.*</style>|<script.*</script>|<pre.*</pre>|<code.*</code>|<!--.*-->|<(?:[^<]|<.*>)*>|\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,8}\b)~iUs", $foo, -1, ( PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) );
        if ( is_array( $matches ) && $matches != array ())
        {
            $foo = "";
            foreach ( $matches as $match )
            {
                if ( $match{0} != "<" && !preg_match( '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,8}$/i', $match ) && !preg_match( '~\[\[.*\]\]~', $match ) )
                {
                    $match = str_replace( array(
                         '&lt;',
                        '&gt;',
                        '&amp;',
                        '&quot;',
                        '&#039;',
                        '&nbsp;' 
                    ), array(
                         '<',
                        '>',
                        '&',
                        '"',
                        '\'',
                        "\xC2\xA0" 
                    ), $match );
                    $match = preg_replace( '~(' . $string . ')~ui', '_span class=_highlight__$1_/span_', $match );
                    $match = str_replace( array(
                         '&',
                        '<',
                        '>',
                        '"',
                        '\'',
                        "\xC2\xA0" 
                    ), array(
                         '&amp;',
                        '&lt;',
                        '&gt;',
                        '&quot;',
                        '&#039;',
                        '&nbsp;' 
                    ), $match );
                    $match = str_replace( array(
                         '_span class=_highlight__',
                        '_/span_' 
                    ), array(
                         '<span class="highlight">',
                        '</span>' 
                    ), $match );
                }
                $foo .= $match;
            }
        }
        
        if ( DEFAULT_CHARSET != 'utf-8' )
        {
            $foo = umlauts_to_entities( $foo, 'UTF-8' );
        }
        return $foo;
    }
}


/**
 *  Function to get or display the current page title
 *
 *  @param  string  Spacer between the items; default is "-"
 *  @param  string  The template-string itself
 *  @param  boolean  The return-mode: 'true' will return the value, false will direct echo the string
 *
 */
if ( !function_exists( 'page_title' ) )
{
    function page_title( $spacer = ' - ', $template = '[WEBSITE_TITLE][SPACER][PAGE_TITLE]', $mode = false )
    {
        $vars   = array(
             '[WEBSITE_TITLE]',
            '[PAGE_TITLE]',
            '[MENU_TITLE]',
            '[SPACER]' 
        );
        $values = array(
             WEBSITE_TITLE,
            PAGE_TITLE,
            MENU_TITLE,
            $spacer 
        );
        $temp   = str_replace( $vars, $values, $template );
        if ( true === $mode )
        {
            return $temp;
        }
        else
        {
            echo $temp;
            return true;
        }
    }
}

/**
 *  Function to get the current page description
 *
 *  @param  bool  false == direct echo of the page-description
 *          true == return the page-description
 *
 */
if ( !function_exists( 'page_description' ) )
{
    function page_description( $mode = false )
    {
        global $wb;
        $temp = ( $wb->page_description != '' ) ? $wb->page_description : WEBSITE_DESCRIPTION;
        if ( true === $mode )
        {
            return $temp;
        }
        else
        {
            echo $temp;
            return true;
        }
    }
}

/**
 *  Function to get the page keywords
 *
 *  @param  bool  mode: true for returning the value, false for direct echo
 *
 */
if ( !function_exists( 'page_keywords' ) )
{
    function page_keywords( $mode = false )
    {
        global $wb;
        $temp = ( $wb->page_keywords != '' ) ? $wb->page_keywords : WEBSITE_KEYWORDS;
        if ( true === $mode )
        {
            return $temp;
        }
        else
        {
            echo $temp;
            return true;
        }
    }
}

/**
 *  Function to get the page header
 *
 *  @param  bool  Return-Mode: true returns the value, false will direct output the string.
 *
 */
if ( !function_exists( 'page_header' ) )
{
    function page_header( $mode = false )
    {
        if ( true === $mode )
        {
            return WEBSITE_HEADER;
        }
        else
        {
            echo WEBSITE_HEADER;
            return true;
        }
    }
}

/**
 *  Function to get the page footer
 *
 *  @param  string  A date-format for the processtime.
 *  @param  bool  Return-mode: true returns the value, false will direct output the string.
 *
 */
if ( !function_exists( 'page_footer' ) )
{
    function page_footer( $date_format = 'Y', $mode = false )
    {
        global $starttime;
        $vars        = array(
             '[YEAR]',
            '[PROCESS_TIME]' 
        );
        $processtime = array_sum( explode( " ", microtime() ) ) - $starttime;
        $values      = array(
             date( $date_format ),
            $processtime 
        );
        $temp        = str_replace( $vars, $values, WEBSITE_FOOTER );
        if ( true === $mode )
        {
            return $temp;
        }
        else
        {
            echo $temp;
            return true;
        }
    }
}

function language_menu()
{
    global $wb, $parser;
    if (defined('PAGE_LANGUAGES') && PAGE_LANGUAGES)
    {
        if ( ! class_exists( 'LEPTON_Pages', false ) )
        {
	        include sanitize_path( dirname(__FILE__).'/CAT/Pages.php' );
		}
        $pages = LEPTON_Pages::getInstance();
        $items = $pages->get_linked_by_language(PAGE_ID);
    }
    if( isset($items) && count($items) )
    {
        // initialize template search path
        $parser->setPath(CAT_PATH.'/templates/'.TEMPLATE.'/templates');
        $parser->setFallbackPath(CAT_THEME_PATH.'/templates');
        if($parser->hasTemplate('languages.lte'))
        {
            $parser->output('languages.lte', array('items'=>$items));
        }
    }
}

function page_content( $block = 1 ) { return wb::$pg->getPageContent($block); }


?>