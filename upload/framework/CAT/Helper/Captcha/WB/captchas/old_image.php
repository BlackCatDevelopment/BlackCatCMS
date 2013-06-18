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

require_once(CAT_PATH.'/framework/CAT/Helper/Captcha/WB/captcha.php');

if(!isset($_SESSION['captcha_time']))
	exit;
//unset($_SESSION['captcha_time']);

// Captcha
srand((double)microtime()*100000);
$sec_id = '';
if(isset($_GET['s'])) $sec_id = $_GET['s'];
$_SESSION['captcha'.$sec_id] = rand(10000,99999);

// create reload-image
$reload = ImageCreateFromPNG(CAT_PATH.'/framework/CAT/Helper/Captcha/WB/reload_120_30.png'); // reload-overlay

$w=120;
$h=30;
$image = imagecreate($w, $h);
$white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
$gray = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
$darkgray = imagecolorallocate($image, 0x50, 0x50, 0x50);

srand((double)microtime()*1000000);
for($i = 0; $i < 30; $i++) {
	$x1 = rand(0,$w);
	$y1 = rand(0,$h);
	$x2 = rand(0,$w);
	$y2 = rand(0,$h);
	imageline($image, $x1, $y1, $x2, $y2 , $gray);  
}

$x = 0;
for($i = 0; $i < 5; $i++) {
	$fnt = rand(3,5);
	$x = $x + rand(12 , 20);
	$y = rand(7 , 12); 
	imagestring($image, $fnt, $x, $y, substr($_SESSION['captcha'.$sec_id], $i, 1), $darkgray); 
}

imagealphablending($reload, TRUE);
imagesavealpha($reload, TRUE);

// overlay
imagecopy($reload, $image, 0,0,0,0, 120,30);
imagedestroy($image);
$image = $reload;

captcha_header();
ob_start();
imagepng($image);
header("Content-Length: ".ob_get_length()); 
ob_end_flush();
imagedestroy($image);

?>