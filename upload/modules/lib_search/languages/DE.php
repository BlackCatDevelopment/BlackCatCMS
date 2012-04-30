<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.lepton-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", 
		$_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

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