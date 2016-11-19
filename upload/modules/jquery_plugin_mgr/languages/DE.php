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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         jQuery Plugin Manager
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

$LANG = array(
    'Already installed plugins' => 'Bereits installierte Plugins',
    'Please note'               => 'Hinweis',
    'Upload/install plugin'     => 'Plugin hochladen / installieren',
    'Upload'                    => 'Hochladen',
    'In general, you can add any jQuery Plugin here (ZIP format only!).'
        => 'Grundsätzlich kann hier jedes beliebige jQuery Plugin hinzugefügt werden (nur ZIP Format!)',
    'Of course, we cannot guarantee that plugins uploaded here will work in general or with BlackCat CMS especially.'
        => 'Natürlich können wir nicht garantieren, daß die hochgeladenen Plugins auch funktionieren, weder allgemein noch mit BlackCat CMS im Besonderen.',
    'Some plugins are available especially packed for BlackCat CMS, so you should prefer these over common ones.'
        => 'Einige Plugins wurden speziell für die Verwendung mit BlackCat CMS vorbereitet, diese sollten bevorzugt werden.',
);