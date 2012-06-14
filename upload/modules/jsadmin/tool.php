<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the BSD License.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          jsadmin 
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, Ryan Djurovich,WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         BSD License
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */ 

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

 

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(!file_exists(WB_PATH .'/modules/jsadmin/languages/'.LANGUAGE .'.php')) {
	// no module language file exists for the language set by the user, include default module language file EN.php
	require_once(WB_PATH .'/modules/jsadmin/languages/EN.php');
} else {
	// a module language file exists for the language defined by the user, load it
	require_once(WB_PATH .'/modules/jsadmin/languages/'.LANGUAGE .'.php');
}

// check if backend.css file needs to be included into the <body></body>
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH .'/modules/jsadmin/backend.css')) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/jsadmin/backend.css');
	echo "\n</style>\n";
}

require_once(WB_PATH.'/modules/jsadmin/jsadmin.php');

// Check if user selected what add-ons to reload
if(isset($_POST['submit']) AND $_POST['submit'] != '') {
	if (true === method_exists($admin, 'checkFTAN')) {
		if (!$admin->checkFTAN()) {
		$admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'],$_SERVER['REQUEST_URI']);
		exit();
	}
}
	// Include functions file
	require_once(WB_PATH.'/framework/functions.php');
	save_setting('mod_jsadmin_persist_order', isset($_POST['persist_order']));
	save_setting('mod_jsadmin_ajax_order_pages', isset($_POST['ajax_order_pages']));
	save_setting('mod_jsadmin_ajax_order_sections', isset($_POST['ajax_order_sections']));
	echo '<div style="border: solid 2px #9c9; background: #ffd; padding: 0.5em; margin-top: 1em">'.$MESSAGE['SETTINGS']['SAVED'].'</div>';
}

// Display form
$persist_order = get_setting('mod_jsadmin_persist_order', true) ? 'checked="checked"' : '';
$ajax_order_pages = get_setting('mod_jsadmin_ajax_order_pages', true) ? 'checked="checked"' : '';
$ajax_order_sections = get_setting('mod_jsadmin_ajax_order_sections', true) ? 'checked="checked"' : '';
?>
<?php

// THIS ROUTINE CHECKS THE EXISTING OFF ALL NEEDED YUI FILES
  $YUI_ERROR=false; // ist there an Error
  $YUI_PUT ='';   // String with javascipt includes
  $YUI_PUT_MISSING_Files=''; // String with missing files
  reset($js_yui_scripts);
  foreach($js_yui_scripts as $script) {
     if(!file_exists($WB_MAIN_RELATIVE_PATH.$script)){
        $YUI_ERROR=true;
        $YUI_PUT_MISSING_Files =$YUI_PUT_MISSING_Files."- ".WB_URL.$script."<br />";   // catch all missing files
    }
	}
	if($YUI_ERROR)
	{
    ?><div id="jsadmin_install" style="border: solid 2px #c99; background: #ffd; padding: 0.5em; margin-top: 1em">

     <?php echo $MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'].$YUI_PUT_MISSING_Files; ?>
      </div>
      <?php
	}
  	else
	{
  ?>
   <form id="jsadmin_form" style="margin-top: 1em;" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
   <table cellpadding="4" cellspacing="0" border="0">
   <tr>
	     <td colspan="2"><?php echo $MOD_JSADMIN['TXT_HEADING_B']; ?>:</td>
   </tr>
   <tr>
	     <td width="20"><input type="checkbox" name="persist_order" id="persist_order" value="true" <?php echo $persist_order; ?>/></td>
	     <td><label for="persist_order"><?php echo $MOD_JSADMIN['TXT_PERSIST_ORDER_B']; ?></label></td>
   </tr>
   <tr>
	     <td width="20"><input type="checkbox" name="ajax_order_pages" id="ajax_order_pages" value="true" <?php echo $ajax_order_pages; ?>/></td>
	     <td><label for="ajax_order_pages"><?php echo $MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B']; ?></label></td>
   </tr>
   <tr>
	     <td width="20"><input type="checkbox" name="ajax_order_sections" id="ajax_order_sections" value="true" <?php echo $ajax_order_sections; ?>/></td>
	     <td><label for="ajax_order_sections"><?php echo $MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B']; ?></label></td>
   </tr>
   <tr>
	     <td>&nbsp;</td>
	     <td>
		   <input type="submit" name="submit" value="<?php echo $TEXT['SAVE']; ?>" />
	    </td>
   </tr>
   </table>
   </form>
 <?php
 }
?>
