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
$sec_id = '';
if(isset($_GET['s'])) $sec_id = $_GET['s'];
$_SESSION['captcha'.$sec_id] = '';
mt_srand((double)microtime()*1000000);
$n = mt_rand(1,3);
switch ($n) {
	case 1:
		$x = mt_rand(1,9);
		$y = mt_rand(1,9);
		$_SESSION['captcha'.$sec_id] = $x + $y;
		$cap = "$x+$y"; 
		break; 
	case 2:
		$x = mt_rand(10,20);
		$y = mt_rand(1,9);
		$_SESSION['captcha'.$sec_id] = $x - $y; 
		$cap = "$x-$y"; 
		break;
	case 3:
		$x = mt_rand(2,10);
		$y = mt_rand(2,5);
		$_SESSION['captcha'.$sec_id] = $x * $y; 
		$cap = "$x*$y"; 
		break;
}

// create reload-image
$reload = ImageCreateFromPNG(CAT_PATH.'/framework/CAT/Helper/Captcha/WB/reload_120_30.png'); // reload-overlay

$image = imagecreate(120, 30);

$white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
$gray = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
$darkgray = imagecolorallocate($image, 0x30, 0x30, 0x30);

for($i = 0; $i < 30; $i++) {
	$x1 = mt_rand(0,120);
	$y1 = mt_rand(0,30);
	$x2 = mt_rand(0,120);
	$y2 = mt_rand(0,30);
	imageline($image, $x1, $y1, $x2, $y2 , $gray);  
}

$x = 10;
$l = strlen($cap);
for($i = 0; $i < $l; $i++) {
	$fnt = mt_rand(3,5);
	$x = $x + mt_rand(12 , 20);
	$y = mt_rand(7 , 12); 
	imagestring($image, $fnt, $x, $y, substr($cap, $i, 1), $darkgray); 
}

imagealphablending($reload, TRUE);
imagesavealpha($reload, TRUE);

// overlay
imagecopy($reload, $image, 0,0,0,0, 120,30);
imagedestroy($image);
$image = $reload;

captcha_header();
imagepng($image);
imagedestroy($image);

?>