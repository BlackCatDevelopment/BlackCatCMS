$(document).ready(function()
{
        if($(".jcalendar").length) {
            $.insert(WB_URL+"/include/jscalendar/calendar-system.css");
          }

        if($(".jsadmin").length) {
            $.insert(WB_URL+"/modules/jsadmin/backend.css");
          }

 		if($(".round").length) {
			$.insert(THEME_URL+"/jquery/jquery-corner.js");
			$(".rounded").corner('round 12px');
        }

	//Add external link class to external links -
	$('a[href^="http://"]').filter(function() {
		//Compare the anchor tag's host name with location's host name
	    return this.hostname && this.hostname !== location.hostname;
	  }).addClass("external").attr("target", "_blank");

	/* Add internal link class to external links -   */
	$('a[href^="http://"]').filter(function() {
		//Compare the anchor tag's host name with location's host name
	    return this.hostname && this.hostname == location.hostname;
	  }).addClass("internal");

	$('form').attr('autocomplete', 'off');

});


/*With jQuery Cookie $(document).ready(function()  */
$(document).ready(function()
{

	if($("div.tab").length)
	{
		$.insert(THEME_URL+"/jquery/tabber-plugin.js");
		$('#loaderdiv img').removeClass('hide');
		$('div.tabber').addClass('tabs_hide');
		$.insert(THEME_URL+"/jquery/tabber-minimized.js");
		$('div.tab').addClass('tabber');
		$('div.dialog').addClass('hide');
		$('div.tab.tabber').removeClass('tabs_hide');
		$('button').addClass('hide');

	}

/* ShowTabber */

	if($("div.show-tabber").length)
	{
	      var ShowTabber = $.cookie('ShowTabber');
	      if (ShowTabber == true)
		  {
	        $('#loaderdiv, #dialog').removeClass('hide');
			$('button').addClass('hide');
			$('h4, div').removeClass('noscript');
			$('ul.tabbernav').removeClass('tabs_hide');
			$('input').removeClass('input_hide');
	      } else {
			$('div.tab').removeClass('tabber');
			$('div.tab').removeClass('tabberlive');
			$('h2').removeClass('tabs_hide');
			$('ul.tabbernav').addClass('tabs_hide');
	      }

		(function($)
		{
			$("#hidden_tab_button").click(function() {
              if (ShowTabber == false ) {
				$('#loaderdiv img').removeClass('hide');
	            $.cookie('ShowTabber', '1');
				location.reload(); ;
		      } else {
				$('#loaderdiv img').removeClass('hide');
	            $.cookie('ShowTabber', '0');
				location.reload(); ;
		      	}

			});
		})(jQuery);


	}


});