if( typeof jQuery != 'undefined' ) {
	jQuery('#black').show();
	jQuery('#be_preview_note').show();
	jQuery('#be_preview').show();
	jQuery(document).ready( function($) {
	
	    jQuery('input[type="text"]').each( function () {
			jQuery(this).click( function() { $(this).select(); } );
		});

        jQuery('a.dlg').click(function() {
            var $link = $(this);
    		var $dialog = $('<div></div>')
    			.load($link.attr('href'))
    			.dialog({
    				autoOpen: false,
    				title: $link.attr('title'),
    				width: 800,
    				height: 600,
                    buttons: {
                        "Close": function() {
                            $(this).dialog("close");
                        }
			}
    			});
                if($link.attr('href').substring($link.attr('href').lastIndexOf("."),$link.attr('href').length) == '.log') {
                    $('.ui-dialog-content').wrap('<pre>')
                }
    			$dialog.dialog('open');
    			return false;
        });

        $('body').keydown(function(e) {
            if (e.keyCode == 13) {
                $('#btn_next').trigger('click');
            }
        });
		
		jQuery('#installer_backend_theme').change( function() {
		    var old_img = jQuery('#preview_image').attr('src').replace(/\\/g,'/').replace( /.*\//, '' ).replace(/\.png/,'');
		    var new_img = jQuery('#installer_backend_theme').val();
			jQuery('#preview_image').attr('src',jQuery('#preview_image').attr('src').replace( old_img, 'tn_'+new_img ));
			var old_link = old_img.replace('tn_','');
			jQuery('#preview_link').attr('href',jQuery('#preview_image').attr('src').replace('tn_','') );
		});
		
        jQuery('#installer_default_wysiwyg').change( function() {
            if(jQuery('#installer_default_wysiwyg option:selected').val().indexOf('opt_') !== -1) {
                jQuery('#installer_default_wysiwyg_optional_info').fadeIn();
            }
            else {
                jQuery('#installer_default_wysiwyg_optional_info').fadeOut();
            }
        });
		
	    var animation = true;
	    if ( $.cookie('cat_animate') == 'no' ) {
	        animation = false;
		}
		if ( animation ) {
	        // fade in effect
	    	jQuery('div#black').fadeOut(3000).queue(
				function () {
					jQuery('div#content').css('z-index','99999').fadeIn(3000);
				}
			);
			$.cookie('cat_animate', 'no', { expires: 1, path: '/' });
		}
		else {
		    jQuery('div#black').hide();
		    jQuery('div#content').css('z-index','99999').show();
		}
	});
}
else {
	alert( 'jQuery not loaded!' );
}
