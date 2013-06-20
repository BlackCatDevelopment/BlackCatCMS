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
 *   @package         wysiwyg_admin
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

$module_description = 'WYSIWYG Admin ermöglicht die Verwaltung diverser Editor-Einstellung; standardmäßig sind das Skin, Toolbar, Breite und Höhe. '
                    . 'Der Editor kann weitere Optionen definieren, die dann ebenfalls verwaltet werden können.';

$LANG = array(
    'Manage settings for editor' => 'Verwalte Einstellungen für Editor',
    'Common options' => 'Allgemeine Einstellungen',
    'Additional options' => 'Weitere Einstellungen',
    'Additional plugins' => 'Optionale Plugins',
    'Available Filemanager' => 'Verfügbare Dateimanager',
    'Skin preview' => 'Skin Vorschau',
	'Editor width' => 'Editor Breite',
	'Editor height' => 'Editor H&ouml;he',
	'Editor toolbar' => 'Editor Toolbar',
    'Enable HTMLPurifier' => 'HTMLPurifier aktivieren',
	'Invalid width: {{width}}% > 100%!' => 'Ungültige Breite: {{width}}% > 100%!',
    'Invalid height: {{width}}% > 100%!' => 'Ungültige Höhe: {{width}}% > 100%!',
    'Invalid width: Too large! (>10000)' => 'Ungültige Breite: Zu groß! (>10000)',
    'Invalid height: Too large! (>10000)' => 'Ungültige Höhe: Zu groß! (>10000)',
    'Invalid skin!' => 'Ungültiger Skin!',
    'Invalid boolean value!' => 'Ungültiger Boolean Wert!',
    'If this option is enabled, all WYSIWYG content will be cleaned by using HTMLPurifier before it is stored. Users that are members of group "Administrators" are still allowed to use all HTML, including forms and script.'
       => 'Wenn aktiviert, wird der WYSIWYG Inhalt mit Hilfe von HTMLPurifier bereinigt, bevor er gespeichert wird. Benutzer der Gruppe "Administratoren" dürfen nach wie vor alles, inklusive Formularen und Scripts.',
);