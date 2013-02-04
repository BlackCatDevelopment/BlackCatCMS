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


// displays the image or text inside an <iframe>
if(!function_exists('display_captcha_real')) {
	function display_captcha_real($kind='image') {
		$t = time();
		$_SESSION['captcha_time'] = $t;
		$sec_id = '';
		if(isset($_GET['s']) && is_numeric($_GET['s'])) $sec_id = $_GET['s'];
		if($kind=='image') {
			?><a class="reload" title="reload" href="<?php echo CAT_URL.'/include/captcha/captcha.php?display_captcha_X986E21=2'; ?>">
			  <img class="lep_captcha" style="border: none;" src="<?php echo CAT_URL.'/include/captcha/captchas/'.CAPTCHA_TYPE.".php?t=$t&amp;s=$sec_id"; ?>" alt="Captcha" />
				</a><?php
		} else {
			echo 'error';
		}
	}
}

// called from an <iframe>
if(isset($_GET['display_captcha_X986E21'])) {
	switch(CAPTCHA_TYPE) {
	case 'calc_image':
	case 'calc_ttf_image':
	case 'ttf_image':
	case 'old_image':
		display_captcha_real('image');
		break;
	}
	exit(0);
}


// output-handler for image-captchas to determine size of image
if(!function_exists('captcha_header')) {
	function captcha_header() {
		header("Expires: Mon, 1 Jan 1990 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate");
		header("Pragma: no-cache");
		header("Content-type: image/png");
		return;
	}
}

global $wb, $admin;
if ( ! is_object($wb) )
{
    if ( ! is_object($admin) )
    {
        $wb = new frontend();
    }
    else
    {
        $wb =& $admin;
    }
}

// get list of available CAPTCHAS for the dropdown-listbox in admin-tools
$useable_captchas = array(
    'calc_text' => $wb->lang->translate('Calculation as text'),
    'text'      => $wb->lang->translate('Text-CAPTCHA'),
);

if(extension_loaded('gd') && function_exists('imagepng') )
{
    $useable_captchas['calc_image'] = $wb->lang->translate('Calculation as image');
    $useable_captchas['old_image']  = $wb->lang->translate('Old style (not recommended)');
    if (function_exists('imagettftext'))
    {
	    $useable_captchas['calc_ttf_image'] = $wb->lang->translate('Calculation as image with varying fonts and backgrounds');
		$useable_captchas['ttf_image']      = $wb->lang->translate('Image with varying fonts and backgrounds');
	}
}

if(!function_exists('call_captcha')) {
	function call_captcha($action='all', $style='', $sec_id='') {
		global $MOD_CAPTCHA;
		$t = time();
		$_SESSION['captcha_time'] = $t;

		// get width and height of captcha image for use in <iframe>
		switch(CAPTCHA_TYPE) {
		case 'calc_image':
			$captcha_width = 142;
			$captcha_height = 30;
			break;
		case 'calc_ttf_image':
			$captcha_width = 162;
			$captcha_height = 40;
			break;
		case 'ttf_image':
			$captcha_width = 162;
			$captcha_height = 40;
			break;
		case 'old_image':
			$captcha_width = 142;
			$captcha_height = 30;
			break;
		default:
			$captcha_width = 250;
			$captcha_height = 100;
		}

		if($action=='all') {
			switch(CAPTCHA_TYPE) {
				case 'text': // text-captcha
					?><div class="captcha_table"><div class="captcha_table_text">
					<span class="text_captcha">
						<?php include(CAT_PATH.'/include/captcha/captchas/'.CAPTCHA_TYPE.'.php'); ?>
					</span>
					
					<input class="text_captcha_length" type="text" name="captcha" maxlength="50"  style="width:150px;" />
					<span class="captcha_expl"><?php echo $MOD_CAPTCHA['VERIFICATION_INFO_QUEST']; ?></span>
					</div></div><?php
					break;
				case 'calc_text': // calculation as text
					?><div class="captcha_table"><div class="captcha_table_calc">
					<span class="text_captcha">
						<?php include(CAT_PATH.'/include/captcha/captchas/'.CAPTCHA_TYPE.'.php'); ?>
					</span>
					<input class="text_captcha_length" type="text" name="captcha" maxlength="10"  style="width:20px;" />
					<span class="captcha_expl"><?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?></span>
					</div></div><?php
					break;
				case 'calc_image': // calculation with image (old captcha)
				case 'calc_ttf_image': // calculation with varying background and ttf-font
				  ?><div class="captcha_table"><div class="captcha_table_imgcalc">
					<span class="image_captcha">
						<?php echo "<iframe class=\"captcha_iframe\" width=\"$captcha_width\" height=\"$captcha_height\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" name=\"captcha_iframe_$sec_id\" src=\"". CAT_URL ."/include/captcha/captcha.php?display_captcha_X986E21=1&amp;s=$sec_id"; ?>">
						<img class="lep_imgcalc" src="<?php echo CAT_URL.'/include/captcha/captchas/'.CAPTCHA_TYPE.".php?t=$t&amp;s=$sec_id"; ?>" alt="Captcha" />
						</iframe>
					</span>
					 
					<input class="text_captcha_length" type="text" name="captcha" maxlength="10" style="width:20px;" />
					<span class="captcha_expl"><?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?></span>
					</div></div><?php
					break;
				// normal images
				case 'ttf_image': // captcha with varying background and ttf-font
				case 'old_image': // old captcha
					?><div class="captcha_table"><div class="captcha_table_img">
					<span class="image_captcha">
						<?php echo "<iframe class=\"captcha_iframe\" width=\"$captcha_width\" height=\"$captcha_height\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" name=\"captcha_iframe_$sec_id\" src=\"". CAT_URL ."/include/captcha/captcha.php?display_captcha_X986E21=1&amp;s=$sec_id"; ?>">
						<img class="lep_imgcaptcha" src="<?php echo CAT_URL.'/include/captcha/captchas/'.CAPTCHA_TYPE.".php?t=$t&amp;s=$sec_id"; ?>" alt="Captcha" />
						</iframe>
					</span>
					
					<input class="text_captcha_length" type="text" name="captcha" maxlength="10" style="width:50px;" />
					<span class="captcha_expl"><?php echo $MOD_CAPTCHA['VERIFICATION_INFO_TEXT']; ?></span>
					</div></div><?php
					break;
			}
		} elseif($action=='image') {
			switch(CAPTCHA_TYPE) {
				case 'text': // text-captcha
				case 'calc_text': // calculation as text
					echo ($style?"<span $style>":'');
					include(CAT_PATH.'/include/captcha/captchas/'.CAPTCHA_TYPE.'.php');
					echo ($style?'</span>':'');
					break;
				case 'calc_image': // calculation with image (old captcha)
				case 'calc_ttf_image': // calculation with varying background and ttf-font
				case 'ttf_image': // captcha with varying background and ttf-font
				case 'old_image': // old captcha
					echo "<img $style src=\"".CAT_URL.'/include/captcha/captchas/'.CAPTCHA_TYPE.".php?t=$t&amp;s=$sec_id\" />";
					break;
			}
		} elseif($action=='image_iframe') {
			switch(CAPTCHA_TYPE) {
				case 'text': // text-captcha
					echo ($style?"<span $style>":'');
					include(CAT_PATH.'/include/captcha/captchas/'.CAPTCHA_TYPE.'.php');
					echo ($style?'</span>':'');
					break;
				case 'calc_text': // calculation as text
					include(CAT_PATH.'/include/captcha/captchas/'.CAPTCHA_TYPE.'.php');
					break;
				case 'calc_image': // calculation with image (old captcha)
				case 'calc_ttf_image': // calculation with varying background and ttf-font
				case 'ttf_image': // captcha with varying background and ttf-font
				case 'old_image': // old captcha
					?>
					<?php echo "<iframe class=\"captcha_iframe\" width=\"$captcha_width\" height=\"$captcha_height\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" name=\"captcha_iframe_$sec_id\" src=\"". CAT_URL ."/include/captcha/captcha.php?display_captcha_X986E21=1&amp;s=$sec_id"; ?>">
					<?php
					echo "<img $style alt=\"Captcha\" src=\"".CAT_URL.'/include/captcha/captchas/'.CAPTCHA_TYPE.".php?t=$t\" />";
					?></iframe><?php
					break;
			}
		} elseif($action=='input') {
			switch(CAPTCHA_TYPE) {
				case 'text': // text-captcha
					echo '<input type="text" name="captcha" '.($style?$style:'style="width:150px;" maxlength="50"').' />';
					break;
				case 'calc_text': // calculation as text
				case 'calc_image': // calculation with image (old captcha)
				case 'calc_ttf_image': // calculation with varying background and ttf-font
					echo '<input type="text" name="captcha" '.($style?$style:'style="width:20px;" maxlength="10"').' />';
					break;
				case 'ttf_image': // captcha with varying background and ttf-font
				case 'old_image': // old captcha
					echo '<input type="text" name="captcha" '.($style?$style:'style="width:50px;" maxlength="10"').' />';
					break;
			}
		} elseif($action=='text') {
			echo ($style?"<span $style>":'');
			switch(CAPTCHA_TYPE) {
				case 'text': // text-captcha
					echo $MOD_CAPTCHA['VERIFICATION_INFO_QUEST'];
					break;
				case 'calc_text': // calculation as text
				case 'calc_image': // calculation with image (old captcha)
				case 'calc_ttf_image': // calculation with varying background and ttf-font
					echo $MOD_CAPTCHA['VERIFICATION_INFO_RES'];
					break;
				case 'ttf_image': // captcha with varying background and ttf-font
				case 'old_image': // old captcha
					echo $MOD_CAPTCHA['VERIFICATION_INFO_TEXT'];
					break;
			}
			echo ($style?'</span>':'');
		}
	}
}

