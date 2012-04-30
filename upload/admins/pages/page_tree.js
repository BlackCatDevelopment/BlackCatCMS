/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
function find_and_open( page_id ) {
  if ( typeof jQuery != 'undefined' ) {
    jQuery('.pages_list').find('td.list_page_id').each( function() {
      if( jQuery.trim(jQuery(this).text()) == page_id ) {
        var elem = jQuery(this).parentsUntil('ul').parent();
        elem.show();
        elem.find('tr').css('background-color','#ddd');
        jQuery('html,body').animate({scrollTop:elem.offset().top},'slow');
      }
    });

  }
}
function search_form_toggle() {
  var div = document.getElementById('search_page_form');
  if ( typeof div != 'undefined' ) {
    if ( div.style != 'null' && div.style.display == 'none' ) {
      div.style.display = 'block';
      document.getElementById('search_form_toggle_img').src = THEME_URL+'/images/up_16.png';
    }
    else {
      div.style.display = 'none';
      document.getElementById('search_form_toggle_img').src = THEME_URL+'/images/down_16.png';
    }
  }
}