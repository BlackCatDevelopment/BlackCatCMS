<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file prevents directory listing.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2006-2009
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		0.70
 * @platform	Website Baker 2.8
*/

// prevent directory listing
header('Location: ../../../../index.php');

?>