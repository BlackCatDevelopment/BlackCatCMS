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

?>
<script type="text/javascript" src="<?php echo CAT_URL ?>/include/jscalendar/calendar.js"></script>
<?php // some stuff for jscalendar
	// language
	$jscal_lang = defined('LANGUAGE')?strtolower(LANGUAGE):'en';
	$jscal_lang = $jscal_lang!=''?$jscal_lang:'en';
	if(!file_exists(CAT_PATH."/include/jscalendar/lang/calendar-$jscal_lang.js")) {
		$jscal_lang = 'en';
	}
	
	/**
	 *	today
	 *
	 *	If $jscal_use_today_time is set to true, the actual time will be set, otherwise 00:00:00 is used.
	 *
	 */
	if( !isset( $jscal_use_today_time ) ) $jscal_use_today_time = false; 
	$jscal_today = ( $jscal_use_today_time === true )
			? date('Y/m/d H:i', time())
			: date('Y/m/d')
			;
			
	// first-day-of-week
	$jscal_firstday = '1'; // monday
	if(LANGUAGE=='EN')
		$jscal_firstday = '0'; // sunday
	// date and time format for the text-field and for jscal's "ifFormat". We offer dd.mm.yyyy or yyyy-mm-dd or mm/dd/yyyy
	// ATTN: strtotime() fails with "dd.mm.yyyy" and PHP4. So the string has to be converted to e.g. "yyyy-mm-dd", which will work.
	switch(DATE_FORMAT) {
		case 'd.m.Y':
		case 'd M Y':
		case 'l, jS F, Y':
		case 'jS F, Y':
		case 'D M d, Y':
		case 'd-m-Y':
		case 'd/m/Y':
			$jscal_format = 'd.m.Y'; // dd.mm.yyyy hh:mm
			$jscal_ifformat = '%d.%m.%Y';
			break;
		case 'm/d/Y':
		case 'm-d-Y':
		case 'M d Y':
		case 'm.d.Y':
			$jscal_format = 'm/d/Y'; // mm/dd/yyyy hh:mm
			$jscal_ifformat = '%m/%d/%Y';
			break;
		default:
			$jscal_format = 'Y-m-d'; // yyyy-mm-dd hh:mm
			$jscal_ifformat = '%Y-%m-%d';
			break;
	}
	if( isset( $jscal_use_time) && ( $jscal_use_time==TRUE ) ) {
		$jscal_format .= ' H:i';
		$jscal_ifformat .= ' %H:%M';
	}
	// load scripts for jscalendar
?>
<script type="text/javascript" src="<?php echo CAT_URL ?>/include/jscalendar/lang/calendar-<?php echo $jscal_lang ?>.js"></script>
<script type="text/javascript" src="<?php echo CAT_URL ?>/include/jscalendar/calendar-setup.js"></script>
