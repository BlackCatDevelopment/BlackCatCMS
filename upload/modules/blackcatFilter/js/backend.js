jQuery(document).ready( function() {

    $('button.filter_add').click(function() {
        $('div.filter_form_container').toggle('slide');
    });

    // Activate Ajax for Links having class 'ajaxLink'
    $('a.ajaxAction').click( function(e) {
        e.preventDefault();
        var ajaxUrl = $(this).prop('href');
        var dates   = getDatesFromQuerystring(ajaxUrl.split('?')[1]);
        var clicked = $(this);
        dates['_cat_ajax'] = 1;

    	$.ajax({
    		type:		'POST',
    		url:		ajaxUrl,
    		data:		dates,
    		beforeSend:	function( data )
    		{
    			// deactive .fc_popup before send data
    			$('.fc_popup').remove();
    			// Set activity and store in a variable to use it later
    			data.process	= set_activity( cattranslate('Saving') );
    			// check if a function beforeSend is defined and call it if true
    			if ( typeof beforeSend != 'undefined' && beforeSend !== false )
    			{
    				beforeSend.call(this);
    			}
    		},
    		success:		function( data, textStatus, jqXHR )
    		{
    			return_success( jqXHR.process, data.message );
    			// Check if there is a div.success_box in returned data that implements that the request was completely successful
    			if ( data.success === true )
    			{
                    var replace     = ( dates['action'] == 'activate' ) ? '/inactive' : '/active';
                    var replacement = ( dates['action'] == 'activate' ) ? '/active'   : '/inactive';
                    clicked.find('img').prop('src', clicked.find('img').prop('src').replace(replace,replacement));
                    replace     = ( dates['action'] == 'activate' ) ? 'action=activate'   : 'action=deactivate';
                    replacement = ( dates['action'] == 'activate' ) ? 'action=deactivate' : 'action=activate';
                    clicked.prop('href',clicked.prop('href').replace(replace,replacement));
                    if( dates['action'] == 'delete' ) {
                        window.location.replace(location.href);
                    }
    			}
    			else {
    				// return error
    				return_error( jqXHR.process , data.message );
    			}
    		}
    	});
    });

});