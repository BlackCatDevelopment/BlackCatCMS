<?php

/**
 *  @module         code2
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2012 Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 */

$module_directory       = 'code2';
$module_name            = 'Code2';
$module_function        = 'page';
$module_version         = '2.2.4';
$module_platform        = '1.x';
$module_author          = 'Ryan Djurovich, Chio Maisriml, Thorn, Aldus.';
$module_license         = 'GNU General Public License';
$module_license_terms   = '-';
$module_description     = 'This module allows you to execute PHP, HTML, Javascript commands and internal comments (<span style="color:#FF0000;">limit access to users you can trust!</span>).';
$module_home            = 'http://www.lepton-cms.org';
$module_guid            = 'e5e36d7f-877a-4233-8dac-e1481c681c8d';


/*
 *	2.2.3	2011-12-21	- Update/rework secure-block in the module files.
 *
 *	2.2.2	2011-08-06	- Bugfix for { and } inside HTML/PHP-Code.
 *
 *	2.2.1	2011-06-30	- Update all files within the LEPTON secure header.
 *						- Remove TAN-Check inside save.php and TAN-code in modify.php
 *
 *	2.2.0	2011-06-29	- Remove FTAN check inside the save.php.
 *						- Add LEPTON secure header inside the save.php
 *						  This modul is from now only running with LEPTON.
 *
 *	2.1.11	2011-04-22	- Codeaddition inside add.php and delete.php to avoid an direct access
 *						- of theese files.
 *
 *	2.1.10	2011-01-23	- Recode some parts of save.php - rework some logical-structurs.
 *
 *	2.1.9	2011-01-17	- Build in FTAN code for secure-reasons inside modify.php and save.php.
 *						- Upgrade the modul-platform to 2.8.x and skip 2.7 support for the module.
 *
 *	2.1.7	2010-10-15	- Some bugfixes inside modify.php to display the correct value of the mode.
 *
 *	2.1.6	2010-05-03	- Minor typos-bugfix inside the html-template
 *
 *	2.1.5	2010-05-03	- Bugfix in the templatefile for the width of the textarea that causes
 *						  problems within e.g. argos-theme.
 *
 *	2.1.4	2010-04-12	- Add (md5) hash-id[-test] to the modify.php and save.php code.
 *
 *	2.1.3	2010-04-10	- Codechanges and optimazions inside modify.php.
 *
 *	2.1.3	2010-04-09	- Ad admin-permission test to save.php
 *						- Remove the HTTP-REFERER test from the save.php
 *
 *	2.1.2	2010-04-06	- Remove obsolete PHP-Code for correct loading the backend.js.
 *						- Remove directory "js" from the project.
 *
 *	2.1.1	2010-04-06	- Bugfix typos inside the module-description.
 *						- Bugfix inside upgrade.php, if there isn't a code2-section, the
 *						  script trys to add an existing field.
 *
 *	2.1.0 	2010-04-02	- Code-additions inside save.php for more security.
 *						- Minor cosmetic code-changes inside the info.php.
 *						- Removing wrong HTML-Tags and other replacements in the template.htt.
 *						- Removing backend.js to avoid javascript-conflicts ... the used javascript
 *						  is still loaded!
 *						- Massive recoding the upgrade.php script.
 *
 */

?>