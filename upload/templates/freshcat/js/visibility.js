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
