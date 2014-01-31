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

// AJAX call
if(CAT_Helper_Validate::sanitizePost('file'))
{
    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/'.CAT_Helper_Validate::sanitizePost('file'));
    if(file_exists($file))
    {
        $lines = file($file);
        echo implode('',$lines);
    }
    else
    {
        echo CAT_Helper_Validate::getInstance()->lang()->translate("File not found")
            . ": ".str_ireplace( array(str_replace('\\','/',CAT_PATH),'\\'), array('/abs/path/to','/'), $file );
    }
    exit;
}

if(CAT_Helper_Validate::sanitizePost('remove'))
{
    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/'.CAT_Helper_Validate::sanitizePost('remove'));
    if(file_exists($file))
    {
        unlink($file);
    }
    else
    {
        echo CAT_Helper_Validate::getInstance()->lang()->translate("File not found")
            . ": ".str_ireplace( array( str_replace('\\','/',CAT_PATH),'\\'), array('/abs/path/to','/'), $file );
    }
    exit;
}

// clean up log files (older than 24 hours and size 0)
$files = CAT_Helper_Directory::findFiles('log_\d{4}-\d{2}-\d{2}\.txt',CAT_PATH.'/temp');
if(count($files))
    foreach($files as $f)
        if(filemtime($f)<(time()-24*60*60)&&filesize($f)==0)
            unlink($f);
$files = CAT_Helper_Directory::findFiles('log_\d{4}-\d{2}-\d{2}\.txt',CAT_PATH.'/temp/logs');
if(count($files))
    foreach($files as $f)
        if(filemtime($f)<(time()-24*60*60)&&filesize($f)==0)
            unlink($f);

$widget_name = 'Logfiles';
$logs  = array();
$list  = array();
$files = CAT_Helper_Directory::getInstance()
         ->maxRecursionDepth(2)
         ->setSuffixFilter(array('txt'))
         ->setSkipDirs(array('cache','compiled'))
         ->setSkipFiles(array('index.php'))
         ->findFiles('log_\d{4}-\d{2}-\d{2}\.txt',CAT_PATH.'/temp');

if(count($files))
    foreach($files as $f)
        if(filesize($f)!==0)
            $list[] = $f;

if(count($list))
{
    foreach(array_values($list) as $f)
    {
        $file = str_ireplace(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/'),'',CAT_Helper_Directory::sanitizePath($f));
        if(substr($file,0,1)=="/")
            $file = substr_replace($file,'',0,1);
        $logs[] = $file;
    }
}
else
{
    echo CAT_Helper_Directory::getInstance()->lang()->translate('No logfiles (or all empty)');
}

global $parser;
$parser->setPath(dirname(__FILE__).'/../templates/default');
$parser->output('logs.tpl',array('logs'=>$logs));

