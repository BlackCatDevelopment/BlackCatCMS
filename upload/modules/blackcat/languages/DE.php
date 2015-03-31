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
 *   @package         bcversion_widget
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

$LANG = array(
    "You're up-to-date!" => 'Ihre Version ist aktuell!',
    'A newer version is available!' => 'Eine neue Version ist verfügbar!',
    'Edit connection settings' => 'Verbindungseinstellungen',
    'Help links' => 'Hilfeseiten',
    'Last checked' => 'Letzte Überprüfung',
    'Last edited' => 'Letzte Änderung',
    'Latest changed pages' => 'Zuletzt geänderte Seiten',
    'Local version' => 'Lokale Version',
    'Logfiles' => 'Logdateien',
    'Maintenance mode' => 'Wartungsmodus',
    'Maintenance mode is off.' => 'Wartungsmodus ist ausgeschaltet.',
    'No logfiles (or all empty)' => 'Keine Logdateien (oder nur leere)',
    'Number of last edited pages to show' => 'Anzahl zuletzt geänderter Seiten anzeigen',
    'Please note: The system is in maintenance mode!' => 'Hinweis: Das System befindet sich im Wartungsmodus!',
    'Proxy host (leave empty if you don\'t have one)' => 'Proxy Host (leer lassen wenn nicht vorhanden)',
    'Proxy port (leave empty if you don\'t need a proxy)' => 'Proxy Port (leer lassen wenn nicht vorhanden)',
    'Refresh now' => 'Jetzt erneut prüfen',
    'Remote version' => 'Ermittelte Version',
    'Statistics' => 'Statistiken',
    'To disable, go to Settings -> System settings -> Maintenance mode -> set to "off".' => 'Zum Abschalten: Einstellungen -> Systemeinstellungen -> Wartungsmodus -> auf "off" stellen.',
    'Version check' => 'Versionsprüfung',
    'Version check failed!' => 'Versionsprüfung fehlgeschlagen!',
    'Version check source file' => 'Quelldatei für die Versionsprüfung',
    'Visit download page' => 'Downloadseite besuchen',
    'Warning: no mailer libs installed!' => 'Warnung: Keine Mailbibliotheken installiert!',
    'Warning: no WYSIWYG Editors installed!' => 'Warnung: Keine WYSIWYG Editoren installiert!',
);