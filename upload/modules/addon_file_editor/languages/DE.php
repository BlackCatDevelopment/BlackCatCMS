<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the German text outputs of the module.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.1.0
 * @language	German
 * @translation	Christian Sommer (doc)
 * @platform	Website Baker 2.8
*/

// German module description
$module_description = 'AFE erm&ouml;glicht die Bearbeitung von Text- und Bilddateien installierter Add-ons aus dem Backend. Weitere Infos in der <a href="{WB_URL}/modules/addon_file_editor/help/help_en.html" target="_blank">README</a> Datei.';

// declare module language array
$LANG = array();

// Text outputs for the version check
$LANG[0] = array(
	'TXT_VERSION_ERROR'			=> 'Fehler: Das Modul "Addon File Editor" ben&ouml;tigt Website Baker 2.7 oder h&ouml;her.',
);

// Text outputs overview page (htt/addons_overview.htt)
$LANG[1] = array(
	'TXT_DESCRIPTION'			=> 'Nachfolgende Liste z&auml;hlt alle Addons auf, auf die PHP lesend zugreifen kann. Um Addon ' .
								   'Dateien zu bearbeiten, klicke auf den Namen der Erweiterung. Das Download Symbol erstellt ' .
								   'eine installierbare Sicherungskopie einer Erweiterung und sendet diese an den Browser.',
	'TXT_FTP_NOTICE'			=> 'F&uuml;r rote Addons/Dateien besitzt PHP keine Schreibrechte. Dies ist z.B. der Fall, wenn ' .
								   'Addons per FTP installiert wurden. Um solche Addons bearbeiten zu k&ouml;nnen, muss die ' .
								   '<a class="link" target="_blank" href="{URL_ASSISTANT}">FTP-Unterst&uuml;tzung</a> '.
								   'des Addon File Editors aktiviert sein.',
	'TXT_HEADING'				=> 'Installierte Erweiterungen (Module, Templates, Sprachdateien)',
	'TXT_HELP'					=> 'Hilfe',

	'TXT_RELOAD'				=> 'Neu laden',
	'TXT_ACTION_EDIT'			=> 'Bearbeiten',
	'TXT_ACTION_DELETE'			=> 'L&ouml;schen',
	'TXT_FTP_SUPPORT'			=> ' (&Auml;nderungen nur mit aktivierten FTP-Schreibzugriff m&ouml;glich)',

	'TXT_MODULES'				=> 'Module',
	'TXT_LIST_OF_MODULES'		=> 'Liste der installierten Module',
	'TXT_EDIT_MODULE_FILES'		=> 'Moduldateien bearbeiten',
	'TXT_ZIP_MODULE_FILES'		=> 'Modul zippen und herunterladen',

	'TXT_TEMPLATES'				=> 'Templates',
	'TXT_LIST_OF_TEMPLATES'		=> 'Liste der installierten Templates',
	'TXT_EDIT_TEMPLATE_FILES'	=> 'Templatedateien bearbeiten',
	'TXT_ZIP_TEMPLATE_FILES'	=> 'Template zippen und herunterladen',

	'TXT_LANGUAGES'				=> 'Sprachdateien',
	'TXT_LIST_OF_LANGUAGES'		=> 'Liste der installierten WB-Sprachdateien',
	'TXT_EDIT_LANGUAGE_FILES'	=> 'Sprachdatei bearbeiten',
	'TXT_ZIP_LANGUAGE_FILES'	=> 'Sprachdatei herunterladen',
);

// Text outputs filemanager page (htt/filemanager.htt)
$LANG[2] = array(
	'TXT_EDIT_DESCRIPTION'		=> 'Der Dateimanager erlaubt das bearbeiten, umbenennen, erstellen, l&ouml;schen und hochladen ' .
								   'von Dateien. Text- oder Bildateien, k&ouml;nnen durch klick auf den Dateinamen bearbeitet ' .
								   'oder angzeigt werden.',
	'TXT_BACK_TO_OVERVIEW'		=> 'zur&uuml;ck zur &Uuml;bersicht',

	'TXT_MODULE'				=> 'Modul',
	'TXT_TEMPLATE'				=> 'Template',
	'TXT_LANGUAGE'				=> 'WB Sprachdatei',
	'TXT_FTP_SUPPORT'			=> ' (&Auml;nderungen nur mit aktivierten FTP-Schreibzugriff m&ouml;glich)',

	'TXT_RELOAD'				=> 'Neu laden',
	'TXT_CREATE_FILE_FOLDER'	=> 'Datei/Ordner erstellen',
	'TXT_UPLOAD_FILE'			=> 'Datei hochladen',
	'TXT_VIEW'					=> 'Anzeigen',
	'TXT_EDIT'					=> 'Bearbeiten',
	'TXT_RENAME'				=> 'Umbenennen',
	'TXT_DELETE'				=> 'L&ouml;schen',

	'TXT_FILE_INFOS'			=> 'Datei Informationen',
	'TXT_FILE_ACTIONS'			=> 'Aktionen',
	'TXT_FILE_TYPE_TEXTFILE'	=> 'Textdatei',
	'TXT_FILE_TYPE_FOLDER'		=> 'Verzeichnis',
	'TXT_FILE_TYPE_IMAGE'		=> 'Bilddatei',
	'TXT_FILE_TYPE_ARCHIVE'		=> 'Archivdatei',
	'TXT_FILE_TYPE_OTHER'		=> 'Unbekannt',

	'DATE_FORMAT'				=> 'd.m.y / H:m',
);


// General text outputs for the file handler templates
$LANG[3] = array(
	'ERR_WRONG_PARAMETER'		=> 'Die &uuml;bergebenen Parameter sind fehlerhaft oder unvollst&auml;ndig.',
	'TXT_MODULE'				=> 'Modul',
	'TXT_TEMPLATE'				=> 'Template',
	'TXT_LANGUAGE'				=> 'WB Sprachdatei',
	'TXT_ACTION'				=> 'Aktion',
	'TXT_ACTUAL_FILE'			=> 'Aktuelle Datei',
	'TXT_SUBMIT_CANCEL'			=> 'Abbrechen',
);	

// Text outputs file handler (htt/action_handler_edit_textfile.htt)
$LANG[4] = array(
	'TXT_ACTION_EDIT_TEXTFILE'	=> 'Textdatei bearbeiten',
	'TXT_SUBMIT_SAVE'			=> 'Speichern',
	'TXT_SUBMIT_SAVE_BACK'		=> 'Speichern &amp; Zur&uuml;ck',
	'TXT_ACTUAL_FILE'			=> 'Aktuelle Datei',
	'TXT_SAVE_SUCCESS'			=> 'Datei&auml;nderungen wurden erfolgreich gespeichert.',
	'TXT_SAVE_ERROR'			=> 'Datei konnte nicht gespeichert werden. Bitte Schreibrechte pr&uuml;fen.',
);

// Text outputs file handler (htt/action_handler_rename_file_folder.htt)
$LANG[5] = array(
	'TXT_ACTION_RENAME_FILE'	=> 'Datei/Ordner umbenennen',
	'TXT_OLD_FILE_NAME'			=> 'Datei/Ordner (alt)',
	'TXT_NEW_FILE_NAME'			=> 'Datei/Ordner (neu)',
	'TXT_SUBMIT_RENAME'			=> 'Umbenennen',
	'TXT_RENAME_SUCCESS'		=> 'Datei/Ordner wurde erfolgreich umbenannt.',
	'TXT_RENAME_ERROR'			=> 'Datei/Ordner konnte nicht umbenannt werden. Bitte Schreibrechte und Dateinamen pr&uuml;fen.',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
);

// Text outputs file handler (htt/action_handler_delete_file_folder.htt)
$LANG[6] = array(
	'TXT_ACTION_DELETE_FILE'	=> 'Datei/Ordner l&ouml;schen',
	'TXT_SUBMIT_DELETE'			=> 'L&ouml;schen',
	'TXT_ACTUAL_FOLDER'			=> 'Aktueller Ordner',
	'TXT_DELETE_WARNING'		=> '<strong>Warnung: </strong>Das l&ouml;schen von Dateien oder Ordnern kann nicht ' .
								   'r&uuml;ckg&auml;ngig gemacht werden. Beim L&ouml;schen eines Ordners werden alle darin ' .
								   'enthaltenen Dateien und Unterordner gel&ouml;scht.',
	'TXT_DELETE_SUCCESS'		=> 'Datei/Order wurde erfolgreich gel&ouml;scht.',
	'TXT_DELETE_ERROR'			=> 'Datei/Order konnte nicht gel&ouml;scht werden. Bitte Schreibrechte &uuml;berpr&uuml;fen.<br />' .
								   '<em>Hinweis: Um Ordner &uuml;ber FTP l&ouml;schen zu k&ouml;nnen, darf dieser keine weiteren ' .
								   'Dateien oder Ordner enthalten.</em>',
);

// Text outputs file handler (htt/action_handler_create_file_folder.htt)
$LANG[7] = array(
	'TXT_ACTION_CREATE_FILE'	=> 'Datei/Ordner erstellen',
	'TXT_CREATE'				=> 'Erstelle',
	'TXT_FILE'					=> 'Datei',
	'TXT_FOLDER'				=> 'Ordner',
	'TXT_FILE_NAME'				=> 'Dateiname',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
	'TXT_TARGET_FOLDER'			=> 'Zielverzeichnis',
	'TXT_SUBMIT_CREATE'			=> 'Erstellen',
	'TXT_CREATE_SUCCESS'		=> 'Datei/Ordner wurde erstellt.',
	'TXT_CREATE_ERROR'			=> 'Datei/Ordner konnte nicht erstellt werden. Bitte Schreibrechte und Dateinamen pr&uuml;fen.',
);

// Text outputs file handler (htt/action_handler_upload_file.htt)
$LANG[8] = array(
	'TXT_ACTION_UPLOAD_FILE'	=> 'Datei hochladen',
	'TXT_SUBMIT_UPLOAD'			=> 'Hochladen',

	'TXT_FILE'					=> 'Datei',
	'TXT_TARGET_FOLDER'			=> 'Zielverzeichnis',

	'TXT_UPLOAD_SUCCESS'		=> 'Datei wurde erfolgreich hochgeladen.',
	'TXT_UPLOAD_ERROR'			=> 'Hochladen der Datei fehlgeschlagen. Bitte Schreibrechte und Dateigr&ouml;sse pr&uuml;fen.',
);

// Text outputs for the download handler
$LANG[9] = array(
	'ERR_TEMP_PERMISSION'		=> 'PHP hat keine Schreibrechte f&uuml;r das WB Tempor&auml;rverzeichnis (/temp).',
	'ERR_ZIP_CREATION'			=> 'Das Ziparchiv konnte nicht erstellt werden.',
	'ERR_ZIP_DOWNLOAD'			=> 'Fehler beim Herunterladen des Ziparchivs.<br />Manuell <a href="{URL}">herunterladen</a>.',
);

// Text outputs for the FTP checking (htt/ftp_connection_check.htt)
$LANG[10] = array(
	'TXT_FTP_HEADING'			=> 'FTP Konfigurations-Assistent',
	'TXT_FTP_DESCRIPTION'		=> 'Der Konfigurations-Assistent unterst&uuml;zt Sie beim Einrichten und Testen der ' .
								   'Addon File Editor FTP Unterst&uuml;tzung.',

	'TXT_FTP_SETTINGS'			=> 'Aktuelle FTP Einstellungen',
	'TXT_FTP_SUPPORT'			=> 'FTP Unterst&uuml;tzung',
	'TXT_ENABLE'				=> 'Aktivieren',
	'TXT_DISABLE'				=> 'Deaktivieren',
	'TXT_FTP_SERVER'			=> 'Server',
	'TXT_FTP_USER'				=> 'Benutzer',
	'TXT_FTP_PASSWORD'			=> 'Password',
	'TXT_FTP_PORT'				=> 'Port',
	'TXT_FTP_START_DIR'			=> 'Startverzeichnis',

	'TXT_FTP_CONNECTION_TEST'	=> 'FTP Verbindung testen',
	'TXT_CHECK_FTP_CONNECTION'	=> 'Um die Verbindung zum FTP Server zu testen, bitte nachfolgende Schaltfl&auml;che dr&uuml;cken.',
	'TXT_FTP_CHECK_NOTE'		=> '<strong>Hinweis: </strong>Der Verbindungstest kann bis zu 15 Sekunden in Anspruch nehmen.',
	'TXT_SUBMIT_CHECK'			=> 'Verbinden',
	'TXT_FTP_LOGIN_OK'			=> 'Verbindungsaufbau zum FTP Server erfolgreich. Die FTP Unterst&uuml;tzung ist aktiviert.',
	'TXT_FTP_LOGIN_FAILED'		=> 'Verbindungsaufbau zum FTP Server fehlgeschlagen. Bitte FTP Einstellungen pr&uuml;fen. ' .
								   '<br /><strong>Hinweis: </strong>Das Startverzeichnis muss auf die WB Installation zeigen.',
);

?>