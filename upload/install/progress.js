if( typeof jQuery != 'undefined' ) {
    var lastStep = 0;
    function updateProgress() {
	    $.ajax({
  			url:     URL + "/progress.php",
  			data:    { step: lastStep },
  			success: function(data) { jQuery('#progress_msg').append(data); },
			error:   function() { alert( 'Unable to load the progress' ) ; },
	    });
	}
	jQuery(document).ready( function($) {
		if ( $('#progress').length ) {
		    setTimeout("updateProgress()", 100);
		}
	});
}

