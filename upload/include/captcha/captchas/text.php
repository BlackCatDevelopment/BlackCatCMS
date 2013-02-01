<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

global $database;
$name = 'text';
$file = CAT_PATH."/temp/.captcha_$name.php";

srand((double)microtime()*100000);
$_SESSION['captcha'.$sec_id] = rand(0,99999);

// get questions and answers
$text_qa='';
$table = CAT_TABLE_PREFIX.'mod_captcha_control';
if($query = $database->query("SELECT ct_text FROM $table")) {
	$data = $query->fetchRow();
	$text_qa = $data['ct_text'];
}
$content = explode("\n", $text_qa);

reset($content);
while($s = current($content)) {
	// get question
	$s=trim(rtrim(rtrim($s,"\n"),"\r")); // remove newline
	if($s=='' OR $s{0}!='?') {
		next($content);
		continue;
	}
	if(isset($s{3}) && $s{3}==':') {
		$lang=substr($s,1,2);
		$q=substr($s,4);
	}	else {
		$lang='XX';
		$q=substr($s,1);
		if($q=='') {
			next($content);
			continue;
		}
	}
	// get answer
	$s=next($content);
	$s=trim(rtrim(rtrim($s,"\n"),"\r")); // remove newline
	if(isset($s{0}) && $s{0}=='!') {
		$a=substr($s,1);
		$qa[$lang][$q]=$a;
		next($content);
	}
}
if(!isset($qa) || $qa == array()) {
	echo '<b>Error</b>: no text defined! Enter <b>0</b> to solve this captcha';
	$_SESSION['captcha'] = '0';
	return;
}

// choose language to use
if(defined('LANGUAGE') && isset($qa[LANGUAGE]))
	$lang = LANGUAGE;
else
	$lang = 'XX';
if(!isset($qa[$lang])) {
	echo '<b>Error</b>: no text defined! Enter <b>0</b> to solve this captcha';
	$_SESSION['captcha'] = '0';
	return;
}

// choose random question
$k = array_rand($qa[$lang]);

$_SESSION['captcha'.$sec_id] = $qa[$lang][$k];

echo $k;

?>
