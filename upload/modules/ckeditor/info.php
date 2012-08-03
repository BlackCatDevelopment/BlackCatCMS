<?php
/**
 *	@module			ckeditor
 *	@version		see info.php of this module
 *	@authors		Michael Tenschert, Dietrich Roland Pehlke
 *	@copyright	2010-2012 Michael Tenschert, Dietrich Roland Pehlke
 *	@license		GNU General Public License
 *	@license terms	see info.php of this module
 *	@platform		see info.php of this module
 *	@requirements	PHP 5.2.x and higher
 *	@version		$Id$
 *
 */
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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
// end include class.secure.php

$module_directory	= 'ckeditor';
$module_name		= 'CKEditor';
$module_function	= 'WYSIWYG';
$module_version		= '0.10.0';
$module_platform	= '1.x';
$module_author		= 'Michael Tenschert, erpe, Dietrich Roland Pehlke, B. Martinovic (last)';
$module_license		= '<a target="_blank" href="http://www.gnu.org/licenses/lgpl.html">LGPL</a>';
$module_license_terms = '-';
$module_description = 'includes CKEditor 3.6.4; CKE allows editing content and can be integrated in frontend and backend modules.';
$module_guid 		= '613AF469-9EE6-40AB-B91A-AC308791D64C';
$module_home		= 'http://www.lepton-cms.org';

/**
 *
 *  0.10.0  2012-08-03  - Upgrade CKE to 3.6.4
 *                      - added Autosave plugin
 *                      - added Code Highlighting with GeSHi
 *
 *	0.9.0	2012-04-17	- Upgrade CK-Editor internal to 3.6.3.
 *
 *	0.8.0	2012-02-02	- Add "rel" attribute to linked images.
 * 
 *	0.7.9	2012-02-01	- Bugfix for misbehavoir of the $pretty flag; the default is now 'false'. Thanks to Jochen.
 *
 *	0.7.8	2011-12-28	- Insert utf-8 handling depending of DEFAULT_CHARSET; if it is set to utf-8, no entities are used.
 *
 *	0.7.7	2011-12-06	- Minor Bugfixes (thanks to Evi) and minor cosmetic code-optimations.
 *
 *	0.7.6	2011-11-08	- Add language-support for the ck-editor-template files.
 *						- Rename wbdroplets to dropleps and wbmodule to pagelink.
 *						- Minor bugfixes and cosmetic codechanges.
 *
 *	0.7.5	2011-09-30	- However, declare $paths and $ckeditor as globals to avoid some unexpected non-working results.
 *
 *	0.7.4	2011-09-14	- Upgrade CK-Editor internal to 3.6.2.
 *
 *	0.7.3	2011-09-11	- Bugfixes for Droplets and LEPTON-Link.
 *
 *	0.7.2	2011-09-10	- Bugfix - reload modules wb-link and wb-droplets.
 *
 *	0.7.1	2011-09-10	- Upgrade CK-Editor internal to 3.6.1.
 *						- Bugfixes for wysiwyg-admin.
 *
 *	0.7.0	20011-08-30	- Add class var $force to give the module-authors the chance to force
 *							their own height and width settings instead of using the given ones
 *							from the wysiwyg-admin.
 *						- New guid for this modul. At this version the module is targeted to LEPTON-CMS.
 *
 *	0.6.6	2011-08-20	- Change icon of wb-link.
 *						- Change language files form wb-link and wb-droplet .
 *
 *	0.6.5	2011-08-20	- Some bugfixes inside wb-link and wb-droplets modul to get this plugins
 *							to work within LEPTON-CMS.
 *						- Bugfix for Office 2007 skin.
 *
 *	0.6.3	2011-06-16	- update CKE to 3.6.1
 *
 *	0.6.2	2011-05-09	- update CKE to 3.6.
 *
 *	0.6.1	2011-04-07	- update CKE to 3.5.3.
 *
 *	0.6.0	2011-03-26	- update CKE to 3.5.2.
 *
 *	0.5.9	2011-02-14	- update CKE to 3.5.1.
 *
 *	0.5.8	2011-02-12	- Bugfix inside include.php for missing wysiwyg-admin module.
 *
 *	0.5.7	2010-10-24	- Add folder "editor" to the search-paths.
 *						- Remove the "custom." prefix in the custom files.
 *						- Add more custom templates (thanks to Sgt.Nops).
 *
 *	0.5.6	2010-10-23	- Bugfix inside link-plugin.
 *						- Add "templates_files" to the search-paths.
 *
 *	0.5.5	2010-10-21	- Bugfix for load a template-style.js correctly.
 *
 *	0.5.4	2010-09-21	- Update the ck-editor to 3.4.1
 *
 *	0.5.3	2010-09-21	- Replace the include.php within the right one.
 *
 *	0.5.2	2010-09-21	- Bugfix inside pagelink-module for wrong placed quotes in the
 *							generated JavaScript.
 *
 *	0.5.1	2010-06-06	- Bugfix inside the ck-editor filemanager to handle filenames
 *							within spaces in the name in the preview.
 *						- Insert new rule for the "<br />" tag.
 *
 *	0.5.0	2010-06-05	- Minor typos and cosmetic codechanges.
 *						- (Js-) Bugfix inside the ck-editor filemanager.
 *						- Add language-support for SCAYT inside "index.php".
 *						- Stop supporting PHP 4.
 *
 */	

?>