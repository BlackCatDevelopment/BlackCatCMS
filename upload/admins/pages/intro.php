<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
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



require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_intro');

// Get page content
$filename = WB_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;
if(file_exists($filename)) {
	$handle = fopen($filename, "r");
	$content = fread($handle, filesize($filename));
	fclose($handle);
} else {
	$content = '';
}

if(!isset($_GET['wysiwyg']) OR $_GET['wysiwyg'] != 'no') {
	if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
		function show_wysiwyg_editor($name,$id,$content,$width,$height) {
			echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
		}
	} else {
		$id_list=array('content');
		require(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
	}
}
?>

<form action="intro2.php" method="post">
<input type="hidden" name="leptoken" value="<?php echo $_GET['leptoken']; ?>" />

<?php
show_wysiwyg_editor('content','content',$content,'100%','500px');
?>

<table cellpadding="0" cellspacing="0" border="0" class="form_submit">
<tr>
	<td class="left">
		<input type="submit" value="<?php echo $TEXT['SAVE'];?>" class="submit" />
	</td>
	<td class="right">
		<input type="button" value="<?php echo $TEXT['CANCEL'];?>" onclick="javascript: window.location = 'index.php';" class="submit" />
	</td>
</tr>
</table>

</form>
<?php
// Print admin footer
$admin->print_footer();

?>