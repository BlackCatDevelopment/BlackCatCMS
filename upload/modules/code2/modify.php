<?php

/**
 *  @module         code2
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2011 Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
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

/**
 *	Load Language file
 */
$lang = (dirname(__FILE__))."/languages/". LANGUAGE .".php";
require_once ( !file_exists($lang) ? (dirname(__FILE__))."/languages/EN.php" : $lang );

// Setup template object
$template = new Template(WB_PATH.'/modules/code2');
$template->set_file('page', 'htt/modify.htt');
$template->set_block('page', 'main_block', 'main');

/**
 *	Get page content
 */
$query = "SELECT `content`, `whatis` FROM `".TABLE_PREFIX."mod_code2` WHERE `section_id`= '".$section_id."'";
$get_content = $database->query($query);
$content = $get_content->fetchRow( MYSQL_ASSOC );
$whatis = (int)$content['whatis'];

$mode = ($whatis >= 10) ? (int)($whatis / 10) : 0;
$whatis = $whatis % 10;

$groups = $admin->get_groups_id();

if ( ( $whatis == 4) AND (!in_array(1, $groups)) ) {
	$content = $content['content'];
	echo '<div class="code2_admin">'.$content.'</div>';
} else {	
	$content = htmlspecialchars($content['content']);
	$whatis_types = array('PHP', 'HTML', 'Javascript', 'Internal');
	if (in_array(1, $groups)) $whatis_types[]="Admin";
	$whatisarray = array();
	foreach($whatis_types as $item) $whatisarray[] = $MOD_CODE2[strtoupper($item)];
	
	$whatisselect = '';
	for($i=0; $i < count($whatisarray); $i++) {
   		$select = ($whatis == $i) ? " selected='selected'" : "";
   		$whatisselect .= '<option value="'.$i.'"'.$select.'>'.$whatisarray[$i].'</option>'."\n";
  	}
    
    $modes_names = array('smart', 'full');
    $modes = array();
    foreach($modes_names as $item) $modes[] = $MOD_CODE2[strtoupper($item)];
    $mode_options = "";
    $counter = 0;
    foreach($modes as $item) {
    	$mode_options .= "<option value='".$counter."' ".(($counter==$mode)?" selected='selected'":"").">".$item."</option>";
		$counter++;
	}
	
	// Insert vars
	$template->set_var(array(
			'PAGE_ID' => $page_id,
			'SECTION_ID' => $section_id,
			'WB_URL' => WB_URL,
			'CONTENT' => $content,
			'WHATIS' => $whatis,
			'WHATISSELECT' => $whatisselect,
			'TEXT_SAVE' => $TEXT['SAVE'],
			'TEXT_CANCEL' => $TEXT['CANCEL'],
			'MODE'	=> $mode_options,
			'MODE_' => $mode,
			'LANGUAGE' => LANGUAGE,
			'MODES'	=> $MOD_CODE2['MODE']
		)
	);

	// Parse template object
	$template->parse('main', 'main_block', false);
	echo $template->parse('output', 'page', false, false);	
}
?>