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

// protect
$backend = CAT_Backend::getInstance('Start','start',false,false);
if(!CAT_Users::is_authenticated()) exit; // just to be _really_ sure...

$widget_settings = array(
    'allow_global_dashboard'    => true,
    'auto_add_global_dashboard' => true,
    'widget_title'              => CAT_Helper_I18n::getInstance()->translate('Logs'),
    'preferred_column'          => 3
);

if(!function_exists('render_widget_blackcat_logs'))
{
    function render_widget_blackcat_logs()
    {
        // view
        if(CAT_Helper_Validate::sanitizePost('file'))
        {
    $date = CAT_Helper_Validate::sanitizePost('file');
    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/logs/log_'.$date.'.txt');
            if(file_exists($file))
            {
                $lines  = file($file);
                $output = implode('<br />',$lines);
                $output = str_replace(
                    array(
                        'INFO',
                        'WARN',
                        'CRIT'
                    ),
                    array(
                        '<span style="color:#006600">INFO</span>',
                        '<span style="color:#FF6600">WARN</span>',
                        '<span style="color:#990000;font-weight:900;">CRIT</span>',
                    ),
                    $output
                );
                echo $output;
            }
            else
            {
                echo CAT_Helper_Validate::getInstance()->lang()->translate("File not found")
                    . ": ".str_ireplace( array(str_replace('\\','/',CAT_PATH),'\\'), array('/abs/path/to','/'), $file );
            }
            exit;
        }
        // download
        if(CAT_Helper_Validate::sanitizeGet('dl'))
        {
    		$date = CAT_Helper_Validate::sanitizeGet('dl');
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/logs/log_'.$date.'.txt');
            if(file_exists($file))
            {
                $zip = CAT_Helper_Zip::getInstance(pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME).'.zip');
                $zip->config('removePath',pathinfo($file,PATHINFO_DIRNAME))
                    ->create(array($file));
                if(!$zip->errorCode() == 0)
                {
                    echo CAT_Helper_Validate::getInstance()->lang()->translate("Unable to pack the file")
                        . ": ".str_ireplace( array( str_replace('\\','/',CAT_PATH),'\\'), array('/abs/path/to','/'), $file );
                }
                else
                {
                    $filename = pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME).'.zip';
                    header("Pragma: public"); // required
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false); // required for certain browsers
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length: ".filesize($filename));
                    readfile("$filename");
                    exit;
                }
            }
            else
            {
                echo CAT_Helper_Validate::getInstance()->lang()->translate("File not found")
                    . ": ".str_ireplace( array(str_replace('\\','/',CAT_PATH),'\\'), array('/abs/path/to','/'), $file );
            }
            exit;
        }

        // remove
        if(CAT_Helper_Validate::sanitizePost('remove'))
        {
    $date = CAT_Helper_Validate::sanitizePost('remove');
    $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/logs/log_'.$date.'.txt');
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

        $widget_name = CAT_Object::lang()->translate('Logfiles');
        $current     = strftime('%Y-%m-%d');

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
                    $list[] = array('file'=>$f,'size'=>filesize($f));

        if(count($list))
        {
            foreach(array_values($list) as $f)
            {
                $file = str_ireplace(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/'),'',CAT_Helper_Directory::sanitizePath($f['file']));
                if(substr($file,0,1)=="/")
                    $file = substr_replace($file,'',0,1);
                if(pathinfo($f['file'],PATHINFO_BASENAME) == 'log_'.$current.'.txt')
                    $removable = false;
                else
                    $removable = true;
        $logs[] = array('file'=>$file,'size'=>CAT_Helper_Directory::byte_convert($f['size']),'removable'=>$removable,'date'=>str_ireplace(array('log_','logs/','.txt'),'',$file));
            }
        }
        else
        {
            return CAT_Helper_Directory::getInstance()->lang()->translate('No logfiles');
        }

        global $parser;
        $parser->setPath(dirname(__FILE__).'/../templates/default');
        $output = $parser->get('logs.tpl',array('logs'=>$logs));
        $parser->resetPath();
        return $output;
    }
}

if(CAT_Helper_Validate::sanitizePost('_cat_ajax')==true)
    render_widget_blackcat_logs();
