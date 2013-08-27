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
	if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$LANG = array(
    'Local version' => 'Lokale Version',
    'Remote version' => 'Ermittelte Version',
    'Last checked' => 'Letzte Überprüfung',
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
);