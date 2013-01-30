<?php
 /**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Frank Heyne for the LEPTON Project 
 * @copyright       2011, Frank Heyne, LEPTON Project
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

if(!defined('CAT_PATH')) {
	header('Location: ../index.php');
	exit(0);
}

//require_once(CAT_PATH.'/framework/class.securecms.php');

function addTokens( &$html, &$sf) {
	if (!LEPTOKEN_LIFETIME) return;
	//$sf = new SecureCMS();
	$token = $sf->getToken();
	$token1 = "$1?leptoken=$token$3";  		// no parameters so far
	$token2 = "leptoken=$token";  			// for replacing placeholder in JS functions
	$token3 = "$1&amp;leptoken=$token$3";  	// with existing parameters, produces html-valid code
	$token4 = "$1?leptoken=$token$2";		// for special cases
	$hiddentoken = 'type="hidden" name="leptoken" value="'.$token.'"';	// for GET forms, add a hidden field too

	// finds absolute Links with Parameter:
	$qs = '^((href|action|window\.location)\s?=\s?[\'"]' . LEPTON_URL . '[\w\-\./]+\.php\?[\w\-\.=&%;/]+)([#[\w]*]?[\'"])^';  
	$html = preg_replace($qs, $token3, $html, -1); 
	
	// finds absolute Links without Parameter:
	$qs = '^((href|action|ajaxfilemanagerurl|window\.location)\s?=\s?[\'"]' . LEPTON_URL . '[\w\-\./]+\.php)([#[\w]*]?[\'"])^';  
	$html = preg_replace($qs, $token1, $html, -1);
	
	// finds relative Links with Parameter:
	$qs = '^((href|action|window\.location)\s?=\s?[\'"][\w/]+\.php\?[\w\-\.=%&;/]+)([#[\w]*]?[\'"])^';  
	$html = preg_replace($qs, $token3, $html, -1);

	// finds relative Links without Parameter:
	$qs = '^((href|action|window\.location)\s?=\s?[\'"][\w/]+\.php)([#[\w]*]?[\'"])^';  
	$html = preg_replace($qs, $token1, $html, -1);

	// finds Start page without Parameter:
	$qs = '^(href\s?=\s?[\'"]' . LEPTON_URL . ')([\'"])^';  
	$html = preg_replace($qs, $token4, $html, -1);

	// finds Testmail in Options:
	$qs = '^(send_testmail\(\''. ADMIN_URL. '/settings/ajax_testmail\.php)(\'\))^';
	$html = preg_replace($qs, $token4, $html, -1);

	// finds forms with method=get and adds a hidden field
	$qs = '^type="hidden" name="leptoken" value=""^';
	//$qs = '^(<form\s+action=[\'"][\w:\.\?/]+leptoken=\w\w\w[\'"]\s+method=[\'"]get[\'"]\s*>)^';
	$html = preg_replace($qs, $hiddentoken, $html, -1);
	
	// set leptoken in JS functions
	$qs = '^leptokh=#-!leptoken-!#^';
	$html = preg_replace($qs, $token2, $html, -1);
}

?>