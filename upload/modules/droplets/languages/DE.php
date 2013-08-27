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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         droplets
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

$module_description = 'Verwaltung der lokalen Droplets';

$LANG = array(
    'Yes' => 'Ja',
    'No' => 'Nein',
    'Save' => 'Speichern',
    'Save and Back' => 'Speichern und zurück',
    'Cancel' => 'Abbrechen',
 	'Actions' => 'Aktionen',
 	'Back to overview' => 'Zurück zur Übersicht',
 	'Backup file deleted: {{file}}' => 'Backup Datei gelöscht: {{file}}',
 	'Contained files' => 'Enthaltene Dateien',
 	'Date' => 'Erstelldatum',
 	'Files' => 'Dateien',
 	'List Backups' => 'Backups auflisten',
 	'Manage backups' => 'Backups verwalten',
 	'No Backups found' => 'Keine Backups gefunden',
 	'No Droplets found' => 'Keine Droplets gefunden',
 	'Size' => 'Dateigröße',
 	'The Droplet was saved' => 'Droplet gespeichert',
 	'You have entered no code!' => 'Es wurde kein Code eingegeben!',
	'An error occurred when trying to import the Droplet(s)' => 'Beim Import ist ein Fehler aufgetreten',
	'Backup created' => 'Backup erzeugt',
	'Delete' => 'Löschen',
	'Duplicate' => 'Kopieren',
	'Export' => 'Exportieren',
	'Import' => 'Importieren',
	'Invalid' => 'Nicht valide',
	'marked' => 'markierte',
	'Modify' => 'Bearbeiten',
	'Packaging error' => 'Fehler beim Packen',
	'Please check the syntax!' => 'Bitte die Syntax überprüfen!',
    'Please choose a file' => 'Datei auswählen',
	'Please enter a name!' => 'Bitte einen Namen eingeben!',
	'Please mark some Droplets to delete' => 'Bitte Droplet(s) zum Löschen markieren',
	'Please mark some Droplets to export' => 'Bitte einige Droplets zum Export markieren',
	'Successfully imported [{{count}}] Droplet(s)' => '[{{count}}] Droplet(s) erfolgreich importiert',
	'Unable to delete droplet: {{id}}' => 'Fehler beim Löschen von Droplet: {{id}}',
	'Use' => 'Verwendung',
	'Valid' => 'Valide',
	'Groups' => 'Gruppen',
	'Permissions' => 'Rechte',
	'Droplet permissions' => 'Droplet Rechte',
	'Manage permissions' => 'Rechte verwalten',
	'Permissions saved' => 'Rechte gespeichert',
	'Manage global permissions' => 'Globale Rechte verwalten',
	'Manage Droplet permissions' => 'Droplet Rechte verwalten',
	'Edit datafile' => 'Datendatei bearbeiten',
    'Edit Droplet' => 'Droplet bearbeiten',
 	'Create new' => 'Neues Droplet',
 	'Description' => 'Beschreibung',
    'Comments' => 'Kommentar',
 	'Active' => 'Aktiv',
 	'Search' => 'Suche',
 	'Droplet is NOT registered in Search' => 'Das Droplet ist NICHT für die Suche aktiv',
 	'Droplet is registered in Search' => 'Das Droplet ist für die Suche aktiv',
 	'No valid Droplet file (missing description and/or usage instructions)' => 'Kein valides Droplet (weder Beschreibung noch Angaben zur Verwendung vorhanden)',
	// ----- permissions -----
	'add_droplets' => 'Droplets hinzufügen',
	'modify_droplets' => 'Droplets bearbeiten',
	'manage_backups' => 'Backups verwalten',
	'manage_perms' => 'Rechte verwalten',
	'export_droplets' => 'Droplets exportieren',
	'import_droplets' => 'Droplets importieren',
	'delete_droplets' => 'Droplets löschen',
	'edit_groups' => 'Dieses Droplet bearbeiten',
	'view_groups' => 'Dieses Droplet benutzen',
	
);

?>