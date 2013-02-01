/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2012, LEPTON Project
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *
 */

function search_box_onfocus(input, search_string) {
  if (input.value == search_string){
    input.value='';
    input.className='search_box_input_active';
  } 
  else {
    input.select();
  }
}

function search_box_onblur(input, search_string) {
  if (input.value==''){ 
    input.value = search_string;
    input.className = 'search_box_input';
  }
}