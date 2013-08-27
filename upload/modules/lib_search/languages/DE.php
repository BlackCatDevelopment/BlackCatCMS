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
 *   @category        CAT_Core
 *   @package         CAT_Core
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

if ('á' != "\xc3\xa1") {
    // important: language files must be saved as UTF-8 (without BOM)
    trigger_error('The language file <b>/modules/'.dirname(basename(__FILE__)).'/languages/'.
	    basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

$LANG = array(
        '- unknown -'
            => '- unbekannt -',
        '- unknown date -'
            => '- Datum unbekannt -',
        '- unknown time -'
            => '- Zeit unbekannt -',
        '- unknown user -'
            => ' unbekannter Benutzer -',
        'all words'
            => 'alle Wörter',
        'any word'
            => 'einzelne Wörter',
        'Content locked'
            => 'gesperrter Inhalt',
        'Error creating the directory <b>{{ directory }}</b>.'
            => 'Das Verzeichnis <b>{{ directory }}</b> konnte nicht angelegt werden.',
        'exact match'
            => 'genaue Wortfolge',
        'LEPTON Search Error' 
            => 'Fehlermeldung der LEPTON Suche',
        'LEPTON Search Message'
            => 'Mitteilung der LEPTON Suche',
        'Matching images'
            => 'Gefundene Bilder',
        'No matches!'
            => 'keine Treffer!',
        'only images'
            => 'nur Bilder',
        'Search'
            => 'Suche',
        'Search ...'
            => 'Suche ...',
        'Submit'
            => 'Start',
        'The LEPTON Search is disabled!'
            => 'Die LEPTON Suchfunktion ist ausgeschaltet!',
        'This content is reserved for registered users.'
            => 'Auf diesen Inhalt können nur registrierte Anwender zugreifen.',
        );

?>