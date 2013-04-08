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
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         droplets
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

function do_eval( $_x_codedata, $_x_varlist, &$content )
{
    extract( $_x_varlist, EXTR_SKIP );
    return ( eval( $_x_codedata ) );
}

function processDroplets( &$content )
{
    // collect all droplets from document
    $droplet_tags = array();
    $droplet_replacements = array();
    if ( preg_match_all( '/\[\[(.*?)\]\]/', $content, $found_droplets ) )
    {
        foreach ( $found_droplets[ 1 ] as $droplet )
        {
            if ( array_key_exists( '[[' . $droplet . ']]', $droplet_tags ) == false )
            {
                // go in if same droplet with same arguments is not processed already
                $varlist = array();
                // split each droplet command into droplet_name and request_string
                $tmp            = preg_split( '/\?/', $droplet, 2 );
                $droplet_name   = $tmp[ 0 ];
                $request_string = ( isset( $tmp[ 1 ] ) ? $tmp[ 1 ] : '' );
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
                            $varlist[ $variable ] = htmlentities( $value, ENT_COMPAT, DEFAULT_CHARSET );
                        }
                    }
                }
                else
                {
                    // no arguments given, so
                    $droplet_name = $droplet;
                }
                // request the droplet code from database
                $sql      = 'SELECT `code` FROM `' . CAT_TABLE_PREFIX . 'mod_droplets` WHERE `name` LIKE "' . $droplet_name . '" AND `active` = 1';
// ----- ARGH!!! -----
                $codedata = $GLOBALS[ 'database' ]->get_one( $sql );
// ----- ARGH!!! -----
                if ( !is_null( $codedata ) )
                {
                    $newvalue = do_eval( $codedata, $varlist, $content );
                    // check returnvalue (must be a string of 1 char at least or (bool)true
                    if ( $newvalue == '' && $newvalue !== true )
                    {
                        if ( DEBUG === true )
                        {
                            $newvalue = '<span class="mod_droplets_err">Error in: ' . $droplet . ', no valid returnvalue.</span>';
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
                    // push droplet-tag and it's replacement into Search/Replace array after executing only
                }
                else
                {
                    // just remove droplet placeholder if no code was found
                    if ( DEBUG === true )
                    {
                        $newvalue = '<span class="mod_droplets_err">No such droplet: ' . $droplet . '</span>';
                    }
                    else
                    {
                        $newvalue = true;
                    }
                }
                $droplet_tags[]         = '[[' . $droplet . ']]';
                $droplet_replacements[] = $newvalue;
            }
        }    // End foreach( $found_droplets[1] as $droplet )
        // replace each Droplet-Tag with coresponding $newvalue
        $content = str_replace( $droplet_tags, $droplet_replacements, $content );
    }
    // returns TRUE if droplets found in content, FALSE if not
    return ( count( $droplet_tags ) != 0 );
}   // end function processDroplets()

function evalDroplets( &$content, $max_loops = 3 )
{
    $max_loops = ( (int) $max_loops = 0 ? 3 : (int) $max_loops );
    while ( ( processDroplets( $content ) == true ) && ( $max_loops > 0 ) )
    { 
        $max_loops--;
    }
    return $content;
}   // end function evalDroplets()

?>