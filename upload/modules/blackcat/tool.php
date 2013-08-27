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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
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

include dirname(__FILE__).'/data/config.inc.php';
$info = NULL;

if(CAT_Helper_Validate::getInstance()->sanitizePost('submit'))
{
    $val   = CAT_Helper_Validate::getInstance();
    $diffs = 0;
    foreach($settings as $i => $set)
    {
        $field = $set['name'];
        if ( $field == 'source' ) continue;
        $new   = $val->sanitizePost($field);
        if ( $new != $set['value'] )
        {
            $settings[$i]['value'] = $new;
            $diffs++;
        }
    }
    if($diffs)
    {
        $inc  = file_get_contents(dirname(__FILE__).'/data/config.inc.php');
        $ainc = preg_split( '~// --- do not change this manually, use the Admin Tool! ---~', $inc, NULL, PREG_SPLIT_DELIM_CAPTURE);
        $fh   = fopen(dirname(__FILE__).'/data/config.inc.php','w');
        fwrite($fh,$ainc[0]);
        fwrite($fh,"// --- do not change this manually, use the Admin Tool! ---\n\$current = array(\n");
        foreach($settings as $i => $set) {
            fwrite($fh,"    '".$set['name'].'\' => \''.$set['value'].'\','."\n");
        }
        fwrite($fh,');');
        fclose($fh);
        $info = CAT_Helper_Validate::getInstance()->lang()->translate(
            'Settings saved'
        );
    }
}

$parser->setPath(dirname(__FILE__).'/templates/default');
$parser->output('tool.tpl',array('settings'=>$settings,'current'=>$current,'info'=>$info));