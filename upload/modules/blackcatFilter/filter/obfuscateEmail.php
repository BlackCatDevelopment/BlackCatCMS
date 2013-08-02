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
    $content = str_ireplace(
        '</head>',
        '<script charset="windows-1250" src="'.CAT_URL.'/modules/blackcatFilter/filter/obfuscateEmail.js" type="text/javascript"></script></head>',
        $content
    );
}   // end function

function obfuscate($match)
{
    return 'deobfuscate(\''
        . str_rot13(base64_encode($match))
        . '\')';
}