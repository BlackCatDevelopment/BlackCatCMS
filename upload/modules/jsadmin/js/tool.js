/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the BSD License.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          jsadmin 
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, Ryan Djurovich,WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         BSD License
 * @license_terms   please see info.php of this module
 *
 *
 */ 

// Copyright 2006 Stepan Riha
// www.nonplus.net

JsAdmin.init_tool = function() {
	var instruction = YAHOO.util.Dom.get('jsadmin_install');
	if(instruction) {
		instruction.style.display = 'none';
	}
	var form = YAHOO.util.Dom.get('jsadmin_form');
	if(form) {
		form.style.display = '';
	}
};
