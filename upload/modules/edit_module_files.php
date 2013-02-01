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

// prevent this file from being accessed directly
if(!(isset($_POST['page_id']) && isset($_POST['section_id']) && isset($_POST['action'])
	&& isset($_POST['mod_dir'])  && isset($_POST['edit_file']))) die(header('Location: index.php'));

// include the and admin wrapper script
require(CAT_PATH.'/modules/admin.php');

// leave if the required module.functions.php file does not exist
if(!file_exists(CAT_PATH .'/framework/module.functions.php')) {
	echo 'The required file: /framework/module.functions.php is missing - script stopped.';
	die;
}

echo (function_exists('registerEditArea')) ? registerEditArea('code_area', 'css', false) : 'none';

// set default text output if varibles are not defined in the global WB language files
$HEADING_CSS_FILE = (isset($GLOBALS['TEXT']['HEADING_CSS_FILE'])) ?$GLOBALS['TEXT']['HEADING_CSS_FILE'] :'Actual module file: ';
$TXT_EDIT_CSS_FILE = (isset($GLOBALS['TEXT']['TXT_EDIT_CSS_FILE'])) ?$GLOBALS['TEXT']['TXT_EDIT_CSS_FILE'] :'Edit the CSS definitions in the textarea below.';

// include functions to edit the optional module CSS files (frontend.css, backend.css)
require_once(CAT_PATH .'/framework/module.functions.php');

// check if the module directory is valid
$mod_dir = check_module_dir($_POST['mod_dir']);
if($mod_dir == '')
{
	echo 'The specified module directory is invalid - script stopped.';
	die;
};

// check if action is: save or edit
if($_POST['action'] == 'save' && mod_file_exists($mod_dir, $_POST['edit_file'])) {
	/** 
		SAVE THE UPDATED CONTENTS TO THE CSS FILE
	*/
	$css_content = '';
	if (isset($_POST['css_data']) && strlen($_POST['css_data']) > 0) {
		$css_content = stripslashes($_POST['css_data']);
	}

	$bytes = 0;
	if ($css_content != '')
    {
		// open the module CSS file for writting
		$mod_file = fopen(CAT_PATH .'/modules/' .$mod_dir .'/' .$_POST['edit_file'], 'wb');
		// write new content to the module CSS file
		$bytes = fwrite($mod_file, $css_content);
		// close the file
		fclose($mod_file);
	}

	// write out status message
	if($bytes == 0 )
    {
		$admin->print_error($TEXT['ERROR'], CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
	} else {
		$admin->print_success($TEXT['SUCCESS'], CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
	}


} else {
	/** 
		MODIFY CONTENTS OF THE CSS FILE VIA TEXT AREA 
	*/
	// check if module backend.css file needs to be included into the <body>
	if((!method_exists($admin, 'register_backend_modfiles') || !isset($_GET['page_id']))
			&& file_exists(CAT_PATH .'/modules/'.$mod_dir.'/backend.css')) {
		echo '<style type="text/css">';
		include(CAT_PATH .'/modules/' .$mod_dir .'/backend.css');
		echo "\n</style>\n";
	}

	// check which module file to edit (frontend.css, backend.css or '')
	$css_file = (in_array($_POST['edit_file'], array('frontend.css', 'backend.css'))) ? $_POST['edit_file'] : '';

	// display output
	if($css_file == '')
    {
		// no valid module file to edit; display error message and backlink to modify.php
		echo "<h2>Nothing to edit</h2>";
		echo "<p>No valid module file exists for this module.</p>";
		$output  = "<a href=\"#\" onclick=\"javascript: window.location = '";
		$output .= CAT_ADMIN_URL ."/pages/modify.php?page_id=" .$page_id ."'\">back</a>";
		echo $output;
	
	} else {
		// store content of the module file in variable
		$css_content = file_get_contents(CAT_PATH .'/modules/' .$mod_dir .'/' .$css_file);
		// write out heading
		echo '<h2>' .$HEADING_CSS_FILE .'"' .$css_file .'"</h2>';
		// include button to switch between frontend.css and backend.css (only shown if both files exists)
		toggle_css_file($mod_dir, $css_file); 
		echo '<p>' .$TXT_EDIT_CSS_FILE .'</p>';

		// output content of module file to textareas
	?>
		<form name="edit_module_file" action="<?php echo $_SERVER['SCRIPT_NAME'];?>" method="post" style="margin: 0;">
	  	<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
	  	<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
	  	<input type="hidden" name="mod_dir" value="<?php echo $mod_dir; ?>" />
		<input type="hidden" name="edit_file" value="<?php echo $css_file; ?>" />
	  	<input type="hidden" name="action" value="save" />
		<textarea id="code_area" name="css_data" cols="115" rows="25" wrap="VIRTUAL" style="margin:2px;width:100%;"><?php
			echo htmlspecialchars($css_content); 
		?></textarea>
<?php

?>
  			<table cellpadding="0" cellspacing="0" border="0" width="100%">
  			<tr>
    			<td class="left">
 				<input name="save" type="submit" value="<?php echo $TEXT['SAVE'];?>" style="width: 100px; margin-top: 5px;" />
    			</td>
  				<td class="right">
      			<input type="button" value="<?php echo $TEXT['CANCEL']; ?>"
						onclick="javascript: window.location = '<?php echo CAT_ADMIN_URL;?>/pages/modify.php?page_id=<?php echo $page_id; ?>';"
						style="width: 100px; margin-top: 5px;" />
  				</td>
  			</tr>
  			</table>
		</form>
		<?php 
	}
}

// Print admin footer
$admin->print_footer();

?>