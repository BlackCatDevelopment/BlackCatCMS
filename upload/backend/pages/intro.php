<?php

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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$backend  = CAT_Backend::getInstance('Pages', 'pages_intro');
$tpl_data = array();

// get used languages (languages that have visible pages)
$tpl_data['pages'] = CAT_Helper_I18n::getUsedLangs();

// get current settings
$forward_by = CAT_Registry::get('INTRO_FORWARD_BY',NULL,'disabled');
$tpl_data['values'] = array(
    'intro_forward_by_lang'     => ( $forward_by == 'lang'     ? true : false ),
    'intro_forward_by_domain'   => ( $forward_by == 'domain'   ? true : false ),
    'intro_forward_by_disabled' => ( $forward_by == 'disabled' ? true : false ),
);

// domain mapping
$dom_map = CAT_Registry::get('INTRO_FORWARD_BY_DOMAIN_SETTINGS');
if(strlen($dom_map))
{
    $dom_map = unserialize($dom_map);
    // default to bottom
    $default = $dom_map['default'];
    unset($dom_map['default']);
    // sort by domain name
    ksort($dom_map);
    $tpl_data['domains'] = $dom_map;
    $tpl_data['default'] = $default;
}

$parser->output('backend_pages_intro', $tpl_data);

// Print admin footer
$backend->print_footer();

