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

/*-- Addition for remembering expanded state of pages --*/
function writeSessionCookie (cookieName, cookieValue) {
	document.cookie = escape(cookieName) + "=" + escape(cookieValue) + ";";
}

function toggle_viewers() {
	if(document.add.visibility.value == 'private') {
		document.getElementById('viewers').style.display = 'block';
	} else if(document.add.visibility.value == 'registered') {
		document.getElementById('viewers').style.display = 'block';
	} else {
		document.getElementById('viewers').style.display = 'none';
	}
}
function toggle_visibility(id){
	if(document.getElementById(id).style.display == "block") {
		document.getElementById(id).style.display = "none";
		writeSessionCookie (id, "0");//Addition for remembering expanded state of pages
	} else {
		document.getElementById(id).style.display = "block";
		writeSessionCookie (id, "1");//Addition for remembering expanded state of pages
	}
}
var plus = new Image;
plus.src = THEME_URL+"/images/plus_16.png";
var minus = new Image;
minus.src = THEME_URL+"/images/minus_16.png";
function toggle_plus_minus(id) {
	var img_src = document.images['plus_minus_' + id].src;
	if(img_src == plus.src) {
		document.images['plus_minus_' + id].src = minus.src;
	} else {
		document.images['plus_minus_' + id].src = plus.src;
	}
}