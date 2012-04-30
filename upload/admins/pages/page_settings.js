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

function toggle_viewers() {
	if(document.settings.visibility.value == 'private' || document.settings.visibility.value == 'registered') {
		document.getElementById('allowed_viewers').style.display = 'block';
	} else {
		document.getElementById('allowed_viewers').style.display = 'none';
	}
}
var lastselectedindex = new Array();

function disabled_hack_for_ie(sel) {
	var sels = document.getElementsByTagName("select");
	var i;
	var sel_num_in_doc = 0;
	for (i = 0; i <sels.length; i++) {
		if (sel == sels[i]) {
			sel_num_in_doc = i;
		}
	}
	// never true for browsers that support option.disabled
	if (sel.options[sel.selectedIndex].disabled) {
		sel.selectedIndex = lastselectedindex[sel_num_in_doc];
	} else {
		lastselectedindex[sel_num_in_doc] = sel.selectedIndex;
	}
	return true;
}