<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */


// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php



// Define that this file is loaded
if(!defined('LANGUAGE_LOADED')) {
	define('LANGUAGE_LOADED', true);
}

// Set the language information
$language_code = 'FI';
$language_name = 'Finnish';
$language_version = '1.0';
$language_platform = '1.0.x';
$language_author = 'Jouni Reivolahti';
$language_license = 'GNU General Public License';
$language_guid = '2a4f8878-0b11-4715-8910-bfc719024727';

$MENU = array(
	'ACCESS' 				=> 'K&auml;ytt&ouml;oikeudet', //Access
	'ADDON' 				=> 'Liit&auml;nn&auml;inen', //Add-on
	'ADDONS' 				=> 'Liit&auml;nn&auml;iset', //Add-ons
	'ADMINTOOLS' 			=> 'Hallinnointi', //Admin-Tools
	'BREADCRUMB' 			=> 'Sijaintisi: ', //You are here:
	'FORGOT' 				=> 'K&auml;ytt&auml;j&auml;tunnukset hukassa...', //Retrieve Login Details
	'GROUP' 				=> 'Ryhm&auml;', //Group
	'GROUPS' 				=> 'Ryhm&auml;t', //Groups
	'HELP' 					=> 'Ohje', //Help
	'LANGUAGES' 			=> 'Kielet', //Languages
	'LOGIN' 				=> 'Kirjaudu', //Login
	'LOGOUT' 				=> 'Kirjaudu ulos', //Log-out
	'MEDIA' 				=> 'Media', //Media
	'MODULES' 				=> 'Moduulit', //Modules
	'PAGES' 				=> 'Sivut', //Pages
	'PREFERENCES' 			=> 'Omat tiedot', //Preferences
	'SETTINGS' 				=> 'Asetukset', //Settings
	'START' 				=> 'Hallinnointin&auml;kym&auml;', //Start
	'TEMPLATES' 			=> 'Sivumallit', //Templates
	'USERS' 				=> 'K&auml;ytt&auml;j&auml;t', //Users
	'VIEW' 					=> 'Sivusto', //View
	'SERVICE'				=> 'Huoltotoimet' //Service
); // $MENU

$TEXT = array(
	'ACCOUNT_SIGNUP' 		=> 'Rekister&ouml;ityminen', //Account Sign-Up
	'ACTION_NOT_SUPPORTED'	=> 'Toimintoa ei tueta', //Action not supported
	'ACTIONS' 				=> 'Toiminnot', //Actions
	'ACTIVE' 				=> 'K&auml;yt&ouml;ss&auml;', //Active
	'ADD' 					=> 'Lis&auml;&auml;', //Add
	'ADDON' 				=> 'Liit&auml;nn&auml;inen', //Add-on
	'ADD_SECTION' 			=> 'Lis&auml;&auml; lohko', //Add Section
	'ADMIN' 				=> 'Admin', //Admin
	'ADMINISTRATION' 		=> 'Hallinnointi', //Adminstration
	'ADMINISTRATION_TOOL' 	=> 'Hallinnointity&ouml;kalut', //Administration tool
	'ADMINISTRATOR' 		=> 'J&auml;rjestelm&auml;nvalvoja', //Adminstrator
	'ADMINISTRATORS' 		=> 'J&auml;rjestelm&auml;nvalvojat', //Adminstrators
	'ADVANCED' 				=> 'Lis&auml;asetukset', //Advanced
	'ALLOWED_FILETYPES_ON_UPLOAD' => 'Palvelimelle ladattavissa olevat tiedostotyypit', //Allowed filetypes on upload
	'ALLOWED_VIEWERS' 		=> 'Sallitut k&auml;ytt&auml;j&auml;t', //Allowed Viewers
	'ALLOW_MULTIPLE_SELECTIONS' => 'Salli monivalinta', //Allow Multiple Selections
	'ALL_WORDS' 			=> 'Kaikki sanat', //All Words
	'ANCHOR' 				=> 'Ankkuri', //Anchor
	'ANONYMOUS' 			=> 'Tuntematon', //Anonymous
	'ANY_WORDS' 			=> 'Mik&auml; tahansa sanoista', //Any Words
	'APP_NAME' 				=> 'Sovelluksen nimi', //Application Name
	'ARE_YOU_SURE' 			=> 'Oletko varma?', //Are you sure?
	'AUTHOR' 				=> 'Tekij&auml;', //Author
	'BACK' 					=> 'Takaisin', //Back
	'BACKUP' 				=> 'Varmuuskopiointi', //Backup
	'BACKUP_ALL_TABLES' 	=> 'Varmista kaikki tietokannan taulut', //Backup all tables in database
	'BACKUP_DATABASE' 		=> 'Varmista tietokanta', //Backup Database
	'BACKUP_MEDIA' 			=> 'Varmista Media-tiedostot', //Backup Media
	'BACKUP_WB_SPECIFIC' 	=> 'Varmista vain j&auml;rjestelm&auml;n taulut', //Backup only WB-specific tables
	'BASIC' 				=> 'Perusn&auml;kym&auml;', //Basic
	'BLOCK' 				=> 'Lohko', //Block
	'CALENDAR' 				=> 'Kalenteri', //Calendar
	'CANCEL' 				=> 'Peruuta', //Cancel
	'CAN_DELETE_HIMSELF' 	=> 'Voi poistaa itsens&auml;', //Can delete himself
	'CAPTCHA_VERIFICATION' 	=> 'Captcha -varmistus', //Captcha Verification
	'CAP_EDIT_CSS' 			=> 'CSS-muokkaus', //Edit CSS
	'CHANGE' 				=> 'Muokkaa', //Change
	'CHANGES' 				=> 'Muutokset', //Changes
	'CHANGE_SETTINGS' 		=> 'Muokkaa asetuksia', //Change Settings
	'CHARSET' 				=> 'Merkist&ouml;', //Charset
	'CHECKBOX_GROUP' 		=> 'Valintaruutujen ryhm&auml;', //Checkbox Group
	'CLOSE' 				=> 'Sulje', //Close
	'CODE' 					=> 'Koodi', //Code
	'CODE_SNIPPET' 			=> 'Koodileike', //Code-snippet
	'COLLAPSE' 				=> 'Tiivist&auml;', //Collapse
	'COMMENT' 				=> 'Palaute', //Commen
	'COMMENTING' 			=> 'Palautteen anto', //Commenting
	'COMMENTS' 				=> 'Palautteet', //Comments
	'CREATE_FOLDER' 		=> 'Luo uusi kansio', //Create Folder
	'CURRENT' 				=> 'Nykyinen', //Current
	'CURRENT_FOLDER' 		=> 'Nykyinen kansio', //Current Folder
	'CURRENT_PAGE' 			=> 'Nykyinen sivu', //Current Page
	'CURRENT_PASSWORD' 		=> 'Nykyinen salasana', //Current Password
	'CUSTOM' 				=> 'Muokattu', //Custom
	'DATABASE' 				=> 'Tietokanta', //Database
	'DATE' 					=> 'P&auml;iv&auml;ys', //Date
	'DATE_FORMAT' 			=> 'P&auml;iv&auml;yksen muoto', //Date Format
	'DEFAULT' 				=> 'Oletusarvo', //Default
	'DEFAULT_CHARSET' 		=> 'Oletusmerkist&ouml;', //Default Charset
	'DEFAULT_TEXT' 			=> 'Oletusteksti', //Default Text
	'DELETE' 				=> 'Poista', //Delete
	'DELETED' 				=> 'Poistettu', //Deleted
	'DELETE_DATE' 			=> 'Poista p&auml;iv&auml;ys', //Delete date
	'DELETE_ZIP' 			=> 'Poista zip-tiedosto purkamisen j&auml;lkeen', // Delete zip archive after unpacking
	'DESCRIPTION' 			=> 'Kuvaus', //Description
	'DESIGNED_FOR' 			=> 'Kohde', //Designed For
	'DIRECTORIES' 			=> 'Hakemistot', //Directories
	'DIRECTORY_MODE' 		=> 'Hakemistotila', //Directory Mode
	'DISABLED' 				=> 'Ei k&auml;yt&ouml;ss&auml;', //Disabled
	'DISPLAY_NAME' 			=> 'N&auml;kyv&auml; nimi', //Display Name
	'EMAIL' 				=> 'S&auml;hk&ouml;posti', //Email
	'EMAIL_ADDRESS' 		=> 'S&auml;hk&ouml;postiosoite', //Email Address
	'EMPTY_TRASH' 			=> 'Tyhjenn&auml; roskakori', //Empty Trash
	'ENABLE_JAVASCRIPT'		=> "T&auml;m&auml;n lomakkeen k&auml;yt&ouml; edellytt&auml;&auml; JavaScript-tukea. Ole hyv&auml; ja muokkaa selaimesi asetuksia.", //Please enable your JavaScript to use this form.
	'ENABLED' 				=> 'K&auml;yt&ouml;ss&auml;', //Enabled
	'END' 					=> 'Loppu', //End
	'ERROR' 				=> 'Virhe', //Error
	'EXACT_MATCH' 			=> 'T&auml;sm&auml;lleen sama', //Exact Match
	'EXECUTE' 				=> 'Suorita', //Execute
	'EXPAND' 				=> 'Laajenna', //Expand
	'EXTENSION' 			=> 'Tiedostotyypin tunnus', //Extension
	'FIELD' 				=> 'Kentt&auml;', //Field
	'FILE' 					=> 'Tiedosto', //File
	'FILES' 				=> 'Tiedostot', //Files
	'FILESYSTEM_PERMISSIONS' => 'Tiedostoj&auml;rjestelm&auml;n oikeudet', //Filesystem Permissions
	'FILE_MODE' 			=> 'Tiedostotila', //File Mode
	'FINISH_PUBLISHING' 	=> 'Lopeta julkaisu', //Finish Publishing
	'FOLDER' 				=> 'Kansio', //Folder
	'FOLDERS' 				=> 'Kansiot', //Folders
	'FOOTER' 				=> 'Alatunniste', //Footer
	'FORGOTTEN_DETAILS' 	=> 'K&auml;ytt&auml;j&auml;tunnukset hukassa?', //Forgotten your details?
	'FORGOT_DETAILS' 		=> 'K&auml;ytt&auml;j&auml;tunnukset hukassa?', //Forgot Details?
	'FROM' 					=> 'Kohteesta', //From
	'FRONTEND' 				=> 'Julkisivu', //Front-end
	'FULL_NAME' 			=> 'Koko nimi', //Full Name
	'FUNCTION' 				=> 'Toiminto', //Function
	'GROUP' 				=> 'Ryhm&auml;', //Group
	'HEADER' 				=> 'Yl&auml;tunniste', //Header
	'HEADING' 				=> 'Otsikko', //Heading
	'HEADING_CSS_FILE' 		=> 'Moduulin css-tiedosto: ', //Actual module file: 
	'HEIGHT' 				=> 'Korkeus', //Height
	'HELP_LEPTOKEN_LIFETIME'		=> 'sekunneissa, 0 = ei CSRF-suojausta!', //in seconds, 0 means no CSRF protection!
	'HELP_MAX_ATTEMPTS'		=> 'T&auml;m&auml; on enimm&auml;ism&auml;&auml;r&auml; kirjautumisyrityksi&auml; yhden istunnon aikana.', //When reaching this number, more login attempts are not possible for this session.
	'HIDDEN' 				=> 'Piilotettu', //Hidden
	'HIDE' 					=> 'Piilota', //Hide
	'HIDE_ADVANCED' 		=> 'Piilota lis&auml;asetukset', //Hide Advanced Options
	'HOME' 					=> 'P&auml;&auml;sivu', //Home
	'HOMEPAGE_REDIRECTION' 	=> 'Edelleenohjaus kotisivulle', //Homepage Redirection
	'HOME_FOLDER' 			=> 'K&auml;ytt&auml;j&auml;kohtainen kansio', //Personal Folder
	'HOME_FOLDERS' 			=> 'K&auml;ytt&auml;j&auml;kohtaiset kansiot', //Personal Folders
	'HOST' 					=> 'Is&auml;nt&auml;', //Host
	'ICON' 					=> 'Kuvake', //Icon
	'IMAGE' 				=> 'Kuva', //Image
	'INLINE' 				=> 'Kaksivaiheinen', //In-line
	'INSTALL' 				=> 'Asenna', //Install
	'INSTALLATION' 			=> 'Asennus', //Installation
	'INSTALLATION_PATH' 	=> 'Asennuspolku', //Asennuspolku
	'INSTALLATION_URL' 		=> 'Asennuksen URL', //Asennuksen URL
	'INSTALLED' 			=> 'asennettu', //installed
	'INTRO' 				=> 'Intro', //Intro
	'INTRO_PAGE' 			=> 'Introsivu', //Intro Page
	'INVALID_SIGNS' 		=> 't&auml;ytyy aloittaa kirjaimella tai se sis&auml;lt&auml;&auml; merkkej&auml;, jotka eiv&auml;t ole sallittuja', //must begin with a letter or has invalid signs
	'KEYWORDS' 				=> 'Avainsanat', //Keywords
	'LANGUAGE' 				=> 'Kieli', //Language
	'LAST_UPDATED_BY' 		=> 'Viimeisen muutoksen tehnyt ', //Last Updated By
	'LENGTH' 				=> 'Pituus', //Length
	'LEPTOKEN_LIFETIME'		=> 'Leptokenin voimassaoloaika', //Leptoken Lifetime
	'LEVEL' 				=> 'Taso', //Level
	'LIBRARY'				=> 'Kirjasto', //Library
	'LICENSE'				=> 'Lisenssi', //License
	'LINK' 					=> 'Linkki', //Link
	'LINUX_UNIX_BASED' 		=> 'Linux/Unix -pohjainen', //Linux/Unix based
	'LIST_OPTIONS' 			=> 'Valintalista', //List Options
	'LOGGED_IN' 			=> 'Kirjautuneena', //Logged-In
	'LOGIN' 				=> 'Kirjaudu', //Login
	'LONG' 					=> 'Pitk&auml;', //Long
	'LONG_TEXT' 			=> 'Pitk&auml; teksti', //Long Text
	'LOOP' 					=> 'Silmukka', //Loop
	'MAIN' 					=> 'P&auml;&auml;lohko', //Main
	'MANAGE' 				=> 'Hallinnoi', //Manage
	'MANAGE_GROUPS' 		=> 'Ryhmien hallinnointi', //Manage Groups
	'MANAGE_USERS' 			=> 'K&auml;ytt&auml;jien hallinnointi', //Manage Users
	'MATCH' 				=> 'Vastaavuus', //Match
	'MATCHING' 				=> 'Vastaava', //Matching
	'MAX_ATTEMPTS'		=> 'Sallittu ep&auml;onnistuneiden kirjautumisten m&auml;&auml;r&auml;', //Allowed wrong login attempts
	'MAX_EXCERPT' 			=> 'Tulosten enimm&auml;ism&auml;&auml;r&auml;', //Max lines of excerpt
	'MAX_SUBMISSIONS_PER_HOUR' => 'Lis&auml;ysten enimm&auml;ism&auml;&auml;r&auml; tunnissa', //Max. Submissions Per Hour
	'MEDIA_DIRECTORY' 		=> 'Mediahakemisto', //Media Directory
	'MENU' 					=> 'Valikko', //Menu
	'MENU_TITLE' 			=> 'Valikkoteksti', //Menu Title
	'MESSAGE' 				=> 'Viesti', //Message
	'MODIFY' 				=> 'Muokkaa', //Modify
	'MODIFY_CONTENT' 		=> 'Muokkaa sis&auml;lt&ouml;&auml;', //Modify Content
	'MODIFY_SETTINGS' 		=> 'Muokkaa asetuksia', //Modify Settings
	'MODULE_ORDER' 			=> 'Moduulien hakuj&auml;rjestys', //Module-order for searching
	'MODULE_PERMISSIONS' 	=> 'Moduulien k&auml;ytt&ouml;oikeudet', //Module Permissions
	'MORE' 					=> 'Lis&auml;&auml;...', //More
	'MOVE_DOWN' 			=> 'Siirr&auml; alas', //Move Down
	'MOVE_UP' 				=> 'Siirr&auml; yl&ouml;s', //Move Up
	'MULTIPLE_MENUS' 		=> 'Useita valikkoja', //Multiple Menus
	'MULTISELECT' 			=> 'Monivalinta', //Multi-select
	'NAME' 					=> 'Nimi', //Name
	'NEED_CURRENT_PASSWORD' => 'varmista antamalla voimassa oleva salasanasi', //confirm with current password
	'NEED_PASSWORD_TO_CONFIRM' => 'Ole hyv&auml; ja varmista muutokset antamalla voimassa oleva salasanasi', //Please confirm the changes with your current password
	'NEED_TO_LOGIN' 		=> 'Tarvitaanko kirjautuminen?', //Need to log-in?
	'NEW_PASSWORD' 			=> 'Uusi salasana', //New Password
	'NEW_USER_HINT'			=> 'K&auml;ytt&auml;j&auml;tunnuksen v&auml;himm&auml;ispituus on %d merkki&auml;. Salasanan v&auml;himm&auml;ispituus on %d merkki&auml;.', //Minimum length for user name: %d chars, Minimum length for Password: %d chars!
	'NEW_WINDOW' 			=> 'Uusi ikkuna', //New Window
	'NEXT' 					=> 'Seuraava', //Next
	'NEXT_PAGE' 			=> 'Seuraava sivu', //Next Page
	'NO' 					=> 'Ei', //No
	'NO_LEPTON_ADDON'  => 'T&auml;t&auml; liit&auml;nn&auml;ist&auml; ei voi k&auml;ytt&auml;&auml; LEPTONin kanssa', //This addon cannot be used with LEPTON
	'NONE' 					=> 'Ei yht&auml;&auml;n', //None
	'NONE_FOUND' 			=> 'Ei l&ouml;ytyneit&auml;', //None Found
	'NOT_FOUND' 			=> 'Ei l&ouml;ytynyt', //Not Found
	'NOT_INSTALLED' 		=> 'ei asennettu', //not installed
	'NO_RESULTS' 			=> 'Ei tuloksia', //No Results
	'OF' 					=> '/', //Of
	'ON' 					=> 'P&auml;&auml;ll&auml;', //On
	'OPEN' 					=> 'Avaa', //Open
	'OPTION' 				=> 'Vaihtoehto', //Option
	'OTHERS' 				=> 'Muut', //Others
	'OUT_OF' 				=> '/', //Out Of
	'OVERWRITE_EXISTING' 	=> 'Korvaa nykyinen', //Overwrite existing
	'PAGE' 					=> 'Sivu', //Page
	'PAGES_DIRECTORY' 		=> 'Sivuhakemisto', //Pages Directory
	'PAGES_PERMISSION' 		=> 'Sivujen k&auml;ytt&ouml;oikeus', //Pages Permission
	'PAGES_PERMISSIONS' 	=> 'Sivujen k&auml;ytt&ouml;oikeudet', //Pages Permissions
	'PAGE_EXTENSION' 		=> 'Sivun tiedostotyyppi', //Page Extension
	'PAGE_ID'      => 'Sivun ID', //Page ID
	'PAGE_LANGUAGES' 		=> 'Sivun kielet', //Page Languages
	'PAGE_LEVEL_LIMIT' 		=> 'Sivun tasojen enimm&auml;ism&auml;&auml;r&auml;', //Page Level Limit
	'PAGE_SPACER' 			=> 'Sivun erotinmerkki', //Page Spacer
	'PAGE_TITLE' 			=> 'Sivun otsikko', //Page Title
	'PAGE_TRASH' 			=> 'Sivujen arkistointi', //Page Trash
	'PARENT' 				=> 'Is&auml;nt&auml;', //Parent
	'PASSWORD' 				=> 'Salasana', //Password
	'PATH' 					=> 'Polku', //Path
	'PHP_ERROR_LEVEL' 		=> 'PHP-virheilmoitusten taso', //PHP Error Reporting Level
	'PLEASE_LOGIN' 			=> 'Ole hyv&auml; ja kirjaudu', //Please login
	'PLEASE_SELECT' 		=> 'Ole hyv&auml; ja valitse', //Please select
	'POST' 					=> 'Viesti', //Post
	'POSTS_PER_PAGE' 		=> 'Viestej&auml; sivulla', //Posts Per Page
	'POST_FOOTER' 			=> 'Viestin alatunniste', //Post Footer
	'POST_HEADER' 			=> 'Viestin yl&auml;tunniste', //Post Header
	'PREVIOUS' 				=> 'Edellinen', //Previous
	'PREVIOUS_PAGE' 		=> 'Edellinen sivu', //Previous Page
	'PRIVATE' 				=> 'Yksityinen', //Private
	'PRIVATE_VIEWERS' 		=> 'Yksityiset kytt&auml;j&auml;t', //Private Viewers
	'PROFILES_EDIT' 		=> 'Muokkaa profiilia', //Change the profile
	'PUBLIC' 				=> 'Julkinen', //Public
	'PUBL_END_DATE' 		=> 'Julkaisun poistop&auml;iv&auml;', //End date
	'PUBL_START_DATE' 		=> 'Julkaisup&auml;iv&auml;', //Start date
	'RADIO_BUTTON_GROUP' 	=> 'Valintapainikeryhm&auml;', //Radio Button Group
	'READ' 					=> 'Lue', //Read
	'READ_MORE' 			=> 'Lue lis&auml;&auml;...', //Read More
	'REDIRECT_AFTER' 		=> 'Uudelleenohjaa kun kulunut', //Redirect after
	'REGISTERED' 			=> 'Rekister&ouml;itynyt', //Registered
	'REGISTERED_VIEWERS' 	=> 'Rekister&ouml;ityneet k&auml;ytt&auml;j&auml;t', //Registered Viewers
	'REGISTERED_CONTENT'	=> 'T&auml;h&auml;n osioon p&auml;&auml;sev&auml;t vain sivustolle rekister&ouml;ityneet k&auml;ytt&auml;j&auml;t', //Only registered visitors of this website have access to this content
	'RELOAD' 				=> 'Lataa uudelleen', //Reload
	'REMEMBER_ME' 			=> 'Muista minut', //Remember Me
	'RENAME' 				=> 'Nime&auml; uudelleen', //Rename
	'RENAME_FILES_ON_UPLOAD' => 'Uudelleennime&auml; ladatut tiedostot', //Rename Files On Upload
	'REQUIRED' 				=> 'Pakollinen', //Required
	'REQUIREMENT' 			=> 'Vaatimus', //Requirement
	'RESET' 				=> 'Palauta alkutila', //Reset
	'RESIZE' 				=> 'Muuta kokoa', //Re-size
	'RESIZE_IMAGE_TO' 		=> 'Aseta kuvan uudeksi kooksi', //Resize Image To
	'RESTORE' 				=> 'Palauta', //Restore
	'RESTORE_DATABASE' 		=> 'Palauta tietokanta varmuuskopiosta', //Restore Database
	'RESTORE_MEDIA' 		=> 'Palauta Mediatiedostot varmuuskopiosta', //Restore Media
	'RESULTS' 				=> 'Tulokset', //Results
	'RESULTS_FOOTER' 		=> 'Tulosten alatunniste', //Results Footer
	'RESULTS_FOR' 			=> 'Tulokset kyselylle', //Results For
	'RESULTS_HEADER' 		=> 'Tulosten yl&auml;tunniste', //Results Header
	'RESULTS_LOOP' 			=> 'Tulossilmukka', //Results Loop
	'RETYPE_NEW_PASSWORD' 	=> 'Kirjoita viel&auml; uusi salasanasi', //Re-type New Password
	'RETYPE_PASSWORD' 		=> 'Kirjoita salasanasi uudelleen', //Re-type Password
	'SAME_WINDOW' 			=> 'Sama ikkuna', //Same Window
	'SAVE' 					=> 'Tallenna', //Save
	'SEARCH' 				=> 'Haku', //Search
	'SEARCH_FOR'  			=> 'Hakuehto', //Search by
	'SEARCHING' 			=> 'Haku k&auml;ynniss&auml;', //Searching
	'SECTION' 				=> 'Lohko', //Section
	'SECTION_BLOCKS' 		=> 'Lohkon osat', //Section Blocks
	'SECTION_ID' 			=> 'Lohkon ID', //Sektion ID
	'SEC_ANCHOR' 			=> 'Lohkon ankkuriteksti', //Section-Anchor text
	'SELECT_BOX' 			=> 'Valintalista', //Select Box
	'SEND_DETAILS' 			=> 'L&auml;het&auml; tiedot', //Send Details
	'SEPARATE' 				=> 'Erottele', //Separate
	'SEPERATOR' 			=> 'Erotin', //Separator
	'SERVER_EMAIL' 			=> 'Palvelimen s&auml;hk&ouml;posti', //Server Email
	'SERVER_OPERATING_SYSTEM' => 'Palvelimen k&auml;ytt&ouml;j&auml;rjestelm&auml;', //Server Operating System
	'SESSION_IDENTIFIER' 	=> 'Istunnon tunniste', //Session Identifier
	'SETTINGS' 				=> 'Asetukset', //Settings
	'SHORT' 				=> 'Lyhyt', //Short
	'SHORT_TEXT' 			=> 'Lyhyt teksti', //Short Text
	'SHOW' 					=> 'N&auml;yt&auml;', //Show
	'SHOW_ADVANCED' 		=> 'N&auml;yt&auml; lis&auml;asetukset', //Show Advanced Options
	'SIGNUP' 				=> 'Rekister&ouml;ityminen', //Sign-up
	'SIZE' 					=> 'Koko', //Size
	'SMART_LOGIN' 			=> '&Auml;lyk&auml;s kirjautuminen', //Smart Login
	'START' 				=> 'Aloita', //Start
	'START_PUBLISHING' 		=> 'Aloita julkaisu', //Start Publishing
	'SUBJECT' 				=> 'Aihe', //Subject
	'SUBMISSIONS' 			=> 'Tallennetut vastaukset', //Submissions
	'SUBMISSIONS_STORED_IN_DATABASE' => 'Tallennettu tietokantaan', //Submissions Stored In Database
	'SUBMISSION_ID' 		=> 'Tallennuksen ID', //Submission ID
	'SUBMITTED' 			=> 'Tallennusaika', //Submitted
	'SUCCESS' 				=> 'Onnistui', //Success
	'SYSTEM_DEFAULT' 		=> 'J&auml;rjestelm&auml;n oletusarvo', //System Default
	'SYSTEM_PERMISSIONS' 	=> 'J&auml;rjestelm&auml;n k&auml;ytt&ouml;oikeudet', //System Permissions
	'TABLE_PREFIX' 			=> 'Tietokantataulun etuliite', //Table prefix
	'TARGET' 				=> 'Kohde', //Target
	'TARGET_FOLDER' 		=> 'Kohdekansio', //Target folder
	'TEMPLATE' 				=> 'Sivumalli', //Template
	'TEMPLATE_PERMISSIONS' 	=> 'Sivumallien k&auml;ytt&ouml;oikeudet', //Template Permissions
	'TEXT' 					=> 'Teksti', //Text
	'TEXTAREA' 				=> 'Tekstialue', //Textarea
	'TEXTFIELD' 			=> 'Tekstikentt&auml;', //Textfield
	'THEME' 				=> 'Hallintan&auml;kym&auml;n sivumalli', //Backend-Theme
	'TIME' 					=> 'Aika', //Time
	'TIMEZONE' 				=> 'Aikavy&ouml;hyke', //Timezone
	'TIME_FORMAT' 			=> 'Ajan esitysmuoto', //Time Format
	'TIME_LIMIT' 			=> 'Enimm&auml;isaika moduulikohtaiselle tulosten etsinn&auml;lle', //Max time to gather excerpts per module
	'TITLE' 				=> 'Otsikko', //Title
	'TO' 					=> 'Kohteeseen', //To
	'TOP_FRAME' 			=> 'Ylin kehys', //Top Frame
	'TRASH_EMPTIED' 		=> 'Roskakori on tyhjennetty', //Trash Emptied
	'TXT_EDIT_CSS_FILE' 	=> 'Muokkaa CSS-m&auml;&auml;rityksi&auml; alla olevalla tekstialueella.', //Edit the CSS definitions in the textarea below.
	'TYPE' 					=> 'Tyyppi', //Type
	'UNDER_CONSTRUCTION' 	=> 'Kehitysty&ouml; kesken', //Under Construction
	'UNINSTALL' 			=> 'Poista asennus', //Uninstall
	'UNKNOWN' 				=> 'Tuntematon', //Unknown
	'UNLIMITED' 			=> 'Rajoittamaton', //Unlimited
	'UNZIP_FILE' 			=> 'Lataa ja pura zip-tiedosto', //Upload and unpack a zip archive
	'UP' 					=> 'Yl&ouml;s', //Up
	'UPGRADE' 				=> 'P&auml;ivit&auml;', //Upgrade
	'UPLOAD_FILES' 			=> 'Lataa tiedosto(ja) palvelimelle', //Upload File(s)
	'URL' 					=> 'URL', //URL
	'USER' 					=> 'K&auml;ytt&auml;j&auml;', //User
	'USERNAME' 				=> 'K&auml;ytt&auml;j&auml;tunnus', //Username
	'USERS_ACTIVE' 			=> 'K&auml;ytt&auml;j&auml;tunnus on k&auml;yt&ouml;ss&auml;', //User is set active
	'USERS_CAN_SELFDELETE' 	=> 'K&auml;ytt&auml;j&auml; voi poistaa itsens&auml;', //User can delete himself
	'USERS_CHANGE_SETTINGS' => 'K&auml;ytt&auml;j&auml; voi muuttaa omia asetuksiaan', //User can change his own settings
	'USERS_DELETED' 		=> 'K&auml;ytt&auml;j&auml;tunnus on merkitty poistetuksi', //User is marked as deleted
	'USERS_FLAGS' 			=> 'K&auml;ytt&auml;j&auml;optiot', //User-Flags
	'USERS_PROFILE_ALLOWED' => 'K&auml;ytt&auml;j&auml; voi luoda laajennetun profiilin', //User can create extended profile
	'VERIFICATION' 			=> 'Varmistus', //Verification
	'VERSION' 				=> 'Versio', //Version
	'VIEW' 					=> 'N&auml;yt&auml;', //View
	'VIEW_DELETED_PAGES' 	=> 'N&auml;yt&auml; roskakorissa olevat sivut', //View Deleted Pages
	'VIEW_DETAILS' 			=> 'N&auml;yt&auml; yksityiskohdat', //View Details
	'VISIBILITY' 			=> 'N&auml;kyvyys', //Visibility
	'WBMAILER_DEFAULT_SENDER_MAIL' => 'L&auml;hett&auml;j&auml;n oletuss&auml;hk&ouml;postiosoite', //Default From Mail
	'WBMAILER_DEFAULT_SENDER_NAME' => 'L&auml;hett&auml;j&auml;n oletusnimi', //Default From Name
	'WBMAILER_DEFAULT_SETTINGS_NOTICE' => 'Ole hyv&auml; ja m&auml;&auml;rittele l&auml;hett&auml;j&auml;n s&auml;hk&ouml;postiosoitteelle ja nimelle oletusarvot. L&auml;hetysosoitteeksi suosittelemme muotoa: <strong>admin@omadomain.com</strong>. Jotkin postipalveluiden tarjoajat (esim. <em>mail.com</em>) saattavat roskapostitusten v&auml;ltt&auml;miseksi hyl&auml;t&auml; s&auml;hk&ouml;postit, joiden l&auml;hett&auml;j&auml;n osoite on muotoa <em>name@mail.com</em> silloin kun ne v&auml;litet&auml;&auml;n ulkomaisten palvelimien kautta.<br /><br />Oletusarvot ovat k&auml;yt&ouml;ss&auml; vain silloin kun Leptonissa ei ole m&auml;&auml;ritelty muita arvoja. Jos palvelimessasi on <acronym title="Simple mail transfer protocol">SMTP</acronym>-tuki, saatat haluta k&auml;ytt&auml;&auml; sit&auml;.',
									//'Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Lepton. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.'
	'WBMAILER_FUNCTION' 	=> 'S&auml;hk&ouml;postin k&auml;sittelytapa', //Mail Routine
	'WBMAILER_NOTICE' 		=> '<strong>SMTP-palvelinasetukset:</strong><br />Alla olevia asetuksia tarvitaan vain kun k&auml;yt&auml;t s&auml;hk&ouml;postien l&auml;hett&auml;miseen <acronym title="Simple mail transfer protocol">SMTP</acronym>-protokollaa. Jos et ole varma SMTP-palvelimesta tai sen asetuksista, on varminta k&auml;ytt&auml;&auml; s&auml;hk&ouml;postin oletusk&auml;sittelytapaa: PHP MAIL.',
									//'<strong>SMTP Mailer Settings:</strong><br />The settings below are only required if you want to send mails via <acronym title="Simple mail transfer protocol">SMTP</acronym>. If you do not know your SMTP host or you are not sure about the required settings, simply stay with the default mail routine: PHP MAIL.'
	'WBMAILER_PHP' 			=> 'PHP MAIL', //PHP MAIL
	'WBMAILER_SEND_TESTMAIL' => 'L&auml;het&auml; testiposti', //Send test eMail
	'WBMAILER_SMTP' 		=> 'SMTP', //SMTP
	'WBMAILER_SMTP_AUTH' 	=> 'SMTP-todennus', //SMTP Authentification
	'WBMAILER_SMTP_AUTH_NOTICE' => 'aseta p&auml;&auml;lle vain, jos SMTP-palvelimesi vaatii autentikointia', //only activate if your SMTP host requires authentification
	'WBMAILER_SMTP_HOST' 	=> 'SMTP-palvelin', //SMTP Host
	'WBMAILER_SMTP_PASSWORD' => 'SMTP-salasana', //SMTP Password
	'WBMAILER_SMTP_USERNAME' => 'SMTP-k&auml;ytt&auml;j&auml;tunnus', //SMTP Username
  'WBMAILER_TESTMAIL_FAILED' => 'Testipostin l&auml;hetys ep&auml;onnistui! Tarkista postiasetukset!', //The test eMail could not be sent! Please check your settings!
	'WBMAILER_TESTMAIL_SUCCESS' => 'Testiposti l&auml;hetettiin onnistuneesti. Tarkista s&auml;hk&ouml;postisi saapuneiden postien kansio.', //The test eMail was sent successfully. Please check your inbox.
  'WBMAILER_TESTMAIL_TEXT' => 'T&auml;m&auml; on testiposti: PHP-postitus on toimintakunnossa', //This is the required test mail: php mailer is working
	'WEBSITE' 				=> 'Sivusto', //Website
	'WEBSITE_DESCRIPTION' 	=> 'Sivuston kuvaus', //Website Description
	'WEBSITE_FOOTER' 		=> 'Sivuston alatunniste', //Website Footer
	'WEBSITE_HEADER' 		=> 'Sivuston yl&auml;tunniste', //Website Header
	'WEBSITE_KEYWORDS' 		=> 'Sivuston avainsanat', //Website Keywords
	'WEBSITE_TITLE' 		=> 'Sivuston otsikko', //Website Title
	'WELCOME_BACK' 			=> 'Tervetuloa takaisin', //Welcome back
	'WIDTH' 				=> 'Leveys', //Width
	'WINDOW' 				=> 'Ikkuna', //Window
	'WINDOWS' 				=> 'Windows', //Windows
	'WORLD_WRITEABLE_FILE_PERMISSIONS' => 'Avoimet tiedostojen kirjoitusoikeudet', //World-writeable file permissions
	'WRITE' 				=> 'Kirjoita', //Write
	'WYSIWYG_EDITOR' 		=> 'WYSIWYG-editori',  //WYSIWYG Editor
	'WYSIWYG_STYLE'	 		=> 'WYSIWYG-tyyli', //WYSIWYG Style
	'YES' 					=> 'Kyll&auml;', //Yes
	'BASICS'	=> array(
		'day'		=> "p&auml;iv&auml;",		# day, singular
		'day_pl'	=> "p&auml;iv&auml;&auml;",	# day, plural
		'hour'		=> "tunti", 	# hour, singular
		'hour_pl'	=> "tuntia",	# hour, plural
		'minute'	=> "minuutti",	# minute, singular
		'minute_pl'	=> "minuuttia",	# minute, plural
	)
); // $TEXT

$HEADING = array(
	'ADDON_PRECHECK_FAILED' => 'Liit&auml;nn&auml;isen minimivaatimukset eiv&auml;t t&auml;yty', //Add-On requirements not met
	'ADD_CHILD_PAGE' 		=> 'Lis&auml;&auml; alisivu', //Add child page
	'ADD_GROUP' 			=> 'Lis&auml;&auml; ryhm&auml;', //Add Group
	'ADD_GROUPS' 			=> 'Lis&auml;&auml; ryhmi&auml;', //Add Groups
	'ADD_HEADING' 			=> 'Lis&auml;&auml; otsikko', //Add Heading
	'ADD_PAGE' 				=> 'Lis&auml;&auml; sivu', //Add Page
	'ADD_USER' 				=> 'Lis&auml;&auml; k&auml;ytt&auml;j&auml;', //Add User
	'ADMINISTRATION_TOOLS' 	=> 'Hallinnointity&ouml;kalut', //Administration Tools
	'BROWSE_MEDIA' 			=> 'Selaa Mediakansiota', //Browse Media
	'CREATE_FOLDER' 		=> 'Luo uusi kansio', //Create Folder
	'DEFAULT_SETTINGS' 		=> 'Oletusasetukset', //Default settings
	'DELETED_PAGES' 		=> 'Poistetut sivut', //Deleted Pages
	'FILESYSTEM_SETTINGS' 	=> 'Tiedostoj&auml;rjestelm&auml;n asetukset', //Filesystem Settings
	'GENERAL_SETTINGS' 		=> 'Yleiset asetukset', //General settings
	'INSTALL_LANGUAGE' 		=> 'Asenna kieli', //Install Language
	'INSTALL_MODULE' 		=> 'Asenna moduuli', //Install Module
	'INSTALL_TEMPLATE' 		=> 'Asenna sivumalli', //Install Template
	'INVOKE_MODULE_FILES' 	=> 'Suorita moduuli manuaalisesti', //Execute module files manually
	'LANGUAGE_DETAILS' 		=> 'Kielen tiedot', //Language Details
	'MANAGE_SECTIONS' 		=> 'Hallinnoi lohkoja', //Manage Sections
	'MODIFY_ADVANCED_PAGE_SETTINGS' => 'Muokkaa sivun lis&auml;asetuksia', //Modify Advanced Page Settings
	'MODIFY_DELETE_GROUP' 	=> 'Muokkaa/Poista ryhm&auml;', //Modify/Delete Group
	'MODIFY_DELETE_PAGE' 	=> 'Muokkaa/Poista sivu', //Modify/Delete Page
	'MODIFY_DELETE_USER' 	=> 'Muokkaa/Poista k&auml;ytt&auml;j&auml;', //Modify/Delete User
	'MODIFY_GROUP' 			=> 'Muokkaa ryhm&auml;&auml;', //Modify Group
	'MODIFY_GROUPS' 		=> 'Muokkaa ryhmi&auml;', //Modify Groups
	'MODIFY_INTRO_PAGE' 	=> 'Muokkaa introsivua', //Modify Intro Page
	'MODIFY_PAGE' 			=> 'Muokkaa sivua', //Modify Page
	'MODIFY_PAGE_SETTINGS' 	=> 'Muokkaa sivun asetuksia', //Modify Page Settings
	'MODIFY_USER' 			=> 'Muokkaa k&auml;ytt&auml;j&auml;tietoja', //Modify User
	'MODULE_DETAILS' 		=> 'Moduulin tiedot', //Module Details
	'MY_EMAIL' 				=> 'S&auml;hk&ouml;postiosoite', //My Email
	'MY_PASSWORD' 			=> 'Salasana', //My Password
	'MY_SETTINGS' 			=> 'Omat asetukset', //My Settings
	'SEARCH_SETTINGS' 		=> 'Hakuasetukset', //Search Settings
	'SECURITY_SETTINGS'		=> 'Turva-asetukset', //Security Setting
	'SERVER_SETTINGS' 		=> 'Palvelinasetukset', //Server Settings
	'TEMPLATE_DETAILS' 		=> 'Sivumallin tiedot', //Template Details
	'UNINSTALL_LANGUAGE' 	=> 'Poista kieli', //Uninstall Language
	'UNINSTALL_MODULE' 		=> 'Poista moduuli', //Uninstall Module
	'UNINSTALL_TEMPLATE' 	=> 'Poista sivumalli', //Uninstall Template
	'UPGRADE_LANGUAGE' 		=> 'Kielen rekister&ouml;inti/p&auml;ivitys', //Language register/updating
	'UPLOAD_FILES' 			=> 'Lataa tiedosto(ja) palvelimelle', //Upload File(s)
	'VISIBILITY' 			=> 'N&auml;kyvyys', //Visibility
	'WBMAILER_SETTINGS' 	=> 'Postiasetukset' //Mailer Settings
); // $HEADING

$MESSAGE = array(
	'ADDON_ERROR_RELOAD' 	=> 'Liit&auml;nn&auml;isen p&auml;ivityksess&auml; tapahtui virhe', //Error while updating the Add-On information.
	'ADDON_GROUPS_MARKALL' => 'Valitse kaikki/poista valinnat', //Mark / unmark all
	'ADDON_LANGUAGES_RELOADED' => 'Kielten lataus onnistui', //Languages reloaded successfully
	'ADDON_MANUAL_FTP_LANGUAGE' => '<strong>HUOMIO!</strong> Tietoturvasyist&auml; kielitiedostot tulee ladata FTP:t&auml; k&auml;ytt&auml;en kansioon /languages/ ja rekister&ouml;id&auml; tai p&auml;ivitt&auml;&auml; P&auml;ivit&auml; -toiminnolla.',
								// <strong>ATTENTION!</strong> For safety reasons uploading languages files in the folder/languages/ only by FTP and use the Upgrade function for registering or updating.
	'ADDON_MANUAL_FTP_WARNING' => 'Varoitus: Moduuliin liittyv&auml;t tietokantataulut tyhjennet&auml;&auml;n. ', //Warning: Existing module database entries will get lost. 
	'ADDON_MANUAL_INSTALLATION' => 'Uusien moduulien lataamista FTP:ll&auml; ei suositella, koska moduulien asennukseen liittyv&auml;t toiminnot <tt>install</tt>, <tt>upgrade</tt> ja <tt>uninstall</tt> eiv&auml;t t&auml;ll&ouml;in tapahdu automaattisesti. Manuaalisesti asennettuna moduulin toiminta tai sen poistaminen v&auml;ltt&auml;m&auml;tt&auml; toimi suunnitellusti.<br /><br />Alla voit erityistapauksessa k&auml;ynnist&auml;&auml; FTP:ll&auml; ladattujen moduulien toiminnot manuaalisesti.',
								// When modules are uploaded via FTP (not recommended), the module installation functions <tt>install</tt>, <tt>upgrade</tt> or <tt>uninstall</tt> will not be executed automatically. Those modules may not work correct or do not uninstall properly.<br /><br />You can execute the module functions manually for modules uploaded via FTP below.
	'ADDON_MANUAL_INSTALLATION_WARNING' => 'Varoitus: Moduuliin liittyv&auml;t tietokantataulut tyhjennet&auml;&auml;n. K&auml;yt&auml; t&auml;t&auml; toimintoa vain, jos olet ladannut moduulin FTP:ll&auml; ja havaitset siin&auml; ongelmia.',
								// Warning: Existing module database entries will get lost. Only use this option if you experience problems with modules uploaded via FTP.
	'ADDON_MANUAL_RELOAD_WARNING' => 'Varoitus: Moduuliin liittyv&auml;t tietokantataulut tyhjennet&auml;&auml;n. ', //Warning: Existing module database entries will get lost. 
	'ADDON_MODULES_RELOADED' => 'Moduulien lataus onnistui', // Modules reloaded successfully
	'ADDON_PRECHECK_FAILED' => 'Liit&auml;nn&auml;isen asentaminen ep&auml;onnistui. J&auml;rjestelm&auml; ei t&auml;yt&auml; liit&auml;nn&auml;isen minimivaatimuksia. Korjaa alla mainitut ep&auml;kohdat liit&auml;nn&auml;isen oikean toiminnan varmistamiseksi:',
								//Add-on installation failed. Your system does not fulfill the requirements of this Add-on. To make this Add-on working on your system, please fix the issues summarized below.
	'ADDON_RELOAD' 			=> 'P&auml;ivit&auml; tietokanta liit&auml;nn&auml;isen tiedoilla (esim. kun liit&auml;nn&auml;inen on ladattu FTP:ll&auml;)', //Update database with information from Add-on files (e.g. after FTP upload).
	'ADDON_TEMPLATES_RELOADED' => 'Sivumallien lataus onnistui', // Templates reloaded successfully
	'ADMIN_INSUFFICIENT_PRIVELLIGES' => 'K&auml;ytt&auml;j&auml;oikeutesi eiv&auml;t riit&auml;', // Insufficient privelliges to be here
	'FORGOT_PASS_ALREADY_RESET' => 'Salasanan resetointi on mahdollista korkeintaan kerran tunnissa', // Password cannot be reset more than once per hour, sorry
	'FORGOT_PASS_CANNOT_EMAIL' => 'Salasanan l&auml;hett&auml;minen s&auml;hk&ouml;postitse ei onnistu. Ota yhteytt&auml; sivuston yll&auml;pitoon', // Unable to email password, please contact system administrator
	'FORGOT_PASS_EMAIL_NOT_FOUND' => 'Sy&ouml;tt&auml;m&auml;&auml;si s&auml;hk&ouml;postiosoitetta ei l&ouml;ydy j&auml;rjestelm&auml;st&auml;', // The email that you entered cannot be found in the database
	'FORGOT_PASS_NO_DATA' 	=> 'Ole hyv&auml; ja kirjoita s&auml;hk&ouml;postiosoitteesi alla olevaan kentt&auml;&auml;n', // Please enter your email address below
	'FORGOT_PASS_PASSWORD_RESET' => 'K&auml;ytt&auml;j&auml;tunnus ja salasana on l&auml;hetetty s&auml;hk&ouml;postiosoitteeseesi', // Your username and password have been sent to your email address
	'FRONTEND_SORRY_NO_ACTIVE_SECTIONS' => 'Sivulla ei ole voimassa olevaa sis&auml;lt&ouml;&auml;', // Sorry, no active content to display
	'FRONTEND_SORRY_NO_VIEWING_PERMISSIONS' => 'Sinulla ei valitettavasti ole riitt&auml;vi&auml; oikeuksia t&auml;lle sivulle', //Sorry, you do not have permissions to view this page
	'GENERIC_ALREADY_INSTALLED' => 'Asennettu aiemmin', // Already installed
	'GENERIC_BAD_PERMISSIONS' => 'Kohdekansioon kirjoittaminen ei onnistu', // Unable to write to the target directory
	'GENERIC_CANNOT_UNINSTALL' => 'Poistaminen ei onnistu', // Cannot uninstall
	'GENERIC_CANNOT_UNINSTALL_IN_USE' => 'Poistaminen ei onnistu: valittu tiedosto on k&auml;yt&ouml;ss&auml;', // Cannot Uninstall: the selected file is in use
	'GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL' => '<br /><br />{{type}} <b>{{type_name}}</b> ei ole poistettavissa, koska se on edelleen k&auml;yt&ouml;ss&auml; {{pages}}.<br /><br />',
								// <br /><br />{{type}} <b>{{type_name}}</b> could not be uninstalled, because it is still in use on {{pages}}.<br /><br />
	'GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES' => 'sivulla;sivuilla', // this page;these pages
	'GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE' => 'Sivumallia <b>{{name}}</b> ei voi poistaa, koska se on sivuston oletusmalli!',
								// Can\'t uninstall the template <b>{{name}}</b>, because it is the default template!
	'GENERIC_CANNOT_UNZIP' 	=> 'Tiedoston purkaminen ei onnistu', // Cannot unzip file
	'GENERIC_CANNOT_UPLOAD' => 'Tiedoston lataaminen ei onnistu', // Cannot upload file
	'GENERIC_COMPARE' 		=> ' onnistui', //  successfully
	'GENERIC_ERROR_OPENING_FILE' => 'Virhe avattaessa tiedostoa.', // Error opening file.
	'GENERIC_FAILED_COMPARE' => ' ei onnistunut', //  failed
	'GENERIC_FILE_TYPE' 	=> 'Huomioi, ett&auml; palvelimelle ladattavan tiedostotyypin pit&auml;&auml; olla:', // Please note that the file you upload must be of the following format:
	'GENERIC_FILE_TYPES' 	=> 'Huomioi, ett&auml; palvelimelle ladattavan tiedostotyypin pit&auml;&auml; olla jokin seuraavista:', // Please note that the file you upload must be in one of the following formats:
	'GENERIC_FILL_IN_ALL' 	=> 'Ole hyv&auml;, palaa takaisin ja t&auml;yt&auml; kaikki kent&auml;t.', // Please go back and fill-in all fields
	'GENERIC_INSTALLED' 	=> 'Asennus onnistui', // Installed successfully
	'GENERIC_INVALID' 		=> 'Ladattu tiedosto on virheellinen', // The file you uploaded is invalid
	'GENERIC_INVALID_ADDON_FILE' => 'Lepton-asennustiedosto on virheellinen. Ole hyv&auml; ja tarkista pakattu tiedosto.', // Invalid Lepton installation file. Please check the *.zip format.
	'GENERIC_INVALID_LANGUAGE_FILE' => 'Lepton-kielitiedosto on virheellinen. Ole hyv&auml; ja tarkista tekstitiedosto.',
	'GENERIC_IN_USE' 		=> ' mutta on k&auml;yt&ouml;ss&auml; kohteessa ', // but used in
	'GENERIC_MODULE_VERSION_ERROR' => 'Moduulia ei ole asennettu oikein!', // The module is not installed properly!
	'GENERIC_NOT_COMPARE' 	=> 'ei ole mahdollinen', //  not possibly
	'GENERIC_NOT_INSTALLED' => 'Ei asennettu', // Not installed 
	'GENERIC_NOT_UPGRADED' 	=> 'P&auml;ivitys ei ole mahdollinen', // Actualization not possibly
	'GENERIC_PLEASE_BE_PATIENT' => 'T&auml;m&auml; saattaa kest&auml;&auml; hetken. Ole hyv&auml; ja odota!', // Please be patient, this might take a while.
	'GENERIC_PLEASE_CHECK_BACK_SOON' => 'Tervetuloa piakkoin uudelleen...', // Please check back soon...
	'GENERIC_SECURITY_ACCESS'	=> 'K&auml;ytt&ouml;oikeusrike!! P&auml;&auml;sy kielletty', // Security offense!! Access denied
	'GENERIC_SECURITY_OFFENSE'	=> 'K&auml;ytt&ouml;oikeusrike!! Tietojen tallennusta ei voitu tehd&auml;!!', // Security offense!! data storing was refused!!
	'GENERIC_UNINSTALLED' 	=> 'Poisto onnistui', // Uninstalled successfully
	'GENERIC_UPGRADED' 		=> 'P&auml;ivitys onnistui', // Upgraded successfully
	'GENERIC_VERSION_COMPARE' => 'Version tarkistus', // Version comparison
	'GENERIC_VERSION_GT' 	=> 'P&auml;ivitys tarvitaan', // Upgrade necessary!
	'GENERIC_VERSION_LT' 	=> 'Palauta aiempi versio', // Downgrade
	'GENERIC_WEBSITE_UNDER_CONSTRUCTION' => 'Sivusto ty&ouml;n alla', //Website Under Construction
	'GROUPS_ADDED' 			=> 'Ryhm&auml;n lis&auml;ys onnistui', // Group added successfully
	'GROUPS_CONFIRM_DELETE' => 'Haluatko varmasti poistaa valitun ryhm&auml;n (ja kaikki ryhm&auml;&auml;n kuuluvat k&auml;ytt&auml;j&auml;t)?', // Are you sure you want to delete the selected group (and any users that belong to it)?
	'GROUPS_DELETED' => 'Ryhm&auml;n poistaminen onnistui', // Group deleted successfully
	'GROUPS_GROUP_NAME_BLANK' => 'Ryhm&auml;n nimi on tyhj&auml;', // Group name is blank
	'GROUPS_GROUP_NAME_EXISTS' => 'Saman niminen ryhm&auml; on jo olemassa', // Group name already exists
	'GROUPS_NO_GROUPS_FOUND' => 'Ei ryhmi&auml;', // No groups found
	'GROUPS_SAVED' 			=> 'Ryhm&auml;n tallennus onnistui', // Group saved successfully
	'LANG_MISSING_PARTS_NOTICE' => 'Kielen asennus ei onnistu. Yksi tai useampia seuraavista muuttujista puuttuu:<br />language_code<br />language_name<br />language_version<br />language_license',
								// Language installation failed, one (or more) of the following variables is missing:<br />language_code<br />language_name<br />language_version<br />language_license
	'LOGIN_AUTHENTICATION_FAILED' => 'K&auml;ytt&auml;j&auml;tunnus tai salasana on virheellinen', // Username or password incorrect
	'LOGIN_BOTH_BLANK' 		=> 'Ole hyv&auml; ja kirjoita k&auml;ytt&auml;j&auml;tunnuksesi ja salasanasi', // Please enter your username and password below
	'LOGIN_PASSWORD_BLANK' 	=> 'Ole hyv&auml; ja kirjoita salasana', // Please enter a password
	'LOGIN_PASSWORD_TOO_LONG' => 'Antamasi salasana on liian pitk&auml;', // Supplied password to long
	'LOGIN_PASSWORD_TOO_SHORT' => 'Antamasi salasana on liian lyhyt', // Supplied password to short
	'LOGIN_USERNAME_BLANK' 	=> 'Ole hyv&auml; ja kirjoita k&auml;ytt&auml;j&auml;tunnus', // Please enter a username
	'LOGIN_USERNAME_TOO_LONG' => 'Antamasi k&auml;ytt&auml;j&auml;tunnus on liian pitk&auml;', // Supplied username to long
	'LOGIN_USERNAME_TOO_SHORT' => 'Antamasi k&auml;ytt&auml;j&auml;tunnus on liian lyhyt', // Supplied username to short
	'MEDIA_BLANK_EXTENSION' => 'Et antanut tiedoston p&auml;&auml;tett&auml;',  // You did not enter a file extension
	'MEDIA_BLANK_NAME' 		=> 'Et antanut uutta nime&auml;', // You did not enter a new name
	'MEDIA_CANNOT_DELETE_DIR' => 'Valittua kansiota ei voi poistaa', // Cannot delete the selected folder
	'MEDIA_CANNOT_DELETE_FILE' => 'Valittua tiedostoa ei voi poistaa', // Cannot delete the selected file
	'MEDIA_CANNOT_RENAME' 	=> 'Uudelleennime&auml;minen ep&auml;onnistui', // Rename unsuccessful
	'MEDIA_CONFIRM_DELETE' 	=> 'Haluatko varmasti poistaa seuraavan tiedoston tai kansion?', // Are you sure you want to delete the following file or folder?
	'MEDIA_CONFIRM_DELETE_FILE'	=> 'Haluatko varmasti poistaa tiedoston {name}?', // Are you sure you want to delete file {name}?
	'MEDIA_CONFIRM_DELETE_DIR'	=> 'Haluatko varmasti poistaa kansion {name}?', // Are you sure you want to delete the directory {name}?
	'MEDIA_DELETED_DIR' 	=> 'Kansion poisto onnistui', // Folder deleted successfully
	'MEDIA_DELETED_FILE' 	=> 'Tiedoston poisto onnistui', //File deleted successfully
	'MEDIA_DIR_ACCESS_DENIED' => 'Annettua hakemistoa ei ole olemassa tai siihen ei ole tarvittavia oikeuksia.', // Specified directory does not exist or is not allowed.
	'MEDIA_DIR_DOES_NOT_EXIST' => 'Hakemistoa ei l&ouml;ydy', // Directory does not exist
	'MEDIA_DIR_DOT_DOT_SLASH' => 'Kansion nimess&auml; ei saa olla ../', // Cannot include ../ in the folder name
	'MEDIA_DIR_EXISTS' 		=> 'Antamasi nimen mukainen kansio on jo olemassa', // A folder matching the name you entered already exists
	'MEDIA_DIR_MADE' 		=> 'Kansion luonti onnistui', // Folder created successfully
	'MEDIA_DIR_NOT_MADE' 	=> 'Kansiota ei voi luoda', // Unable to create folder
	'MEDIA_FILE_EXISTS' 	=> 'Antamasi nimen mukainen tiedosto on jo olemassa', // A file matching the name you entered already exists
	'MEDIA_FILE_NOT_FOUND' 	=> 'Tiedostoa ei l&ouml;ydy', // File not found
	'MEDIA_NAME_DOT_DOT_SLASH' => 'Nimess&auml; ei saa olla ../', // Cannot include ../ in the name
	'MEDIA_NAME_INDEX_PHP' 	=> 'Index.php ei ole sallittu nimi', // Cannot use index.php as the name
	'MEDIA_NONE_FOUND' 		=> 'Nykyisest&auml; kansiosta ei l&ouml;ydy mediatiedostoja', // No media found in the current folder
	'MEDIA_RENAMED' 		=> 'Uudelleennime&auml;minen onnistui', // Rename successful
	'MEDIA_SINGLE_UPLOADED' => ' tiedoston lataus onnistui', //  file was successfully uploaded
	'MEDIA_TARGET_DOT_DOT_SLASH' => 'Kohdekansion nimess&auml; ei saa olla ../', // Cannot have ../ in the folder target
	'MEDIA_UPLOADED' 		=> ' tiedostojen lataus onnistui', //  files were successfully uploaded
	'MOD_MISSING_PARTS_NOTICE' => 'Moduulin "%s" asennus ei onnistu. Yksi tai useampia seuraavista muuttujista puuttuu:<br />module_directory<br />module_name<br />module_version<br />module_author<br />module_license<br />module_guid<br />module_function',
							// The installation of module "%s" failed, one (or more) of the following variables is missing:<br />module_directory<br />module_name<br />module_version<br />module_author<br />module_license<br />module_guid<br />module_function
	'MOD_FORM_EXCESS_SUBMISSIONS' => 'T&auml;h&auml;n lomakkeeseen on liitetty tietty enimm&auml;ism&auml;&auml;r&auml; l&auml;hetyksi&auml; saman tunnin aikana. Enimm&auml;ism&auml;&auml;r&auml; lomakkeen l&auml;hetyksi&auml; on ylittynyt. Ole hyv&auml; ja yrit&auml; my&ouml;hemmin uudelleen.', // Sorry, this form has been submitted too many times so far this hour. Please retry in the next hour.
  'MOD_FORM_INCORRECT_CAPTCHA' => 'Sy&ouml;tt&auml;m&auml;si lomakkeen varmistustunnus (Captcha) on virheellinen. Jos Captcha-varmistuksessa on ongelmia, voit ottaa yhteytt&auml; s&auml;hk&ouml;postilla: <a href="mailto:'.SERVER_EMAIL.'">'.SERVER_EMAIL.'</a>',
							// The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email: <a href="mailto:'.SERVER_EMAIL.'">'.SERVER_EMAIL.'</a>
	'MOD_FORM_REQUIRED_FIELDS' => 'Seuraavin kenttiin tulee sy&ouml;tt&auml;&auml; tieto', // You must enter details for the following fields
	'PAGES_ADDED' 			=> 'Sivun lis&auml;ys onnistui', // Page added successfully
	'PAGES_ADDED_HEADING' 	=> 'Sivun otsikon lis&auml;ys onnistui', // Page heading added successfully
	'PAGES_BLANK_MENU_TITLE' => 'Ole hyv&auml; ja sy&ouml;t&auml; valikkoteksti', // Please enter a menu title
	'PAGES_BLANK_PAGE_TITLE' => 'Ole hyv&auml; ja sy&ouml;t&auml; sivun otsikko', // Please enter a page title
	'PAGES_CANNOT_CREATE_ACCESS_FILE' => 'Suojaustiedoston luonti sivuhakemistoon (page) ep&auml;onnistui, k&auml;ytt&ouml;oikeudet eiv&auml;t riit&auml;', // 
	'PAGES_CANNOT_DELETE_ACCESS_FILE' => 'Suojaustiedoston poisto sivuhakemistosta (page) ep&auml;onnistui, k&auml;ytt&ouml;oikeudet eiv&auml;t riit&auml;', // 
	'PAGES_CANNOT_REORDER' 	=> 'Sivujen uudelleenj&auml;rjestely ep&auml;onnistui', // Error re-ordering page
	'PAGES_DELETED' 		=> 'Sivun poisto onnistui', // Page deleted successfully
	'PAGES_DELETE_CONFIRM' 	=> 'Haluatko varmasti poistaa valitun sivun &laquo;%s&raquo; (ja kaikki sen alisivut)', // Are you sure you want to delete the selected page &laquo;%s&raquo; (and all of its sub-pages)
	'PAGES_INSUFFICIENT_PERMISSIONS' => 'K&auml;ytt&auml;j&auml;oikeutesi eiv&auml;t riit&auml; t&auml;m&auml;n sivun muokkaamiseen', // You do not have permissions to modify this page
	'PAGES_INTRO_EMPTY' 		=> 'Tyhj&auml;&auml; introsivua ei voi tallentaa. Ole hyv&auml; ja lis&auml;&auml; sivulle sis&auml;lt&ouml;.', // Please insert content, an empty intro page cannot be saved.
	'PAGES_INTRO_LINK' 		=> 'Muokkaa introsivua klikkaamalla T&Auml;ST&Auml;', // Click HERE to modify the intro page
	'PAGES_INTRO_NOT_WRITABLE' => 'Tiedostoa intro.php ei voi tallentaa sivuhakemistoon (page), k&auml;ytt&ouml;oikeudet eiv&auml;t riit&auml;', // Cannot write to file page-directory/intro.php, (insufficient privileges)
	'PAGES_INTRO_SAVED' 	=> 'Introsivun tallennus onnistui', // Intro page saved successfully
	'PAGES_LAST_MODIFIED' 	=> 'Viimeksi muokannut', // Last modification by
	'PAGES_NOT_FOUND' 		=> 'Sivua ei l&ouml;ydy', // Page not found
	'PAGES_NOT_SAVED' 		=> 'Sivun tallennuksessa tapahtui virhe', // Error saving page
	'PAGES_PAGE_EXISTS' 	=> 'Sivu vastaavalla nimell&auml; on jo olemassa', // A page with the same or similar title exists
	'PAGES_REORDERED' 		=> 'Sivujen uudelleenj&auml;rjestely onnistui', // Page re-ordered successfully
	'PAGES_RESTORED' 		=> 'Sivun palautus onnistui', // Page restored successfully
	'PAGES_RETURN_TO_PAGES' => 'Palaa sivuille', // Return to pages
	'PAGES_SAVED' 			=> 'Sivun tallennus onnistui', // Page saved successfully
	'PAGES_SAVED_SETTINGS' 	=> 'Sivun asetusten tallennus onnistui', // Page settings saved successfully
	'PAGES_SECTIONS_PROPERTIES_SAVED' => 'Lohkon asetusten tallennus onnistui', // Section properties saved successfully
	'PREFERENCES_CURRENT_PASSWORD_INCORRECT' => 'Sy&ouml;tt&auml;m&auml;si nykyinen salasana on virheellinen', // The (current) password you entered is incorrect
	'PREFERENCES_DETAILS_SAVED' => 'Tietojen tallennus onnistui', // Details saved successfully
	'PREFERENCES_EMAIL_UPDATED' => 'S&auml;hk&ouml;postitietojen p&auml;ivitys onnistui', // Email updated successfully
	'PREFERENCES_INVALID_CHARS' => 'Salasanassa on virheellisi&auml; merkkej&auml;. Salasanassa voi k&auml;ytt&auml;&auml; seuraavia merkkej&auml;: a-z\A-Z\0-9\_\-\!\#\*\+ ', // Invalid password chars used, vailid chars are: a-z\A-Z\0-9\_\-\!\#\*\+
	'PREFERENCES_PASSWORD_CHANGED' => 'Salasanan vaihto onnistui', // Password changed successfully
	'RECORD_MODIFIED_FAILED' => 'Tietueen p&auml;ivityksess&auml; tapahtui virhe.', // The change of the record has missed.
	'RECORD_MODIFIED_SAVED' => 'Muutetun tietueen p&auml;ivitys onnistui', // The changed record was updated successfully.
	'RECORD_NEW_FAILED' 	=> 'Tietueen lis&auml;yksess&auml; tapahtui virhe.', // Adding a new record has missed.
	'RECORD_NEW_SAVED' 		=> 'Tietueen lis&auml;ys onnistui.', // New record was added successfully.
	'SETTINGS_MODE_SWITCH_WARNING' => 'Huom! Kaikki tallentamattomat muutokset katoavat kun painat t&auml;t&auml; nappia', // Please Note: Pressing this button resets all unsaved changes
	'SETTINGS_SAVED' 		=> 'Asetusten tallennus onnistui', // Settings saved successfully
	'SETTINGS_UNABLE_OPEN_CONFIG' => 'Asetustiedoston avaaminen ei onnistu', // Unable to open the configuration file
	'SETTINGS_UNABLE_WRITE_CONFIG' => 'Asetustiedostoon ei pysty kirjoittamaan', // Cannot write to configuration file
	'SETTINGS_WORLD_WRITEABLE_WARNING' => 'Huom! T&auml;t&auml; suositellaan k&auml;ytett&auml;v&auml;ksi vain testitarkoituksessa', // Please note: this is only recommended for testing environments
	'SIGNUP2_ADMIN_INFO' 	=> '
Sivustolle rekister&ouml;ityi uusi k&auml;ytt&auml;j&auml;.

K&auml;ytt&auml;j&auml;nimi: {LOGIN_NAME}
K&auml;ytt&auml;j&auml;-ID: {LOGIN_ID}
S&auml;hk&ouml;posti: {LOGIN_EMAIL}
IP-osoite: {LOGIN_IP}
Rekister&ouml;itymisp&auml;iv&auml;: {SIGNUP_DATE}
--------------------------------------------
T&auml;m&auml; on automaattinen viesti j&auml;rjestelm&auml;lt&auml;!

',
//A new user was registered.
//
//Username: {LOGIN_NAME}
//UserId: {LOGIN_ID}
//E-Mail: {LOGIN_EMAIL}
//IP-Adress: {LOGIN_IP}
//Registration date: {SIGNUP_DATE}
//----------------------------------------
//This message was automatic generated!

	'SIGNUP2_BODY_LOGIN_FORGOT' => '
Tervehdys {LOGIN_DISPLAY_NAME},

Vastaanotit t&auml;m&auml;n viestin, koska olet ilmoittanut unohtaneesi salasanasi.

Tunnuksesi sivustolle \'{LOGIN_WEBSITE_TITLE}\' ovat:

K&auml;ytt&auml;j&auml;: {LOGIN_NAME}
Salasana: {LOGIN_PASSWORD}

Sinulle on luotu uusi salasana, jonka n&auml;et yll&auml;.
Vanhalla salasanallasi ei en&auml;&auml; pysty kirjautumaan sivustolle!
Jos sinulla on kysymyksi&auml; tai ongelmia k&auml;ytt&auml;j&auml;tietoihisi liittyen,
ota yhteytt&auml; sivuston \'{LOGIN_WEBSITE_TITLE}\' yll&auml;pitoon.
Mahdollisten kirjautumisongelmien v&auml;ltt&auml;miseksi suosittelemme selaimen 
v&auml;limuistin tyhjent&auml;mist&auml; ennen kuin kirjaudut uusilla tunnuksilla.

Terveisin
--------------------------------------------
T&auml;m&auml; on automaattinen viesti j&auml;rjestelm&auml;lt&auml;!

',

//Hello {LOGIN_DISPLAY_NAME},
//
//This mail was sent because the \'forgot password\' function has been applied to your account.
//
//Your new \'{LOGIN_WEBSITE_TITLE}\' login details are:
//
//Username: {LOGIN_NAME}
//Password: {LOGIN_PASSWORD}
//
//Your password has been reset to the one above.
//This means that your old password will no longer work anymore!
//If you\'ve got any questions or problems within the new login-data
//you should contact the website-team or the admin of \'{LOGIN_WEBSITE_TITLE}\'.
//Please remember to clean you browser-cache before using the new one to avoid unexpected fails.
//
//Regards
//------------------------------------
//This message was automatic generated

	'SIGNUP2_BODY_LOGIN_INFO' => '
Tervehdys {LOGIN_DISPLAY_NAME},

Tervetuloa sivustollemme \'{LOGIN_WEBSITE_TITLE}\'.

Tunnuksesi sivustolle \'{LOGIN_WEBSITE_TITLE}\' ovat:
K&auml;ytt&auml;j&auml;: {LOGIN_NAME}
Salasana: {LOGIN_PASSWORD}

Terveisin

Voit poistaa viestin ilman lis&auml;toimenpiteit&auml;, jos 
se on saapunut sinulle virheen tai erehdyksen vuoksi!
--------------------------------------------
T&auml;m&auml; on automaattinen viesti j&auml;rjestelm&auml;lt&auml;!
',
//Hello {LOGIN_DISPLAY_NAME},
//
//Welcome to our \'{LOGIN_WEBSITE_TITLE}\'.
//
//Your \'{LOGIN_WEBSITE_TITLE}\' login details are:
//Username: {LOGIN_NAME}
//Password: {LOGIN_PASSWORD}
//
//Regards
//
//Please:
//if you have received this message by an error, please delete it immediately!
//-------------------------------------
//This message was automatic generated!
	'SIGNUP2_SUBJECT_LOGIN_INFO' => 'LEPTON -kirjautumistietosi...', // Your LEPTON login details...
	'SIGNUP_NO_EMAIL' 		=> 'Ole hyv&auml; ja kirjoita s&auml;hk&ouml;postiosoitteesi', // You must enter an email address
	'START_CURRENT_USER' 	=> 'Olet t&auml;ll&auml; hetkell&auml; kirjautuneena tunnuksella:', // You are currently logged in as:
	'START_INSTALL_DIR_EXISTS' => 'Varoitus! Asennushakemisto "/install" on edelleen olemassa! Tietoturvan vuoksi sen poistaminen on eritt&auml;in suositeltavaa.', // Warning, Installation Directory Still Exists!
	'START_WELCOME_MESSAGE' => 'Tervetuloa LEPTON-yll&auml;pitoon', // Welcome to Lepton Administration
	'SYSTEM_FUNCTION_DEPRECATED'=> 'Toimintoa <b>%s</b> ei en&auml;&auml; tueta, k&auml;yt&auml; sen sijasta toimintoa <b>%s</b>!', // The function <b>%s</b> is deprecated, use the function <b>%s</b> instead!
	'SYSTEM_FUNCTION_NO_LONGER_SUPPORTED' => 'Toiminto <b>%s</b> on vanhentunut, eik&auml; sit&auml; en&auml;&auml; tueta!', // The function <b>%s</b> is out of date and no longer supported!
	'SYSTEM_SETTING_NO_LONGER_SUPPORTED' => 'Asetus <b>%s</b> ei ole en&auml;&auml; k&auml;yt&ouml;ss&auml; eik&auml; sit&auml; n&auml;in ollen oteta huomioon!', // The setting <b>%s</b> is no longer supported and will be ignored!
	'TEMPLATES_CHANGE_TEMPLATE_NOTICE' => 'Huom! K&auml;ytett&auml;v&auml;n sivumallin voi vaihtaa Asetukset-osiosta', // Please note: to change the template you must go to the Settings section
	'TEMPLATES_MISSING_PARTS_NOTICE' => 'Sivumallin asennus ei onnistu. Yksi tai useampia seuraavista muuttujista puuttuu:<br />template_directory<br />template_name<br />template_version<br />template_author<br />template_license<br />template_function',
								// Template installation failed, one (or more) of the following variables is missing:<br />template_directory<br />template_name<br />template_version<br />template_author<br />template_license<br />template_function ("theme" oder "template")
	'USERS_ADDED' 			=> 'K&auml;ytt&auml;j&auml;n lis&auml;ys onnistui', // User added successfully
	'USERS_CANT_SELFDELETE' => 'Toimintoa ei voi suorittaa. Et voi poistaa itse&auml;si!', // Function rejected, You can not delete yourself!
	'USERS_CHANGING_PASSWORD' => 'Huom! &Auml;l&auml; muuta yll&auml;olevia tietoja ellet halua muuttaa valitun k&auml;ytt&auml;j&auml;n salasanaa',
								// Please note: You should only enter values in the above fields if you wish to change this users password
	'USERS_CONFIRM_DELETE' 	=> 'Haluatko varmasti poistaa valitun k&auml;ytt&auml;j&auml;n?', // Are you sure you want to delete the selected user?
	'USERS_DELETED' 		=> 'K&auml;ytt&auml;j&auml;n poisto onnistui', // User deleted successfully
	'USERS_EMAIL_TAKEN' 	=> 'Antamasi s&auml;hk&ouml;postiosoite on jo k&auml;yt&ouml;ss&auml;', // The email you entered is already in use
	'USERS_INVALID_EMAIL' 	=> 'Antamasi s&auml;hk&ouml;postiosoite on virheellinen', // The email address you entered is invalid
	'USERS_NAME_INVALID_CHARS' => 'K&auml;ytt&auml;j&auml;nimess&auml; on kirjaimia, jotka eiv&auml;t ole sallittuja', // Invalid chars for username found
	'USERS_NO_GROUP' 		=> 'Ryhm&auml;&auml; ei ole valittu', // No group was selected
	'USERS_PASSWORD_MISMATCH' => 'Antamasi salasanat ovat erilaiset', // The passwords you entered do not match
	'USERS_PASSWORD_TOO_SHORT' => 'Antamasi salasana on liian lyhyt', // The password you entered was too short
	'USERS_SAVED' 			=> 'K&auml;ytt&auml;j&auml;n tallennus onnistui', // User saved successfully
	'USERS_USERNAME_TAKEN' 	=> 'Antamasi k&auml;ytt&auml;j&auml;tunnus on jo k&auml;yt&ouml;ss&auml;', // The username you entered is already taken
	'USERS_USERNAME_TOO_SHORT' => 'Antamasi k&auml;ytt&auml;j&auml;tunnus on liian lyhyt' // The username you entered was too short
); // $MESSAGE

$OVERVIEW = array(
	'ADMINTOOLS' 			=> 'Lepton-j&auml;rjestelm&auml;hallinnan ty&ouml;kalut', // Access the Lepton administration tools...
	'GROUPS' 				=> 'K&auml;ytt&auml;j&auml;ryhmien ja niiden oikeuksien yll&auml;pito...', // Manage user groups and their system permissions...
	'HELP' 					=> 'Kysytt&auml;v&auml;&auml;? T&auml;&auml;lt&auml; l&ouml;yd&auml;t vastaukset...', // Got a questions? Find your answer...
	'LANGUAGES' 			=> 'Kielten yll&auml;pito...', // Manage Lepton languages...
	'MEDIA' 				=> 'Mediakansiossa olevien tiedostojen yll&auml;pito...', // Manage files stored in the media folder...
	'MODULES' 				=> 'Lepton-moduulien yll&auml;pito...', // Manage Lepton modules...
	'PAGES' 				=> 'Sivuston rakenteen yll&auml;pito...', // Manage your websites pages...
	'PREFERENCES' 			=> 'Omien tietojen (s&auml;hk&ouml;posti, salasana jn.) yll&auml;pito...', // Change preferences such as email address, password, etc... 
	'SETTINGS' 				=> 'Lepton-sivuston asetusten yll&auml;pito...', // Changes settings for Lepton...
	'START' 				=> 'Yll&auml;pitosivuston aloitusn&auml;kym&auml;', // Administration overview
	'TEMPLATES' 			=> 'Muokkaa sivuston ulkoasua erilaisten sivumallien avulla...', // Change the look and feel of your website with templates...
	'USERS' 				=> 'Sivuston k&auml;ytt&auml;jien yll&auml;pito...', // Manage users who can log-in to Lepton...
	'VIEW' 					=> 'Avaa sivusto uudessa ikkunassa...' // Quickly view and browse your website in a new window...
);

/* 
 * Create the old languages definitions only if specified in settings 
 */ 
if (ENABLE_OLD_LANGUAGE_DEFINITIONS) {
	foreach ($MESSAGE as $key => $value) {
		$x = strpos($key, '_');
		$MESSAGE[substr($key, 0, $x)][substr($key, $x+1)] = $value;
	}
}
?>