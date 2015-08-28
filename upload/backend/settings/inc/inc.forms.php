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
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
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

$FORMS = array(
    'seo' => array(
        array(
            'type'        => 'text',
            'name'        => 'website_title',
            'label'       => 'Website title',
            'title'       => 'Used for the title tag in the HTML header.',
        ),
        array(
            'type'        => 'checkbox',
            'name'        => 'use_short_urls',
            'label'       => 'Use short URLs (Apache webserver only, requires mod_rewrite!)',
            'title'       => 'This will allow to use SEO friendly URLs like http://www.yourdomain.com/path/to/page instead of http://www.yourdomain.com/page/path/to/page.php',
        ),
        array(
            'type'        => 'textarea',
            'name'        => 'website_description',
            'label'       => 'Website description',
            'title'       => 'Used for the description META attribute. The description should be a nice &quot;human readable&quot; text with 70 up to 156 characters.',
            'class'       => 'fc_input_300',
        ),
        array(
            'type'        => 'textarea',
            'name'        => 'website_keywords',
            'label'       => 'Website keywords',
            'title'       => 'Used for the keywords META attribute. Most search engines do not use this anymore.',
        ),

        array(
            'type'        => 'legend',
            'label'       => 'Sitemap settings',
        ),
        array(
            'type'        => 'select',
            'label'       => 'Default update frequency',
            'name'        => 'sitemap_update_freq',
            'options'     => array(
                'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
            ),
            'selected'    => 'weekly',
            'class'       => 'fbleave',
        ),
        array(
            'type'        => 'checkbox',
            'name'        => 'update_sitemap',
            'label'       => 'Update sitemap.xml on save',
            'title'       => 'If checked, the sitemap.xml will be re-generated after save.',
        ),
    ),
);