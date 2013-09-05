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
 *   @package         CAT_Installer
 *
 */

$LANG = array(
// ----- pre Installation / Update errors -----
    'Sorry, the BlackCat CMS Installation prerequisites check failed.'
        => 'Entschuldigung, die Prüfung der BlackCat CMS Installations-Voraussetzungen ist fehlgeschlagen.',
    'Sorry, the BlackCat CMS Update prerequisites check failed.'
        => 'Entschuldigung, die Prüfung der BlackCat CMS Update Voraussetzungen ist fehlgeschlagen.',
    'You need to have <strong>BlackCat CMS v0.11.0Beta</strong> installed to use the Update.<br />You have <strong>{{version}}</strong> installed.'
        => 'Für das Update muss mindestens <strong>BlackCat CMS v0.11.0Beta</strong> installiert sein.<br />Es wurde <strong>{{version}}</strong> gefunden.',
    'You will need to fix the errors quoted above to start the installation.'
        => 'Die oben aufgeführten Probleme müssen behoben werden, um die Installation zu starten.',
    'Installation Wizard' => 'Installation Wizard',
    'BlackCat CMS Step by Step Installation Wizard' => 'BlackCat CMS Schritt-für-Schritt Installation',
    'Step' => 'Schritt',
// ----- Update wizard -----
    'To start the update, please click here' => 'Zum Starten des Updates bitte hier klicken',
    'Update done' => 'Update erfolgreich',
    'Click here to enter the backend' => 'Hier klicken, um das Backend aufzurufen',
// ----- Nav ----
	'Welcome' => 'Willkommen',
	'Precheck' => 'Checks',
	'OS settings' => 'OS Einstellungen',
	'Database settings' => 'Datenbank Einstellungen',
    'Optional' => 'Optionale Add-Ons',
// ----- Welcome page -----
    'Welcome to your BlackCat CMS Installation' => 'Willkommen zu Ihrer BlackCat CMS Installation',
    'This wizard will help you to install and configure BlackCat CMS on your server. But first, please read the license agreement below.'
		=> 'Dieser Wizard hilft Ihnen bei der Installation und Konfiguration von BlackCat CMS auf Ihrem Server. Bitte lesen Sie zunächst untenstehende Lizenzinformationen.',
    'License Agreement' => 'Lizenzvereinbarung',
// ----- Precheck page -----
    'CMS root directory' => 'CMS Basisverzeichnis',
    'CMS installation directory' => 'CMS Installationsverzeichnis',
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
    'None found' => 'Keiner gefunden',
    'Server Operating System' => 'Server Betriebssystem',
    'Linux/Unix based' => 'Linux/Unix basiert',
    'World-writeable file permissions' => 'Jeder darf schreiben',
	'Please note: only recommended for testing environments'
	    => 'Hinweis: Nur für Testumgebungen empfohlen',
    'Create GUID' => 'GUID erzeugen',
    "The GUID allows to identify an installation while being completely anonymous as it contains no information about you, the CMS contents or anything else. You may use it to distinguish different installations, for example. If you don't wish to create one now, you may make it up later from the backend."
        => 'Die GUID erlaubt es einerseits, eine Installation zu identifizieren, ist aber andererseits völlig anonym, da keinerlei Informationen über den Eigentümer oder die Inhalte des CMS enthalten sind. Zudem können Sie sie nutzen, um mehrere Installationen voneinander zu unterscheiden. Wenn Sie jetzt keine GUID erzeugen möchten, können Sie das später über das Backend nachholen.',
    'optional Add-On!' => 'optionales Add-On!',
    'Please do not forget to check this Add-On at the [Optional] step! The WYSIWYG Editor will not work otherwise!'
        => 'Vergessen Sie nicht, dieses Add-On im Schritt [Optional] zu installieren! Der WYSIWYG Editor wird sonst nicht funktionieren!',
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
    "Don't check database password" => 'Datenbankkennwort nicht prüfen',
    'You have set an empty password. Please check the checkbox above to confirm that this is really what you want.'
		=> 'Das Datenbankkennwort ist leer. Zur Bestätigung bitte obige Checkbox markieren.',
    'Please check the database password!' => 'Bitte das Datenbankkennwort prüfen',
    "If you don't have a database password, or a password that doesn't meet common security constraints, please check this checkbox. Please note that this is a security risk in public environments! Use empty and/or short passwords in (local) testing environments only."
		=> 'Wenn das Datenbankkennwort leer bleiben soll, oder nicht den üblichen Sicherheitskriterien entspricht, bitte nebenstehende Checkbox anhaken. Hinweis: Dies ist ein Sicherheitsrisiko! Leere und/oder sehr kurze Kennworte sollten nur in (lokalen) Testumgebungen verwendet werden.',
    'The required password complexity is not met'
        => 'Die erforderliche Kennwort-Komplexität ist nicht erfüllt',
    'The database does not exist! Please check your settings!'
        => 'Die Datenbank existiert nicht! Bitte prüfen Sie Ihre Einstellungen!',
// ----- site -----
	'Site settings' => 'Site Einstellungen',
	'Website title' => 'Webseitentitel',
	'Backend theme' => 'Backend Darstellung',
	'Username' => 'Administrator Kennung',
    'at least 5 chars' => 'mindestens 5 Zeichen',
	'E-Mail' => 'Administrator E-Mail',
	'Password' => 'Kennwort',
	'Retype Password' => 'Kennwort wiederholen',
	'Please enter an admin username (choose "admin", for example)!'
		=> 'Bitte eine Administrator-Kennung angeben (z. B. "admin")',
	'Please enter an email address!' => 'Bitte eine Mailadresse für das Administratorkonto angeben',
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
	'Invalid password!' => 'Das Kennwort ist ungültig!',
	'The password is too short.' => 'Das Kennwort ist zu kurz.',
    "Don't check admin password" => 'Administrator-Kennwort nicht prüfen',
    "If you wish to set a password that doesn't meet common security constraints, please check this checkbox. Please note that this is a security risk in public environments! Use empty and/or short passwords in (local) testing environments only."
        => 'Wenn das Kennwort nicht auf die üblichen Sicherheitskriterien geprüft werden soll, bitte nebenstehende Checkbox anhaken. Hinweis: Dies ist ein Sicherheitsrisiko! Leere und/oder sehr kurze Kennworte sollten nur in (lokalen) Testumgebungen verwendet werden.',
// ----- postcheck -----
    'Please check your settings before finishing the installation process.'
        => 'Bitte überprüfen Sie nochmals Ihre Einstellungen, bevor Sie fortfahren',
// ----- optional addons -----
    'Optional addons' => 'Optionale Add-Ons',
    'The following optional addons were found in the <i>./optional</i> subfolder. Please check the ones you wish to install. (Default: all)'
        => 'Folgende optionale Add-Ons wurden im Verzeichnis <i>./optional</i> gefunden. Bitte markieren Sie diejenigen, die installiert werden sollen. (Standard: alle)',
    "This one can't be unchecked because you chose to use it!"
        => 'Kann nicht deaktiviert werden, da Sie es zur Verwendung ausgewählt haben!',
    'Set max_execution_time to {{ sec }} seconds' => 'Einstellung max_execution_time auf {{ sec }} Sekunden gesetzt',
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
	'cat_url' => 'BlackCat CMS URL',
	'default_timezone_string' => 'Standard Zeitzone',
	'default_language' => 'Standardsprache',
    'default_wysiwyg' => 'Standard WYSIWYG Editor',
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
    'create_guid' => 'GUID erzeugen',
    'guid_prefix' => 'GUID Präfix',
    'no_validate_db_password' => 'Datenbankkennwort nicht prüfen',
    'optional_addon' => 'Optionale Add-Ons',

);

?>