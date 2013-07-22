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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         mojito
 *
 */


$(document).ready(function()
{
	$('li.menu-expand').not('.menu-parent').children('ul').addClass('hidden').slideUp(0);
	$('li.menu-expand').mouseover( function()
	{
		if ( typeof navigationTimer != 'undefined' )
		{
			clearInterval(navigationTimer);
		}
		var current		= $(this);
		current.addClass('active_menu');
		if ( current.not('.menu-parent').children('ul').hasClass('hidden') )
		{
			current.not('.menu-parent').children('ul').removeClass('hidden').slideUp(0);
		}
		current.not('.menu-parent').children('ul').css({height: 'auto'}).stop().slideDown(400);
	}).mouseleave( function()
	{
		var current		= $(this);
		current.removeClass('active_menu');
		navigationTimer		= setTimeout( function ()
		{
			if ( $('.active_menu').size() == 0 )
			{
				$('li.menu-expand').not('.menu-parent').children('ul').css({height: 'auto'}).stop().slideUp(1000);
			}
		}, 1500);
	});
});