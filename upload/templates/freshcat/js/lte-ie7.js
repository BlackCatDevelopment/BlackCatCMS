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

/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'blackcat\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-pencil' : '&#xe001;',
			'icon-brush' : '&#xe002;',
			'icon-color-palette' : '&#xe003;',
			'icon-pictures' : '&#xe004;',
			'icon-library' : '&#xe000;',
			'icon-copy' : '&#xe005;',
			'icon-file' : '&#xe006;',
			'icon-file-add' : '&#xe007;',
			'icon-file-remove' : '&#xe008;',
			'icon-tag' : '&#xe009;',
			'icon-mail' : '&#xe00a;',
			'icon-alarm' : '&#xe00b;',
			'icon-calendar' : '&#xe00c;',
			'icon-screen' : '&#xe00d;',
			'icon-screen-2' : '&#xe00e;',
			'icon-database' : '&#xe00f;',
			'icon-comments' : '&#xe010;',
			'icon-user' : '&#xe011;',
			'icon-user-add' : '&#xe012;',
			'icon-user-remove' : '&#xe013;',
			'icon-users' : '&#xe014;',
			'icon-users-2' : '&#xe015;',
			'icon-binocular' : '&#xe016;',
			'icon-search' : '&#xe017;',
			'icon-key' : '&#xe018;',
			'icon-cogs' : '&#xe019;',
			'icon-cog' : '&#xe01a;',
			'icon-equalizer' : '&#xe01b;',
			'icon-unlocked' : '&#xe01c;',
			'icon-locked' : '&#xe01d;',
			'icon-wrench' : '&#xe01e;',
			'icon-tools' : '&#xe01f;',
			'icon-bug' : '&#xe020;',
			'icon-meter' : '&#xe021;',
			'icon-remove' : '&#xe022;',
			'icon-puzzle' : '&#xe023;',
			'icon-shield' : '&#xe024;',
			'icon-switch' : '&#xe025;',
			'icon-power-cord' : '&#xe026;',
			'icon-download' : '&#xe027;',
			'icon-upload' : '&#xe028;',
			'icon-globe' : '&#xe029;',
			'icon-eye' : '&#xe02a;',
			'icon-eye-blocked' : '&#xe02b;',
			'icon-eye-2' : '&#xe02c;',
			'icon-eye-blocked-2' : '&#xe02d;',
			'icon-star' : '&#xe02e;',
			'icon-star-2' : '&#xe02f;',
			'icon-star-3' : '&#xe030;',
			'icon-notification' : '&#xe031;',
			'icon-cancel' : '&#xe032;',
			'icon-cancel-2' : '&#xe033;',
			'icon-warning' : '&#xe034;',
			'icon-info' : '&#xe035;',
			'icon-help' : '&#xe036;',
			'icon-checkmark' : '&#xe037;',
			'icon-minus' : '&#xe038;',
			'icon-plus' : '&#xe039;',
			'icon-resize' : '&#xe03a;',
			'icon-arrow-up' : '&#xe03b;',
			'icon-arrow-right' : '&#xe03c;',
			'icon-arrow-down' : '&#xe03d;',
			'icon-arrow-left' : '&#xe03e;',
			'icon-share' : '&#xe03f;',
			'icon-facebook' : '&#xe040;',
			'icon-github' : '&#xe041;',
			'icon-libreoffice' : '&#xe042;',
			'icon-file-pdf' : '&#xe043;',
			'icon-file-openoffice' : '&#xe044;',
			'icon-file-word' : '&#xe045;',
			'icon-file-excel' : '&#xe046;',
			'icon-file-powerpoint' : '&#xe047;',
			'icon-file-zip' : '&#xe048;',
			'icon-file-xml' : '&#xe049;',
			'icon-file-css' : '&#xe04a;',
			'icon-home' : '&#xe04b;',
			'icon-folder-add' : '&#xe04c;',
			'icon-folder' : '&#xe04d;',
			'icon-screen-3' : '&#xe04e;',
			'icon-menu' : '&#xe04f;',
			'icon-logo_bc' : '&#xe050;',
			'icon-creativecat' : '&#xe051;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};