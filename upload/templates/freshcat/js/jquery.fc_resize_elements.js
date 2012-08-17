/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

// Function to resize sidebar, sidebar_footer (both only height) on left side and the content_container (height and width) on the right side, after while user is resizing the browser
(function ($) {
	$.fn.resize_elements = function (options)
	{
		var defaults =
		{
			sidebar:			$('#fc_sidebar'),
			sidebar_content:	$('#fc_sidebar_content'),
			main:				$('#fc_main_content'),
			leftside:			$('#fc_sidebar, #fc_content_container'),
			rightside:			$('#fc_content_container, #fc_content_footer'),
			overview_list:		$('#fc_list_overview')
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			var window		= $(this);
			// Bind reizing window with function resize_contents
			window.resize( function ()
			{
				// Get current height and width of browser
				var window_height	= parseInt( window.height() ),
					window_width	= parseInt( window.width() ),
					sidebar_width	= parseInt( options.sidebar.width() );
				// set some values
				options.leftside.css(
				{
					height:		window_height - 80 + 'px'
				});
				options.sidebar_content.css(
				{
					height:		window_height - 102 + 'px'
				});
				options.overview_list.css(
				{
					maxHeight:	window_height - 204 + 'px'
				});
				options.main.css(
				{
					height:		window_height - 124 + 'px'
				});
				// set some values
				options.rightside.css(
				{
					width:	( window_width - sidebar_width ) + 'px'
				});
			}).resize();
		});
	}
})(jQuery);