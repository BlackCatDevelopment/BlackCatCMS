/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
  *
 */

jQuery(document).ready(function(){
	$('#fc_list_overview li').fc_set_tab_list();
	$('select[name=default_theme]').change( function()
	{
		$(this).closest('form').removeClass('ajaxForm').unbind();
	});
});