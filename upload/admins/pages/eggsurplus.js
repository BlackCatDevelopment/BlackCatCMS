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

function add_child_page(page_id) {
	//find and select the page in the parent dropdown
	var selectBox = document.add.parent;
	var max = selectBox.options.length;
	for (var i = 0; i < max; i++) {
		if (selectBox.options[i].value == page_id) {
			selectBox.selectedIndex = i;
			break;
		}
	}
	//set focus to add form
	document.add.title.focus();
}