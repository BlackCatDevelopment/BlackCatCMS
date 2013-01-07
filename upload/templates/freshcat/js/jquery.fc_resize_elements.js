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
 */

// Function to resize sidebar, sidebar_footer (both only height) on left side and the content_container (height and width) on the right side, after while user is resizing the browser
(function ($) {
	$.fn.resize_elements = function (options)
	{
		var defaults =
		{
			sidebar:			$('#fc_sidebar'),
			sidebar_content:	$('#fc_sidebar_content'),
			main_content:		$('#fc_main_content'),
			leftside:			$('#fc_sidebar, #fc_content_container'),
			rightside:			$('#fc_content_container, #fc_content_footer'),
			overview_list:		$('#fc_list_overview'),
			side_add:			$('#fc_add_page'),
			media:				$('#fc_media_browser')
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
				options.main_content.css(
				{
					maxHeight:	window_height - 131 + 'px'
				});
				options.media.css(
				{
					maxHeight:	window_height - 131 + 'px'
				});
				options.leftside.height(window_height - 80);
				options.rightside.width(window_width - sidebar_width);
				options.sidebar_content.height(window_height - 102);

				options.overview_list.css(
				{
					maxHeight:	window_height - 187 + 'px'
				});
				options.side_add.css(
				{
					left:	sidebar_width + 'px',
					height:	window_height - 80 + 'px'
				});

			}).resize();
		});
	}
})(jQuery);
