<?php
/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @author          B. Martinovic (translation)
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @version         $Id$
 *
 */
 
$LANG = array(
    'Installation Wizard' => 'Installation Wizard',
    'LEPTON Step by Step Installation Wizard' => 'LEPTON Schritt-für-Schritt Installation',
    'Step' => 'Schritt',
// ----- Nav ----
	'Welcome' => 'Willkommen',
	'Precheck' => 'Checks',
	'OS settings' => 'OS Einstellungen',
	'Database settings' => 'Datenbank Einstellungen',
// ----- Welcome page -----
    'Welcome to your LEPTON Installation' => 'Willkommen zu Ihrer LEPTON Installation',
    'This wizard will help you to install and configure LEPTON CMS on your server. But first, please read the license agreement below.'
		=> 'Dieser Wizard hilft Ihnen bei der Installation und Konfiguration von LEPTON CMS auf Ihrem Server. Bitte lesen Sie zunächst untenstehende Lizenzinformationen.',
    'License Agreement' => 'Lizenzvereinbarung',
// ----- Precheck page -----
    'Pre installation checks' => 'Prüfungen vor Installation',
    'Requirement' => 'Anforderung',
    'Required' => 'Erwartet',
    'Current' => 'Tatsächlich',
    'Folder' => 'Verzeichnis',
    'Precheck result' => 'Ergebnis',
    'PHP Settings' => 'PHP Einstellungen (php.ini)',
	'Precheck failed' => 'Prüfung fehlgeschlagen',
    'Installation failed. Your system does not fulfill the defined requirements. Please fix the issues summarized below and try again.'
		=> 'Installation fehlgeschlagen. Ihr System erfüllt nicht die definierten Voraussetzungen. Bitte korrigieren Sie die unten angegebenen Probleme und versuchen Sie es dann erneut.',
  	'Writable' => 'Schreibbar',
	'Not writable!' => 'Nicht schreibbar!',
	'All checks succeeded!' => 'Alle Prüfungen erfolgreich!',
// ----- Global settings page -----
    'Global settings' => 'Globale Einstellungen',
    'Please specify your operating system information, check your path settings, and select a default timezone and a default backend language'
        => 'Bitte spezifizieren Sie Ihre Betriebssystemeinstellungen, prüfen Sie die Pfadeinstellungen, und wählen Sie eine Standard-Zeitzone sowie eine Standardsprache für das Backend',
	'Absolute URL' => 'Absolute URL',
	'Default Timezone' => 'Standard Zeitzone',
	'Default Language' => 'Standardsprache',
    'Server Operating System' => 'Server Betriebssystem',
    'Linux/Unix based' => 'Linux/Unix basiert',
    'World-writeable file permissions' => 'Jeder darf schreiben',
	'Please note: only recommended for testing environments'
	    => 'Hinweis: Nur für Testumgebungen empfohlen',
// ----- db -----
    'Please enter your MySQL database server details below'
        => 'Bitte geben Sie Ihre Datenbankeinstellungen ein',
    'Unable to connect to the database! Please check your settings!'
		=> 'Datenbankverbindung konnte nicht hergestellt werden! Bitte prüfen Sie Ihre Einstellungen!',
	'Host Name' => 'Servername',
	'Database Name' => 'Name der Datenbank',
	'Database User' => 'Datenbank Benutzerkennung',
	'Database Password' => 'Datenbank Kennwort',
	'Table Prefix' => 'Tabellenpräfix',
	'Install Tables' => 'Tabellen installieren',
    'Please note: May remove existing tables and data'
		=> 'Hinweis: Dies entfernt eventuell vorhandene Tabellen und Daten',
	'Yes' => 'Ja',
	'Invalid database password!' => 'Datenbankkennwort fehlt oder ist falsch!',
	'Only characters a-z, A-Z, 0-9, - and _ allowed in database name. Please note that a database name must not be composed of digits only.'
	    => 'Nur die Zeichen a-z, A-Z, 0-9, - und _ sind erlaubt. Bitte beachten, dass ein Datenbankname nicht ausschließlich aus Zahlen bestehen darf.',
    'Only characters a-z, A-Z, 0-9 and _ allowed in table_prefix.'
        => 'Nur die Zeichen a-z, A-Z, 0-9, - und _ sind als table_prefix erlaubt.',
// ----- site -----
	'Site settings' => 'Site Einstellungen',
	'Website title' => 'Webseitentitel',
	'Backend theme' => 'Backend Darstellung',
	'Username' => 'Administrator Kennung',
	'E-Mail' => 'Administrator E-Mail',
	'Password' => 'Kennwort',
	'Retype Password' => 'Kennwort wiederholen',
	'Click on the image on the right to see a preview' => 'Klicken Sie auf das Bild, um eine Vorschau anzusehen',
	'traditional' => 'traditionell',
	'modern (jQuery enhanced)' => 'modern (mit jQuery)',
	'Please enter an admin username (choose "admin", for example)!'
		=> 'Bitte eine Administrator-Kennung angeben (z. B. "admin")',
	'Please enter a valid email address for the Administrator account'
	    => 'Bitte eine gültige E-Mail Adresse für das Administratorkonto angeben',
    'Please enter an admin password!' => 'Bitte ein Kennwort angeben!',
	'Please retype the admin password!' => 'Bitte das Kennwort wiederholen!',
	'The admin passwords you have given do not match!'
	    => 'Die angegebenen Kennwörter stimmen nicht überein!',
    'Name too short! The admin username should be at least 3 chars long.'
        => 'Name zu kurz! Die Administrator-Kennung sollte mindestens 3 Zeichen lang sein.',
    'Only characters a-z, A-Z, 0-9 and _ allowed in admin username'
        => 'Nur die Zeichen a-z, A-Z, 0-9, - und _ sind in der Administrator-Kennung erlaubt.',
    'Password too short! The admin password should be at least 5 chars long.'
		=> 'Kennwort zu kurz! Das Administrator-Kennwort sollte mindestens 5 Zeichen lang sein.',
// ----- postcheck -----
    'Please check your settings before finishing the installation process.'
        => 'Bitte überprüfen Sie nochmals Ihre Einstellungen, bevor Sie fortfahren',
// ----- install -----
	'check tables' => 'Überprüfung der Tabellen',
	'missing' => 'fehlt',
	'The installation failed! Please see check error information below.'
	    => 'Die Installation ist fehlgeschlagen! Bitte prüfen Sie die untenstehenden Fehlernachrichten.',
// ----- buttons -----
	'Back' => 'Zurück',
	'Next' => 'Weiter',
// ----- field names to strings -----
    'operating_system' => 'Betriebssystem',
	'lepton_url' => 'LEPTON URL',
	'default_timezone_string' => 'Standard Zeitzone',
	'default_language' => 'Standardsprache',
	'database_host' => 'Datenbank Servername',
	'database_port' => 'Datenbank Port',
	'database_name' => 'Name der Datenbank',
	'database_username' => 'Datenbank Benutzerkennung',
	'database_password' => 'Datenbank Kennwort',
	'table_prefix' => 'Tabellenpräfix',
	'website_title' => 'Titel der Website',
	'admin_username' => 'Administratorkennung',
	'admin_email' => 'Administrator e-Mail',
	'admin_password' => 'Administratorkennwort',
	'admin_repassword' => 'Kennwort wiederholen',
	'install_tables' => 'Datenbanktabellen installieren',
	'backend_theme' => 'Backend Darstellung',

);

?>