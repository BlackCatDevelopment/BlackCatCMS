/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
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
			rightcontent:		$('#fc_content_container'),
			overview_list:		$('#fc_list_overview'),
			side_add:			$('#fc_add_page'),
			media:				$('#fc_media_browser'),
			bottomright:		130,
			bottomleft:			79
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			var window		= $(this);
			// Bind reizing window with function resize_contents
			window.resize( function ()
			{
				// Get current height and width of browser
				var window_height	= parseInt( window.height(), 10 ),
					window_width	= parseInt( window.width(), 10 ),
					sidebar_width	= parseInt( options.sidebar.width(), 10 );
				// set some values
				options.main_content.css(
				{
					maxHeight:	window_height - options.bottomright + 'px'
				});
				options.media.css(
				{
					maxHeight:	window_height - options.bottomright + 'px'
				});
				options.leftside.height(window_height - options.bottomright + 30);
				options.sidebar.height(window_height - options.bottomright + 48);
				options.rightcontent.height(window_height - options.bottomleft );

				options.rightside.width(window_width - sidebar_width);
				options.sidebar_content.height(window_height - options.bottomright + 26);

				var add_size	= $('#fc_list_add').size() > 0 ? 58 : 30;

				options.overview_list.css(
				{
					maxHeight:	window_height - options.bottomright - add_size + 'px'
				});
				options.side_add.css(
				{
					left:	sidebar_width + 'px',
					height:	window_height - options.bottomright + 51 + 'px'
				});

			}).resize();
		});
	};
})(jQuery);
