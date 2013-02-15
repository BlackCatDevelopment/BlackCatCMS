if( typeof jQuery != 'undefined' ) {
	jQuery('#black').show();
	jQuery('#be_preview_note').show();
	jQuery('#be_preview').show();
	jQuery(document).ready( function($) {
	
	    jQuery('input[type="text"]').each( function () {
			jQuery(this).click( function() { $(this).select(); } );
		});
		jQuery("a.fancybox").fancybox(
			{
			    width: '80%',
			    height: '80%',
			    overlayColor: '#000',
			    speedIn: 1000
			}
		);
		
		jQuery('#installer_backend_theme').change( function() {
		    var old_img = jQuery('#preview_image').attr('src').replace(/\\/g,'/').replace( /.*\//, '' ).replace(/\.png/,'');
		    var new_img = jQuery('#installer_backend_theme').val();
			jQuery('#preview_image').attr('src',jQuery('#preview_image').attr('src').replace( old_img, 'tn_'+new_img ));
			var old_link = old_img.replace('tn_','');
			jQuery('#preview_link').attr('href',jQuery('#preview_image').attr('src').replace('tn_','') );
		});
		
	    var animation = true;
	    if ( $.cookie('lep_animate') == 'no' ) {
	        animation = false;
		}
		if ( animation ) {
	        // fade in effect
	    	jQuery('div#black').fadeOut(3000).queue(
				function () {
					jQuery('div#content').css('z-index','99999').fadeIn(3000);
				}
			);
			$.cookie('lep_animate', 'no', { expires: 1, path: '/' });
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
