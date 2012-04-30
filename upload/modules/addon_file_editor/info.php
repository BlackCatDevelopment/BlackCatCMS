<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file defines the variables required for Website Baker.
 * 
 * 
 * @author		Christian Sommer (doc)
 * @author		Bianka Martinovic (BlackBird)
 * @author		Dietrich Roland Pehlke (aldus) - last
 * @copyright	2008-2012
 * @license		see info.php
 * @version		see info.php
 * @platform	LEPTON 1.0
 *
*/

// OBLIGATORY WEBSITE BAKER VARIABLES
$module_directory   = 'addon_file_editor';
$module_name        = 'Addon File Editor (AFE)';
$module_function    = 'tool';
$module_version     = '2.1.0';
$module_status      = 'stable';
$module_platform    = '2.x';
$module_author      = 'Christian Sommer (doc)';
$module_license     = '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public Licencse 3.0</a>';
$module_license_terms = '-';
$module_description   = 'AFE allows you to edit text- and image files of installed Add-ons via the backend. View <a href="' . WB_URL . '/modules/addon_file_editor/help/help_en.html" target="_blank">README</a> file for details.';
$module_guid        = '8B3A91F7-E26D-4992-B4B8-FEC580F379F5';


/**
 * ------------------------------------------------------------------------------------------------
 *	MODULE VERSION HISTORY
 * ------------------------------------------------------------------------------------------------
 *
 *	v.1.2.1 minor improvements in action_handler
 *
 *	v.1.2.0 stable (Dietrich Roland Pehlke (Aldus): April 19, 2011)
 *	+ added simple path-finding class to functions.inc.php to solve the path-finding-problem if
 *		code-area is inside the 'include' folder or given as a module.
 *	+ change the return-value of function 'myRegisterEditArea' inside functions.inc.php to get
 *		rid of the heredoc.
 *	
 *  v1.1.2 stable (Bianka Martinovic (BlackBird): Mar 30, 2010)
 *  + added *.jquery and *.preset to variable $text_extensions (allows to edit files with this extensions)
 *
 *	v1.1.1 stable (Christian Sommer (doc): Feb 07, 2010)
 *	+ module internal help link now opens the language specific README file
 *
 *	v1.1.0 stable (Christian Sommer (doc): Feb 06, 2010)
 *	+ added Norwegian language file and help file, many thanks to Odd Egil Hanse (oeh) 
 *
 *	v1.0.2 stable (Christian Sommer (doc): Feb 04, 2010)
 *	+ fixed possible warning during backup (PHP 5.x, DEPRACATED warnings enabled, reported by maverik) 
 *	+ fixed possible warning with date function (PHP 5.x, no default time zone set in php.ini, reported by benni)  
 *
 *	v1.0.1 stable (Christian Sommer (doc): Jan 23, 2010)
 *	+ fixed issue with jQuery toggle function (solved issue with open/close and re-open of sections) 
 *	+ replaced WB "wb_wrapper_edit_area.php" call to get rid of EditArea loading errors in IE browsers  
 *	+ the text area now expands to 98% of the backend theme width per default
 *	+ shortened the info text to avoid too big info boxes in Argos theme
 *	+ fixed some smaller CSS issues in IE 7 and IE 8 (compatibility mode only)
 *
 *	v1.0.0 stable (Christian Sommer (doc): Jan 22, 2010)
 *	+ updated copyright tag in all files to reflect year change 
 *	+ made link to help file available in all AFE dialogues
 *	+ switched to new verion numbering modus (<major>.<minor>.<bugfix>) and set version to 1.0.0 (stable) 
 *
 *	v0.90 stable (Christian Sommer (doc): Jan 07, 2010)
 *	+ added second save button to file edit dialogue ("Save and back", "Save") as proposed by mr-fan 
 *  + increased min-width and allowed to resize the EditArea dialogue by the user as proposed by stefek 
 *
 *	v0.81 stable (Christian Sommer (doc): Oct 17, 2009)
 *	+ adapted /lib/PEAR.php to avoid errors with missing file PEAR5.php (PHP with Zend engine higher than 2-dev) 
 *  + added $module_guid to info.php
 *
 *	v0.80 stable (Christian Sommer (doc): Aug 04, 2009)
 *	+ added additional check if Addon File Editor is initialized properly
 *	+ updated README file to reflect the latest changes
 *
 *	v0.80 RC2 (Christian Sommer (doc): Jul 26, 2009)
 *	+ fixed bug in jQuery function (limited JS to overview page only to prevent hidding of required elements)
 *	+ added icon for the file action "edit"
 *	+ modified image handler (click on file name opens image in browser; edit button is shown if PIXLR support is enabled)
 *	+ set flag $show_all files to false to reduce file output (only show files registered; text, images, archives)
 *	+ modified comment header of all files (@platform Website Baker 2.8)
 *
 *	v0.80 RC1 (Christian Sommer (doc): Jul 25, 2009)
 *	+ removed EditArea from module folder (requires WB 2.8 for syntax highlighting)
 *	+ added jQuery support to expand/collapse list view of modules, templates and language files (WB 2.8)
 *
 *	v0.71 stable (Christian Sommer (doc): Jul 20, 2009)
 *	+ directory name shows full sub-path (e.g. /modules/anynews/htt/icon --> new: /htt/icon/ instead; old: /icon/)
 *	+ added *.tmpl and *.tpl to variable $text_extensions (allows to edit files with this extensions)
 *
 *	v0.70 stable (Christian Sommer (doc): Jul 4, 2009)
 *	+ updated 3rd party PEAR packages used to send zipped file to browser and added license files
 *	+ added pre-installation checks to check PHP/WB requirements (introduced with Website Baker 2.8)
 *
 *	v0.60 stable (Christian Sommer (doc): May 25, 2009)
 *	+ added experimental support the online Flash (TM) image editor service http://pixlr.com/
 *	+ requires Flash Plugin for your browser (set $pixlr_support = true in "config.inc.php" to enable this feature)
 *
 *	v0.51 stable (Christian Sommer (doc): May 08, 2009)
 *	+ fixed layout issue with Internet Explorer in backend.css (thanks Stefek)
 *
 *	v0.50 stable (Christian Sommer (doc): Apr 05, 2009)
 *	+ added Dutch language file contributed by the forum member Luckyluke (thanks man)
 *
 *	v0.40 stable (Christian Sommer (doc): Apr 02, 2009)
 *	+ added French language file contributed by the forum member quinto (thanks man)
 *
 *	v0.39 stable (Christian Sommer (doc): Feb 13, 2009)
 *	+ added missing index to function createTargetFolderSelectEntries (thanks to Erpe for reporting)
 *	  (caused wrong folder mapping during upload/creation for folders not created in alphabetic order)
 *	+ fixed warning caused by referrer checks (action_handler.php, download.php)
 *
 *	v0.38 stable (Christian Sommer (doc): Feb 09, 2009)
 *	+ fixed PHP4 inconsistencies (declaration of language array; no default values for ByRef function args)
 *	+ requires PHP version >= 4.3.11 and WB 2.7 (does not work with WB 2.6.x)
 *
 *	v0.37 stable (Christian Sommer (doc): Jan 26, 2009)
 *	+ moved FTP settings from config.inc.php to database
 *	+ added link to Help file
 *
 *	v0.36 RC2 (Christian Sommer (doc): Jan 20, 2009)
 *	+ removed all handlers except "edit" from filemanager (WB language files only)
 *	+ additional check to avoid unnecessary connection trials to function ftpLogin
 *	+ added missing admin footer to action_handler.php and ftp_assistant.php
 *	+ removed <br /> tag from function createSelectEntries
 *	+ fixed CSS and HTML validation errors to create valid output
 *	+ edit file dialogue now always shows Save and Cancel buttons (removed before if saved sucessfully)
 *
 *	v0.35 RC1 (Christian Sommer (doc): Jan 18, 2009)
 *	+ added FTP layer to allow editing of add-ons uploaded by FTP (e.g. language files, default modules)
 *	+ renamed tool into "Addon File Editor"
 *	+ code clean-up
 *
 *	v0.34 beta (Christian Sommer (doc): Jan 15, 2009)
 *	+ added support for WB language files (now all Add-on types are supported)
 *
 *	v0.33 beta (Christian Sommer (doc): Jan 12, 2009)
 *	+ fixed bug with additional referer check (first bugfix only works if no referer is set)
 *	+ fixed typos in German language files (thanks to Maverik)
 *
 *	v0.32 beta (Christian Sommer (doc): Jan 11, 2009)
 *	+ fixed bug with additional referer check (thanks to Maverik for reporting)
 *
 *	v0.31 beta (Christian Sommer (doc): Jan 11, 2009)
 *	+ fixed wrong URL for image preview
 *
 *	v0.30 beta (Christian Sommer (doc): Jan 11, 2009)
 *	+ some code clean-up and small cosmetics on the look and feel
 *
 *	v0.29 beta (Christian Sommer (doc): Jan 10, 2009)
 *	+ added file handler (create, upload)
 *
 *	v0.28 beta (Christian Sommer (doc): Jan 09, 2009)
 *	+ added file handlers (edit, rename, delete)
 *
 *	v0.27 beta (Christian Sommer (doc): Jan 08, 2009)
 *	+ reworked the entire explorer view
 *
 *	v0.26 beta (Christian Sommer (doc): Jan 06, 2009)
 *	+ added PEAR class HTTP:Download to create ZIP archives on the fly and to send it to the browser
 *
 *	v0.25 beta (Christian Sommer (doc): Jan 04, 2009)
 *	+ replaced Codepress with EditArea (syntax highlighting)
 *
 *	v0.24 beta (Christian Sommer (doc): Dec 17, 2008)
 *	+ introduced basic file explorer (all files are shown by default)
 *	+ added icons to highlight folders, and certain file types (text, images, archives, others)
 *
 *	v0.23 beta (Christian Sommer (doc): Dec 16, 2008)
 *	+ added some more options to settings.php
 *	+ added file and folder icons (usefull if display style is set to show files & folders)
 *
 *	v0.22 beta (Christian Sommer (doc): Dec 14, 2008)
 *	+ replaced hard coded extension array in tools.php with extension array in settings.php
 *
 *	v0.21 beta (Christian Sommer (doc): Dec 14, 2008)
 *	+ removed leading path from zip archive for intallable WB archives (tested on Windows only so far)
 *	+ added pclzip error output to the error message shown if zip creation fails
 *
 *	v0.20 beta (Christian Sommer (doc): Dec 13, 2008)
 *	+ reworked backend interface (look and feel)
 *	+ integrated template class to seperate HTML and PHP code
 *
 *	v0.10 alpha (Christian Sommer (doc): Oct 22, 2008)
 *	+ added possibility to edit template and module files based 
 *
 * CREDITS:
 * Inspired by the template edit module created by John and Peter Gilbane (thanks guys)
 * ------------------------------------------------------------------------------------------------
*/
?>