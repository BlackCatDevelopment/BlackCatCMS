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
 *   @category        CAT_Modules
 *   @package         blackcat
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) {
		include($root.'framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

// protect
$backend = CAT_Backend::getInstance('Start','start',false,false);
if(!CAT_Users::is_authenticated()) exit; // just to be _really_ sure...
// there's no real need to protect this widget, just to handle all widgets...


$widget_settings = array(
    'allow_global_dashboard' => true,
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Forum News'),
    'preferred_column'       => 3
);

if(!function_exists('render_widget_blackcat_forennews'))
{
    function render_widget_blackcat_forennews()
    {
        require_once dirname(__FILE__).'/../functions.inc.php';

        $max         = 5;
        $pg          = CAT_Helper_Page::getInstance();
        $widget_name = $pg->lang()->translate('Forum News');
        $tpl_data    = array();

        $dom = new DOMDocument();
        $xml = _loadURL('https://forum.blackcat-cms.org/feed.php?f=2');

        if($xml) {
            $dom->loadXML($xml);
            $items = $dom->getElementsByTagName('entry');
            $cnt   = 0;
            foreach($items as $item)
            {
                if($item->childNodes->length)
                {
                    if(substr(str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),0,3) == 'Re:') continue;
                    $tpl_data[] = array(
                        'published' => $item->getElementsByTagName('published')->item(0)->textContent,
                        'link'      => $item->getElementsByTagName('link')->item(0)->getAttribute('href'),
                        'title'     => str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),
                    );
                    $cnt++;
                    if($cnt == $max) break;
                }
            }

            global $parser;
            $parser->setPath(dirname(__FILE__).'/../templates/default');
            $output = $parser->get('news.tpl',array('news'=>$tpl_data));
            $parser->resetPath();
        } else {
            $output = $pg->lang()->translate('Unable to retrieve news');
        }
        return $output;
    }
}