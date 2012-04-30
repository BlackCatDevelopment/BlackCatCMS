/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the legacy syntax highlighting support for Website Baker versionb below 2.8.
 * To get syntax highlighting working with Addon File Editor v0.80 and WB < 2.8, copy the folder
 * /include/editarea from a WB 2.8 installation package to the /include folder of your installation.
 * The rename this file from backend_legacy.js into backend.js and you are done.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.0.0
 * @platform	Website Baker 2.8
*/

// include the editarea framework
document.write('<script type="text/javascript" src="../../include/editarea/edit_area_full.js"></script>');