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
    'Local version' => 'Lokale Version',
    'Remote version' => 'Ermittelte Version',
    'Last checked' => 'Letzte Überprüfung',
    'Last edited' => 'Letzte Änderung',
    'Refresh now' => 'Jetzt erneut prüfen',
    'A newer version is available!' => 'Eine neue Version ist verfügbar!',
    "You're up-to-date!" => 'Ihre Version ist aktuell!',
    'Version check source file' => 'Quelldatei für die Versionsprüfung',
    'Proxy host (leave empty if you don\'t have one)' => 'Proxy Host (leer lassen wenn nicht vorhanden)',
    'Proxy port (leave empty if you don\'t need a proxy)' => 'Proxy Port (leer lassen wenn nicht vorhanden)',
    'Warning: no WYSIWYG Editors installed!' => 'Warnung: Keine WYSIWYG Editoren installiert!',
    'Warning: no mailer libs installed!' => 'Warnung: Keine Mailbibliotheken installiert!',
    'Edit connection settings' => 'Verbindungseinstellungen',
    'Visit download page' => 'Downloadseite besuchen',
    'Number of last edited pages to show' => 'Anzahl zuletzt geänderter Seiten anzeigen',
    'Latest changed pages' => 'Zuletzt geänderte Seiten',
    'Version check failed!' => 'Versionsprüfung fehlgeschlagen!',
);