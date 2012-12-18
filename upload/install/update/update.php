<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @reformatted     2011-11-20 
 *
 *
 */

$debug = false;
$new_lepton_version = '2.0';

// set error level
if ( $debug ) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

// language helper
include LEPTON_PATH . '/framework/LEPTON/Helper/I18n.php';
$lang = new LEPTON_Helper_I18n();

if ( ! defined('LANGUAGE') )   { define('LANGUAGE',$lang->getLang()); }
if ( ! defined('LEPTON_URL') ) { define('LEPTON_URL',WB_URL);         }

global $admin;
if (!is_object($admin))
{
    require_once(LEPTON_PATH . '/framework/class.admin.php');
    $admin = new admin('Addons', 'modules', false, false);
}

// template engine; creates a global var $parser
global $parser;
if ( file_exists(LEPTON_PATH.'/modules/lib_dwoo/library.php') )
{
	require_once LEPTON_PATH.'/modules/lib_dwoo/library.php';
}
$parser->setPath( dirname(__FILE__).'/../templates/default' );

// Try to guess installer URL
$installer_uri = 'http://' . $_SERVER[ "SERVER_NAME" ] . ( ( $_SERVER['SERVER_PORT'] != 80 ) ? ':'.$_SERVER['SERVER_PORT'] : '' ) . $_SERVER[ "SCRIPT_NAME" ];
$installer_uri = dirname( $installer_uri );

// get currently installed LEPTON version
$lepton_version = $database->get_one("SELECT `value` from `" . TABLE_PREFIX . "settings` where `name`='lepton_version'");

$progress = NULL;
$error    = NULL;
$steps 	  = array();

if ( isset($_REQUEST['update']) && $_REQUEST['update'] == 'y' ) {
	$progress = true;
	$file  = implode( "\n", file( dirname(__FILE__).'/progress.tmp' ) );
	$steps = unserialize( $file );
}
else {
	/**
	 * check upgrade steps
	 **/
	if (version_compare($lepton_version, "1.1.1", "<"))
	{
		$steps[] = array( 'function' => 'update_111', 'name' => $lang->translate( 'Upgrade to LEPTON v1.1.1' ), 'done' => false, 'msg' => false );
	}
	if (version_compare($lepton_version, "1.1.2", "<"))
	{
		$steps[] = array( 'function' => 'update_112', 'name' => $lang->translate( 'Upgrade to LEPTON v1.1.2' ), 'done' => false, 'msg' => false );
	}
	if (version_compare($lepton_version, "1.1.3", "<"))
	{
		$steps[] = array( 'function' => 'update_113', 'name' => $lang->translate( 'Upgrade to LEPTON v1.1.3' ), 'done' => false, 'msg' => false );
	}
	if (version_compare($lepton_version, "1.1.4", "<"))
	{
		$steps[] = array( 'function' => 'update_114', 'name' => $lang->translate( 'Upgrade to LEPTON v1.1.4' ), 'done' => false, 'msg' => false );
	}
	if (version_compare($lepton_version, "2.0", "<"))
	{
		$steps[] = array( 'function' => 'update_200', 'name' => $lang->translate( 'Upgrade to LEPTON v2.0' ), 'done' => false, 'msg' => false );
	}
	if ( false !== ( $fh = fopen( dirname(__FILE__).'/progress.tmp', 'w' ) ) )
	{
	    fwrite( $fh, serialize($steps) );
	    fclose( $fh );
	}
}

/**
 *  check release; update can be done with version >= 1.1.0
 */
if (version_compare($lepton_version, "1.1.0", "<"))
{
    $error = $lang->translate( "Unable to Upgrade!<br /><br />Your LEPTON Version v{{version}} cannot be upgraded using this wizard. We're sorry.", array( 'version' => $lepton_version ) );
}
/**
 * same version?
 **/
if (version_compare($lepton_version, $new_lepton_version, "="))
{
	$error = $lang->translate( "Unable to Upgrade!<br /><br />Your LEPTON Version is already up to date!" );
}
/**
 * no steps?
 **/
if ( ! count($steps) ) {
	$error = $lang->translate( "Unable to Upgrade!<br /><br />Could not find any steps!" );
}

if ( $progress ) {
	foreach( $steps as $step ) {
		if ( $step['done'] ) { continue; }
		$func = $step['function'];
		$func();
	}
}

$parser->output(
	'update.lte',
	array(
	    'installer_uri'      => $installer_uri,
	    'lepton_version'     => $lepton_version,
	    'new_lepton_version' => $new_lepton_version,
	    'debug'              => $debug,
	    'dump'               => print_r( array( $_REQUEST, $steps ), 1 ),
	    'progress'           => $progress,
	    'error'              => $error,
	)
);

/**
 *  reload all addons
 */
require_once ('reload.php');

/**
 * update the $steps array and write it back to the temp file
 **/
function update_steps( $done, $msg ) {
	if ( ! file_exists( dirname(__FILE__).'/progress.tmp') ) {
		echo "no progress file";
		return;
	}
	else {
		$file  = implode( "\n", file( dirname(__FILE__).'/progress.tmp' ) );
	    $steps = unserialize( $file );
	}
 	for( $i=0; $i<count($steps); $i++ ) {
		if ( $steps[$i]['function'] == $done ) {
		    $steps[$i]['msg']  = $msg;
		    $steps[$i]['done'] = true;
		}
	}
	if ( false !== ( $fh = fopen( dirname(__FILE__).'/progress.tmp', 'w' ) ) )
	{
	    fwrite( $fh, serialize($steps) );
	    fclose( $fh );
	}
}   // end function update_steps()


/**
 * update to v1.1.1
 **/
function update_111() {

	global $database, $lang;
	$errcount = 0;

	//  database modifications above 1.1.0
	$all = $database->query(" SELECT * from `" . TABLE_PREFIX . "addons` limit 1");
	if ($all)
	{
	    $temp = $all->fetchRow(MYSQL_ASSOC);
	    if (array_key_exists("php_version", $temp))
	    {
	        $database->query('ALTER TABLE `' . TABLE_PREFIX . 'addons` DROP COLUMN `php_version`, DROP COLUMN `sql_version`');
	    }
	}
	if ( $database->is_error() ) { $errcount++; }
	

	// set new version number
	$database->query('UPDATE `' . TABLE_PREFIX . 'settings` SET `value` =\'1.1.1\' WHERE `name` =\'lepton_version\'');
	if ( $database->is_error() ) { $errcount++; }

	/**
	 *  run upgrade.php of all modified modules
	 *
	 */
	$upgrade_modules = array(
	    "form",
	    "news",
	    "initial_page",
	    "ckeditor",
	    "show_menu2",
	    "wysiwyg_admin"
	);

	upgrade_modules( $upgrade_modules );

	// delete obsolete module phplib
	require_once(WB_PATH . '/framework/functions.php');
	rm_full_dir(WB_PATH . '/modules/phplib');
	
	if ( $errcount ) {
	    update_steps( 'update_111', $lang->translate('Update to LEPTON 1.1.1 (partially) failed!') );
	}
	else {
    	update_steps( 'update_111', $lang->translate('Update to LEPTON 1.1.1 successfull!') );
	}
    
}   // end function update_111()

/**
 *  update to v1.1.2
 */
function update_112 () {

	global $database, $lang;
	$errcount = 0;

	//  database modifications above 1.1.1
	$all = $database->query(" SELECT * from `" . TABLE_PREFIX . "users` limit 1");
	if ($all)
	{
	    $temp = $all->fetchRow(MYSQL_ASSOC);
	    if (array_key_exists("remember_key", $temp))
	    {
	        $database->query('ALTER TABLE `' . TABLE_PREFIX . 'users` DROP COLUMN `remember_key`');
	    }
	}
	if ( $database->is_error() ) { $errcount++; }

	$all = $database->query(" DELETE from `" . TABLE_PREFIX . "settings` WHERE name = 'smart_login'");
	if ( $database->is_error() ) { $errcount++; }

	/**
	 *  database modifications
	 */
	$database->query('UPDATE `' . TABLE_PREFIX . 'settings` SET `value` =\'1.1.2\' WHERE `name` =\'lepton_version\'');
	if ( $database->is_error() ) { $errcount++; }

	/**
	 *  run upgrade.php of all modified modules
	 *
	 */
	$upgrade_modules = array(
	    "news",
	    "initial_page",
	    "ckeditor",
	    "addon_file_editor",
	    "edit_area",
	    "jsadmin",
	    "menu_link",
	    "output_interface",
	    "pclzip",
	    "show_menu2",
	    "wrapper",
	    "phpmailer",
	    "wysiwyg_admin",
	    "lib_jquery"
	);

	upgrade_modules( $upgrade_modules );

    if ( $errcount ) {
	    update_steps( 'update_112', $lang->translate('Update to LEPTON 1.1.2 (partially) failed!') );
	}
	else {
		update_steps( 'update_112', $lang->translate('Update to LEPTON 1.1.2 successfull!') );
	}
	
}   // end function update_112 ()

/**
 *  update to v1.1.3
 */
function update_113 () {

	global $database, $lang;

	/**
	 *  run upgrade.php of all modified modules
	 *
	 */
	$upgrade_modules = array(
	    "ckeditor",
	    "news",
	    "code2",
	    "lib_jquery"
	);
    upgrade_modules( $upgrade_modules );

	/**
	 *  database modification
	 */
	$database->query('UPDATE `' . TABLE_PREFIX . 'settings` SET `value` =\'1.1.3\' WHERE `name` =\'lepton_version\'');

	update_steps( 'update_113', $lang->translate('Update to LEPTON 1.1.3 successfull!') );
	
}   // end function update_113 ()

/**
 *  update to v1.1.4
 */
function update_114 () {

	global $database, $lang;
	$errcount = 0;

	//delete leptoken debug file
	$temp_path = WB_PATH."/framework/__debug_token.txt";
	if (file_exists($temp_path)) {
		$result = unlink ($temp_path);
		if (false === $result) {
			echo "Cannot delete file ".$temp_path.". Please check file permissions and ownership or delete file manually.";
		}
	}

	/**
	 *	try to remove obsolete column 'license_text'
	 *  first check if the COLUMN `license_text` exists in the `addons` TABLE
	 */
	$checkDbTable = $database->query("SHOW COLUMNS FROM `".TABLE_PREFIX."addons` LIKE 'license_text'");
	$column_exists = $checkDbTable->numRows() > 0 ? TRUE : FALSE;

	if (true === $column_exists ) {
		$database->query('ALTER TABLE `' . TABLE_PREFIX . 'addons` DROP COLUMN `license_text`');
		if ( $database->is_error() ) { $errcount++; }
	}

	// import the droplet check-css
	if (file_exists(WB_PATH . '/modules/droplets/example/droplet_check-css.zip')) {
		include_once (WB_PATH . '/modules/droplets/functions.inc.php');
		wb_unpack_and_import(WB_PATH . '/modules/droplets/example/droplet_check-css.zip', WB_PATH . '/temp/unzip/');
	}

	/**
	 *  run upgrade.php of all modified modules
	 *
	 */
	$upgrade_modules = array(
	    "ckeditor",
	    "news",
	    "form",
	    "wrapper",
	    "wysiwyg",
	    "code2",
	    "droplets",
	    "captcha_control",
	    "lib_jquery"
	);

    upgrade_modules( $upgrade_modules );

	/**
	 *  database modification
	 */
	$database->query('UPDATE `' . TABLE_PREFIX . 'settings` SET `value` =\'1.1.4\' WHERE `name` =\'lepton_version\'');

    if ( $errcount ) {
	    update_steps( 'update_114', $lang->translate('Update to LEPTON 1.1.4 (partially) failed!') );
	}
	else {
		update_steps( 'update_114', $lang->translate('Update to LEPTON 1.1.4 successfull!') );
	}

}   // end function update_114 ()

/**
 * update to v2.0
 **/
function update_200 () {

	global $database, $lang;
	$errcount = 0;
	$msg      = array();

	//  database modifications
	$all = $database->query("SELECT * from `" . TABLE_PREFIX . "mod_wrapper` limit 1");
	if ($all)
	{
	    $temp = $all->fetchRow(MYSQL_ASSOC);
	    if (!array_key_exists("width", $temp))
	    {
	        $temp = $lang->translate( 'altering table: {{table}}', array( 'table' => 'mod_wrapper' ) );
	        $database->query('ALTER TABLE `' . TABLE_PREFIX . 'mod_wrapper` ADD COLUMN `width` int(11) NOT NULL DEFAULT \'0\'');
	        if ( $database->is_error() ) { $errcount++; $temp .= ' - failed!'; }
	        $msg[] = $temp;
	    }
	    if (!array_key_exists("wtype", $temp))
	    {
	        $temp = $lang->translate( 'altering table: {{table}}', array( 'table' => 'mod_wrapper' ) );
	        $database->query('ALTER TABLE `' . TABLE_PREFIX . 'mod_wrapper` ADD COLUMN `wtype` varchar(50) NOT NULL DEFAULT \'0\'');
	        if ( $database->is_error() ) { $errcount++; $temp .= ' - failed!'; }
	        $msg[] = $temp;
	    }
	}

	$all = $database->query("SELECT * from `" . TABLE_PREFIX . "pages` limit 1");
	if ($all)
	{
	    $temp = $all->fetchRow(MYSQL_ASSOC);
	    foreach( array( 'page_icon', 'menu_icon_0', 'menu_icon_1' ) as $field ) {
	    	if ( array_key_exists($field, $temp) )
	    	{
				$database->query('ALTER TABLE `' . TABLE_PREFIX . 'pages` DROP COLUMN `'.$field.'`');
			}
		}
	}

	/**
	 *  run upgrade.php of modified modules
	 *
	 */
	$upgrade_modules = array(
	    "ckeditor",
	    "lib_jquery",
	    "dropleps",
	);
	upgrade_modules( $upgrade_modules );
	
	// get server IP
    if (array_key_exists('SERVER_ADDR', $_SERVER)) {
	    $server_addr = $_SERVER['SERVER_ADDR'];
	} else {
	    $server_addr = '127.0.0.1';
	}
	
	/**
	 * upgrade the config.php
	 **/
	$config_content = "" .
"<?php\n".
"\n".
"if(defined('LEPTON_PATH')) { ".
"    die('By security reasons it is not permitted to load \'config.php\' twice!! ".
"Forbidden call from \''.\$_SERVER['SCRIPT_NAME'].'\'!'); }\n\n".
"// *****************************************************************************\n".
"// please set the path names for the Lepton backend subfolders here; that is,\n".
"// if you rename 'admins' to 'myadmin', for example, set 'LEPTON_ADMINS_PATH'\n".
"// to 'myadmin'.\n".
"// *****************************************************************************\n\n".
"// path to old (deprecated) admins subfolder; default name is 'admins'\n".
"define('LEPTON_ADMINS_FOLDER', '" . basename( ADMIN_URL ) . "');\n".
"// path to new admins subfolder; default name is 'backend'\n".
"define('LEPTON_BACKEND_FOLDER', 'backend');\n".
"// do not touch this line! It is set by the options tab in the backend!\n".
"define('LEPTON_BACKEND_PATH', '" . basename( ADMIN_URL ) . "' );\n".
"// *****************************************************************************\n\n".
"define('DB_TYPE', 'mysql');\n".
"define('DB_HOST', '".DB_HOST."');\n".
"define('DB_PORT', '".DB_PORT."');\n".
"define('DB_USERNAME', '".DB_USERNAME."');\n".
"define('DB_PASSWORD', '".DB_PASSWORD."');\n".
"define('DB_NAME', '".DB_NAME."');\n".
"define('TABLE_PREFIX', '".TABLE_PREFIX."');\n".
"\n".
"define('LEPTON_SERVER_ADDR', '".$server_addr."');\n".
"define('LEPTON_PATH', dirname(__FILE__));\n".
"define('LEPTON_URL', '".LEPTON_URL."');\n".
"define('ADMIN_PATH', LEPTON_PATH.'/'.LEPTON_BACKEND_PATH);\n".
"define('ADMIN_URL', LEPTON_URL.'/'.LEPTON_BACKEND_PATH);\n".
"\n".
"define('LEPTON_GUID', '".LEPTON_GUID."');\n".
"define('LEPTON_SERVICE_FOR', '".LEPTON_SERVICE_FOR."');\n".
"define('LEPTON_SERVICE_ACTIVE', ".LEPTON_SERVICE_ACTIVE.");\n".
"\n".
"// wb2 backward compatibility\n".
"include_once LEPTON_PATH.'/framework/wb2compat.php';\n".
"\n".
"if (!defined('LEPTON_INSTALL')) require_once(LEPTON_PATH.'/framework/initialize.php');\n".
"\n".
"?>";

    // Check if the file exists and is writable first.
    $config_filename = LEPTON_PATH.'/config.php';
	if(($handle = @fopen($config_filename, 'w')) === false) {
		$msg[] = $lang->translate(
			"Cannot open the configuration file ({{ file }})",
			array( 'file' => $config_filename )
		);
		$errcount++;
	} else {
		if (fwrite($handle, $config_content, strlen($config_content) ) === FALSE) {
			fclose($handle);
			$msg[] = $lang->translate(
				"Cannot write to the configuration file ({{ file }})",
				array( 'file' => $config_filename )
			);
			$errcount++;
		}
		// Close file
		fclose($handle);
	}
	
	$database->query('UPDATE `' . TABLE_PREFIX . 'settings` SET `value` =\'2.0\' WHERE `name` =\'lepton_version\'');

    if ( $errcount ) {
	    update_steps( 'update_200', implode('<br />', $msg ).'<br />'.$lang->translate('Update to LEPTON 2.0 (partially) failed!') );
	}
	else {
		update_steps( 'update_200', implode('<br />', $msg ).'<br />'.$lang->translate('Update to LEPTON 2.0 successfull!') );
	}
	
}   // end function update_200 ()

function upgrade_modules( $upgrade_modules ) {
	global $database, $admin;
	foreach ($upgrade_modules as $module)
	{
	    $temp_path = LEPTON_PATH . "/modules/" . $module . "/upgrade.php";
	    if (file_exists($temp_path))
	    {
	        require($temp_path);
		}
	}
}   // end function upgrade_modules()