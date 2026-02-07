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
 * @copyright       2016, Black Cat Development
 * @link            https://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

/*
 * A part of this file is based on 'utf8.php' from the DokuWiki-project.
 * (http://www.splitbrain.org/projects/dokuwiki):
 **
 * UTF8 helper functions
 * @license    LGPL (http://www.gnu.org/copyleft/lesser.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 **
 * modified from thorn, Jan. 2008
 *
 * most of the original functions appeared to be to slow with large strings, so i replaced them with my own ones
 * thorn, Mar. 2008
 */

// Functions we use:
//   entities_to_7bit()
//   entities_to_umlauts2()
//   umlauts_to_entities2()

// include class.secure.php to protect this file and the whole CMS!
if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;
    while ($level < 10 && !file_exists($root . "framework/class.secure.php")) {
        $root .= "../";
        $level++;
    }
    if (file_exists($root . "framework/class.secure.php")) {
        include $root . "framework/class.secure.php";
    } else {
        trigger_error(
            sprintf(
                "[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER["SCRIPT_NAME"]
            ),
            E_USER_ERROR
        );
    }
}

// mbstring handling
if (!defined("UTF8_MBSTRING")) {
    define("UTF8_MBSTRING", function_exists("mb_substr") ? 1 : 0);
}
if (UTF8_MBSTRING) {
    mb_internal_encoding("UTF-8");
}

require_once CAT_PATH . "/framework/charsets_table.php";

/**
 * Safe UTF-8 normalizer (replaces deprecated utf8_encode / utf8_decode usage)
 */
function utf8_normalize(string $s): string
{
    if ($s === "") {
        return $s;
    }
    if (mb_check_encoding($s, "UTF-8")) {
        return $s;
    }
    return mb_convert_encoding($s, "UTF-8", "ISO-8859-1");
}

function utf8_isASCII($str): bool
{
    return !preg_match('/[\x80-\xFF]/', $str);
}

function utf8_check($str): bool
{
    if ($str === "" || $str === null) {
        return true;
    }
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $b = ord($str[$i]);
        if ($b < 0x80) {
            continue;
        } elseif (($b & 0xe0) === 0xc0) {
            $n = 1;
        } elseif (($b & 0xf0) === 0xe0) {
            $n = 2;
        } elseif (($b & 0xf8) === 0xf0) {
            $n = 3;
        } else {
            return false;
        }
        for ($j = 0; $j < $n; $j++) {
            if (++$i >= $len || (ord($str[$i]) & 0xc0) !== 0x80) {
                return false;
            }
        }
    }
    return true;
}

function utf8_romanize($string)
{
    if (utf8_isASCII($string)) {
        return $string;
    }
    global $UTF8_ROMANIZATION;
    return strtr($string, $UTF8_ROMANIZATION);
}

function utf8_stripspecials($string, $repl = "", $additional = "")
{
    global $UTF8_SPECIAL_CHARS2;
    static $specials = null;

    if ($specials === null) {
        $specials = preg_quote((string) ($UTF8_SPECIAL_CHARS2 ?? ""), "/");
    }

    return preg_replace(
        "/[" . $additional . '\x00-\x19' . $specials . "]/u",
        $repl,
        $string
    );
}

function utf8_fast_entities_to_umlauts($str)
{
    if ($str === null) {
        return "";
    }

    if (UTF8_MBSTRING) {
        return html_entity_decode($str, ENT_QUOTES | ENT_HTML5, "UTF-8");
    }

    global $named_entities, $numbered_entities;
    $str = str_replace($named_entities, $numbered_entities, $str);

    return preg_replace_callback(
        "/&#([0-9]+);/",
        static function ($m) {
            return code_to_utf8((int) $m[1]);
        },
        $str
    );
}

function code_to_utf8(int $num): string
{
    if ($num <= 0x7f) {
        return chr($num);
    } elseif ($num <= 0x7ff) {
        return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    } elseif ($num <= 0xffff) {
        return chr(($num >> 12) + 224) .
            chr((($num >> 6) & 63) + 128) .
            chr(($num & 63) + 128);
    } elseif ($num <= 0x1fffff) {
        return chr(($num >> 18) + 240) .
            chr((($num >> 12) & 63) + 128) .
            chr((($num >> 6) & 63) + 128) .
            chr(($num & 63) + 128);
    }
    return "?";
}

function utf8_fast_umlauts_to_entities($string, $use_named = true)
{
    if ($string === null) {
        return "";
    }

    if (UTF8_MBSTRING) {
        return htmlentities($string, ENT_QUOTES | ENT_HTML5, "UTF-8");
    }

    global $named_entities, $numbered_entities;
    $out = "";

    $len = strlen($string);
    for ($i = 0; $i < $len; $i++) {
        $c = ord($string[$i]);
        if ($c < 128) {
            $out .= chr($c);
        } else {
            $out .= "&#" . $c . ";";
        }
    }

    if ($use_named) {
        $out = str_replace($numbered_entities, $named_entities, $out);
    }

    return $out;
}

function charset_to_utf8(
    $str,
    $charset_in = DEFAULT_CHARSET,
    $decode_entities = true
) {
    if ($str === null || $str === "") {
        return "";
    }

    $charset_in = strtoupper($charset_in);
    if ($charset_in === "UTF-8" || utf8_isASCII($str)) {
        return $decode_entities ? utf8_fast_entities_to_umlauts($str) : $str;
    }

    if (UTF8_MBSTRING) {
        $str = mb_convert_encoding($str, "UTF-8", $charset_in);
    } elseif (function_exists("iconv")) {
        $str = iconv($charset_in, "UTF-8", $str);
    }

    return $decode_entities ? utf8_fast_entities_to_umlauts($str) : $str;
}

function utf8_to_charset($str, $charset_out = DEFAULT_CHARSET)
{
    if ($str === null || $str === "") {
        return "";
    }

    $charset_out = strtoupper($charset_out);
    if ($charset_out === "UTF-8" || utf8_isASCII($str)) {
        return $str;
    }

    if (UTF8_MBSTRING) {
        return mb_convert_encoding($str, $charset_out, "UTF-8");
    }

    if (function_exists("iconv")) {
        return iconv("UTF-8", $charset_out, $str);
    }

    return $str;
}

function entities_to_7bit($str)
{
    $str = charset_to_utf8($str);
    if (!utf8_check($str)) {
        return $str;
    }

    $str = utf8_stripspecials($str, "_");
    $str = utf8_romanize($str);
    $str = utf8_fast_umlauts_to_entities($str, false);

    $str = preg_replace_callback(
        "/&#([0-9]+);/",
        static function ($m) {
            return dechex((int) $m[1]);
        },
        $str
    );

    return preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $str);
}

function entities_to_umlauts2($string, $charset_out = DEFAULT_CHARSET)
{
    return utf8_to_charset(
        charset_to_utf8($string, DEFAULT_CHARSET, true),
        $charset_out
    );
}

function umlauts_to_entities2($string, $charset_in = DEFAULT_CHARSET)
{
    return utf8_fast_umlauts_to_entities(
        charset_to_utf8($string, $charset_in, false),
        false
    );
}

?>
