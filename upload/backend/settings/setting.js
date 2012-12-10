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
 
function change_wbmailer(type) {
	if(type == 'smtp') {
		document.getElementById('row_wbmailer_smtp_settings').style.display = '';
		document.getElementById('row_wbmailer_smtp_host').style.display = '';
		document.getElementById('row_wbmailer_smtp_auth_mode').style.display = '';
		document.getElementById('row_wbmailer_smtp_username').style.display = '';
		document.getElementById('row_wbmailer_smtp_password').style.display = '';
		if( document.settings.wbmailer_smtp_auth.checked == true ) {
			document.getElementById('row_wbmailer_smtp_username').style.display = '';
			document.getElementById('row_wbmailer_smtp_password').style.display = '';
		} else {
			document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
			document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
		}
	} else if(type == 'phpmail') {
		document.getElementById('row_wbmailer_smtp_settings').style.display = 'none';
		document.getElementById('row_wbmailer_smtp_host').style.display = 'none';
		document.getElementById('row_wbmailer_smtp_auth_mode').style.display = 'none';
		document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
		document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
	}
}

function toggle_wbmailer_auth() {
	if( document.settings.wbmailer_smtp_auth.checked == true ) {
		document.getElementById('row_wbmailer_smtp_username').style.display = '';
		document.getElementById('row_wbmailer_smtp_password').style.display = '';
	} else {
        document.settings.wbmailer_smtp_auth.value = 'false';
		document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
		document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
	}
}

function send_testmail(URL) {
    var xmlHttp = null;
    try {
        // Firefox, Internet Explorer 7. Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer 6.
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }

    xmlHttp.open("POST", URL, true);
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", 0);
    xmlHttp.setRequestHeader("Connection", "close");

    xmlHttp.onreadystatechange=function() {
        if(xmlHttp.readyState==4) {
            try {
                // Get the data from the server's response
                if ( xmlHttp.responseText != "" ) {
                    document.getElementById("ajax_response").innerHTML=xmlHttp.responseText;
                    document.getElementById("ajax_response").style.display='block';
                }
            }
            catch (e) {
                alert("JavaScript error! Maybe your browser does not support AJAX!");
                return false;
            }
            xmlHttp=null;
        }
    }
    xmlHttp.send();
}