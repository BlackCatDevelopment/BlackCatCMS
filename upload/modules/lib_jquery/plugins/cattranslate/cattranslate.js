/**
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_jquery
 *
 */

var url = CAT_URL + '/modules/lib_jquery/plugins/cattranslate/cattranslate.php';
var translated;

if ( typeof jQuery == 'undefined' ) {
  alert( 'FATAL ERROR! jQuery not available!' );
}
else
{  // ----- AJAX Setup -----
  jQuery.ajaxSetup({
        error: function( x, e )
        {
      if( x.status == 0 )           { alert('You are offline!!\n Please Check Your Network.'); }
      else if( x.status == 404 )    { alert('Requested URL not found.');                       }
      else if( x.status == 500 )    { alert('Internal Server Error.');                         }
      else if( e == 'parsererror' ) { alert('Parse error');                                    }
      else if( e == 'timeout' )     { alert('Request Time out.');                              }
      else                          { alert('Unknown Error.\n'+x.responseText);                }
    }
  });
	function cattranslate( string, elem, attributes, module ) {
        translated = '';
        $.ajax({
					type:		'post',
					url:		url,
					data:		{
	      msg:  string,
	      attr: attributes,
          mod: module
	    },
					cache:		false,
                    async:      false,
                    success:    function( data ) {
                                    if ( typeof elem != 'undefined' && typeof elem != '' )
                                    {
		      jQuery(elem).text(jQuery(data).text());
          }
                                    else
                                    {
                                        translated = jQuery(data).text();
                                    }
          }
	    });
        if(translated=='') translated = string;
        return translated;
	}
}