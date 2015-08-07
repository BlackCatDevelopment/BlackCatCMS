/*
       2014, Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         freshcat

*/
jQuery(document).ready(function(){var b="fc_pref_display_name fc_language fc_timezone_string fc_date_format fc_time_format fc_email".split(" ");for(i=0;i<=b.length;i++)$("#"+b[i]).change(function(){$("#fc_modifyUser_currentpw").show("slide")});$("#fc_change_pw").click(function(a){a.preventDefault();$("#fc_modifyUser_setnewpw").show("slide");$("#fc_modifyUser_currentpw").show("slide");$(this).hide()});$(".fc_modifyUser_reset").click(function(){$("#fc_modifyUser_setnewpw").hide("slide");$("#fc_modifyUser_currentpw").hide("slide");
$("#fc_change_pw").show()});$(".fc_preferences_submit").click(function(a){if($("#fc_current_password").val())$(".fc_modifyUser_reset").click();else if(a.preventDefault(),0===$(".popup").size()&&$('<div class="popup" />').appendTo("body"),$(".popup").html('<div class="c_16">'+cattranslate("Confirm with current password")+"</div>"),a=cattranslate("Back"),!$("#fc_current_password").val())return $(".popup").dialog({title:cattranslate("Confirm with current password"),modal:!0,buttons:[{text:a,click:function(){$(this).dialog("close");
$("#fc_modifyUser_currentpw").show("slide");$(".popup").remove()}}]}),!1})});
