<?php
/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if ( defined( 'LEPTON_PATH' ) )
{
    include( LEPTON_PATH . '/framework/class.secure.php' );
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

// set debug level here; see LEPTON_Helper_KLogger for available levels
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

$logger = $wb->get_helper('KLogger', LEPTON_PATH.'/temp', $debug_level );

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
$query                 = "SELECT directory FROM " . TABLE_PREFIX . "addons WHERE type = 'module' AND function = 'snippet'";
$query_result          = $database->query( $query );
if ( $query_result->numRows() > 0 )
{
    while ( $row = $query_result->fetchRow() )
    {
        $module_dir = $row[ 'directory' ];
        if ( file_exists( LEPTON_PATH . '/modules/' . $module_dir . '/include.php' ) )
        {
            include( LEPTON_PATH . '/modules/' . $module_dir . '/include.php' );
            /* check if frontend.css file needs to be included into the <head></head> of index.php
             */
            if ( file_exists( LEPTON_PATH . '/modules/' . $module_dir . '/frontend.css' ) )
            {
                $include_head_link_css .= '<link href="' . LEPTON_URL . '/modules/' . $module_dir . '/frontend.css"';
                $include_head_link_css .= ' rel="stylesheet" type="text/css" media="screen" />' . "\n";
                $include_head_file = 'frontend.css';
            }
            // check if frontend.js file needs to be included into the <body></body> of index.php
            if ( file_exists( LEPTON_PATH . '/modules/' . $module_dir . '/frontend.js' ) )
            {
                $include_head_links .= '<script src="' . LEPTON_URL . '/modules/' . $module_dir . '/frontend.js" type="text/javascript"></script>' . "\n";
                $include_head_file = 'frontend.js';
            }
            // check if frontend_body.js file needs to be included into the <body></body> of index.php
            if ( file_exists( LEPTON_PATH . '/modules/' . $module_dir . '/frontend_body.js' ) )
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
        $sql  = 'SELECT `link` FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $id;
        $link = $database->get_one( $sql );
        return $link;
    }
}

//function to highlight search results
if ( !function_exists( 'search_highlight' ) )
{
    function search_highlight($foo = '', $arr_string = array()) {
        global $database;
        
        require_once( LEPTON_PATH . '/framework/functions.php' );
        
        static $string_ul_umlaut = false;
        static $string_ul_regex = false;
        if ($string_ul_umlaut === false || $string_ul_regex === false) {
            require_once LEPTON_PATH. '/modules/'.SEARCH_LIBRARY.'/search.convert.php';        
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


if ( !function_exists( 'page_content' ) )
{
    function page_content( $block = 1 )
    {
        // Get outside objects
        global $TEXT, $MENU, $HEADING, $MESSAGE;
        global $logger;
        global $globals;
        global $database;
        global $wb;
        global $sec_h;
        $admin =& $wb;

		$logger->logDebug( sprintf( 'getting content for block [%s]', $block ) );
		
        if ( $wb->page_access_denied == true )
        {
            $logger->logDebug( 'Access denied' );
            echo $MESSAGE[ 'FRONTEND_SORRY_NO_VIEWING_PERMISSIONS' ];
            return;
        }
        if ( $sec_h->has_active_sections($wb->page_id) === false )
        {
            $logger->logDebug( 'no active sections found' );
            echo $MESSAGE[ 'FRONTEND_SORRY_NO_ACTIVE_SECTIONS' ];
            return;
        }
        
        if ( isset( $globals ) and is_array( $globals ) )
        {
            $logger->logDebug( 'setting globals', $globals );
            foreach ( $globals as $global_name )
            {
                global $$global_name;
            }
        }
        // Make sure block is numeric
        if ( !is_numeric( $block ) )
        {
            $block = 1;
        }
        // Include page content
        if ( !defined( 'PAGE_CONTENT' ) or $block != 1 )
        {
            $page_id               = intval( $wb->page_id );
            // set session variable to save page_id only if PAGE_CONTENT is empty
            $_SESSION[ 'PAGE_ID' ] = !isset( $_SESSION[ 'PAGE_ID' ] ) ? $page_id : $_SESSION[ 'PAGE_ID' ];
            // set to new value if page_id changed and not 0
            if ( ( $page_id != 0 ) && ( $_SESSION[ 'PAGE_ID' ] <> $page_id ) )
            {
                $_SESSION[ 'PAGE_ID' ] = $page_id;
            }
            // get sections
            $sections = $sec_h->get_active_sections( PAGE_ID, $block );
            // no active sections found, so...
            if ( !is_array( $sections ) || !count( $sections ) )
            {
                $logger->logDebug( 'no active sections found' );
                // ...do we have default block content?
                if ( $wb->default_block_content == 'none' )
                {
                    $logger->logDebug( 'no default content found' );
                    return;
                }
                if ( is_numeric( $wb->default_block_content ) )
                {
                    $logger->logDebug( 'getting default content from default block' );
                    // set page id to default block and get sections
                    $page_id  = $wb->default_block_content;
                    $sections = $sec_h->get_active_sections( $page_id, $block );
                }
                else
                {
                    $logger->logDebug( 'getting default content from default page' );
                    // set page id to default page and get sections
                    $page_id  = $wb->default_page_id;
                    $sections = $sec_h->get_active_sections( $page_id, $block );
                }
                // still no sections?
                if ( !is_array( $sections ) || !count( $sections ) )
                {
                    $logger->logDebug( 'still no sections, return undef' );
                    return;
                }
            }
            // Loop through them and include their module file
            foreach ( $sections as $section )
            {
                $logger->logDebug( 'sections for this block', $sections );
                $section_id = $section[ 'section_id' ];
                $module     = $section[ 'module' ];
                // make a anchor for every section.
                if ( defined( 'SEC_ANCHOR' ) && SEC_ANCHOR != '' )
                {
                    echo '<a class="section_anchor" id="' . SEC_ANCHOR . $section_id . '"></a>';
                }
                // check if module exists - feature: write in errorlog
                if ( file_exists( LEPTON_PATH . '/modules/' . $module . '/view.php' ) )
                {
                    // fetch content -- this is where to place possible output-filters (before highlighting)
                    // fetch original content
                    ob_start();
                    require( LEPTON_PATH . '/modules/' . $module . '/view.php' );
                    $content = ob_get_contents();
                    ob_end_clean();
                }
                else
                {
                    continue;
                }
                
                // highlights searchresults
                if ( isset( $_GET[ 'searchresult' ] ) && is_numeric( $_GET[ 'searchresult' ] ) && !isset( $_GET[ 'nohighlight' ] ) && isset( $_GET[ 'sstring' ] ) && !empty( $_GET[ 'sstring' ] ) )
                {
                    $arr_string = explode( " ", $_GET[ 'sstring' ] );
                    if ( $_GET[ 'searchresult' ] == 2 )
                    {
                        // exact match
                        $arr_string[ 0 ] = str_replace( "_", " ", $arr_string[ 0 ] );
                    }
                    echo search_highlight( $content, $arr_string );
                }
                else
                {
                    echo $content;
                }
            }
        }
        else
        {
            require( PAGE_CONTENT );
        }
    }
}   // if ( !function_exists( 'page_content' ) )

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
	        include sanitize_path( dirname(__FILE__).'/LEPTON/Pages.php' );
		}
        $pages = new LEPTON_Pages();
        $items = $pages->get_linked_by_language(PAGE_ID);
    }
    if( isset($items) && count($items) )
    {
        // initialize template search path
        $parser->setPath(LEPTON_PATH.'/templates/'.TEMPLATE.'/templates');
        $parser->setFallbackPath(THEME_PATH.'/templates');
        if($parser->hasTemplate('languages.lte'))
        {
            $parser->output('languages.lte', array('items'=>$items));
        }
    }
}

function bind_jquery( $file_id = 'jquery' )
{
    /**
     * @deprecated bind_jquery() is deprecated and will be removed in LEPTON 1.3
     */
    trigger_error( 'The function bind_jquery() is deprecated and will be removed in LEPTON 1.3. Please use the function get_page_headers() instead!', E_USER_NOTICE );
}

// Function to add optional module Javascript into the <body> section of the frontend
if ( !function_exists( 'register_frontend_modfiles_body' ) )
{
    function register_frontend_modfiles_body( $file_id = "js" )
    {
        /**
         * @deprecated register_frontend_modfiles_body() is deprecated and will be removed in LEPTON 1.3
         */
        trigger_error( 'The function register_frontend_modfiles_body() is deprecated and will be removed in LEPTON 1.3. Please use the function get_page_footers() instead!', E_USER_NOTICE );
    }
}

// Function to add optional module Javascript or CSS stylesheets into the <head> section of the frontend;
// kept for backward compatibility
if ( !function_exists( 'register_frontend_modfiles' ) )
{
    function register_frontend_modfiles( $file_id = "css" )
    {
        /**
         * @deprecated register_frontend_modfiles() is deprecated and will be removed in LEPTON 1.3
         */
        trigger_error( 'The function register_frontend_modfiles() is deprecated and will be removed in LEPTON 1.3. Please use the function get_page_headers() instead!', E_USER_NOTICE );
        get_page_headers();
        return;
    }
}
?>