/**
 *	@module			wysiwyg Admin
 *	@version		see info.php of this module
 *	@authors		Dietrich Roland Pehlke
 *	@copyright		2010-2011 Dietrich Roland Pehlke
 *	@license		GNU General Public License
 *	@license terms	see info.php of this module
 *	@platform		see info.php of this module
 *	@requirements	PHP 5.2.x and higher
 */

function testform(aRef) {
	var ref = aRef.elements['width'];
	if (ref.value == "") {
		alert ("Please give a valid value for the width!");
		ref.focus();
		return false;
	}
	ref = aRef.elements['height'];
	if (ref.value=="") {
		alert ("Please give a valid value for the height!");
		ref.focus();
		return false;
	}
	return true;
}

function selecteditor(aRef) {
	var val=aRef.options[ aRef.selectedIndex ];
	if (val) {
		var ref = document.getElementById('ckeditor_admin');
		if (ref) {
			ref.elements['job'].value = 'switch_editor';
			ref.submit();
		}
	}
	return false;
}