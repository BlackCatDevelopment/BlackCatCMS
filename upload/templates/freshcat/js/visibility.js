/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
 *
 */

jQuery(document).ready(function($) {
    $('body').append('<div id="cat_preview_visibility" title="'+visibility_title+'" style="display:none">');
    $.getScript(CAT_URL+'/modules/lib_jquery/jquery-ui/ui/jquery-ui.min.js')
     .done(function(script, textStatus) {
         $(function() {
             $( "#cat_preview_visibility" ).dialog({
                 width: 500
                 ,position: { my: "left bottom", at: "left bottom", of: window }
             });
             $( "#cat_preview_visibility" ).html(visibility_text+': '+visibility);
             $( "#cat_preview_visibility" ).dialog('open');
         });
     });

});
