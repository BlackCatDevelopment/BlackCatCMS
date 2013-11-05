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
 *   @package         lib_search
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

/*
	to include your local changes DO NOT alter this file!
	Instead, create your own local file at 
	lib_search/search.convert.custom.php
	which will stay intact even after upgrading Website Baker.

	--Example search_convert_local.php --------------------------
	// allows the user to enter Krasic to find Krašić
	$t["s"]  = array("š","s");
	$t["S"]  = array("Š","S");
	$t["c"]  = array("ć","c");
	$t["C"]  = array("Ć","C");
	...
	--END -------------------------------------------------------
*/


if (!isset($search_language)) {
    $search_language = LANGUAGE;
}

$t = array();

// this file must be UTF-8 encoded!
if ('á' != "\xc3\xa1") {
	trigger_error('The file '.basename(__FILE__).' is damaged, it must be UTF-8 encoded!', E_USER_ERROR);
}

// local german settings
if ($search_language == 'DE') { // add special handling for german umlauts (ä==ae, ...)
    // in german the character 'ß' may be written as 'ss', too. So for each 'ß' look for ('ß' OR 'ss')
	$t["ß"]  = array("ß" ,"ss"); // german SZ-Ligatur
	$t["ä"]  = array("ä" ,"ae"); // german ae
	$t["ö"]  = array("ö" ,"oe"); // german oe
	$t["ü"]  = array("ü" ,"ue"); // german ue
	// the search itself is case-insensitiv, but strtr() (which is used to convert the search-string) isn't,
	// so we have to supply upper-case characters, too!
	$t["Ä"]  = array("Ä" ,"Ae"); // german Ae
	$t["Ö"]  = array("Ö" ,"Oe"); // german Oe
	$t["Ü"]  = array("Ü" ,"Ue"); // german Ue
	// and for each 'ss' look for ('ß' OR 'ss'), too
	$t["ss"] = array("ß" ,"ss"); // german SZ-Ligatur
	$t["ae"] = array("ä" ,"ae"); // german ae
	$t["oe"] = array("ö" ,"oe"); // german oe
	$t["ue"] = array("ü" ,"ue"); // german ue
	$t["Ae"] = array("Ä" ,"Ae"); // german Ae
	$t["Oe"] = array("Ö" ,"Oe"); // german Oe
	$t["Ue"] = array("Ü" ,"Ue"); // german Ue
}

// local Turkish settings
if ($search_language == 'TR') { // add special i/I-handling for Turkish
	$t["i"] = array("i", "İ");
	$t["I"] = array("I", "ı");
}

// include user-supplied file
if (file_exists(CAT_PATH.'/modules/'.basename(dirname(__FILE__)).'/include/search.convert.custom.php'))
	include_once(CAT_PATH.'/modules/'.basename(dirname(__FILE__)).'/include/search.convert.custom.php');

// create arrays
global $search_table_umlauts_local;
$search_table_umlauts_local = array();

foreach($t as $o => $a) {
	$alt = '';
	if(empty($o) || empty($a) || !is_array($a)) continue;
	foreach($a as $c) {
		if(empty($c)) continue;
		$alt .= preg_quote($c,'/').'|';
	}
	$alt = rtrim($alt, '|');
	$search_table_umlauts_local[$o] = "($alt)";
}

// create array for use with frontent.functions.php (highlighting)
$string_ul_umlaut = array_keys($search_table_umlauts_local);
$string_ul_regex = array_values($search_table_umlauts_local);

global $search_table_sql_local;
$search_table_sql_local = array();

foreach($t as $o=>$a) {
	if(empty($o) || empty($a) || !is_array($a)) continue;
	$i = 0;
	foreach($a as $c) {
		if(empty($c)) continue;
		if($o==$c) { $i++; continue; }
		$search_table_sql_local[$i++][$o] = $c;
	}
}

