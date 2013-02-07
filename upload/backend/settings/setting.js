/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 *
 */
 
function send_testmail(URL) {
    if ( typeof jQuery != 'undefined' ) {
        jQuery.ajax({
            type: 'POST',
            url:  URL,
            success:	function( data, textStatus, jqXHR  ) {
                jQuery('#testmail_result').html(data).show();
            }
        });
    }
}
 
function change_wbmailer(type) {
	if(type == 'smtp') {
		document.getElementById('row_catmailer_smtp_settings').style.display = '';
		document.getElementById('row_catmailer_smtp_host').style.display = '';
		document.getElementById('row_catmailer_smtp_auth_mode').style.display = '';
		document.getElementById('row_catmailer_smtp_username').style.display = '';
		document.getElementById('row_catmailer_smtp_password').style.display = '';
		if( document.settings.catmailer_smtp_auth.checked == true ) {
			document.getElementById('row_catmailer_smtp_username').style.display = '';
			document.getElementById('row_catmailer_smtp_password').style.display = '';
		} else {
			document.getElementById('row_catmailer_smtp_username').style.display = 'none';
			document.getElementById('row_catmailer_smtp_password').style.display = 'none';
		}
	} else if(type == 'phpmail') {
		document.getElementById('row_catmailer_smtp_settings').style.display = 'none';
		document.getElementById('row_catmailer_smtp_host').style.display = 'none';
		document.getElementById('row_catmailer_smtp_auth_mode').style.display = 'none';
		document.getElementById('row_catmailer_smtp_username').style.display = 'none';
		document.getElementById('row_catmailer_smtp_password').style.display = 'none';
	}
}

function toggle_catmailer_auth() {
	if( document.settings.catmailer_smtp_auth.checked == true ) {
		document.getElementById('row_catmailer_smtp_username').style.display = '';
		document.getElementById('row_catmailer_smtp_password').style.display = '';
	} else {
        document.settings.catmailer_smtp_auth.value = 'false';
		document.getElementById('row_catmailer_smtp_username').style.display = 'none';
		document.getElementById('row_catmailer_smtp_password').style.display = 'none';
	}
}