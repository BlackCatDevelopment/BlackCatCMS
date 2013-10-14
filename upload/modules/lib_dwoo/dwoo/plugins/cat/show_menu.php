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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_dwoo
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

require_once CAT_PATH.'/framework/functions.php';

function among_constants( $among_array )
{
    $return        = 0;
    if ( strpos($among_array, '+') )
    {
        $array        = explode( '+', $among_array );
        $among_array    = 0;
        foreach( $array as $value )
        {
            $among_array    = $among_array + $value;
        }
    }
    if ( strpos($among_array, '|') !== false  )
    {
        $array        = explode( '|', $among_array );
        foreach($array as $value)
        {
            $return        = $return | $value;
        }
        return $return;
    }
    else return $among_array | 0;
}

function Dwoo_Plugin_show_menu(
        Dwoo $dwoo,
        $aMenu        = 0,
        $aStart       = SM2_ROOT,
        $aMaxLevel    = -1999,
        $aOptions     = SM2_TRIM,
        $aItemOpen    = false,
        $aItemClose   = false,
        $aMenuOpen    = false,
        $aMenuClose   = false,
        $aTopItemOpen = false,
        $aTopMenuOpen = false
    )
{

	if ( CAT_Helper_Page::isMaintenance() && ! CAT_Users::is_root()) return false;

    if (strpos($aOptions, 'SM2_PRETTY') !== false) return "<strong>Error:</strong> show_menu() does not support the SM2_PRETTY Flag!";
    // Set variables to replace strings with constants
    $search_values  = array( 'SM2_ROOT', 'SM2_CURR', 'SM2_ALLMENU', 'SM2_START', 'SM2_MAX', 'SM2_ALLINFO', 'SM2_ALL', 'SM2_TRIM', 'SM2_CRUMB', 'SM2_SIBLING', 'SM2_NUMCLASS', 'SM2_NOCACHE', 'SM2_PRETTY', 'SM2_ESCAPE', 'SM2_NOESCAPE', 'SM2_BUFFER', 'SM2_CURRTREE', 'SM2_SHOWHIDDEN', 'SM2_XHTML_STRICT',  'SM2_NO_TITLE' , 'SM2_ARRAY' );
    $replace_values = array( SM2_ROOT, SM2_CURR, SM2_ALLMENU, SM2_START, SM2_MAX, SM2_ALLINFO, SM2_ALL, SM2_TRIM, SM2_CRUMB, SM2_SIBLING, SM2_NUMCLASS, SM2_NOCACHE, SM2_PRETTY, SM2_ESCAPE, SM2_NOESCAPE, SM2_BUFFER, SM2_CURRTREE, SM2_SHOWHIDDEN, SM2_XHTML_STRICT,  SM2_NO_TITLE, '' );

    // Check if function shall return an array or the menu
    $direct_output  = strpos( $aOptions, 'SM2_ARRAY' ) === false ? true : false;

    // Check if SM2_BUFFER is set, when SM2_ARRAY is set (otherwise simply replace SM2_ARRAY with SM2_BUFFER
	$aOptions		= ( !$direct_output && strpos( $aOptions, 'SM2_BUFFER' ) === false ) ? str_replace( 'SM2_ARRAY', 'SM2_BUFFER', $aOptions ) : $aOptions;

    // Replace all Strings with constants
    $aStart         = str_replace( $search_values, $replace_values, $aStart );
    $aMaxLevel      = str_replace( $search_values, $replace_values, $aMaxLevel );
    $aOptions       = str_replace( $search_values, $replace_values, $aOptions );

    // Among all constants get to bit values
    $Menu           = among_constants( $aMenu );
    $Start          = among_constants( $aStart );
    $MaxLevel       = among_constants( $aMaxLevel );
    $Options        = among_constants( $aOptions );

    if ( $direct_output ) // If direct output simply print show_menu2()
    {
        show_menu2( $Menu, $Start, $MaxLevel, $Options, $aItemOpen, $aItemClose, $aMenuOpen, $aMenuClose, $aTopItemOpen, $aTopMenuOpen );
    }
    else // If SM2_ARRAY is set, the function will return an Array
    {

        $result     = show_menu2( $Menu, $Start, $MaxLevel, $Options );

        $search     = array('</li><li',  '</a><ul',  '</li></ul>',  '</ul></li>',  '</a></li>');
        $replace    = array('</li>|<li', '</a>|<ul', '</li>|</ul>', '</ul>|</li>', '</a>|</li>');
        $result     = str_replace($search, $replace, $result);
        $walk       = explode('|', $result);
        $menu       = array();
        $level      = 0;
        foreach ( $walk as $index => $item )
        {
            trim($item);
            if ($item == '</li>') {
                $menu[] = array('type' => 'link_end', 'level' => $level);
                continue;
            }
            if ($item == '</ul>') {
                $menu[] = array('type' => 'level_end', 'level' => $level);
                $level--;
                continue;
            }
            if (strpos($item, '<ul') !== false) {
                $ul = substr($item, 0, strpos($item, '<li'));
                $level++;
                $link = array();
                $link['type'] = 'level_start';
                $link['level'] = $level;
                
                preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $ul, $attr);
                foreach ($attr as $attributes)
                {
                    foreach ($attributes as $attribut)
                    {
                        if (strpos($attribut, "=") !== false)
                        {
                            list($key, $value) = explode("=", $attribut);
                            $value = trim($value);
                            $value = trim(substr($value, 1, strlen($value)-2));
                            if (!empty($value)) $link[$key] = $value;
                        }
                    }
                }    
                
                $menu[] = $link;
                $item = trim(substr($item, strpos($item, '<li')));
            }
            if ( strpos($item, '<li') !== false )
            {
                $link = array();
                $link['type'] = 'link_start';
                $link['level'] = $level;
                preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $item, $attr);
                foreach ($attr as $attributes) {
                    foreach ($attributes as $attribut) {
                        if (strpos($attribut, "=") !== false) {
                            list($key, $value) = explode("=", $attribut);
                            $value = trim($value);
                            $value = trim(substr($value, 1, strlen($value)-2));
                            $link[$key] = $value;
                        }
                    }
                }
                $link['pagename'] = pathinfo($link['href'],PATHINFO_FILENAME);
                $menu[] = $link;
            }
        }

        return $menu;
    } // end loop for SM2_ARRAY
} // Dwoo_Plugin_show_menu()

?>