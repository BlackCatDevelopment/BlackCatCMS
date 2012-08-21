var url = 'http://localhost/_projects/Lepton2/modules/lib_jquery/plugins/leptranslate/leptranslate.php';

if ( typeof jQuery == 'undefined' ) {
  alert( 'FATAL ERROR! jQuery not available!' );
}
else {

  // ----- AJAX Setup -----
  jQuery.ajaxSetup({
    error: function( x, e ){
      if( x.status == 0 )           { alert('You are offline!!\n Please Check Your Network.'); }
      else if( x.status == 404 )    { alert('Requested URL not found.');                       }
      else if( x.status == 500 )    { alert('Internal Server Error.');                         }
      else if( e == 'parsererror' ) { alert('Parse error');                                    }
      else if( e == 'timeout' )     { alert('Request Time out.');                              }
      else                          { alert('Unknown Error.\n'+x.responseText);                }
    }
  });

	function leptranslate( string, elem, attributes ) {
      jQuery.post(
		url,
		{
	      msg:  string,
	      attr: attributes
	    },
		function( data ) {
          if ( typeof elem != 'undefined' && typeof elem != '' ) {
		      jQuery(elem).append(jQuery(data).text());
          }
          else {
              return jQuery(data).text();
          }
		},
		"text"
	  );
	}
}