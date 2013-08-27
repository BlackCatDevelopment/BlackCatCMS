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

/**
 * obfuscates eMail addresses (links with 'href="mailto:..."') by using
 * Base64 Encoding and ROT13 in combination; this will need JavaScript for
 * de-obfuscation on the client side!
 *
 * @access public
 * @param  string  &$content - page content to parse
 * @return void    edits $content
 **/
function obfuscateEmail(&$content)
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    @$dom->loadHTML($content);
    $x = new DOMXPath($dom);
    foreach($x->query("//a") as $node)
    {
        if ( preg_match( '~^mailto:(.*)~i', $node->getAttribute("href"), $match ) )
        {
            $obfuscated = obfuscate($match[1]);
            $content    = str_replace($match[0],'javascript:'.$obfuscated,$content);
            // replace any other occurance
            $content    = str_replace(
                $match[1],
                '<script type="text/javascript">document.write('.$obfuscated.');</script>',
                $content
            );
        }
    }
    // match any other occurances of email addresses
    preg_match_all(
        '/\b([A-Za-z0-9._%-]+@(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,4})\b/D',
        $content,
        $matches,
        PREG_SET_ORDER
    );
    if(count($matches))
    {
        foreach($matches as $match)
        {
            $content = str_replace(
                $match[1],
                '<script type="text/javascript">document.write('.obfuscate($match[1]).');</script>',
                $content
            );
        }
    }
    // add JS to the header
    register_filter_js(CAT_URL.'/modules/blackcatFilter/js/obfuscateEmail.js');
}   // end function

function obfuscate($match)
{
    return 'deobfuscate(\''
        . str_rot13(base64_encode($match))
        . '\')';
}