<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the Norwegian Bokm&aring;l text outputs of the module.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.1.0
 * @language	Norsk Bokm&aring;l/Norwegian
 * @translation	Odd Egil Hansen	(oeh)
 * @platform	Website Baker 2.8
*/

// Norwegian Bokm&aring;l module description
$module_description = 'AFE lar deg redigere tekst og bilde filer i installerte moduler og tillegg via Admin sidene. Se <a href="{WB_URL}/modules/addon_file_editor/help/help_no.html" target="_blank">les meg</a> filen for n&aelig;rmere detaljer.';

// declare module language array
$LANG = array();

// Text outputs for the version check
$LANG[0] = array(
	'TXT_VERSION_ERROR'			=> 'Feil: Modulen "Addon File Editor" krever Website Baker 2.7 eller h&oslash;yere.',
);

// Text outputs overview page (htt/addons_overview.htt)
$LANG[1] = array(
	'TXT_DESCRIPTION'			=> 'Listen nedenfor viser alle Tillegg (Add-ons), som er lesbare med PHP. Du kan redigere et Tillegg ved &aring; ' . 
								   'klikke p&aring; Tilleggets navn. Ikonet for nedlasting lar deg opprette en installerbar sikkerhetskopi ' .
								   'av Tillegget.',
	'TXT_FTP_NOTICE'			=> 'Tillegg/ filer som er markert med r&oslash;dt er ikke skrivbare for PHP. Dette kan v&aelig;re tilfellet hvis du installerer ' .
								   'Tillegg ved &aring; benytte FTP. Du m&aring; <a class="link" target="_blank" href="{URL_ASSISTANT}">' .
								   'aktivere FTP support </a> for &aring; redigere disse Tilleggene.',
	'TXT_HEADING'				=> 'Installerte Tillegg (Moduler, Designmaler, Spr&aring;kfiler)',
	'TXT_HELP'					=> 'Hjelp',

	'TXT_RELOAD'				=> 'Oppdater',
	'TXT_ACTION_EDIT'			=> 'Rediger',
	'TXT_ACTION_DELETE'			=> 'Slett',
	'TXT_FTP_SUPPORT'			=> ' (krever FTP skrivetilgang for &aring; redigere)',

	'TXT_MODULES'				=> 'Modul',
	'TXT_LIST_OF_MODULES'		=> 'Liste over intallerte Moduler',
	'TXT_EDIT_MODULE_FILES'		=> 'Rediger modul filer',
	'TXT_ZIP_MODULE_FILES'		=> 'Sikkerhetskopier og last ned modul filen',

	'TXT_TEMPLATES'				=> 'Designmaler',
	'TXT_LIST_OF_TEMPLATES'		=> 'Liste over installerte designmaler',
	'TXT_EDIT_TEMPLATE_FILES'	=> 'Rediger designmal filer',
	'TXT_ZIP_TEMPLATE_FILES'	=> 'Sikkerhetskopier og last ned designmal filen',

	'TXT_LANGUAGES'				=> 'Spr&aring;kfiler',
	'TXT_LIST_OF_LANGUAGES'		=> 'Liste over installerte WB spr&aring;kfiler',
	'TXT_EDIT_LANGUAGE_FILES'	=> 'Rediger spr&aring;kfiler',
	'TXT_ZIP_LANGUAGE_FILES'	=> 'Sikkerhetskopier og last ned spr&aring;kfilen',
);

// Text outputs filemanager page (htt/filemanager.htt)
$LANG[2] = array(
	'TXT_EDIT_DESCRIPTION'		=> 'Filbehandleren lar deg reidgere, omd&oslash;pe, opprette, slette og laste opp filer. Ved &aring; klikke ' .
								   'p&aring; en tekst eller bilde fil, &aring;pnes denne for redigering eller gjennomlesing.',
	'TXT_BACK_TO_OVERVIEW'		=> 'tilbake til Tilleggsoversikten',

	'TXT_MODULE'				=> 'Modul',
	'TXT_TEMPLATE'				=> 'Designmal',
	'TXT_LANGUAGE'				=> 'WB Spr&aring;kfil',
	'TXT_FTP_SUPPORT'			=> ' (krever FTP skrivetilgang for &aring; redigere)',

	'TXT_RELOAD'				=> 'Oppdater',
	'TXT_CREATE_FILE_FOLDER'	=> 'Opprett Fil/Folder',
	'TXT_UPLOAD_FILE'			=> 'Last opp Fil',
	'TXT_VIEW'					=> 'Se',
	'TXT_EDIT'					=> 'Rediger',
	'TXT_RENAME'				=> 'Omd&oslash;p',
	'TXT_DELETE'				=> 'Slett',

	'TXT_FILE_INFOS'			=> 'Fil informasjon',
	'TXT_FILE_ACTIONS'			=> 'Handlinger',
	'TXT_FILE_TYPE_TEXTFILE'	=> 'Tekst fil',
	'TXT_FILE_TYPE_FOLDER'		=> 'Folder',
	'TXT_FILE_TYPE_IMAGE'		=> 'Bildefil',
	'TXT_FILE_TYPE_ARCHIVE'		=> 'Arkivfil',
	'TXT_FILE_TYPE_OTHER'		=> 'Ukjent',

	'DATE_FORMAT'				=> 'm/d/y / h:m',
);

// General text outputs for the file handler templates
$LANG[3] = array(
	'ERR_WRONG_PARAMETER'		=> 'Parameterene du spesifiserte var feil eller ufullstendige.',
	'TXT_MODULE'				=> 'Modul',
	'TXT_TEMPLATE'				=> 'Designmal',
	'TXT_LANGUAGE'				=> 'WB Spr&aring;kfil',
	'TXT_ACTION'				=> 'Handling',
	'TXT_ACTUAL_FILE'			=> 'Gjeldende fil',
	'TXT_SUBMIT_CANCEL'			=> 'Avbryt',
);	

// Text outputs file handler (htt/action_handler_edit_textfile.htt)
$LANG[4] = array(
	'TXT_ACTION_EDIT_TEXTFILE'	=> 'Rediger tekstfil',
	'TXT_SUBMIT_SAVE'			=> 'Lagre',
	'TXT_SUBMIT_SAVE_BACK'		=> 'Lagre &amp; tilbake',
	'TXT_ACTUAL_FILE'			=> 'Gjeldende fil',
	'TXT_SAVE_SUCCESS'			=> 'Lagring av endringene var vellykket.',
	'TXT_SAVE_ERROR'			=> 'Kunne ikke lagre endringene. Sjekk tilgangsrettighetene p&aring; filen.',
);

// Text outputs file handler (htt/action_handler_rename_file_folder.htt)
$LANG[5] = array(
	'TXT_ACTION_RENAME_FILE'	=> 'Omd&oslash;p fil/folder',
	'TXT_OLD_FILE_NAME'			=> 'Fil/folder (gammel)',
	'TXT_NEW_FILE_NAME'			=> 'Fil/folder (ny)',
	'TXT_SUBMIT_RENAME'			=> 'Omd&oslash;p',
	'TXT_RENAME_SUCCESS'		=> 'Fil/folder ble omd&oslash;p.',
	'TXT_RENAME_ERROR'			=> 'Kan ikke omd&oslash;pe fil/folder. Sjekk tilgangsrettighetene p&aring; filen/folderen.',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
);

// Text outputs file handler (htt/action_handler_delete_file_folder.htt)
$LANG[6] = array(
	'TXT_ACTION_DELETE_FILE'	=> 'Slett fil/folder',
	'TXT_SUBMIT_DELETE'			=> 'Slett',
	'TXT_ACTUAL_FOLDER'			=> 'Gjeldende folder',
	'TXT_DELETE_WARNING'		=> '<strong>OBS: </strong>Sletting av filer og folere kan ikke angres. Husk at ' .
								   'hvis du sletter en folder, vil ogs&aring; alle filer og foldere som er i denne bli slettet.',
	'TXT_DELETE_SUCCESS'		=> 'Vellykket sletting av Fil/folder.',
	'TXT_DELETE_ERROR'			=> 'Kunne ikke slette file/folder. Sjekk tilgangsrettighetene.<br /><em>OBS: For &aring; slette en folder ' .
								   'med FTP, v&aelig;r sikker p&aring; at denne folderen ikke inneholder andre foldere eller filer.</em>'
);

// Text outputs file handler (htt/action_handler_create_file_folder.htt)
$LANG[7] = array(
	'TXT_ACTION_CREATE_FILE'	=> 'Opprett fil/folder',
	'TXT_CREATE'				=> 'Opprett',
	'TXT_FILE'					=> 'Fil',
	'TXT_FOLDER'				=> 'Folder',
	'TXT_FILE_NAME'				=> 'Fil navn',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
	'TXT_TARGET_FOLDER'			=> 'M&aring;lfolder',
	'TXT_SUBMIT_CREATE'			=> 'Opprett',
	'TXT_CREATE_SUCCESS'		=> 'Fil/folder ble opprettet.',
	'TXT_CREATE_ERROR'			=> 'Kunne ikke opprette fil/folder. Sjekk tilgangsrettighetene og spesifiser et fil/folder navn.',
);

// Text outputs file handler (htt/action_handler_upload_file.htt)
$LANG[8] = array(
	'TXT_ACTION_UPLOAD_FILE'	=> 'Last opp fil',
	'TXT_SUBMIT_UPLOAD'			=> 'Last opp',

	'TXT_FILE'					=> 'Fil',
	'TXT_TARGET_FOLDER'			=> 'M&aring;lfolder',

	'TXT_UPLOAD_SUCCESS'		=> 'Filen ble opprettet.',
	'TXT_UPLOAD_ERROR'			=> 'Kunne ikke laste opp filen. Sjekk tilgangsrettighetene og filst&oslash;rrelsen.',
);

// Text outputs for the download handler
$LANG[9] = array(
	'ERR_TEMP_PERMISSION'		=> 'PHP har ikke skrivetilgang til den tempor&aelig;re WB folderen (/temp).',
	'ERR_ZIP_CREATION'			=> 'Kunne ikke opprette arkivfilen.',
	'ERR_ZIP_DOWNLOAD'			=> 'Feil ved nedlasting av sikkerhetskopifilen.<br /><a href="{URL}">Last ned</a> manuelt.',
);

// Text outputs for the FTP checking (htt/ftp_connection_check.htt)
$LANG[10] = array(
	'TXT_FTP_HEADING'			=> 'FTP Konfigurasjonsassistent',
	'TXT_FTP_DESCRIPTION'		=> 'FTP assistenten hjelper deg &aring; sette opp og teste FTP funksjonene i Addon File Editor.',

	'TXT_FTP_SETTINGS'			=> 'N&aring;v&aelig;rende FTP Innstillinger',
	'TXT_FTP_SUPPORT'			=> 'FTP St&oslash;tte',
	'TXT_ENABLE'				=> 'Sl&aring; p&aring;',
	'TXT_DISABLE'				=> 'Sl&aring; av',
	'TXT_FTP_SERVER'			=> 'Server',
	'TXT_FTP_USER'				=> 'Bruker',
	'TXT_FTP_PASSWORD'			=> 'Passord',
	'TXT_FTP_PORT'				=> 'Port',
	'TXT_FTP_START_DIR'			=> 'Start folder',

	'TXT_FTP_CONNECTION_TEST'	=> 'Sjekk FTP Tilkobling',
	'TXT_CHECK_FTP_CONNECTION'	=> 'Trykk p&aring; knappen under for &aring; sjekke tilkoblingsstatusen for FTP serveren.',
	'TXT_FTP_CHECK_NOTE'		=> '<strong>OBS: </strong>Tilkoblingstesten kan ta inntil 15 sekunder.',
	'TXT_SUBMIT_CHECK'			=> 'Koble til',
	'TXT_FTP_LOGIN_OK'			=> 'Tilkobling til FTP server var vellykket. FTP St&oslash;tte er sl&aring;tt p&aring;.',
	'TXT_FTP_LOGIN_FAILED'		=> 'Tilkobling til FTP server feilet. Sjekk FTP instillingene dine. ' .
								   '<br /><strong>OBS: </strong>Start folderen m&aring; v&amp;aelig;re den mappen som WB er installert i.',
);

?>