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
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;
    while ($level < 10 && !file_exists($root . "/framework/class.secure.php")) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root . "/framework/class.secure.php")) {
        include $root . "/framework/class.secure.php";
    } else {
        trigger_error(
            sprintf(
                "[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER["SCRIPT_NAME"]
            ),
            E_USER_ERROR
        );
    }
}

// Define that this file is loaded
if (!defined("LANGUAGE_LOADED")) {
    define("LANGUAGE_LOADED", true);
}

// Set the language information
$language_code = "DE";
$language_name = "Deutsch";
$language_version = "1.2";
$language_platform = "1.x";
$language_author = "Black Cat Development";
$language_license = "GNU General Public License";
$language_guid = "f49419c8-eb27-4a69-bffb-af61fce6b0c9";
$language_date_long = "%A,|%d.|%B|%Y";
$language_date_short = "%d.%m.%Y";
$language_time = "%H:%I:%S";
$language_time_string = "Uhr";

$LANG = [
    // ----- temporary dashboard info text -----
    'This is your dashboard, but it\'s empty because you do not have superuser permissions. Use the page tree to edit pages. Use the top links to navigate through the backend.' =>
        "Ihr Dashboard ist leider leer, da Sie nicht mit Superuser Rechten angemeldet sind. Verwenden Sie den Seitenbaum, um Seiten zu bearbeiten. Verwenden Sie die Links oben, um durch das Backend zu navigieren.",
    // ----- ./account folder -----
    "Captcha verification" => "Captcha",
    "Change password" => "Kennwort ändern",
    "Confirm" => "Bestätigen",
    "Confirm with current password" => "Mit aktuellem Kennwort bestätigen",
    "Date format" => "Datumsformat",
    "Details saved successfully" => "Einstellungen gespeichert",
    "Forgot your details?" =>
        "Haben Sie Ihre pers&ouml;nlichen Daten vergessen?",
    "Full name" => "Voller Name",
    "My preferences" => "Profileinstellungen",
    "My settings" => "Profil / Benutzereinstellungen",
    "Need to log-in?" => "Anmelden?",
    "New password" => "Neues Kennwort",
    "Retype new password" => "Neues Kennwort wiederholen",
    "You have to set a new password." =>
        "Sie müssen ein neues Passwort setzen.",
    "Current password" => "Aktuelles Kennwort",
    "No group was selected" => "Es wurde keine Gruppe ausgew&auml;hlt",
    "Password cannot be reset more than once per hour" =>
        "Das Passwort kann nur einmal pro Stunde zurückgesetzt werden",
    "Please enter your email address" => "Bitte geben Sie Ihre Mailadresse an",
    "Please enter your email address below" =>
        "Bitte geben Sie nachfolgend Ihre E-Mail Adresse an",
    "Please enter your CURRENT password to confirm your changes!" =>
        "Bitte geben Sie Ihr AKTUELLES Kennwort ein, um die Änderungen zu bestätigen!",
    "Re-Type new password" => "Neues Kennwort wiederholen",
    "Send details" => "Kennwort anfordern",
    "Send eMail" => "eMail schicken",
    "Sign-up" => "Registrieren",
    "The (current) password you entered is incorrect" =>
        "Das eingegebene (aktuelle) Kennwort ist falsch",
    "The email address you entered is invalid" =>
        "Die angegebene Mailadresse ist ungültig",
    "The email that you entered cannot be found in the database" =>
        "Die angegebene E-Mail Adresse wurde nicht in der Datenbank gefunden",
    "The email you entered is already in use" =>
        "Die angegebene Mailadresse wird bereits verwendet",
    "The password you entered was too short" => "Das Kennwort ist zu kurz",
    "The passwords you entered do not match" =>
        "Die Kennworte stimmen nicht überein",
    "The username you entered is already taken" =>
        "Der angegebene Benutzername (Kennung) wird bereits verwendet",
    "Time format" => "Zeitformat",
    "Timezone" => "Zeitzone",
    "Unable to mail login details - no mailer library installed!" =>
        "Das Kennwort kann nicht versendet werden - keine Mail-Bibliothek installiert!",
    "Unable to email password, please contact system administrator" =>
        "Das Passwort konnte nicht versendet werden, bitte kontaktieren Sie den Systemadministrator",
    "You must enter your current password to save your changes" =>
        "Sie müssen zum Speichern Ihr aktuelles Kennwort angeben",
    "Your login details..." => "Ihre Logindaten ...",
    "Your username and password have been sent to your email address" =>
        "Ihr Benutzername und Ihr Passwort wurden an Ihre E-Mail Adresse gesendet",
    "Registration process completed!<br /><br />You should receive an eMail with your login data. If not, please contact {{SERVER_EMAIL}}." =>
        "Die Registrierung ist abgeschlossen.<br /><br />Sie sollten eine eMail mit Ihren Login-Daten erhalten. Falls nicht, kontaktieren Sie bitte  {{SERVER_EMAIL}}.",
    // ----- captcha -----
    'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email to: <a href="mailto:{{SERVER_EMAIL}}">{{SERVER_EMAIL}}</a>' =>
        'Die eingegebene Pr&uuml;fziffer stimmt nicht &uuml;berein. Wenn Sie Probleme mit dem Lesen der Pr&uuml;fziffer haben, schreiben Sie bitte eine E-Mail an: <a href="mailto:{{SERVER_EMAIL}}">{{SERVER_EMAIL}}</a>',
    // ----- settings -----
    "Activate ASP (if available)" => "ASP benutzen (wenn im Modul vorhanden)",
    "Activate CAPTCHA for signup" => "CAPTCHA für Registrierung aktivieren",
    "ASP tries to determine if a form-input was originated from a human or a spam-bot." =>
        "ASP versucht anhand der verschiedenen Verhaltensweisen zu erkennen, ob eine Formular-Eingabe von einem Menschen oder einem Spam-Bot kommt.",
    "CAPTCHA settings for modules are located in the respective module settings" =>
        "Die CAPTCHA-Einstellungen f&uuml;r die Module befinden sich in den jeweiligen Modul-Optionen",
    "Calculation as image" => "Rechnung als Bild",
    "Calculation as image with varying fonts and backgrounds" =>
        "Rechnung als Bild mit wechselnden Schriften und Hintergr&uuml;nden",
    "Calculation as text" => "Rechnung als Text",
    "Captcha and Advanced Spam Protection (ASP)" =>
        "Captcha und Advanced Spam Protection (ASP) Einstellungen",
    "Delete this all to add your own entries" .
    "\n" .
    'or your changes won\'t be saved!' .
    "\n" .
    "### example ###" .
    "\n" .
    "Here you can enter Questions and Answers." .
    "\n" .
    "Use:" .
    "\n" .
    '?What\'s Claudia Schiffer\'s first name?' .
    "\n" .
    "!Claudia" .
    "\n" .
    "?Question 2" .
    "\n" .
    "!Answer 2" .
    "\n" .
    "" .
    "\n" .
    'if language doesn\'t matter.' .
    "\n" .
    " ... " .
    "\n" .
    "Or, if language do matter, use:" .
    "\n" .
    '?EN:What\'s Claudia Schiffer\'s first name?' .
    "\n" .
    "!Claudia" .
    "\n" .
    "?EN:Question 2" .
    "\n" .
    "!Answer 2" .
    "\n" .
    "?DE:Wie ist der Vorname von Claudia Schiffer?" .
    "\n" .
    "!Claudia" .
    "\n" .
    " ... " .
    "\n" .
    "### example ###" .
    "\n" .
    "" =>
        "Bitte hier alles l&ouml;schen" .
        "\n" .
        "sonst werden Ihre &Auml;nderungen nicht gespeichert!" .
        "\n" .
        "### Beispiel ###" .
        "\n" .
        "Hier k&ouml;nnen sie Fragen und Antworten eingeben." .
        "\n" .
        "Entweder:" .
        "\n" .
        "?Wie ist der Vorname von Claudia Schiffer?" .
        "\n" .
        "!Claudia" .
        "\n" .
        "?Frage 2" .
        "\n" .
        "!Antwort 2" .
        "\n" .
        " ... " .
        "\n" .
        "wenn nur eine Sprache benutzt wird." .
        "\n" .
        "" .
        "\n" .
        "Oder, wenn die Sprache relevant ist:" .
        "\n" .
        '?EN:What\'s Claudia Schiffer\'s first name?' .
        "\n" .
        "!Claudia" .
        "\n" .
        "?EN:Question 2" .
        "\n" .
        "!Answer 2" .
        "\n" .
        "?DE:Wie ist der Vorname von Claudia Schiffer?" .
        "\n" .
        "!Claudia" .
        "\n" .
        " ... " .
        "\n" .
        "### Beispiel ###" .
        "\n" .
        "",
    "Image with varying fonts and backgrounds" =>
        "Bild mit wechselnden Schriften und Hintergr&uuml;nden",
    "Installation path" =>
        "Installationsverzeichnis",
    "Old style (not recommended)" => "Alter Stil (nicht empfohlen)",
    "Please note: These settings only concern the old Captcha derived from WebsiteBaker. At the moment, there are no settings for the SecurImage library here." =>
        "Hinweis: Diese Einstellungen betreffen die alten Captcha Methoden, die von WebsiteBaker übernommen wurden. Zur Zeit können hier keine Einstellungen für die SecurImage Bibliothek vorgenommen werden.",
    "Questions and Answers" => "Fragen und Antworten",
    "Text-CAPTCHA" => "Text-CAPTCHA",
    "To make ASP work with modules, modifications in the module itself are necessary." =>
        "Damit ASP in Modulen funktioniert, sind entsprechende Anpassungen im Modul selbst erforderlich.",
    "Type of CAPTCHA" => "CAPTCHA-Typ",
    // ----- common -----
    "Add" => "Anlegen",
    "Add Group" => "Gruppe hinzuf&uuml;gen",
    "Add page" => "Seite hinzufügen",
    "Administrators" => "Administratoren",
    "Back" => "Zurück",
    "by" => "von",
    "Cancel" => "Abbrechen",
    "Close" => "Schließen",
    "Close & Reset" => "Schließen & Zurücksetzen",
    "Confirmation" => "Bestätigung",
    "Count" => "Anzahl",
    "Done" => "Fertig",
    "Edit" => "Bearbeite",
    "From" => "Von",
    "To" => "Bis",
    "General Settings" => "Allgemeine Optionen",
    "Last modified" => "Zuletzt geändert",
    "Logged-In" => "Angemeldet",
    "Modify" => "Bearbeiten",
    "Need to log-in?" => "Wollen Sie sich anmelden?",
    "No" => "Nein",
    "Notification" => "Hinweis",
    "Open" => "Öffnen",
    "Options" => "Optionen",
    "or" => "oder",
    "Out Of" => "von",
    "Page" => "Seite",
    "Page title" => "Seitentitel",
    "Pages" => "Seiten",
    "Please check back soon..." =>
        "Bitte versuchen Sie es sp&auml;ter noch einmal ...",
    "Please select" => "Bitte wählen",
    "Reset" => "Zurücksetzen",
    "Save & Close" => "Speichern & Schließen",
    "Save" => "Speichern",
    "Search" => "Suche",
    "Search..." => "Suchen...",
    "Success" => "Erfolgreich",
    "View" => "Ansicht",
    "Website Under Construction" => "Momentan in Bearbeitung.",
    "Welcome back" => "Willkommen",
    "Yes" => "Ja",
    // ----- error messages -----
    "Error creating access file in the pages directory, insufficient privileges" =>
        "Beim Anlegen der Zugangsdatei im Seitenverzeichnis ist ein Fehler aufgetreten (Ungen&uuml;gende Zugangsrechte)",
    "Error creating access file in the pages directory, cannot open file" =>
        "Beim Anlegen der Zugangsdatei im Seitenverzeichnis ist ein Fehler aufgetreten (Datei kann nicht geschrieben werden)",
    "Error creating access file in the pages directory, path not writable or forbidden file / directory name" =>
        "Beim Anlegen der Zugangsdatei im Seitenverzeichnis ist ein Fehler aufgetreten (Verzeichnis nicht schreibbar oder verbotener Datei-/Verzeichnisname)",
    "Error message" => "Fehlermeldung",
    "File not found" => "Datei nicht gefunden",
    "File upload error: {{error}}" => "Datei Upload Fehler: {{error}}",
    "Invalid characters in username found" =>
        "Ungültige Zeichen im Benutzernamen gefunden",
    'Invalid password chars used, valid chars are: a-z\A-Z\0-9\_\-\!\#\*\+' =>
        'Es wurden ung&uuml;ltige Zeichen f&uuml;r des Passwort verwendet, g&uuml;ltig sind: a-z\A-Z\0-9\_\-\!\#\*\+',
    "Missing page_id!" => "Keine page_id angegeben!",
    "No search library installed!" => "Es ist keine Suchfunktion installiert!",
    "Ooops... A fatal error occured while processing your request!" =>
        "Es ist leider ein Problem beim Bearbeiten Ihrer Anfrage aufgetreten!",
    "Searched paths" => "Durchsuchte Pfade",
    "Sorry, but you don't have the permissions for this action" =>
        "Sie haben leider nicht die notwendigen Rechte für diese Aktion",
    "Sorry, but the system is unable to use mail to send your details. Please contact the administrator." =>
        "Entschuldigung, leider kann Ihnen keine eMail mit Ihren Daten zugesandt werden. Bitte kontaktieren Sie den Administrator.",
    "Source" => "Quelle",
    "The max. Login name length could not be saved. There is/are {{ count }} user/s that have longer names." =>
        "Die Maximallänge des Anmeldenamens konnte nicht gespeichert werden. Es gibt {{ count }} Benutzer mit einem längeren Namen.",
    "The min. Login name length could not be saved. There is/are {{ count }} user/s that have shorter names." =>
        "Die Mindestlänge des Anmeldenamens konnte nicht gespeichert werden. Es gibt {{ count }} Benutzer mit einem kürzeren Namen.",
    "The page does not have any content!" =>
        "Diese Seite hat keine aktiven Inhalte!",
    "The pages directory is not writable!" =>
        "Das Seitenverzeichnis ist schreibgeschützt!",
    "The password you entered was too short (Please use at least {{AUTH_MIN_PASS_LENGTH}} chars)" =>
        "Das Kennwort ist zu kurz (Bitte mindestens {{AUTH_MIN_PASS_LENGTH}} Zeichen verwenden)",
    "The template [{{ tpl }}] does not exists in one of the possible template paths!" =>
        "Das Template [{{ tpl }}] wurde in keinem der Template-Pfade gefunden!",
    "Unable to {{ action }} {{ type }} {{ module }}!" =>
        "Fehler bei Aktion {{ action }}, {{ type }} {{ module }}!",
    "Unable to render the page" => "Fehler beim Darstellen der Seite",
    "Username or password incorrect" =>
        "Der Benutzername oder das Passwort ist nicht korrekt.",
    "Username too long (max.: {{ length }})" =>
        "Benutzername zu lang (max.: {{ length }})",
    "Username too short (min.: {{ length }})" =>
        "Benutzername zu kurz (min.: {{ length }})",
    "We're sorry!" => "Wir bitten um Entschuldigung!",
    "You are not allowed to view this page!" =>
        "Sie besitzen nicht die erforderlichen Rechte, um diese Seite zu besuchen!",
    "You sent an invalid value" => "Es wurde ein ungültiger Wert angegeben",
    // ----- MENU -----
    "Access" => "Benutzerverwaltung",
    "Add-on" => "Add-on",
    "Add-ons" => "Erweiterungen",
    "Admin-Tools" => "Admin-Tools",
    "You are here: " => "Sie sind hier: ",
    "Retrieve Login Details" => "Anmelde-Daten anfordern",
    "Group" => "Gruppe",
    "Groups" => "Gruppen",
    "Help" => "Hilfe",
    "Languages" => "Sprachen",
    "Login" => "Anmeldung",
    "Log-out" => "Abmelden",
    "Media" => "Medien",
    // ----- LOGIN PAGE -----
    "A technical cookie is required for backend login." =>
        "Für die Anmeldung am Backend ist ein technisches Cookie erforderlich.",
    "A technical cookie is required for login." =>
        "Für die Anmeldung ist ein technisches Cookie erforderlich.",
    "allow" => "erlauben",
    "Please enter your username and password." =>
        "Bitte Benutzernamen und Passwort eingeben.",
    "The password you entered was too short" =>
        "Das angegebene Passwort ist zu kurz!",
    "The username you entered was too short" =>
        "Der eingegebene Benutzername war zu kurz",
    "Invalid credentials" => "Die Logindaten sind ungültig",
    "You have to allow a technical cookie for login." =>
        "Für das Backend Login ist ein technischer Cookie erforderlich.",
    "Your account has been disabled. Please contact the administrator." =>
        "Ihr Account wurde deaktiviert. Bitte kontaktieren Sie den Administrator.",
    // ----- BACKEND -----
    "Welcome to Black Cat CMS Administration" =>
        "Willkommen im Black Cat CMS Administrationsbereich",
    "This is your dashboard. At the moment, it is not possible to change the widgets shown here or to set permissions. This will be done in next version of BlackCat CMS." =>
        "Dies ist Ihr Dashboard. Zur Zeit ist es leider nicht möglich, die hier angezeigten Widgets zu administrieren bzw. Rechte zu vergeben. Dies wird in der nächsten Version von BlackCat CMS umgesetzt.",
    'To use <span class="icon-logo">Black Cat CMS</span>, please enable JavaScript in your browser and try again.' =>
        'Um <span class="icon-logo">Black Cat CMS</span> zu verwenden, bitte JavaScript im Browser aktivieren und nochmal versuchen.',
    'Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Black Cat CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.' =>
        'Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Black Cat CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.',
    // ----- page -----
    "A page with the same or similar link exists" =>
        "Eine Seite mit einem &auml;hnlichen oder demselben Titel existiert bereits",
    "Access file created successfully" => "Zugangsdatei erfolgreich erzeugt",
    "Add child page" => "Unterseite hinzuf&uuml;gen",
    "Add jQuery Plugin" => "jQuery Plugin hinzufügen",
    "Add Page" => "Seite hinzuf&uuml;gen",
    "Administration Tools" => "Admin Tools",
    "An error occured (using trash: {{trash}})" =>
        "Es ist ein Fehler aufgetreten (verwende Papierkorb: {{trash}})",
    "Auto-add modules (configured in info.php)" =>
        "Module automatisch hinzufügen (in der info.php konfiguriert)",
    "Change settings" => "Einstellungen bearbeiten",
    "Create link" => "Verknüpfung erstellen",
    "CSS files" => "CSS Dateien",
    "Current links" => "Vorhandene Verknüpfungen",
    "Current page" => "Aktuelle Seite",
    "Currently, no extra files are defined" =>
        "Zur Zeit sind keine Extra-Dateien konfiguriert",
    "Default page" => "Standardseite",
    "Delete" => "Löschen",
    "Delete page finally" => "Seite endgültig löschen",
    "Delete section" => "Sektion löschen",
    "Description" => "Beschreibung",
    "Disabled (no forwarding)" => "Deaktiviert (keine Weiterleitung)",
    "Do you really want to delete this page?" =>
        "Wollen Sie die Seite wirklich löschen?",
    "Do you really want to delete this section?" =>
        "Wollen Sie die Sektion wirklich löschen?",
    "Edit file" => "Datei bearbeiten",
    "Edit the file contents here" => "Bearbeiten Sie hier den Dateiinhalt",
    "Enter the name of the subdomain and set the page from the list of pages." =>
        "Geben Sie den Namen der Subdomain an und wählen Sie die entsprechende Seite aus der Liste.",
    "Forward user by browser language" =>
        "Benutzer nach Browsersprache weiterleiten",
    "Forward user by sub domain" => "Benutzer nach Subdomain weiterleiten",
    "General Settings" => "Allgemein",
    "Header files" => "Kopfdateien",
    "Hidden" => "Versteckt",
    "Hide all sections" => "Alle Sektionen verstecken",
    'If you choose the option "Disabled" and save, the intro.php will be deleted!' =>
        'Bei Auswahl von "Deaktiviert" wird die intro.php beim Speichern gelöscht!',
    "Intro page not writable!" => "Einstiegsseite ist nicht schreibbar!",
    "Intro page saved" => "Einstiegsseite gespeichert",
    "Javascript files" => "Javascript Dateien",
    "Language" => "Sprache",
    "Language Mappings" => "Sprach-Verknüpfungen",
    "Language mappings allow to link pages of different languages together. In combination with the <tt>language_menu()</tt> function in the template, you will get links to all available languages for a page." =>
        "Sprach-Verknüpfungen erlauben das Verlinken von Seiten in unterschiedlichen Sprachen. In Kombination mit der <tt>language_menu()</tt> Funktion im Template erhält man automatisch Links zu allen anderen Sprachen, in denen eine Seite verfügbar ist.",
    "Last modification by" => "Letzte Änderung von",
    "Main" => "Hauptblock",
    "Map to language" => "Zielsprache",
    "Map to page" => "Zielseite",
    "Menu" => "Menü",
    "Menu title" => "Menütitel",
    "Modify header files" => "Kopfdateien verwalten",
    "Modify language mappings" => "Sprach-Verknüpfungen bearbeiten",
    "Modify page" => "Seite bearbeiten",
    "Modify Page Settings" => "Seiteneinstellungen bearbeiten",
    "Modify section" => "Sektion bearbeiten",
    "New window" => "Neues Fenster",
    "No current links" => "Keine Verknüpfungen vorhanden",
    "No editable pages were found" => "Keine bearbeitbaren Seiten verfügbar",
    "No more languages available" => "Keine weiteren Sprachen verfügbar",
    "No pages available" => "Keine Seiten vorhanden",
    "No sections were found for this page" =>
        "Keine Sektionen für diese Seite gefunden",
    "None" => "Keine",
    "Page added successfully" => "Seite wurde erfolgreich angelegt",
    "Page(s) deleted successfully" => "Seite(n) erfolgreich gelöscht",
    "Page groups" => "Seitengruppen",
    "Page saved successfully" => "Seite erfolgreich gespeichert",
    "Page settings" => "Seiteneinstellungen",
    "Page settings saved successfully" =>
        "Seiteneinstellungen erfolgreich gespeichert",
    "Parent" => "Übergeordnete Seite",
    "Please choose a file to edit" =>
        "Bitte eine Datei zum Bearbeiten auswählen",
    "Please enter a page title" => "Bitte einen Seitentitel angeben",
    'Please note that there is a bunch of files that is loaded automatically, so there\'s no need to add them here.' =>
        "Bitte beachten, dass es eine Reihe von Dateien gibt, die automatisch geladen werden und daher hier nicht verwaltet werden können und müssen.",
    "Please note: At the moment, the two global options (by language / by sub domain) are mutually exclusive." =>
        "Bitte beachten: Derzeit schließen sich die beiden globalen Optionen (nach Sprache / nach Subdomain) gegenseitig aus.",
    'Please note: By default, all *.js and *.css files in the plugin\'s folder are added to the list. You may have to remove some in the next step.' =>
        "Hinweis: Standardmäßig werden alle *.js und *.css Dateien hinzugefügt, die im Plugin-Verzeichnis vorgefunden werden. Bei Bedarf können überzählige Dateien im nächsten Schritt wieder entfernt werden.",
    "Preferences" => "Profileinstellungen",
    "Private" => "Privat",
    "Public" => "Öffentlich",
    "Re-ordered successfully" => "Neusortierung erfolgreich",
    "Remove plugin" => "Plugin entfernen",
    "Quick changes" => "Expresseinstellungen",
    "Registered" => "Registriert",
    "Remove jQuery Plugin" => "jQuery Plugin entfernen",
    "Remove page" => "Seite löschen",
    "Restore page" => "Seite wiederherstellen",
    "Same window" => "Selbes Fenster",
    "Save page" => "Seite speichern",
    "Saving page" => "Speichere Seite",
    "Searching" => "Suche",
    "Section properties saved successfully" =>
        "Sektionseinstellungen gespeichert",
    "Security Settings" => "Sicherheit",
    "SEO Settings" => "SEO Einstellungen",
    "Show all sections" => "Alle Sektionen zeigen",
    "show/hide section" => "Sektion anzeigen/verstecken",
    "Sorry, no active content to display" =>
        "Kein aktiver Inhalt auf dieser Seite vorhanden",
    "Sorry, you do not have permissions to view this page" =>
        "Sie sind nicht berechtigt, diese Seite zu sehen",
    "System default" => "Standardeinstellung",
    "Target" => "Ziel",
    "The intro page will be created automatically if you enable one or more options." =>
        "Die Einstiegsseite wird automatisch erzeugt, sobald eine der Optionen aktiviert wird.",
    "The visitor will be forwarded depending on his browser language" =>
        "Der Besucher wird auf Basis der eingestellten Browsersprache weitergeleitet",
    "The visitor will be forwarded by analyzing the subdomain" =>
        "Der Besucher wird auf Basis der Subdomain weitergeleitet",
    "There is already a page for this language!" =>
        "Für diese Sprache ist bereits eine Seite vorhanden!",
    "These settings are page based, to manage global settings, goto Settings -> Header files." =>
        "Diese Einstellungen sind seitenbasiert, globale Einstellungen können unter Einstellungen -> Kopfdateien vorgenommen werden.",
    "This page is used if no page is found for the browser language, or the browser language cannot be determined" =>
        "Diese Seite wird verwendet wenn für die Browsersprache keine Seite konfiguriert ist, oder die Browsersprache nicht ermittelt werden konnte",
    "This will delete the intro page!" => "Die Einstiegsseite wird gelöscht!",
    "Title" => "Seitentitel",
    "Top frame" => "Top Frame",
    "Type" => "Typ",
    "Unable to create the page: " => "Seite kann nicht angelegt werden: ",
    "Unable to re-create the access file!" =>
        "Fehler beim Erneuern der Zugangsdatei!",
    "View page" => "Ansicht",
    "Visibility" => "Sichtbarkeit",
    "You can manage Javascript- and CSS-Files resp. jQuery plugins to be loaded into the page header here." =>
        "Hier können Javascript- und CSS-Dateien bzw. jQuery Plugins verwaltet werden, die in den Seitenkopf geladen werden sollen.",
    'You cannot modify sections. Please enable "Manage section".' =>
        'Es können keine Sektionen verwaltet werden. Bitte "Sektionen verwalten" einschalten.',
    "You do not have the permission to add a page." =>
        "Sie haben nicht die notwendigen Berechtigungen zum Anlegen einer Seite.",
    "You do not have the permission add a page here." =>
        "Sie haben nicht die notwendigen Berechtigungen zum Anlegen einer Seite an dieser Stelle.",
    "You do not have the permission to delete a page." =>
        "Sie haben nicht die notwendigen Berechtigungen zum Löschen einer Seite.",
    "You do not have the permission to delete this page." =>
        "Sie haben nicht die notwendigen Berechtigungen zum Löschen dieser Seite.",
    "You do not have the permissions to modify this page." =>
        "Sie haben nicht die notwendigen Berechtigungen zum Bearbeiten dieser Seite.",
    "Re-create access file" => "Zugangsdatei erneuern",
    // ----- page preview -----
    "Visibility of this page" => "Sichtbarkeit dieser Seite",
    "Black Cat CMS Page Preview" => "Black Cat CMS Seitenvorschau",
    "none" => "keine (der Besucher kann die Seite nicht aufrufen)",
    // ----- settings -----
    "0 means default, which is 7200s = 2 hours; allowed values" =>
        "0 bedeutet Standardeinstellung, diese ist 7200 Sekunden = 2 Stunden; erlaubte Werte",
    "A user can have an individual start page in the backend. Enable this option to use this feature. The start page is set in the user settings." =>
        "Benutzer können individuelle Einstiegsseiten im Backend haben. Diese Option aktivieren, um diese Funktion zu nutzen. Die Startseite wird in den Benutzereinstellungen gesetzt.",
    "After some actions, success or error messages are displayed. This is the time such messages are shown before the backend redirects you back to the calling page." =>
        "Nach manchen Aktionen werden zunächst Erfolgs- oder Fehlernachrichten angezeigt. Dies ist die Zeit, bevor zur aufrufenden Seite zurückgeleitet wird.",
    "Allow frontend login" => "Anmeldung im Frontend erlauben",
    "Allow mail address as login name" => "Erlaube Mailadresse als Login-Namen",
    "Allowed filetypes on upload" => "Erlaubte Dateitypen für Dateiupload",
    "Allowed values" => "erlaubte Werte",
    "Allows to assign user based home folders located under media. Please remember to create the folders in the media section. To assign a home folder to a user, proceed to the user settings." =>
        "Erlaubt die Zuweisung benutzerspezifischer Homeverzeichnisse unterhalb von media. Die Verzeichnisse müssen im Media-Bereich angelegt werden. Die Zuweisung erfolgt in den Benutzereinstellungen.",
    "Allows to set a maximal login name length. Set a higher value if you allow email addresses as login names." =>
        "Erlaubt das Setzen einer Maximallänge für Benutzerkennungen. Sofern Mailadressen als Loginkennungen zugelassen sind, sollte ein höherer Wert verwendet werden.",
    "Allows to set a maximal password length. You should not restrict the maximal length too much." =>
        "Erlaubt das Setzen einer Maximallänge für Kennworte. Die Kennwortlänge sollte nicht zu sehr eingeschränkt werden.",
    "Allows to set a minimal login name length. Good values start with 5 chars. Please note that this should not be changed if a large number of users already exists." =>
        "Erlaubt das Setzen einer Mindestlänge für Benutzerkennungen. Gute Werte beginnen mit 5 Zeichen. Diese Einstellung sollte nicht mehr verändert werden, wenn es schon viele Benutzerkonten gibt.",
    "Allows to set a minimal password length. Please note that longer passwords are more secure." =>
        "Erlaubt das Setzen einer Minimallänge für Kennworte. Längere Kennworte sind in der Regel sicherer.",
    "Allows to use email addresses as login names. Influences the list of allowed chars in the user login." =>
        "Erlaubt die Verwendung von Mailadressen als Loginnamen. Beeinflußt die Liste erlaubter Zeichen im Loginnamen.",
    "Allows visitors to sign-up from the frontend to become members of your site and get access to special regions. Any sign-up will be accepted automatically and the new user will become a member of the group you select here." =>
        "Besucher können sich im Frontend registrieren und werden automatisch Mitglied der hier gewählten Gruppe. Registrierungen werden automatisch akzeptiert, das heißt das Konto wird automatisch freigeschaltet.",
    "Backend settings" => "Backend Einstellungen",
    "Backend theme" => "Backend Layoutvorlage",
    "By default, wrong login attempts are only saved in the session. To lock the user account after the max. attempts are reached, use the appropriate security setting. (Security -> Disable user accounts when max login attempts is reached)" =>
        "Standardmäßig werden fehlerhafte Loginversuche nur in der Session gespeichert. Um das Benutzerkonto automatisch zu sperren, die entsprechende Sicherheitseinstellung verwenden. (Sicherheit -> Benutzerkonto deaktivieren, wenn die max. Anzahl Anmeldeversuche überschritten wurde).",
    "Check mime type of uploaded files" =>
        "MIME Typ bei hochgeladenen Dateien prüfen",
    "Choose a backend theme." => "Backend-Layout auswählen",
    'Choose a template variant here. Available variants are defined in the template\'s info.php.' =>
        "Hier eine Template-Variante auswählen. Die verfügbaren Varianten sind in der info.php des Templates definiert.",
    "Default MIME type" => "Standard MIME Typ",
    "for better security, choose 16 or more" =>
        "für erhöhte Sicherheit 16 oder mehr verwenden",
    "Frontend settings" => "Frontend Einstellungen",
    "Global headers" => "Globale Kopfdateien",
    "If no editors are listed here, you have to install one first." =>
        "Wenn hier keine Editoren aufgelistet sind, muß zunächst einer installiert werden.",
    "If the frontend template supports this, a login box is rendered on the frontpage. You may also use the Login box Droplet for this case." =>
        "Sofern das Frontend-Template es unterstützt, wird eine Loginbox angezeigt. Alternativ kann das Loginbox Droplet verwendet werden.",
    "If you enable maintenance mode, your complete site will be OFFLINE!" =>
        "Wenn Sie den Wartungsmodus aktivieren, ist die komplette Seite OFFLINE!",
    "In maintenance mode, only the page you choose here will be exposed to the visitor. All other pages are redirected to the maintenance page." =>
        "Im Wartungsmodus ist nur die hier eingestellte Seite für den Besucher zugänglich. Alle anderen Seiten werden auf die Wartungsseite umgeleitet.",
    "Individual page: Droplet for search result" =>
        "Individuelle Seite: Droplet für Suchergebnis",
    "Language & time" => "Sprache und Zeit",
    "Mailer library" => "Mailbibliothek",
    "Mailer settings" => "Maileinstellungen",
    "Maintenance mode" => "Wartungsmodus",
    "Media directory" => "Medienverzeichnis",
    "Min. Login name length" => "Mindestlänge Anmeldename",
    "Min. password length" => "Mindestlänge Kennwort",
    "Max. Login name length" => "Maximallänge Anmeldename",
    "Max. password length" => "Maximallänge Kennwort",
    "must begin with a letter or has invalid signs" =>
        "muss mit einem Buchstaben beginnen oder hat ung&uuml;ltige Zeichen",
    "No groups found" => "Keine Gruppen gefunden",
    "[none (use internal)]" => "[keine (interne benutzen)]",
    "Page to show in maintenance mode" => "Im Wartungsmodus Seite anzeigen",
    'Page to show on 404 "Not found" error' => '404 "Not found" Fehlerseite',
    "Pages directory" => "Seitenverzeichnis",
    "Pages extension" => "Dateierweiterung für Seiten",
    "Please choose a Mailer library and enter a valid sender address and click [Save] to send a test mail" =>
        "Bitte eine Mailbibliothek auswählen und eine gültige Absender-Adresse eingeben und [Speichern] klicken, um eine Testmail versenden zu können.",
    "Please enter a value between 10 and 120 seconds" =>
        "Bitte einen Wert zwischen 10 und 120 Sekunden eingeben",
    "Please note: The SMTP password will be stored as plain text in the settings table!" =>
        "Bitte beachten: Das SMTP Kennwort wird im Klartext in der Datenbank gespeichert!",
    "Search settings" => "Sucheinstellungen",
    "Section-Anchor text" => "Präfix für Section-Anker",
    "Select the frontend template you wish to use as default. You can choose different templates on a per-page-level." =>
        "Das Standard-Template für das Frontend auswählen. Es können seitenbasiert abweichende Templates eingestellt werden.",
    "SEO settings" => "SEO Einstellungen",
    "Server Operating System" => "Serverbetriebssystem",
    "Server settings" => "Servereinstellungen",
    "Session identifier" => "Session ID",
    "You have to log in again if you change this value." =>
        "Sie müssen sich erneut einloggen, wenn sie diesen Wert ändern.",
    "Session path" => "Pfad zur Session",
    "Sets which PHP errors are reported. For development, use E_ALL&E_STRICT. For production, use E_NONE." =>
        "Bestimmt, welche PHP-Fehler berichtet werden. Für Entwicklungsumgebungen E_ALL&E_STRICT verwenden. Für Produktionsumgebungen E_NONE.",
    "Settings saved" => "Einstellungen gespeichert",
    "Set to at least 255 if mail address is allowed!" =>
        "Mindestens 255 wenn Mailadresse als Login-Name erlaubt",
    "Should be at least" => "Empfehlung (mindestens)",
    "SMTP timeout" => "SMTP Timeout",
    "System settings" => "Systemeinstellungen",
    "The default MIME type is used if the real MIME type cannot be encountered." =>
        "Der Standard-MIME-Typ wird verwendet, wenn der tatsächliche MIME-Typ nicht ermittelt werden kann.",
    "The template may use this as a global footer." =>
        "Das Template kann dies als globalen Seitenfuß verwenden.",
    "The template may use this as a global header." =>
        "Das Template kann dies als globalen Seitenkopf verwenden.",
    "This is the charset to be used for both the frontend and the backend. We recommend to use UTF-8." =>
        "Dies ist der Zeichensatz für Frontend und Backend. Wir empfehlen UTF-8 zu verwenden.",
    "This is the default date format. This setting will be used for guests and as a default for new users." =>
        "Das Standard-Datumsformat. Diese Einstellung wird für Gäste und neu erstellte Benutzerkonten verwendet.",
    "This is the default timezone. This setting will be used for guests and as a default for new users." =>
        "Das Standard-Zeitzone. Diese Einstellung wird für Gäste und neu erstellte Benutzerkonten verwendet.",
    "This is the default language of the system." =>
        "Die Standard-Sprache des Systems.",
    "This is the default timezone. This setting will be used for guests and as a default for new users." =>
        "Die Standard-Zeitzone. Diese Einstellung wird für Gäste und neu erstellte Benutzerkonten verwendet.",
    "This is the default time format. This setting will be used for guests and as a default for new users." =>
        "Das Standard-Zeitformat. Diese Einstellung wird für Gäste und neu erstellte Benutzerkonten verwendet.",
    "This is the only page the visitor sees in maintenance mode. This page should have setting [hidden]." =>
        "Dies ist die einzige Seite, die der Besucher im Wartungsmodus sieht. Die Seite sollte die Einstellung [versteckt] haben.",
    "This will allow to use SEO friendly URLs like http://www.yourdomain.com/path/to/page instead of http://www.yourdomain.com/page/path/to/page.php" =>
        "Erlaubt die Nutzung SEO-freundlicher URLs, wie http://www.yourdomain.com/path/to/page statt http://www.yourdomain.com/page/path/to/page.php",
    "Session lifetime" => "Session Gültigkeitsdauer",
    "Tokens are used to protect against CSRF attacks. Too short token lifetimes will cause problems, so change this setting wisely." =>
        "Tokens werden zum Schutz gegen CSRF-Attacken verwendet. Eine zu kurze Gültigkeitsdauer verursacht Probleme, daher die Einstellung bitte mit Bedacht ändern.",
    "Update sitemap.xml on save" => "Beim Speichern sitemap.xml erneuern",
    "Upload security settings" => "Einstellungen für Datei-Uploads",
    "Use initial page" => "Standard-Startseite verwenden",
    "Used for the description META attribute. The description should be a nice &quot;human readable&quot; text with 70 up to 156 characters." =>
        "Wird für das description-META-Attribut verwendet. Die Beschreibung sollte ein &quot;menschenlesbarer&quot; Text mit mindestens 70 und bis zu 156 Zeichen sein.",
    "Used for the keywords META attribute. Most search engines do not use this anymore." =>
        "Wird für das keywords-META-Attribut verwendet. Die meisten Suchmaschinen verwenden diese Werte nicht mehr.",
    "Used for the title tag in the HTML header." =>
        "Wird für das title-Tag im HTML-Seitenkopf verwendet.",
    "User settings" => "Benutzereinstellungen",
    "You can manage global Javascript- and CSS-Files resp. jQuery plugins to be loaded into all page headers here." =>
        "Hier können globale Javascript- und CSS-Dateien bzw. jQuery Plugins verwaltet werden, die auf allen Seiten in den Seitenkopf geladen werden sollen.",
    "Website description" => "Webseitenbeschreibung",
    "Website keywords" => "Schl&uuml;sselw&ouml;rter",
    "Website title" => "Webseitentitel",
    "You must enter details for the following fields" =>
        "Bitte folgende Angaben erg&auml;nzen",
    // ----- mailer -----
    'Default "from" mail' => 'Standard "VON" Adresse',
    "Default sender name" => "Standard Absender Name",
    'Default for SSL is 587; please check the configuration instructions at your provider\'s homepage for details.' =>
        "Standardport für SSL ist 587; bitte sehen Sie in der Dokumentation Ihres Providers nach.",
    'Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by the CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.' =>
        'Bitte geben Sie eine Standard "VON" Adresse und einen Sendernamen an. Als Absender Adresse empfiehlt sich ein Format wie: <strong>admin@IhreWebseite.de</strong>. Manche E-Mail Provider (z.B. <em>mail.de</em>) stellen keine E-Mails zu, die nicht &uuml;ber den Provider selbst verschickt wurden, in der Absender Adresse aber den Namen des E-Mail Providers <em>name@mail.de</em> enthalten. Die Standard Werte werden nur verwendet, wenn keine anderen Werte von Black Cat CMS gesetzt wurden. Wenn Ihr Service Provider <acronym title="Simple Mail Transfer Protocol">SMTP</acronym> anbietet, sollten Sie diese Option f&uuml;r ausgehende E-Mails verwenden.',
    "Mail routine" => "E-Mail Routine",
    'Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Black Cat CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.' =>
        'Bitte geben Sie unten eine Standard-Absenderadresse und einen Namen an. Es wird empfohlen, eine Adresse wie <strong>admin@yourdomain.com</strong> zu verwenden. Einige Mail-Provider weisen Adressen wie <em>name@mail.com</em> eventuell ab, wenn Sie über einen fremden Mailrelay gesendet werden. (Spamschutz)<br /><br />Die Standardwerte werden nur verwendet, wenn keine anderen Werte angegeben wurden. Wenn Ihr Server <acronym title="Simple mail transfer protocol">SMTP</acronym> unterstützt, können Sie diese Option für ausgehende Mails verwenden.',
    '<strong>SMTP Mailer Settings:</strong><br />The settings below are only required if you want to send mails via <acronym title="Simple mail transfer protocol">SMTP</acronym>. If you do not know your SMTP host or you are not sure about the required settings, simply stay with the default mail routine: PHP MAIL.' =>
        '<strong>SMTP Maileinstellungen:</strong><br />Die nachfolgenden Einstellungen m&uuml;ssen nur angepasst werden, wenn Sie E-Mail &uuml;ber <acronym title="Simple Mail Transfer Protocol">SMTP</acronym> verschicken wollen. Wenn Sie Ihren SMTP Server nicht kennen, oder Sie sich unsicher bei den Einstellungen sind, verwenden Sie einfach die Standard E-Mail Routine: PHP MAIL.',
    "PHP MAIL" => "PHP MAIL",
    "Please make sure your provider supports SSL before enabling this feature!" =>
        "Bitte vor Aktivierung sicherstellen, dass der Provider SSL unterstützt!",
    "Send test mail" => "Testmail verschicken",
    "SMTP Authentification" => "SMTP Authentifikation",
    "only activate if your SMTP host requires authentification" =>
        "nur aktivieren, wenn SMTP Authentifizierung ben&ouml;tigt wird",
    "SMTP Password" => "SMTP Passwort",
    "SMTP Username" => "SMTP Benutzername",
    "The test eMail could not be sent! Please check your settings!" =>
        "Das Versenden der Testmail ist fehlgeschlagen! Bitte die Einstellungen pr&uuml;fen!",
    "The test eMail was sent successfully. Please check your inbox." =>
        "Die Testmail wurde erfolgreich verschickt.",
    "This is the required test mail: CAT mailer is working" =>
        "Dies ist die angeforderte Testmail: Die Maileinstellungen funktionieren",
    "Transport security" => "Übertragungssicherheit",
    "Trying to send testmail, please wait..." =>
        "Teste Mailversand, bitte warten...",
    "Use SSL" => "SSL verwenden",
    "SSL Port" => "SSL Port",
    // ----- security -----
    "Cookie SameSite directive" => "Cookie SameSite Direktive",
    "Disable user accounts when max login attempts is reached" =>
        "Benutzerkonto deaktivieren, wenn die max. Anzahl Anmeldeversuche überschritten wurde",
    "In &quot;Strict&quot; mode, the cookie is not sent with absolutely no cross-site request. The &quot;lax&quot; mode allows the cookie to be sent with some &quot;secure&quot; cross-site requests. None&quot; disables any security." =>
        "Im Modus &quot;Strict&quot; wird das Cookie bei absolut keinem Cross-Site-Request mitgesendet. Der Modus &quot;Lax&quot; erlaubt das Mitsenden des Cookies bei einigen &quot;sicheren&quot; Cross-Site-Requests. &quot;None&quot; deaktiviert jedwede Sicherheit.",
    "<strong>Note:</strong> Changing this setting does not affect already existing cookies." =>
        "<strong>Hinweis:</strong> Eine Änderung dieser Einstellung wirkt sich nicht auf bereits vorhandene Cookies aus.",

    // ----- media -----
    "All files have been uploaded successfully." =>
        "Alle Dateien erfolgreich übertragen",
    "Are you sure you want to delete the directory {name}" =>
        "Wollen Sie das Verzeichnis {name} wirklich löschen?",
    "at" => "um",
    "Change settings" => "Einstellungen ändern",
    "Choose a file..." => "Datei wählen...",
    "Create new folder" => "Neues Verzeichnis",
    "Created at" => "Angelegt am",
    "Delete folder" => "Verzeichnis löschen",
    "Delete folder/file" => "Verzeichnis/Datei löschen",
    "Delete zip archive after unpacking" =>
        "ZIP Archiv nach dem Entpacken löschen",
    "Do you really want to delete this file?" =>
        "Soll die Datei wirklich gelöscht werden?",
    "Do you really want to delete this folder?" =>
        "Soll dieses Verzeichnis wirklich gelöscht werden?",
    "Duplicate folder/file" => "Verzeichnis/Datei duplizieren",
    "File deleted successfully" => "Datei erfolgreich gelöscht",
    "File size" => "Dateigröße",
    "File type" => "Dateityp",
    'File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).'
        => 'Die Größe der Datei überschreitet die Einstellung upload_max_filesize in der php.ini.',
    'File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).'
        => 'Die Größe der Datei überschreitet die Einstellung MAX_FILE_SIZE des HTML-Formulars.',
    'File upload error (the uploaded file was only partially uploaded).'
        => 'Die Datei wurde nur zum Teil hochgeladen.',
    'File upload error (no file was uploaded).'
        => 'Es wurde keine Datei hochgeladen.',
    'File upload error (missing a temporary folder).'
        => 'Kein temp-Verzeichnis gefunden.',
    'File upload error (failed to write file to disk).'
        => 'Die Datei konnte nicht gespeichert werden.',
    "Folder created successfully" => "Verzeichnis erfolgreich angelegt",
    "Media" => "Medien",
    "New folder" => "Neues Verzeichnis",
    "No file extension found." => "Keine Dateiendung gefunden.",
    "No file selected..." => "Keine Datei ausgewählt...",
    "No files available" => "Keine Dateien vorhanden",
    "No preview available" => "Keine Vorschau vorhanden",
    "Overwrite existing files" => "Vorhandene Dateien überschreiben",
    "Rename" => "Umbenennen",
    "Rename folder/file" => "Verzeichnis/Datei umbenennen",
    "Rename successful" => "Umbenennen erfolgreich",
    "Unable to write to the target directory" =>
        "Kann nicht in das Zielverzeichnis schreiben",
    "Unpack zip archive" => "ZIP Archiv entpacken",
    "Upload File(s)" => "Datei(en) hochladen",
    'You don\'t have the permission to delete this file.' =>
        "Sie haben nicht die notwendigen Berechtigungen, um diese Datei zu löschen.",
    // ----- users/groups -----
    "One-time password" => "Einmal-Passwort",
    "Recommended: The user must change his password the next time he logs in." =>
        "Empfohlen: Der Benutzer muss sein Passwort beim nächsten Login ändern.",
    "Activate user" => "Benutzer aktivieren",
    "Add user" => "Benutzer anlegen",
    "Add group" => "Gruppe anlegen",
    "added" => "hinzugefügt",
    "Backend access" => "Backend-Zugang",
    "Backend page" => "Backend Bereich",
    "Create folder" => "Verzeichnisse anlegen",
    "Delete group" => "Gruppe löschen",
    "Delete user" => "Benutzer löschen",
    "Display name" => "Anzeigename",
    "Do you really want to delete this group?" =>
        "Soll diese Gruppe wirklich gelöscht werden?",
    "Do you really want to delete this user?" =>
        "Soll dieser Benutzer wirklich gelöscht werden?",
    "Frontend page" => "Frontend Seite",
    "General System" => "Allgemein System",
    "Group added successfully" => "Gruppe erfolgreich angelegt",
    "Group deleted successfully" => "Gruppe erfolgreich gelöscht",
    "Group loaded successfully" => "Gruppe erfolgreich geladen",
    "Group name" => "Gruppenname",
    "Group saved successfully" => "Gruppe erfolgreich gespeichert",
    "Groups" => "Gruppen",
    "Home folder" => "Heimatverzeichnis",
    "Initial page" => "Standard Startseite",
    "Installed admintools" => "Installierte Admin-Tools",
    "Installed modules" => "Installierte Erweiterungen",
    "Installed templates" => "Installierte Templates",
    "Languages" => "Sprachen",
    "Manage groups" => "Gruppenverwaltung",
    "Manage users" => "Benutzerverwaltung",
    "Members" => "Mitglieder",
    "Minimum length for user name: {{ name }} chars, Minimum length for Password: {{ password }} chars!" =>
        "Minimale Länge für Benutzerkennung: {{ name }} Zeichen, minimale Länge für Kennwort: {{ password }} Zeichen!",
    "Modify content" => "Inhalte ändern",
    "Modify group" => "Gruppe ändern",
    "Modify groups" => "Gruppen bearbeiten",
    "Modify intro page" => "Einstiegsseite ändern",
    "Modify settings" => "Einstellungen ändern",
    "Module permissions" => "Modulberechtigungen",
    "Modify user" => "Benutzer ändern",
    "Modules" => "Module",
    "Optional parameters" => "Optionale Parameter",
    "Password" => "Kennwort",
    "Password requires an uppercase letter." => "Das Kennwort muss einen Großbuchstaben enthalten.",
    "Password requires a number." => "Das Kennwort muss eine Ziffer enthalten.",
    "Password requires a special character." => "Das Kennwort muss ein Sonderzeichen enthalten.",
    "Password should not contain your username." => "Der Benutzername sollte nicht Teil des Kennwortes sein.",
    "Permissions" => "Rechte",
    "Please note: You should only enter values in those fields if you wish to change this users password" =>
        "Hinweis: In die folgenden Felder nur etwas eintragen, wenn das Kennwort geändert werden soll",
    "Removing group" => "Entferne Gruppe",
    "Removing user" => "Entferne Benutzer",
    "Retype password" => "Kennwort wiederholen",
    "Retype new password" => "Neues Kennwort wiederholen",
    "Set new password" => "Neues Passwort setzen",
    "Save group" => "Gruppe speichern",
    "Save user" => "Benutzer speichern",
    "Saving user" => "Speichere Benutzer",
    "saved" => "gespeichert",
    "Settings" => "Einstellungen",
    "Start" => "Start (Dashboard)",
    "System permissions" => "Systemberechtigungen",
    "Template permissions" => "Templateberechtigungen",
    "The group cannot be deleted as it has members" =>
        "Die Gruppe kann nicht gelöscht werden da sie noch Mitglieder hat",
    "The user can add new pages" => "Der Benutzer darf neue Seiten anlegen",
    "The user can add new root pages" =>
        "Der Benutzer darf neue Basisseiten (Level 0) anlegen",
    "The user can delete pages" => "Der Benutzer darf Seiten löschen",
    "The user can enter the backend" =>
        "Der Benutzer darf das Backend aufrufen",
    "The user can modify existing content" =>
        "Der Benutzer darf bestehende Inhalte ändern",
    "The user can modify page settings" =>
        "Der Benutzer darf Seiteneinstellungen ändern",
    "The user can modify the intro page" =>
        "Der Benutzer darf die Einstiegsseite ändern",
    "The user can see the pages tree in the backend" =>
        "Der Benutzer sieht den Seitenbaum im Backend",
    "Upload files" => "Dateien hochladen",
    "User loaded successfully" => "Benutzer erfolgreich geladen",
    "User {{action}} successfully" => "Benutzer erfolgreich {{action}}",
    "Username" => "Benutzerkennung",
    "Users" => "Benutzer",
    "You need to choose at least one group" =>
        "Sie müssen mindestens eine Gruppe wählen",
    // ----- settings -----
    "Allowed wrong login attempts" => "Maximale fehlerhafte Anmeldeversuche",
    "Allows to completely disable the [Manage Sections] option of all pages, disabling the capability to add/remove or reorder the sections of any page" =>
        "Ermöglicht den Zugang zu [Abschnitte verwalten] Einstellungen auf allen Seiten zu deaktivieren und somit die Möglichkeit, Abschnitte hinzuzufügen, zu löschen oder neu zu sortieren zu unterbinden.",
    'By default, the default or \'home\' page is the very first page listed in page tree; this options allows to have an introductory page that is totally different to – and outside the rest of – your site' =>
        "Standardmäßig ist die erste sichtbare Seite im Seitenbaum auch die Einstiegsseite für Besucher; diese Einstellung erlaubt es, eine externe Eingangsseite zu verwenden, die sich komplett vom Rest der Seiten unterscheidet",
    "Create GUID" => "GUID erzeugen",
    "Disabled" => "Deaktiviert",
    "Enabled" => "Aktiviert",
    "Homepage redirection" => "Homepage-Weiterleitung",
    "If the template you are using supports multiple blocks, and you wish to use this feature, enable it here; requires [Manage sections] to be enabled, too" =>
        "Wenn die verwendete Designvorlage mehrere Blöcke unterstützt, schalten Sie diese Option hier ein; [Sektionen verwalten] muss ebenfalls aktiviert sein",
    "If the template you are using supports multiple menus, and you wish to use this feature, enable it here; enabling this feature while using a template with only 1 menu has no effect" =>
        "Wenn die verwendete Designvorlage mehrere Menüs unterstützt, schalten Sie diese Option hier ein; bei Vorlagen mit nur einem Menü hat die Einstellung keine Auswirkungen",
    "Individual page: DropLEP for search result" =>
        "Individuelle Seite: DropLEP für Suchergebnisse",
    "Individual page: PAGE_ID for search result" =>
        "Individuelle Seite: PAGE_ID für Suchergebnisse",
    "Inline" => "Inline",
    "Install date and time" => "Installationsdatum und Uhrzeit",
    "Intro page" => "Einstiegsseite",
    "Linux/Unix based" => "Linux/Unix basierend",
    "Manage sections" => "Sektionen verwalten",
    "Max lines of excerpt" => "Maximale Anzahl Zeilen",
    "Max time to gather excerpts per module" => "Max. Zeit pro Modul",
    "Max. width/height of images in search result" =>
        "Max. Breite/Höhe für Bilder in Suchergebnis",
    "Maximum depth of page tree" =>
        "Maximale Verschachtelungstiefe für den Seitenbaum",
    "Module-order for searching" => "Modulreihenfolge für Suche",
    "Multiple menus" => "Mehrere Menüs",
    "Page languages" => "Seitensprache",
    "Page level limit" => "Seitenlevel Limit",
    "Page spacer" => "Seitentrennzeichen",
    "Page statistics" => "Seitenstatistik",
    "Page trash" => "Seitenmülleimer",
    "Pages are deleted at once" => "Seiten werden sofort gelöscht",
    'Pages are marked as \'deleted\' only and can be restored' =>
        'Seiten werden zunächst nur als \'gelöscht\' markiert und können wiederhergestellt werden',
    "Personal folders" => "Persönliche Ordner",
    "Please note: This filters the output of every module, so it may break the layout if the module output isn't valid!" =>
        "Hinweis: Hiermit wird die Ausgabe aller Module gefiltert, was dazu führen kann, daß das Layout zerstört wird, wenn das Modul non-valides HTML ausgibt!",
    "Please note: this is only recommended for testing environments" =>
        "Bitte beachten: Nur für Testumgebungen empfohlen",
    "Redirect after" => "Weiterleitung nach",
    "Redirect link (URL) for non-public content" =>
        "Redirect link (URL) für nicht-öffentliche Inhalte",
    "Saving settings" => "Speichere Einstellungen",
    "Search for images" => "Nach Bildern suchen",
    "Search for page descriptions" => "Seitenbeschreibungen einbeziehen",
    "Search for page keywords" => "Schlüsselworte in Suche einbeziehen",
    "Search in non-public content" =>
        "Nicht-öffentliche Inhalte in Suche einbeziehen",
    "Search library" => "Suchbibliothek",
    "Sections blocks" => "Blöcke verwalten",
    "Security settings" => "Sicherheit",
    "Show page description in search result" =>
        "Seitenbeschreibung in Suchergebnis zeigen",
    "Signup" => "Benutzerregistrierung",
    "SMTP host" => "SMTP Servername",
    "SMTP authentification" => "SMTP Authentifizierung",
    "SMTP username" => "SMTP Benutzerkennung",
    "SMTP password" => "SMTP Kennwort",
    "Standard page: Template for search result" =>
        "Standardseite: Template für Suchergebnisse",
    "The file [.htaccess] already exists! BlackCat will save the suggested Rewrite Rules into file [htaccess_BlackCatCMS.txt]. You will have to add them manually or rename that file to [.htaccess]." =>
        "Die Datei [.htaccess] existiert bereits! BlackCat speichert die empfohlenen Rewrite Regeln in die Datei [htaccess_BlackCatCMS.txt]. Sie müssen sie manuell hinzufügen oder diese Datei in [.htaccess] umbenennen.",
    "Use HTML Purifier to protect WYSIWYG content" =>
        "WYSIWYG Inhalte mit HTMLPurifier filtern",
    "Use image from content page in search result" =>
        "Bild aus Inhalt in Suchergebnis verwenden",
    "Use jQuery" => "jQuery verwenden",
    "Use jQuery UI" => "jQuery UI verwenden",
    "Use short URLs (Apache webserver only, requires mod_rewrite!)" =>
        "Kurze URLs verwenden (nur Apache Webserver, erfordert mod_rewrite!)",
    "Variant" => "Variante (Skin)",
    "Website footer" => "Webseite Fußzeile",
    "Website header" => "Webseite Kopf",
    'When a visitor first enters your site, the system \'silently\' redirects them to the default page, without changing the address that is displayed in the location bar. If this option is enabled, the redirection will be visible.' =>
        'Wenn ein Besucher Ihre Seiten betritt, leitet das System ihn \'stillschweigend\' auf die Einstiegsseite um, ohne dass sich die im Browser angezeigte Adresse ändert. Wenn diese Option aktiviert ist, ist diese Umleitung im Browser sichtbar.',
    "When enabled, the system automatically hides any page from the website menu that is not in the language of the current logged-in user; guest users will see only the pages in the language chosen as the default for the site" =>
        "Wenn aktiviert, verbirgt das System automatisch alle Seiten im Menü, die nicht auf die vom momentan angemeldeten Benutzer eingestellten Sprache eingestellt sind; Gäste sehen die Seiten in der eingestellten Standardsprache",
    "When reaching this number, more login attempts are not possible for this session." =>
        "Nach Erreichen dieser Anzahl sind keine weiteren Anmeldeversuche mehr möglich.",
    "World-writeable file permissions" => "Jeder darf schreiben",
    // ----- addons - install.php -----
    "Addon successfully installed" => "Addon erfolgreich installiert",
    "Addon successfully upgraded" => "Modul erfolgreich aktualisiert",
    "Unable to extract the file. Please check the ZIP format." =>
        "Kann die Datei nicht entpacken. Bitte das ZIP Format prüfen.",
    "Installation failed. Your system does not fulfill the defined requirements. Please fix the issues summarized below and try again." =>
        "Installation fehlgeschlagen. Das System erfüllt nicht die notwendigen Voraussetzungen. Bitte die untenstehenden Voraussetzungen prüfen und erneut probieren.",
    "Invalid installation file. {{error}}" =>
        "Ungültige Installationsdatei. {{error}}",
    "Unable to find info.php" => "info.php nicht gefunden",
    "Invalid installation file. Wrong extension. Please check the ZIP format." =>
        "Ungültige Installationsdatei. Falsche Dateiendung. Bitte das ZIP Format prüfen.",
    "Module created successfully" => "Modul erfolgreich angelegt",
    "Not installed yet" => "Noch nicht installiert",
    "Pre installation check failed" =>
        "Prüfung der Installationsvoraussetzungen fehlgeschlagen",
    "Pre installation check successful" =>
        "Prüfung der Installationsvoraussetzungen erfolgreich",
    "Precheck result for addon" => "Ergebnis für Addon",
    "Unable to install the module" => "Fehler beim Installieren des Moduls",
    "Unable to install - error copying files" =>
        "Installation nicht möglich - Fehler beim Kopieren der Dateien",
    "Upgraded successfully" => "Erfolgreich aktualisiert",
    "Installed successfully" => "Erfolgreich installiert",
    "Install/Upgrade of add-on failed" => "Installation/Update fehlgeschlagen",
    "Required Addons" => "Erforderliche Addons",
    "Requirement" => "Anforderung",
    "Required" => "Gefordert",
    "Current" => "Vorhanden",
    // ----- addons - CAT_Helper_Addons -----
    "Marked as mandatory" => "Als erforderlich markiert",
    "this page;these pages" => "dieser Seite;diesen Seiten",
    "default template" => "Standardtemplate",
    "default backend theme" => "verwendete Backend Template",
    "standard language" => "Standardsprache",
    'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is the standard WYSWIWYG editor!' =>
        'Kann Modul <span class="highlight_text">{{name}}</span> nicht deinstallieren weil es der Standard-WYSIWYG-Editor ist!',
    'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is marked as mandatory!' =>
        'Kann Modul <span class="highlight_text">{{name}}</span> nicht deinstallieren weil es als erforderlich gekennzeichnet ist!',
    'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is in use on {{pages_string}}:<br /><br />{{pages}}' =>
        'Kann Modul <span class="highlight_text">{{name}}</span> nicht deinstallieren weil es auf {{pages_string}} verwendet wird:<br /><br />{{pages}}',
    'Cannot uninstall this language <span class="highlight_text">{{name}}</span> because it is in use!' =>
        'Die Sprache <span class="highlight_text">{{name}}</span> kann nicht deinstalliert werden, da sie noch verwendet wird!',
    'Cannot uninstall this language <span class="highlight_text">{{name}}</span> because it is the {{type}}!' =>
        'Die Sprache <span class="highlight_text">{{name}}</span> kann nicht deinstalliert werden, sie ist die {{type}}!',
    'Invalid info.php - neither $module_function nor $template_function set' =>
        'Ungültige info.php - weder $module_function noch $template_function gefunden',
    "Invalid language file - missing PHP delimiter" =>
        "Ungültige Sprachdatei - PHP Dings fehlt",
    "invalid directory/language file or info.php is missing, check of language file failed" =>
        "Ungültiges Verzeichnis, ungültige Sprachdatei, oder info.php fehlt.",
    'Cannot uninstall template <span class="highlight_text">{{name}}</span> because it is the {{type}}!' =>
        'Kann Template <span class="highlight_text">{{name}}</span> nicht deinstallieren, weil es das {{type}} ist!',
    'Cannot uninstall template <span class="highlight_text">{{name}}</span> because it is still in use on {{pages}}:' =>
        'Kann Template <span class="highlight_text">{{name}}</span> nicht deinstallieren, weil es auf {{pages}} verwendet wird:',
    "Edit module file(s)" => "Modul-Datei(en) bearbeiten",
    // ----- addons - backend_addons_index.tpl -----
    "A language file with the same name already exists" =>
        "Es existiert bereits eine Sprachdatei mit diesem Namen",
    "A module with the same directory name already exists" =>
        "Es existiert bereits ein Addon mit diesem Verzeichnisnamen",
    "Addon permissions" => "Addon Berechtigungen",
    "Add-On requirements not met" =>
        "Add-On Voraussetzungen nicht erf&uuml;llt",
    "Administration tool" => "Admin-Tool",
    "Same or newer version already installed" =>
        "Gleiche oder neuere Version bereits installiert",
    "An error occured" => "Es ist ein Fehler aufgetreten",
    "Author" => "Autor",
    "Cannot uninstall" => "Deinstallation fehlgeschlagen.",
    "Create new addon" => "Neues Addon erzeugen",
    "DANGER ZONE! This may delete your current data!" =>
        "GEFAHRENZONE! Hier können Daten verloren gehen!",
    "Designed for" => "Erstellt für",
    "Execute install.php manually" => "install.php manuell ausführen",
    "Execute upgrade.php manually" => "upgrade.php manuell ausführen",
    "Function" => "Funktion",
    "If you're adding a language, a language file will be created in the <tt>languages</tt> subfolder." =>
        "Beim Anlegen einer Sprache wird eine Sprachdatei im <tt>languages</tt>-Unterverzeichnis erzeugt.",
    "If you upgrade a module, those settings will have no effect on current permissions." =>
        "Bei einem Update haben die hier gesetzten Rechte keine Auswirkungen.",
    "Incomplete data, please fill out all fields!" =>
        "Unvollständige Daten, bitte alle Felder ausfüllen!",
    "Install addon" => "Erweiterung installieren",
    "Install manually" => "Manuell installieren",
    "Installed" => "Installationsdatum",
    "Invalid info.php - var module_function or var template_function not set" =>
        "Ungültige info.php - Variable module_function oder Variable template_function nicht gesetzt",
    "Invalid installation file. Please check the *.zip format." =>
        "Ung&uuml;ltige Installationsdatei. Bitte *.zip Format pr&uuml;fen.",
    "Invalid language file - missing PHP delimiter" =>
        "Ungültige Sprachdatei - PHP Delimiter fehlt",
    "Library" => "Funktionsbibliothek",
    "License" => "Lizenz",
    "Mark all groups" => "Alle Gruppen markieren",
    "Module created successfully" => "Modul erfolgreich erzeugt",
    "Module details" => "Moduldetails",
    "Module description" => "Beschreibung",
    "Module directory / language code" => "Verzeichnisname / Sprachkürzel",
    "Module / language name" => "Modul-/Sprachname",
    "Module type" => "Modultyp",
    "Module seems to be not installed yet." =>
        "Das Modul ist offenbar noch nicht installiert.",
    "No install.php found! The module cannot be installed!" =>
        "Keine install.php gefunden! Das Modul kann nicht installiert werden!",
    "Please fill out the form to create a new addon. A new directory with the basic files will be created to start with." =>
        "Bitte das Formular vollständig ausfüllen, um ein neues Addon zu erzeugen. Ein Verzeichnis mit den notwendigen Dateien wird als Basis für die weitere Arbeit angelegt.",
    "This module is used on the following pages" =>
        "Dieses Modul wird auf den folgenden Seiten verwendet",
    "Uninstall Addon" => "Addon deinstallieren",
    "Uninstalled successfully" => "Erfolgreich deinstalliert",
    "Unknown" => "Unbekannt",
    "Unmark all groups" => "Keine Gruppe markieren",
    "Upgraded" => "Letzte Aktualisierung",
    "You can customize permissions later on group administration." =>
        "Die Rechte können später in der Gruppenadministration angepaßt werden.",
    "You can execute the module functions manually for modules uploaded via FTP below." =>
        "Die Methoden können unten manuell ausgeführt werden.",
    "You can set permissions for each group to use this addon." =>
        "Es können für jede Gruppe Berechtigungen für dieses Addon gesetzt werden.",
    "When modules are uploaded via FTP (not recommended), the module installation functions install, upgrade or uninstall will not be executed automatically. Those modules may not work correct or do not uninstall properly." =>
        "Wenn Module via FTP hochgeladen werden (nicht empfohlen), werden die Funktionen zur Installation, zum Upgrade oder zur Deinstallation nicht automatisch ausgeführt. Diese Module funktionieren eventuell nicht richtig oder lassen sich nicht deinstallieren.",
    // -----------------------------------------------------------------------------
    // ----- v1.2 -----
    "Create footers.inc.php" => "Datei footers.inc.php erzeugen",
    "Create headers.inc.php" => "Datei headers.inc.php erzeugen",
    "Create precheck.php" => "Datei precheck.php erzeugen",
    "Loading" => "Lade",
    // ----- dashboard -----
    "Add widget" => "Widget hinzufügen",
    "Do you really want to remove this widget from your dashboard?" =>
        "Wollen Sie dieses Widget wirklich vom Dashboard entfernen?",
    "Do you really want to reset your dashboard? This will delete all your settings!" =>
        "Soll das Dashboard wirklich zurückgesetzt werden? Hiermit werden alle Einstellungen gelöscht!",
    "Insert" => "Einfügen",
    "Reset Dashboard" => "Dashboard zurücksetzen",
    "Remove widget" => "Widget entfernen",
    // ----- addons catalog -----
    "Action" => "Aktion",
    "Avail. since" => "Verfügb. seit",
    "Catalog version" => "Katalog Version",
    "Create new" => "Neu erstellen",
    "Current version" => "Aktuelle Version",
    "Do you really want to install this addon?" =>
        "Soll dieses Addon wirklich installiert werden?",
    "Do you really want to uninstall this addon?" =>
        "Soll dieses Addon wirklich deinstalliert werden?",
    "Do you really want to upgrade this addon?" =>
        "Soll dieses Addon wirklich aktualisiert werden?",
    "Install..." => "Installiere...",
    "Install" => "Installieren",
    "Installed version" => "Installierte Version",
    "Show Catalog" => "Katalog",
    "Uninstall" => "Deinstallieren",
    "Uninstall..." => "Deinstalliere...",
    "Update..." => "Aktualisiere...",
    "Update available!" => "Update verfügbar!",
    "Updating catalog..." => "Aktualisiere Katalog...",
    "Upload and install" => "Hochladen und installieren",
    "You need to have addon {{ addon }} version {{ version }} installed for this addon." =>
        "Für dieses Addon muß das Addon {{ addon }} Version {{ version }} installiert sein.",
    "You need to have BlackCat CMS Version {{ version }} installed for this addon. You have {{ version2 }}." =>
        "Für dieses Addon muß BlackCat CMS Version {{ version }} installiert sein. Installiert ist {{ version2 }}.",
    // ----- session -----
    "Your session is about to expire!" => "Ihre Session läuft bald ab!",
    "Keep me signed in" => "Angemeldet bleiben",
    "Sign me out" => "Abmelden",
    "Your session has expired!" => "Ihre Session ist abgelaufen!",
    "Please enter your login details to log in again." =>
        "Bitte geben Sie Ihre Benutzerdaten ein, um sich erneut anzumelden",
    "Please enter your login details!" =>
        "Bitte geben Sie Ihre Benutzerdaten ein!",
    "You will be logged out in" => "Sie werden abgemeldet in:",
    "seconds" => "Sekunden",
    "Remaining session time" => "Verbleibende Sessionzeit",
    // ----- seo -----
    "Default update frequency" => "Vorgabe für Updatehäufigkeit",
    "Sitemap settings" => "Sitemap Einstellungen",
];

// include old lang files
if (defined("WB2COMPAT")) {
    global $HEADING, $TEXT, $MESSAGE, $SETTINGS;
    require dirname(__FILE__) . "/old/" . $language_code . ".php";
}
