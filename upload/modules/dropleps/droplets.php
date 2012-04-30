<?php
/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          dropleps
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 * @reformatted     2011-12-30
 *
 * This code was originally created by Ruud Eisinga (Ruud) and John (PCWacht)
 * for Website Baker CMS and adapted for LEPTON in 2011
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

function do_eval( $_x_codedata, $_x_varlist, &$wb_page_data )
{
    extract( $_x_varlist, EXTR_SKIP );
    return ( eval( $_x_codedata ) );
}

function processDroplets( &$wb_page_data )
{
    // collect all droplets from document
		$droplet_tags = array();
		$droplet_replacements = array();
    if ( preg_match_all( '/\[\[(.*?)\]\]/', $wb_page_data, $found_droplets ) )
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
                $sql      = 'SELECT `code` FROM `' . TABLE_PREFIX . 'mod_droplets` WHERE `name` LIKE "' . $droplet_name . '" AND `active` = 1';
                $codedata = $GLOBALS[ 'database' ]->get_one( $sql );
                if ( !is_null( $codedata ) )
                {
                    $newvalue = do_eval( $codedata, $varlist, $wb_page_data );
                    // check returnvalue (must be a string of 1 char at least or (bool)true
                    if ( $newvalue == '' && $newvalue !== true )
						{
                        if ( DEBUG === true )
							{
                            $newvalue = '<span class="mod_dropleps_err">Error in: ' . $droplet . ', no valid returnvalue.</span>';
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
                        $newvalue = '<span class="mod_dropleps_err">No such droplet: ' . $droplet . '</span>';
						}
						else
						{
							$newvalue = true;
						}
					}
                $droplet_tags[]         = '[[' . $droplet . ']]';
					$droplet_replacements[] = $newvalue;
				}
			}	// End foreach( $found_droplets[1] as $droplet )
        // replace each Droplet-Tag with coresponding $newvalue
        $wb_page_data = str_replace( $droplet_tags, $droplet_replacements, $wb_page_data );
	}
    // returns TRUE if droplets found in content, FALSE if not
    return ( count( $droplet_tags ) != 0 );
}

function evalDroplets( &$wb_page_data, $max_loops = 3 )
{
    $max_loops = ( (int) $max_loops = 0 ? 3 : (int) $max_loops );
    while ( ( processDroplets( $wb_page_data ) == true ) && ( $max_loops > 0 ) )
		{ 
			$max_loops--;
		}
		return $wb_page_data;
}

?>