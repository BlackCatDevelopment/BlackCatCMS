/**
 *   @author          LEPTON v2.0 Black Cat Edition Development
 *   @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 *   @link            http://www.lepton2.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        LEPTON2BCE_Modules
 *   @package         lib_jquery
 *
 */

var url = LEPTON_URL + '/modules/lib_jquery/plugins/leptranslate/leptranslate.php';

if ( typeof jQuery == 'undefined' ) {
  alert( 'FATAL ERROR! jQuery not available!' );
}
else {  // ----- AJAX Setup -----
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
	function leptranslate( string, elem, attributes, module ) {
      jQuery.post(
		url,
		{
	      msg:  string,
	      attr: attributes,
          mod: module
	    },
		function( data ) {
          if ( typeof elem != 'undefined' && typeof elem != '' ) {
		      jQuery(elem).text(jQuery(data).text());
          }
          else {
              return jQuery(data).text();
          }
		},
		"text"
	  );
	}
}