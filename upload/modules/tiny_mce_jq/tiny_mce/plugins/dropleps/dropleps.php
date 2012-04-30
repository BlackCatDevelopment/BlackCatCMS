<?php

/**
 *  @module         TinyMCE-jQ
 *  @version        see info.php of this module
 *  @authors        erpe, Dietrich Roland Pehlke (Aldus)
 *  @copyright      2010-2011 erpe, Dietrich Roland Pehlke (Aldus)
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 *  Please Notice: TINYMCE is distibuted under the <a href="http://tinymce.moxiecode.com/license.php">(LGPL) License</a> 
 *                 Ajax Filemanager is distributed under the <a href="http://www.gnu.org/licenses/gpl.html)">GPL </a> and <a href="http://www.mozilla.org/MPL/MPL-1.1.html">MPL</a> open source licenses 
 *
 */

// Include the config file
require_once('../../../../../config.php');

if ( (!isset($_SESSION['TINY_MCE_INIT'])) && (!isset($_SERVER['HTTP_REFERER'])) ) die();

// Create new admin object
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_modify', false, false);
$groups = $admin->get_groups_id();

// load language file by actuelly wb language
$lang = strtolower(LANGUAGE).'.php';
$plugin_url = WB_URL.'/modules/tiny_mce_jq/tiny_mce/plugins/dropleps';
$plugin_path = WB_PATH.'/modules/tiny_mce_jq/tiny_mce/plugins/dropleps';

require_once(
	file_exists($plugin_path.'/langs/'.$lang) 
	? $plugin_path.'/langs/'.$lang 
	: $plugin_path.'/langs/en.php'
);

// Setup the template
$template = new Template(WB_PATH.'/modules/tiny_mce_jq/tiny_mce/plugins/dropleps');
$template->set_file('page', 'dropleps.htt');
$template->set_block('page', 'main_block', 'main');

// Get pages and put them into the pages list
$template->set_block('main_block', 'droplets_list_block', 'page_list');
$database    = new database();
$get_droplet = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_droplets AS t1 LEFT OUTER JOIN ".TABLE_PREFIX."mod_dropleps_permissions AS t2 ON t1.id=t2.id WHERE active=1 ORDER BY name");
if($get_droplet->numRows() > 0) {
	// Loop through pages
	$list = "";
	while($droplet = $get_droplet->fetchRow( MYSQL_ASSOC )) {
		// check if current user is allowed to use this droplet
	    if ( $droplet['edit_groups'] ) {
	        if ( ! is_in_array( $droplet['edit_groups'], $groups ) ) {
            	continue;
			}
        }
		// method page_is_visible was introduced with WB 2.7
		$title = stripslashes($droplet['name']);
		$desc = stripslashes($droplet['description']);
		$comm = stripslashes($droplet['comments']);
		$template->set_var('TITLE', $title);
		$template->set_var('DESC', $desc);
		$list .= "<div id='".$title."' class='hidden'><b>".$title.": </b> ".$desc."<br>".$comm."</div>";
		$template->parse('page_list', 'droplets_list_block', true);
	}
} else {
	$template->set_var('TITLE', 'None found');
	$template->parse('page_list', 'droplets_list_block', false);
}
$template->set_var('LIST', $list);
$template->set_var("CHARSET", defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8' );

$template->set_var(array(
            'droplepsDlgTitle' => $droplepsDlgTitle,
            'droplepslblInsert' => $droplepslblInsert,
            'droplepslblCancel' => $droplepslblCancel,
            'droplepslblPageSelection' => $droplepslblPageSelection,

         ) );
// Parse the template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

/**
 * checks if any item of $allowed is in $current
 **/
function is_in_array( $allowed, $current ) {
	if ( ! is_array( $allowed ) ) {
	    if ( substr_count( $allowed, '|' ) ) {
	        $allowed = explode('|', $allowed);
		}
		else {
	    $allowed = array( $allowed );
	}
	}
	if ( ! is_array( $current ) ) {
	    if ( substr_count( $current, '|' ) ) {
	        $current = explode('|', $current);
		}
		else {
	    $current = array( $current );
		}
	}
	foreach ( $allowed as $gid ) {
	    if ( in_array( $gid, $current ) ) {
	        return true;
		}
	}
	return false;
}   // end function is_in_array()

?>