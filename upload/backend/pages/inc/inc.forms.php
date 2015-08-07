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
            'type'        => 'legend',
            'label'       => 'Basic settings',
        ),
        array(
            'type'        => 'text',
            'label'       => 'Canonical URL',
            'name'        => 'canonical',
            'class'       => 'fc_input_300',
            'title'       => 'A canonical link element is an HTML element that helps to prevent duplicate content issues by specifying the &quot;canonical&quot; or &quot;preferred&quot; version of a web page.',
        ),
        array(
            'type'        => 'text',
            'label'       => '301 Redirect',
            'name'        => 'redirect',
            'class'       => 'fc_input_300',
        ),
        array(
            'type'        => 'submit',
            'name'        => 'submit1',
            'label'       => 'Save',
        ),

        array(
            'type'        => 'legend',
            'label'       => 'Sitemap settings',
        ),
        array(
            'type'        => 'select',
            'label'       => 'Include in Sitemap',
            'name'        => 'sitemap_include',
            'options'     => array('auto'=>'Automatic detection','always'=>'Always include','never'=>'Never include'),
            'selected'    => 'auto',
            'class'       => 'fbleave', // verhindert, daß der Selectbox das select2 jQuery Plugin hinzugefügt wird
        ),
        array(
            'type'        => 'select',
            'label'       => 'Sitemap priority',
            'name'        => 'sitemap_priority',
            'options'     => array(1=>'1 - Highest priority','0.9'=>'0.9','0.8'=>'0.8','0.7'=>'0.7','0.6'=>'0.6','0.5'=>'0.5 - Default priority','0.4'=>'0.4','0.3'=>'0.3','0.2'=>'0.2','0.1'=>'0.1 - Lowest priority'),
            'selected'    => '0.5',
            'class'       => 'fbleave fc_input_300',
            'title'       => 'The priority of this URL relative to other URLs on your site. This value does not affect how your pages are compared to pages on other sites—it only lets the search engines know which pages you deem most important for the crawlers.',
        ),
        array(
            'type'        => 'select',
            'label'       => 'Update frequency',
            'name'        => 'sitemap_update_freq',
            'options'     => array(
                'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
            ),
            'selected'    => 'weekly',
            'class'       => 'fbleave',
        ),
        array(
            'type'        => 'submit',
            'name'        => 'submit2',
            'label'       => 'Save',
        ),

        array(
            'type'        => 'legend',
            'label'       => 'Robots settings',
        ),
        array(
            'type'        => 'checkboxgroup',
            'name'        => 'robots[]',
            'label'       => 'META Robots',
            'title'       => 'Allows to set the META attributes "noindex" and "nofollow"',
            'options'     => array(
                array('value'=>'noindex','label'=>'no index','title'=>'set to "on" to set "noindex" attribute'),
                array('value'=>'nofollow','label'=>'no follow','title'=>'set to "on" to set "nofollow" attribute'),
                array('value'=>'noodp','label'=>'NO ODP','title'=>'Sometimes, if you are listed in DMOZ (ODP), the search engines will display snippets of text about your site taken from them instead of your description meta tag. You can force the search engine to ignore the ODP information by setting this to on.'),
                array('value'=>'noydir','label'=>'NO YDIR','title'=>'Same als ODP but information is taken from Yahoo! directory.'),
                array('value'=>'noarchive','label'=>'No Archive','title'=>'Prevents the search engines from showing a cached copy of this page.'),
                array('value'=>'nocache','label'=>'No Cache','title'=>'Same as noarchive, but only used by MSN/Live.'),
                array('value'=>'nosnippet','label'=>'No Snippet','title'=>'Prevents the search engines from showing a snippet of this page in the search results and prevents them from caching the page.'),
                array('value'=>'notranslate','label'=>'No translate','title'=>'No translation of this page in search results'),
                array('value'=>'noimageindex','label'=>'No image index','title'=>'Do not add images to index'),

            ),
            'radio_class' => 'fc_checkbox_jq',
        ),
        array(
            'type'        => 'submit',
            'name'        => 'submit3',
            'label'       => 'Save',
        ),
    )
);