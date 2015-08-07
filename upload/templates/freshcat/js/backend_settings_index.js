/*
       2014, Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         freshcat

*/
function send_testmail(a){"undefined"!=typeof jQuery&&jQuery.ajax({type:"POST",url:a,data:{_cat_ajax:1},beforeSend:function(a){jQuery("#testmail_result").html("<div style='border: 2px solid #cc6600; padding: 5px; text-align: center; background-color: #ffcc66;'>"+cattranslate("Trying to send testmail, please wait...")+"</div>").show();return!0},success:function(a,b,c){jQuery("#testmail_result").html(a).show()}})}
function create_guid(a){"undefined"!=typeof jQuery&&jQuery.ajax({type:"GET",url:a,success:function(a,b,c){jQuery("#guid").html(a);$("#fc_createguid").hide()}})}
jQuery(document).ready(function(a){a("#fc_list_overview li").fc_set_tab_list();var d=SESSION+"_settings_open";a("#fc_list_overview li").click(function(){var b=a(this),c={_cat_ajax:1,template:b.find("input").val()};a.cookie(d,b.find("input").val(),{path:"/"});a.ajax({type:"POST",url:CAT_ADMIN_URL+"/settings/ajax_get_settings.php",dataType:"json",data:c,cache:!1,success:function(e,c,d){!0===e.success?(a("div#fc_set_form_content").html(e.settings),a("input#current_page").val(b.find("input").val()),void 0!==
typeof window.qtip&&a('[title!=""]').qtip({content:{attr:"title"},style:{classes:"qtip-light qtip-shadow qtip-rounded"}})):return_error(d.process,e.message)}})});"undefined"!=typeof a.cookie(d)&&a.cookie(d).length&&a("#fc_list_overview li").find('input[value="'+a.cookie(d)+'"]').click();a("#fc_createguid").click(function(){create_guid(CAT_ADMIN_URL+"/settings/ajax_guid.php")});a("#fc_use_short_urls").unbind("click").click(function(){a(this).is(":checked")&&a.ajax({type:"GET",url:CAT_ADMIN_URL+"/settings/ajax_check_htaccess.php",
success:function(a,c,e){!1===a.success&&return_error(e.process,a.message)}})});a("form#settings").submit(function(b){var c=a(this);a.ajax({type:"POST",url:c.prop("action"),data:c.serialize(),dataType:"json",beforeSend:function(a){a.process=set_activity("Saving settings")},success:function(a,c,b){!0===a.success?(return_success(b.process,a.message),"undefined"!==typeof current&&current.slideUp(300,function(){current.remove()})):return_error(b.process,a.message)}});b.preventDefault()})});
