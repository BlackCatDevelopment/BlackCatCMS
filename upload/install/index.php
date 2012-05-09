<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project <info@lepton-cms.org>
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @category        LEPTON_Core
 * @package         Installation
 * @version         $Id$
 */
 
$debug = false;

define('LEPTON_PATH',dirname(__file__).'/..');

// check wether to call update.php or start installation
if (file_exists('../config.php')) {
    include '/update/update.php';
    die();
}

define('LEPTON_INSTALL',true);
define('LOGFILE',dirname(__FILE__).'/../temp/inst.log');
define('WB_PATH',dirname(__file__).'/..');

// Start a session
if ( !defined( 'SESSION_STARTED' ) ) {
	session_name( 'lepton_session_id' );
	session_start();
	define( 'SESSION_STARTED', true );
}
//unset($_SESSION);
error_reporting(E_ALL^E_NOTICE);

// Try to guess installer URL
$installer_uri = 'http://' . $_SERVER[ "SERVER_NAME" ] . ( ( $_SERVER['SERVER_PORT'] != 80 ) ? ':'.$_SERVER['SERVER_PORT'] : '' ) . $_SERVER[ "SCRIPT_NAME" ];
$installer_uri = dirname( $installer_uri );

// *****************************************************************************
// pre installation check: global file system permissions
$dirs = array(
	'temp',
	'install'
);
$pre_inst_err   = array();
// check root folder; needed for config.php
if ( ! is_writable(dirname(__FILE__).'/..') )
{
    $pre_inst_err[] = 'The LEPTON base directory must be writable during installation!<br />Das LEPTON Basisverzeichnis muss während der Installation schreibbar sein!';
}
foreach( $dirs as $i => $dir )
{
    $path = dirname(__FILE__).'/../'.$dir;
    if ( ! is_writable($path) )
	{
	    $pre_inst_err[] = 'The ['.$dir.'] subfolder must be writable!<br />Das Verzeichnis ['.$dir.'] muss schreibbar sein!';
	}
}
if ( count($pre_inst_err) )
{
	pre_installation_error( implode( '<br /><br />', $pre_inst_err ) );
	exit;
}
// *****************************************************************************

// language helper
include dirname(__FILE__).'/../framework/LEPTON/Helper/I18n.php';
$lang = new LEPTON_Helper_I18n();

// the admin dummy defines some methods needed for module installation and error handling
include dirname(__FILE__).'/admin_dummy.inc.php';
$admin = new admin_dummy();

// user class for checking password
include dirname(__FILE__).'/../framework/LEPTON/Users.php';
$users  = new LEPTON_Users();

// directory helper
include dirname(__FILE__).'/../framework/LEPTON/Helper/Directory.php';
$dirh   = new LEPTON_Helper_Directory();


// *****************************************************************************
// define the steps we are going through
$steps = array(
	array( 'id' => 'intro',    'text' => $lang->translate('Welcome'),          'done' => false, 'success' => true , 'current' => true,  'errors' => NULL ),
	array( 'id' => 'precheck', 'text' => $lang->translate('Precheck'),         'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
	array( 'id' => 'globals',  'text' => $lang->translate('Global settings'),  'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
	array( 'id' => 'db',       'text' => $lang->translate('Database settings'),'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
	array( 'id' => 'site',     'text' => $lang->translate('Site settings'),    'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
	array( 'id' => 'postcheck','text' => $lang->translate('Postcheck'),        'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
	array( 'id' => 'finish',   'text' => $lang->translate('Finish'),           'done' => false, 'success' => false, 'current' => false, 'errors' => NULL ),
);
// *****************************************************************************

// current state is saved to a temp. file
if ( file_exists( dirname(__FILE__).'/steps.tmp' ) )
{
    $file  = implode( "\n", file( dirname(__FILE__).'/steps.tmp' ) );
    $steps = unserialize( $file );
}

// this is a helper for easy mapping of the current step to the steps array
$id_to_step_index = array();
foreach( $steps as $i => $step ) {
    $id_to_step_index[$step['id']] = $i;
}

// template engine; creates a global var $parser
global $parser;
if ( file_exists(dirname(__FILE__).'/../modules/lib_dwoo/library.php') )
{
	require_once dirname(__FILE__).'/../modules/lib_dwoo/library.php';
}
$parser->setPath( dirname(__FILE__).'/templates/default' );

// set some globals
$parser->setGlobals(
	array(
	    'installer_uri' => $installer_uri,
	)
);

// get the current step
$this_step = 'intro';
if ( isset($_REQUEST['btn_back']) )
{
	$this_step = $_REQUEST['prevstep'];
}
elseif ( isset($_REQUEST['btn_next']) )
{
	$this_step = $_REQUEST['nextstep'];
}
elseif ( isset($_REQUEST['goto']) ) {
    $this_step = $_REQUEST['goto'];
}
$parser->setGlobals(
	array(
	    'this_step' => $this_step,
	)
);

// let's see if we have some stored data from previous steps or installations
if ( file_exists( dirname(__FILE__).'/instdata.tmp' ) )
{
    $file   = implode( "\n", file( dirname(__FILE__).'/instdata.tmp' ) );
    $config = unserialize( $file );
}
else {
    $config = array();
}

if ( isset($config['lepton_url']) && $config['lepton_url'] != '' )
{
    $parser->setGlobals(
		array(
		    'lepton_url' => $config['lepton_url'],
		)
	);
}

// call the check-method for last step (if any)
if ( isset($_REQUEST['laststep']) )
{
	// save the form data into temp file
	foreach( $_REQUEST as $key => $value )
	{
	    if ( preg_match( '~^installer_(.*)$~i', $key, $match ) )
	    {
	        $_SESSION[$key] = $value;
	        $key            = $match[1];
	        $config[$key]   = $value;
	    }
	}
	if ( function_exists( 'check_step_'.$_REQUEST['laststep'] ) )
	{
		$callback = 'check_step_'.$_REQUEST['laststep'];
		list( $ok, $errors ) = $callback();
		if ( ! $ok ) {
		    $this_step = $_REQUEST['laststep'];
			$steps[$id_to_step_index[$this_step]]['errors'] = $errors;
		}
	}
	if ( false !== ( $fh = fopen( dirname(__FILE__).'/instdata.tmp', 'w' ) ) )
	{
	    fwrite( $fh, serialize($config) );
	    fclose( $fh );
	}
}

// reset the 'current' marker for all steps
foreach( $steps as $i => $step ) {
	$steps[$i]['current'] = false;
}

foreach( $steps as $i => $step ) {
	// set the 'done' marker for all steps < current
	$steps[$i]['done']    = true;
	// for current step...
	if ( $step['id'] == $this_step ) {
	    // reset errors for this step
	    $steps[$i]['errors']     = NULL;
	    // do we have a presentation method for this step?
	    $callback = 'show_step_'.$step['id'];
	    if ( function_exists($callback) ) {
	        list( $result, $output ) = $callback( $step );
    		$steps[$i]['success']    = $result;
	    }
	    // set 'current' marker for this step
    	$steps[$i]['current'] = true;
    	// reset 'done' marker for this step
    	$steps[$i]['done']    = false;
    	// find next and previous steps
    	if ( $i < count($steps)-1 ) {
    		$nextstep    =& $steps[$i+1];
		}
		if ( $i > 0 ) {
    		$prevstep    =& $steps[$i-1];
		}
    	$currentstep =& $steps[$i];
    	// leave the rest as-is
    	break;
	}
}

// save the current state to temp. file
if ( false !== ( $fh = fopen( dirname(__FILE__).'/steps.tmp', 'w' ) ) )
{
    fwrite( $fh, serialize($steps) );
    fclose( $fh );
}

// print the page
if ( ! $output ) {
	// default page = step 0
	$tpl = 'welcome.lte';
	if ( file_exists( dirname(__FILE__).'/templates/default/welcome_'.$lang->getLang().'.lte' ) )
	{
	    $tpl = 'welcome_'.$lang->getLang().'.lte';
	}
	$output = $parser->get( $tpl,array());
}

$parser->output(
	'index.lte',
	array(
	    'debug'             => $debug,
	    'steps'             => $steps,
	    'nextstep'          => $nextstep['id'],
	    'prevstep'          => $prevstep['id'],
	    'status'            => ( $currentstep['success'] ? true : false ),
	    'output'            => $output,
	    'this_step'         => $this_step,
	    'dump'              => print_r( array( $this_step, $_REQUEST, $prevstep, $nextstep, $currentstep, $steps ), 1 ),
	)
);

/**
 * check the basic prerequisites for the LEPTON installation; uses
 * precheck.php to do this. Returns the result of preCheckAddon() method
 **/
function show_step_precheck() {

	global $lang, $parser, $installer_uri;
	$ok   = true;

	// precheck.php
	include dirname(__FILE__).'/../framework/LEPTON/Helper/Addons.php';
	$addons = new LEPTON_Helper_Addons();
	$result = $addons->preCheckAddon( NULL, dirname(__FILE__), false, true );
	$parser->setPath( dirname(__FILE__).'/templates/default' );
	$result = $parser->get(
	    'precheck.lte',
	    array( 'output' => $result )
	);
	
	// scan the HTML for errors; this is easier than to extend the methods in
	// the Addons helper
	if ( preg_match( '~class=\"fail~i', $result, $match ) ) {
		$ok = false;
	}
	
	$install_dir = pathinfo( dirname(__FILE__), PATHINFO_BASENAME );

	// file permissions check
	$dirs = array(
	    array( 'name' => '', 'ok' => false ),
		array( 'name' => 'page', 'ok' => false ),
		array( 'name' => 'media', 'ok' => false ),
		array( 'name' => 'templates', 'ok' => false ),
		array( 'name' => 'modules', 'ok' => false ),
		array( 'name' => 'languages', 'ok' => false ),
		array( 'name' => 'temp', 'ok' => false ),
	);
	foreach( $dirs as $i => $dir ) {
	    $path           = dirname(__FILE__).'/../'.$dir['name'];
        $dirs[$i]['ok'] = is_writable($path);
	    if ( $dir['name'] == '' ) {
	        $dirs[$i]['name'] = $lang->translate('LEPTON root directory');
	    }
		else {
		    $dirs[$i]['name'] = '/'.$dirs[$i]['name'].'/';
		}
		if ( $dirs[$i]['ok'] === false ) {
		    $ok = false;
		}
	}
	
	// special check for install dir (must be world writable)
	$inst_is_writable = is_writable(dirname(__FILE__)); //( substr(sprintf('%o', fileperms(dirname(__FILE__))), -1) == 7 ? true : false );
	if ( ! $inst_is_writable ) {
	    $ok = false;
	};
	$dirs[] = array( 'name' => $lang->translate('LEPTON installation directory') . ' (' . $install_dir . ')', 'ok' => $inst_is_writable );

	$output = $parser->get(
		'fperms.lte',
		array(
		    'dirs'   => $dirs,
		    'ok'     => $ok,
		    'result' => (
				$ok
				? $lang->translate('All checks succeeded!')
				: $lang->translate('Sorry, we encountered some issue(s) that will inhibit the installation. Please check the results above and fix the issue(s) listed there.')
			)
		)
	);
	
	return array( $ok, $result.$output );
	
}   // end function show_step_precheck()

/**
 * global settings
 **/
function show_step_globals( $step ) {

    global $lang, $parser, $installer_uri, $config;
    global $timezone_table;

    include dirname(__FILE__).'/../framework/timezones.php';
    if ( !isset( $config['default_timezone_string' ] ) ) {
        if (date_default_timezone_get()) {
		    $config['default_timezone_string'] = date_default_timezone_get();
		}
		elseif ( ini_get('date.timezone') ) {
		    $config['default_timezone_string'] = ini_get('date.timezone');
		}
		else {
			$config['default_timezone_string'] = "Europe/Berlin";
		}
	}

	$lang_dir = "../languages/";
	$dir      = dir( $lang_dir );
	$langs    = array();
	
	while( $temp_file = $dir->read() ) {
		if ($temp_file[0] == ".") continue;
		if ($temp_file == "index.php" ) continue;
		$str = file( $lang_dir.$temp_file );
		$language_name = "";
		foreach($str as $line) {
			if (strpos( $line, "language_name") != false) {
				eval ($line);
				break;
			}
		}
		$lang_short = pathinfo( $temp_file, PATHINFO_FILENAME );
		$langs[$lang_short] = $language_name;

	}
	
	$dir->close();
	ksort($langs);
	
	if ( !isset( $config['default_language' ] ) ) {
	    $config['default_language' ] = $lang->getLang();
	}
	
	// operating system
	// --> FrankH: Detect OS
	$ctrue  = " checked='checked'";
	$cfalse = "";
	if ( substr( php_uname( 's' ), 0, 7 ) == "Windows" ) {
		$osw        = $ctrue;
		$osl        = $cfalse;
		$startstyle = "none";
	} else {
		$osw        = $cfalse;
		$osl        = $ctrue;
		$startstyle = "block";
	}
	// <-- FrankH: Detect OS
	
    $output = $parser->get(
        'globals.lte',
        array(
			'installer_lepton_url' 				=> dirname( $installer_uri ).'/',
			'timezones'  						=> $timezone_table,
			'installer_default_timezone_string' => $config['default_timezone_string'],
			'languages' 						=> $langs,
			'installer_default_language'		=> $config['default_language'],
			'is_linux'   						=> $osl,
            'is_windows' 						=> $osw,
            'errors'            				=> $step['errors'],
		)
	);
	return array( true, $output );
}   // end function show_step_globals()

/**
 *
 **/
function check_step_globals() {
	global $config, $lang;
	$errors = array();
	if ( ! isset($config['lepton_url']) || $config['lepton_url'] == '' )
	{
	    $errors['installer_lepton_url'] = $lang->translate('Please insert the base URL!');
	}
	return array(
		( count($errors) ? false : true ),
		$errors
	);
}   // end function check_step_globals()

/**
 * database settings
 **/
function show_step_db( $step ) {
    global $parser, $config;
    $output = $parser->get(
        'db.lte',
        array(
            'installer_database_host'     => ( isset($config['database_host'])     ? $config['database_host']     : 'localhost'    ),
            'installer_database_port'     => ( isset($config['database_port'])     ? $config['database_port']     : '3306'         ),
            'installer_database_username' => ( isset($config['database_username']) ? $config['database_username'] : 'my-user-name' ),
            'installer_database_password' => ( isset($config['database_password']) ? $config['database_password'] : ''             ),
            'installer_database_name' 	  => ( isset($config['database_name'])     ? $config['database_name'] 	  : 'my-db-name'   ),
            'installer_table_prefix'      => ( isset($config['table_prefix'])      ? $config['table_prefix']      : 'lep_'         ),
            'installer_install_tables'    => ( isset($config['install_tables'])    ? $config['install_tables']    : 'y'            ),
            'installer_empty_db_password' => ( isset($config['empty_db_password']) ? $config['empty_db_password'] : ''             ),
            'errors'            		  => $step['errors']
		)
	);
	return array( true, $output );
}   // end function show_step_db()

/**
 * check the db connection
 **/
function check_step_db() {
	// do not check if back button was clicked
	if ( isset($_REQUEST['btn_back']) ) {
	    return array( true, array() );
	}
	$errors = __lep_check_db_config();
	return array(
		( count($errors) ? false : true ),
		$errors
	);
}   // end function check_step_db()

/**
 *
 **/
function show_step_site( $step ) {
	global $lang, $config, $parser;
	$output = $parser->get(
        'site.lte',
        array(
            'installer_website_title'    => ( isset($config['website_title'])    ? $config['website_title']    : 'LEPTON CMS' ),
            'installer_admin_username'   => ( isset($config['admin_username'])   ? $config['admin_username']   : ''    		  ),
            'installer_admin_password'   => ( isset($config['admin_password'])   ? $config['admin_password']   : ''    		  ),
            'installer_admin_repassword' => ( isset($config['admin_repassword']) ? $config['admin_repassword'] : ''    		  ),
            'installer_admin_email' 	 => ( isset($config['admin_email'])  	 ? $config['admin_email']      : ''    		  ),
            'installer_backend_theme' 	 => ( isset($config['backend_theme'])    ? $config['backend_theme']    : 'algos'      ),
			'errors' => $step['errors']
		)
	);
	return array( true, $output );
}   // end function show_step_site()

/**
 *
 **/
function check_step_site() {
	global $lang, $config, $users, $parser;
	// do not check if back button was clicked
	if ( isset($_REQUEST['btn_back']) ) {
	    return array( true, array() );
	}
	$errors = array();
	if ( ! isset($config['website_title']) || $config['website_title'] == '' ) {
	    $errors['installer_website_title'] = $lang->translate( 'Please enter a website title!' );
	}
	
	// check admin user name
	if ( ! isset($config['admin_username']) || $config['admin_username'] == '' ) {
	    $errors['installer_admin_username'] = $lang->translate( 'Please enter an admin username (choose "admin", for example)!' );
	}
	else {
	    if ( strlen($config['admin_username']) < 3 ) {
	        $errors['installer_admin_username'] = $lang->translate('Name too short! The admin username should be at least 3 chars long.');
	    }
	    elseif ( ! preg_match( '/^[a-z0-9][a-z0-9_-]+$/i', $config['admin_username'] ) ) {
	        $errors['installer_admin_username'] = $lang->translate('Only characters a-z, A-Z, 0-9 and _ allowed in admin username');
	    }
	}
	
	// check admin password
	if ( ! isset($config['admin_password']) || $config['admin_password'] == '' ) {
	    $errors['installer_admin_password'] = $lang->translate( 'Please enter an admin password!' );
	}
	if ( ! isset($config['admin_repassword']) || $config['admin_repassword'] == '' ) {
	    $errors['installer_admin_repassword'] = $lang->translate( 'Please retype the admin password!' );
	}
	if (
		   isset($config['admin_password']) && isset($config['admin_repassword'])
        && $config['admin_password'] != ''  && $config['admin_repassword'] != ''
		&& strcmp( $config['admin_password'], $config['admin_repassword'] )
	) {
	    $errors['installer_admin_password']   = $lang->translate( 'The admin passwords you have given do not match!' );
	    $errors['installer_admin_repassword'] = $lang->translate( 'The admin passwords you have given do not match!' );
	}

    if ( ! $users->validatePassword($config['admin_password']) )
    {
		$errors['installer_admin_password'] = $lang->translate('Invalid password!')
											. ' (' . $users->getPasswordError() . ')';
	}
	
	// check admin email address
	if ( ! isset($config['admin_email']) || $config['admin_email'] == '' ) {
	    $errors['installer_admin_email'] = $lang->translate( 'Please enter an email address!' );
	}
	else {
		if ( ! preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i', $config['admin_email'] ) ) {
			$errors['installer_admin_email'] = $lang->translate('Please enter a valid email address for the Administrator account');
		}
	}
	
	return array(
		( count($errors) ? false : true ),
		$errors
	);
}   // end function check_step_site()

/**
 *
 **/
function show_step_postcheck() {
	global $lang, $config, $parser;
	foreach ( $config as $key => $value ) {
	    if ( preg_match( '~password~i', $key ) ) {
	        $config[$key] = '********';
		}
		if ( preg_match( '~repassword~i', $key ) ) {
		    unset($config[$key]);
		}
	}
	$output = $parser->get(
        'postcheck.lte',
        array( 'config' => $config )
	);
	return array( true, $output );
}   // end function show_step_postcheck()

/**
 *
 **/
function show_step_finish() {
	global $lang, $parser, $installer_uri, $config;
	list( $result, $output ) = __do_install();
	if ( ! $result ) {
	    return array( true, $output );
	}
	$tpl = 'finish.lte';
	if ( file_exists( dirname(__FILE__).'/templates/default/finish_'.$lang->getLang().'.lte' ) )
	{
	    $tpl = 'finish_'.$lang->getLang().'.lte';
	}
	$output = $parser->get(
		$tpl,
		array(
			'backend_path'  => ( $config['backend_theme'] == 'freshcat' ? 'backend' : 'admins' ),
			'installer_uri' => $installer_uri,
		)
	);
	return array( true, $output );
}   // function show_step_finish()


/*******************************************************************************
 *                 HELPER FUNCTIONS
 ******************************************************************************/

function __lep_check_db_config() {

	global $lang, $users, $config;
	
	$errors = array();
	$regexp = '/^[^\x-\x1F]+$/D';

	// Check if user has entered a database host
	if ( ! isset( $config['database_host'] ) || $config['database_host'] == '' )
	{
		$errors['installer_database_host'] = $lang->translate('Please enter a database host name');
	}
	else
	{
		if ( preg_match( $regexp, $config['database_host'], $match ) )
		{
			$database_host = $match[0];
		}
		else
		{
			$errors['installer_database_host'] = $lang->translate('Invalid database hostname!');
		}
	}

	// check for valid port number
	if ( !isset( $config['database_port'] ) || $config['database_port'] == '' )
	{
		$errors['installer_database_port'] = $lang->translate('Please enter a database port');
	}
	else
	{
		if ( is_numeric( $config['database_port'] ) )
		{
			$database_port = $config['database_port'];
		}
		else
		{
			$errors['installer_database_port'] = $lang->translate('Invalid port number!');
		}
	}
	// Check if user has entered a database username
	if ( !isset( $config['database_username'] ) || $config['database_username'] == '' )
	{
		$errors['installer_database_username'] = $lang->translate('Please enter a database username');
	}
	else
	{
		if ( preg_match( $regexp, $config['database_username'], $match ) )
		{
			$database_username = $match[0];
		}
		else
		{
			$errors['installer_database_username'] = $lang->translate('Invalid database username!');
		}
	}
	// Check if user has entered a database password
	if ( !isset( $config['database_password'] ) || $config['database_password'] == '' )
	{
		$database_password = '';
		if ( ! isset($config['empty_db_password']) )
		{
            $errors['installer_database_password_empty'] = true;
		}
	}
	else
	{
	    if ( ! $users->validatePassword($config['database_password']) )
	    {
			$errors['installer_database_password'] = $lang->translate('Invalid database password!')
												   . ' ' . $users->getPasswordError();
		}
		else
		{
		    $database_password = $users->getLastValidatedPassword();
		}
	}

	// Check if user has entered a database name
	if ( !isset( $config['database_name'] ) || $config['database_name'] == '' )
	{
		$errors['installer_database_name'] = $lang->translate('Please enter a database name');
	}
	else
	{
		// make sure only allowed characters are specified; it is not allowed to
		// have a DB name with digits only!
		if ( preg_match( '/^[a-z0-9][a-z0-9_-]+$/i', $config['database_name'] ) && ! is_numeric($config['database_name']) )
		{
			$database_name = $config['database_name'];
		}
		else
		{
			// contains invalid characters (only a-z, A-Z, 0-9 and _ allowed to avoid problems with table/field names)
			$errors['installer_database_name'] = $lang->translate('Only characters a-z, A-Z, 0-9, - and _ allowed in database name. Please note that a database name must not be composed of digits only.');
		}
	}
	// table prefix
	if ( isset($config['table_prefix']) && $config['table_prefix'] != '' && ! preg_match('/^[a-z0-9_]+$/i', $config['table_prefix']) ) {
	    $errors['installer_table_prefix'] = $lang->translate('Only characters a-z, A-Z, 0-9 and _ allowed in table_prefix.');
	}

	if ( !count( $errors ) )
	{
		// check database connection
		$host = ( $database_port !== '3306' ) ? $database_host . ':' . $database_port : $database_host;
		$ret  = @mysql_connect( $host, $database_username, $database_password );
		if ( ! is_resource($ret) )
		{
			$errors['global'] = $lang->translate('Unable to connect to the database! Please check your settings!');
		}
	}

	return $errors;

}   // end function __lep_check_db_config()

/**
 * find the default permissions for new files
 **/
function default_file_mode() {
	// we've already created some new files, so just check the perms they've got
	if ( file_exists( dirname(__FILE__).'/steps.tmp' ) ) {
	    $filename = dirname(__FILE__).'/steps.tmp';
		$default_file_mode = '0'.substr(sprintf('%o', fileperms($filename)), -3);
	} else {
		$default_file_mode = '0777';
	}
	return $default_file_mode;
}   // end function default_file_mode()

/**
 * find the default permissions for new directories by creating a test dir
 **/
function default_dir_mode($temp_dir) {
	if(is_writable($temp_dir)) {
		$dirname = $temp_dir.'/test_permissions/';
		mkdir($dirname);
		$default_dir_mode = '0'.substr(sprintf('%o', fileperms($dirname)), -3);
		rmdir($dirname);
	} else {
		$default_dir_mode = '0777';
	}
	return $default_dir_mode;
}   // end function default_dir_mode()

/**
 * install tables
 **/
function install_tables ($database) {

	global $config ;

	$errors = array();

	if (!defined('LEPTON_INSTALL_PROCESS')) define ('LEPTON_INSTALL_PROCESS', true);

	// ----- Remove tables -----

	// Pages table
	$pages = "DROP TABLE IF EXISTS `".TABLE_PREFIX."pages`";
	$database->query($pages);
	// Sections table
	$sections = "DROP TABLE IF EXISTS `".TABLE_PREFIX."sections`";
	$database->query($sections);
	// Settings table
	$settings = "DROP TABLE IF EXISTS `".TABLE_PREFIX."settings`";
	$database->query($settings);
	// Users table
	$users = "DROP TABLE IF EXISTS `".TABLE_PREFIX."users`";
	$database->query($users);
	// Groups table
	$groups = "DROP TABLE IF EXISTS `".TABLE_PREFIX."groups`";
	$database->query($groups);
	// Addons table
	$addons = "DROP TABLE IF EXISTS `".TABLE_PREFIX."addons`";
	$database->query($addons);

	// force db to utf-8
	$database->query("ALTER DATABASE `".DB_NAME."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
	
	// open log file
	$logh = fopen( LOGFILE, 'a' );

	// ----- Install tables -----

	// Pages table
	$pages = 'CREATE TABLE `'.TABLE_PREFIX.'pages` ( '
		   . ' `page_id` INT NOT NULL auto_increment,'
	       . ' `parent` INT NOT NULL DEFAULT \'0\','
	       . ' `root_parent` INT NOT NULL DEFAULT \'0\','
	       . ' `level` INT NOT NULL DEFAULT \'0\','
	       . ' `link` TEXT NOT NULL,'
	       . ' `target` VARCHAR( 7 ) NOT NULL DEFAULT \'\' ,'
	       . ' `page_title` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `menu_title` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `description` TEXT NOT NULL ,'
	       . ' `keywords` TEXT NOT NULL ,'
	       . ' `page_trail` TEXT NOT NULL  ,'
	       . ' `template` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `visibility` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `position` INT NOT NULL DEFAULT \'0\','
	       . ' `menu` INT NOT NULL DEFAULT \'0\','
	       . ' `language` VARCHAR( 5 ) NOT NULL DEFAULT \'\' ,'
	       . ' `searching` INT NOT NULL DEFAULT \'0\','
	       . ' `admin_groups` TEXT NOT NULL ,'
	       . ' `admin_users` TEXT NOT NULL ,'
	       . ' `viewing_groups` TEXT NOT NULL ,'
	       . ' `viewing_users` TEXT NOT NULL ,'
	       . ' `modified_when` INT NOT NULL DEFAULT \'0\','
	       . ' `modified_by` INT NOT NULL  DEFAULT \'0\','
	       . ' PRIMARY KEY ( `page_id` ) '
	       . ' )';
	$database->query($pages);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['pages'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [pages]'."\n" );
	}

	// Sections table
	$sections = 'CREATE TABLE `'.TABLE_PREFIX.'sections` ( '
		   . ' `section_id` INT NOT NULL auto_increment,'
	       . ' `page_id` INT NOT NULL DEFAULT \'0\','
	       . ' `position` INT NOT NULL DEFAULT \'0\','
	       . ' `module` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `block` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `publ_start` VARCHAR( 255 ) NOT NULL DEFAULT \'0\' ,'
	       . ' `publ_end` VARCHAR( 255 ) NOT NULL DEFAULT \'0\' ,'
	       . ' `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'no name\' ,'
	       . ' PRIMARY KEY ( `section_id` ) '
	       . ' )';
	$database->query($sections);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['sections'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [sections]'."\n" );
	}

	include(ADMIN_PATH.'/interface/version.php');
	// Settings table
	$settings='CREATE TABLE `'.TABLE_PREFIX.'settings` ( '
		   . '`setting_id` INT NOT NULL auto_increment,'
		   . ' `name` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
		   . ' `value` TEXT NOT NULL ,'
		   . ' PRIMARY KEY ( `setting_id` ) '
		   . ' )';
	$database->query($settings);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['settings'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [settings]'."\n" );
	}

	// Users table
	$users = 'CREATE TABLE `'.TABLE_PREFIX.'users` ( '
		   . ' `user_id` INT NOT NULL auto_increment,'
	       . ' `group_id` INT NOT NULL DEFAULT \'0\','
	       . ' `groups_id` VARCHAR( 255 ) NOT NULL DEFAULT \'0\','
	       . ' `active` INT NOT NULL DEFAULT \'0\','
		   . ' `statusflags` INT NOT NULL DEFAULT \'6\','
	       . ' `username` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `password` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `last_reset` INT NOT NULL DEFAULT \'0\','
	       . ' `display_name` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `email` TEXT NOT NULL ,'
	       . " `timezone_string` VARCHAR( 50 ) NOT NULL DEFAULT '".$config['default_timezone_string']."',"
	       . ' `date_format` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `time_format` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `language` VARCHAR( 5 ) NOT NULL DEFAULT \'' .$config['default_language'] .'\' ,'
	       . ' `home_folder` TEXT NOT NULL ,'
	       . ' `login_when` INT NOT NULL  DEFAULT \'0\','
	       . ' `login_ip` VARCHAR( 15 ) NOT NULL DEFAULT \'\' ,'
	       . ' PRIMARY KEY ( `user_id` ) '
	       . ' )';
	$database->query($users);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['users'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [users]'."\n" );
	}

	// Groups table
	$groups = 'CREATE TABLE `'.TABLE_PREFIX.'groups` ( '
		   . ' `group_id` INT NOT NULL auto_increment,'
	       . ' `name` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
	       . ' `system_permissions` TEXT NOT NULL ,'
	       . ' `module_permissions` TEXT NOT NULL ,'
	       . ' `template_permissions` TEXT NOT NULL ,'
	       . ' PRIMARY KEY ( `group_id` ) '
	       . ' )';
	$database->query($groups);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['groups'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [groups]'."\n" );
	}

	// Addons table
	$addons = 'CREATE TABLE `'.TABLE_PREFIX.'addons` ( '
			.'`addon_id` INT NOT NULL auto_increment,'
			.'`type` VARCHAR( 128 ) NOT NULL DEFAULT \'\' ,'
			.'`directory` VARCHAR( 128 ) NOT NULL DEFAULT \'\' ,'
			.'`name` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.'`description` TEXT NOT NULL ,'
			.'`function` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.'`version` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.'`guid` VARCHAR( 50 ) NOT NULL,'
			.'`platform` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.'`author` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.'`license` VARCHAR( 255 ) NOT NULL DEFAULT \'\' ,'
			.' PRIMARY KEY (`addon_id`), '
			.' UNIQUE KEY `type` (`type`,`directory`) '
			.' )';
			
	$database->query($addons);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['addons'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'created table [addons]'."\n" );
	}
	
	fclose($logh);
	
	return array(
		( count($errors) ? false : true ),
		$errors
	);

}   // end function install_tables()

/**
 * INSTALLATION GOES HERE!!!
 **/
function __do_install() {

	global $config, $parser, $dirh;

	include dirname(__FILE__).'/../framework/functions.php';
	$lepton_path = sanitize_path( dirname(__FILE__).'/..' );
	$inst_path   = sanitize_path( $lepton_path.'/'.pathinfo( dirname(__FILE__), PATHINFO_BASENAME ) );

	if( isset($config['install_tables']) && $config['install_tables'] == 'true' ) {
		$install_tables = true;
	} else {
		$install_tables = false;
	}

	// get server IP
    if (array_key_exists('SERVER_ADDR', $_SERVER)) {
	    $server_addr = $_SERVER['SERVER_ADDR'];
	} else {
	    $server_addr = '127.0.0.1';
	}

	// create a new GUID for this installation
	$lepton_guid = createGUID();
	// define service vars
	$lepton_service_for = '';
	$lepton_service_active = 0;

	// check if file lepton.info exists within installation path
	if (file_exists($lepton_path.'/'.$inst_path.'/lepton.info')) {
		// read lepton.info into an array
	  if (false !== ($lepton_info = file($lepton_path.'/'.$inst_path.'/lepton.info'))) {
	    // walk through array
	    foreach ($lepton_info as $item) {
	      if (strpos($item, '=') !== false) {
	        // split string into key and value
	        list($key, $value) = explode('=', $item);
					$key = strtolower(trim($key));
	      	if (in_array($key, array('$lepton_guid', '$lepton_service_for', '$lepton_service_active'))) {
	      		// get Lepton service values
	      		$value = str_replace(array(' ', ';', "'", '"'), '', trim($value));
	      		switch ($key):
	      		case '$lepton_service_for':
	      			if (!empty($value)) $lepton_service_for = $value;
	      			break;
	      		case '$lepton_service_active':
	      			if (!empty($value) && is_numeric($value)) $lepton_service_active = intval($value);
	      			break;
	      		case '$lepton_guid':
	      			if ((strlen($value) == 36) && (substr_count($value, '-') == 4)) $lepton_guid = $value;
	      			break;
	      		endswitch;
	      	}
	      }
	    }
	  }
	}
	
	// remove trailing /
	$config_lepton_url = rtrim( $config['lepton_url'], '/' );

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
"define('LEPTON_ADMINS_FOLDER', 'admins');\n".
"// path to new admins subfolder; default name is 'backend'\n".
"define('LEPTON_BACKEND_FOLDER', 'backend');\n".
"// do not touch this line! It is set by the options tab in the backend!\n".
"define('LEPTON_BACKEND_PATH', ".($config['backend_theme'] == 'freshcat' ? 'LEPTON_BACKEND_FOLDER' : 'LEPTON_ADMINS_FOLDER')." );\n".
"// *****************************************************************************\n\n".
"define('DB_TYPE', 'mysql');\n".
"define('DB_HOST', '".$config['database_host']."');\n".
"define('DB_PORT', '".$config['database_port']."');\n".
"define('DB_USERNAME', '".$config['database_username']."');\n".
"define('DB_PASSWORD', '".$config['database_password']."');\n".
"define('DB_NAME', '".$config['database_name']."');\n".
"define('TABLE_PREFIX', '".$config['table_prefix']."');\n".
"\n".
"define('LEPTON_SERVER_ADDR', '".$server_addr."');\n".
"define('LEPTON_PATH', dirname(__FILE__));\n".
"define('LEPTON_URL', '".$config_lepton_url."');\n".
"define('ADMIN_PATH', LEPTON_PATH.'/'.LEPTON_BACKEND_PATH);\n".
"define('ADMIN_URL', LEPTON_URL.'/'.LEPTON_BACKEND_PATH);\n".
"\n".
"define('LEPTON_GUID', '".$lepton_guid."');\n".
"define('LEPTON_SERVICE_FOR', '".$lepton_service_for."');\n".
"define('LEPTON_SERVICE_ACTIVE', ".$lepton_service_active.");\n".
"\n".
"// wb2 backward compatibility\n".
"include_once LEPTON_PATH.'/framework/wb2compat.php';\n".
"\n".
"if (!defined('LEPTON_INSTALL')) require_once(LEPTON_PATH.'/framework/initialize.php');\n".
"\n".
"?>";

	$config_filename = $lepton_path.'/config.php';

	// Check if the file exists and is writable first.
	if(($handle = @fopen($config_filename, 'w')) === false) {
		return array(
			false,
			$lang->translate(
				"Cannot open the configuration file ({{ file }})",
				array( 'file' => $config_filename )
			)
		);
	} else {
		if (fwrite($handle, $config_content, strlen($config_content) ) === FALSE) {
			fclose($handle);
			return array(
			    false,
				$lang->translate(
					"Cannot write to the configuration file ({{ file }})",
					array( 'file' => $config_filename )
				)
			);
		}
		// Close file
		fclose($handle);
	}

	// avoid to lead config.php here
	if ( ! defined('LEPTON_PATH') ) 		  { define('LEPTON_PATH',$lepton_path);                    }
	if ( ! defined('LEPTON_ADMINS_FOLDER') )  { define('LEPTON_ADMINS_FOLDER', '/admins');             }
	if ( ! defined('LEPTON_BACKEND_FOLDER') ) { define('LEPTON_BACKEND_FOLDER', '/backend');           }
	if ( ! defined('LEPTON_BACKEND_PATH') )   { define('LEPTON_BACKEND_PATH', ( $config['backend_theme'] == 'freshcat' ? LEPTON_BACKEND_FOLDER : LEPTON_ADMINS_FOLDER ) );  }
	if ( ! defined('ADMIN_PATH') )  		  { define('ADMIN_PATH', LEPTON_PATH.LEPTON_BACKEND_PATH); }
	if ( ! defined('ADMIN_URL') )   		  { define('ADMIN_URL', LEPTON_URL.LEPTON_BACKEND_PATH);   }

	foreach( $config as $key => $value ) {
		if ( ! defined( strtoupper($key) ) )
		{
			define( str_replace( 'DATABASE_', 'DB_', strtoupper($key) ),$value);
		}
	}

	// WB compatibility
	if ( ! defined('WB_URL') 	  ) { define('WB_URL',$config['lepton_url']); 	  }
	if ( ! defined('WB_PATH')     ) { define('WB_PATH',$lepton_path); 			  }

 	require $lepton_path.'/framework/class.login.php';
	$database = new database();

	// remove old inst.log
	@unlink( LOGFILE );

	// ---- install tables -----
	if ( $install_tables ) {
		list ( $result, $errors ) = install_tables($database);
		// only try to fill tables if the creation succeeded
		if ( $result && ! count($errors) ) {
			// ----- fill tables -----
			list ( $result, $fillerrors ) = fill_tables($database,$lepton_guid);
			if ( ! $result || count($fillerrors) ) {
				$errors['populate tables'] = $fillerrors;
			}
			// only try to install modules if fill tables succeeded
			else {
				// ----- install addons -----
				list ( $result, $insterrors ) = install_modules($lepton_path);
				if ( ! $result || count($insterrors) ) {
					$errors['install modules'] = $insterrors;
				}
				// only check if all above succeeded
				else {
				    // ----- check tables ----
					list ( $result, $checkerrors ) = check_tables($database);
					if ( ! $result || count($checkerrors) ) {
						$errors['check tables'] = $checkerrors;
					}
					else {
						create_default_page($database);
					}
				}
			}
		}
	}
	
	// ---- set index.php to read only ----
	$dirh->setReadOnly( $lepton_path.'/index.php' );
	
	// ---- make sure we have an index.php everywhere ----
	$dirh->recursiveCreateIndex( $lepton_path );

	if ( count($errors) )
	{
		$output = $parser->get(
	        'install_errors.lte',
	        array( 'errors' => $errors )
		);
		return array(
			( count($errors) ? false : true ),
			$output
		);
	}
	else {
	    return array ( true, '' );
	}

}   // end function __do_install()

/**
 * fills the tables created by install_tables()
 **/
function fill_tables($database,$lepton_guid) {

	global $config;
	
	$errors = array();
	
	// create a random session name
	list($usec,$sec) = explode(' ',microtime());
	srand((float)$sec+((float)$usec*100000));
	$session_rand = rand(1000,9999);
	
	// Work-out file permissions
	if( $config['operating_system'] == 'windows') {
		$file_mode = '0644';
		$dir_mode = '0755';
	} elseif( isset($config['world_writeable']) && $config['world_writeable'] == 'true' ) {
		$file_mode = '0666';
		$dir_mode = '0777';
	} else {
		$file_mode = default_file_mode();
		$dir_mode = default_dir_mode('../temp');
	}
	
	$settings_rows =	"INSERT INTO `".TABLE_PREFIX."settings` "
		." (name, value) VALUES "
		." ('lepton_version', '".VERSION."'),"
		." ('lepton_guid', '$lepton_guid'),"
		." ('website_title', '".$config['website_title']."'),"
		." ('website_description', ''),"
		." ('website_keywords', ''),"
		." ('website_header', 'LEPTON CMS'),"
		." ('website_footer', 'settings/website footer'),"
		." ('rename_files_on_upload', 'jpg,jpeg,gif,gz,png,pdf,tif,zip'),"
		." ('er_level', ''),"
		." ('prompt_mysql_errors', 'true'),"
		." ('default_language', '".$config['default_language']."'),"
		." ('app_name', 'lep$session_rand'),"
		." ('sec_anchor', 'lep_'),"
		." ('default_timezone_string', '".$config['default_timezone_string']."'),"
		." ('default_date_format', 'M d Y'),"
		." ('default_time_format', 'g:i A'),"
		." ('redirect_timer', '1500'),"
		." ('leptoken_lifetime', '1800'),"
		." ('max_attempts', '9'),"
		." ('home_folders', 'true'),"
		." ('default_template', 'lepton'),"
		." ('default_theme', '".$config['backend_theme']."'),"
		." ('default_charset', 'utf-8'),"
		." ('multiple_menus', 'true'),"
		." ('page_level_limit', '4'),"
		." ('intro_page', 'false'),"
		." ('page_trash', 'inline'),"
		." ('homepage_redirection', 'false'),"
		." ('page_languages', 'false'),"
		." ('wysiwyg_editor', 'tiny_mce_jq'),"
		." ('manage_sections', 'true'),"
		." ('section_blocks', 'true'),"
		." ('frontend_login', 'false'),"
		." ('frontend_signup', 'false'),"
		." ('search', 'public'),"
		." ('page_extension', '.php'),"
		." ('page_spacer', '-'),"
		." ('pages_directory', '/page'),"
		." ('media_directory', '/media'),"
		." ('operating_system', '".$config['operating_system']."'),"
		." ('string_file_mode', '$file_mode'),"
		." ('string_dir_mode', '$dir_mode'),"
		." ('wbmailer_routine', 'phpmail'),"
		." ('server_email', 'admin@yourdomain.tld'),"		// avoid that mail provider (e.g. mail.com) reject mails like yourname@mail.com
		." ('wbmailer_default_sendername', 'LEPTON Mailer'),"
		." ('wbmailer_smtp_host', ''),"
		." ('wbmailer_smtp_auth', ''),"
		." ('wbmailer_smtp_username', ''),"
		." ('wbmailer_smtp_password', ''),"
		." ('mediasettings', ''),"
		." ('enable_old_language_definitions','true'"
		. ")";
		
    $logh = fopen( LOGFILE, 'a' );
		
	$database->query($settings_rows);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['settings'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'filled table [settings]'."\n" );
	}
	
	// Admin group
	$full_system_permissions = 'pages,pages_view,pages_add,pages_add_l0,pages_settings,pages_modify,pages_intro,pages_delete,media,media_view,media_upload,media_rename,media_delete,media_create,addons,modules,modules_view,modules_install,modules_uninstall,templates,templates_view,templates_install,templates_uninstall,languages,languages_view,languages_install,languages_uninstall,settings,settings_basic,settings_advanced,access,users,users_view,users_add,users_modify,users_delete,groups,groups_view,groups_add,groups_modify,groups_delete,admintools,service';
	$insert_admin_group = "INSERT INTO `".TABLE_PREFIX."groups` VALUES ('1', 'Administrators', '$full_system_permissions', '', '')";
	$database->query($insert_admin_group);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['groups'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'filled table [group]'."\n" );
	}

	// Admin user
	$insert_admin_user = "INSERT INTO `".TABLE_PREFIX."users` (user_id,group_id,groups_id,active,username,password,email,display_name) VALUES ('1','1','1','1','".$config['admin_username']."','".md5($config['admin_password'])."','".$config['admin_email']."','Administrator')";
	$database->query($insert_admin_user);
	if ( $database->is_error() ) {
		trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
		$errors['users'] = $database->get_error();
	}
	else {
	    fwrite( $logh, 'filled table [users]'."\n" );
	}
	
	fclose($logh);

	return array(
		( count($errors) ? false : true ),
		$errors
	);
}   // end function fill_tables()

/**
 * installs all modules, templates, and languages
 **/
function install_modules ($lepton_path) {

	global $admin;
	
	$errors = array();

	require_once($lepton_path.'/framework/initialize.php');

	// Load addons into DB
	$dirs = array(
		'modules'	=> $lepton_path.'/modules/',
		'templates'	=> $lepton_path.'/templates/',
		'languages'	=> $lepton_path.'/languages/'
	);
	$ignore_files= array(
		'admin.php',
		'index.php',
		'edit_module_files.php'
	);
	
	$logh = fopen( LOGFILE, 'a' );

	foreach($dirs AS $type => $dir) {
		if(false !== ($handle = opendir($dir))) {
			$temp_list = array();
			while(false !== ($file = readdir($handle))) {
				if(($file != '') && (substr($file, 0, 1) != '.') && (!in_array($file, $ignore_files))) {
					$temp_list[] = $file;
				}
			}
			natsort($temp_list);

			foreach($temp_list as $file) {
			    $admin->error = NULL;
				// Get addon type
				if($type == 'modules' && file_exists($dir.'/'.$file.'/info.php')) {
					require ($dir.'/'.$file.'/info.php');
					fwrite( $logh, 'installing module ['.$file.']'."\n" );
					load_module($dir.'/'.$file, true);
					foreach(
						array(
							'module_license', 'module_author'  , 'module_name', 'module_directory',
							'module_version', 'module_function', 'module_description',
							'module_platform', 'module_guid'
						) as $varname
					) {
						if (isset(  ${$varname} ) ) unset( ${$varname} );
					}
					if ($admin->error!='') {
						$errors[$file] = $admin->error;
						fwrite( $logh, 'error installing module ['.$file.']: '.$admin->error ."\n" );
					}
				} elseif($type == 'templates') {
					require ($dir.'/'.$file.'/info.php');
					fwrite( $logh, 'installing template ['.$file.']' ."\n" );
					load_template($dir.'/'.$file);
					foreach(
						array(
							'template_license', 'template_author'  , 'template_name', 'template_directory',
							'template_version', 'template_function', 'template_description',
							'template_platform', 'template_guid'
						) as $varname
					) {
						if (isset(  ${$varname} ) ) unset( ${$varname} );
					}
                    if ($admin->error!='') {
						$errors[$file] = $admin->error;
						fwrite( $logh, 'error installing template ['.$file.']: '.$admin->error ."\n" );
					}
				} elseif($type == 'languages') {
				    fwrite( $logh, 'installing language ['.$file.']'."\n" );
					load_language($dir.'/'.$file);
					if ($admin->error!='') {
						$errors[$file] = $admin->error;
						fwrite( $logh, 'error installing language ['.$file.']: '.$admin->error ."\n" );
					}
				}
			}
			closedir($handle);
			unset($temp_list);
		}
		else {
			$errors[$dir] = $dir;
		}
	}

	fclose($logh);
	
	return array(
		( count($errors) ? false : true ),
		$errors
	);
}   // end function install_modules ()

/**
 * checks important tables for existance
 **/
function check_tables($database) {

	global $config;
	$errors = array();

	$table_prefix = $config['table_prefix'];

	$requested_tables = array("pages","sections","settings","users","groups","addons","search","mod_droplets","mod_dropleps_settings","mod_dropleps_permissions","mod_wysiwyg","mod_wysiwyg_admin");
	for($i=0;$i<count($requested_tables);$i++) $requested_tables[$i] = $table_prefix.$requested_tables[$i];

	$result = mysql_query("SHOW TABLES FROM ".DB_NAME);
	$all_tables = array();
	for($i=0; $i < mysql_num_rows($result); $i++) $all_tables[] = mysql_table_name($result, $i);

	$missing_tables = array();
	foreach($requested_tables as $temp_table) {
		if (!in_array($temp_table, $all_tables)) {
			$missing_tables[] = $temp_table;
		}
	}

	/**
	 *	If one or more needed tables are missing, so
	 *	we can't go on and have to display an error
	 */
	if ( count($missing_tables) > 0 ) {
		$errors['missing'] = $missing_tables;
	}

	/**
	 *	Try to get some default settings ...
	 *	Keep in Mind, that the values are only used as default, if an entry isn't found.
	 */
	$vars = array(
		'DEFAULT_THEME'	=> "algos",
		'THEME_URL'		=> LEPTON_URL."/templates/algos",
		'THEME_PATH'	=> LEPTON_PATH."/templates/algos",
		'LANGUAGE'		=> $_POST['default_language'],
		'SERVER_EMAIL'	=> "admin@yourdomain.tld",
		'PAGES_DIRECTORY' => '/page',
		'ENABLE_OLD_LANGUAGE_DEFINITIONS' => true
	);
	foreach($vars as $k => $v) {
		if (!defined($k)) {
			$temp_val = $database->get_one("SELECT `value` from `".$table_prefix."settings` where `name`='".strtolower($k)."'");
			if ( $temp_val ) $v = $temp_val;
			define($k, $v);
		}
	}

	if (!isset($MESSAGE)) include (LEPTON_PATH."/languages/".LANGUAGE.".php");

	/**
	 *	The important part ...
	 *	Is there an valid user?
	 */
	$result = $database->query("SELECT * from `".$table_prefix."users` where `username`='".$config['admin_username']."'");
	if ( $database->is_error() ) {
		$errors['adminuser'] = $database->get_error();
	}
	
	if ($result->numRows() == 0) {
		$errors['adminuser'] = false;
	} else {
		$data = $result->fetchRow( MYSQL_ASSOC );
	 	/**
	 	 *	Does the password match
	 	 */
	 	if ( md5($config['admin_password']) != $data['password']) {
	 		$errors['password'] = false;
	 	}
	}
	
	return array(
		( count($errors) ? false : true ),
		$errors
	);
	
}   // end function check_tables()

function create_default_page($database) {
    global $config;
    $url    = "http://www.lepton-cms.org/_packinstall/start-package.html";
    // look if page already exists
	$result = $database->query('SELECT * FROM '.TABLE_PREFIX."mod_wrapper WHERE url='$url';" );
	if ( $result->numRows() == 0 ) {
		require_once("c_lepton_init_page.php");
		$p = new lepton_init_page( $database );
		$p->url = $url;
		$p->language = $config['default_language'];
		$p->build_page();
	}
}   // end function create_default_page()

function pre_installation_error( $msg ) {
	global $installer_uri;
	echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>LEPTON Installation Prerequistes Error</title>
 	<link rel="stylesheet" href="'.$installer_uri.'/templates/default/index.css" type="text/css" />
   </head>
  <body>
  <div style="width:800px;min-width:500px;margin-left:auto;margin-right:auto;margin-top:100px;text-align:center;">
    <div style="float:left">
	  <img src="templates/default/images/fail.png" alt="Fail" title="Fail" />
    </div>
    <div style="float:left">
  	  <h1>LEPTON Installation Prerequistes Error</h1>
  	  <h2>Sorry, the LEPTON Installation prerequisites check failed.</h2>
  	  <span style="color:#fff;">'.$msg.'</span><br /><br />
  	  <h2>You will need to fix the errors quoted above to start the installation.</h2>
    </div>
  </div>
  <div id="header">
    <div>Installation Wizard</div>
  </div>
  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>feel free to keep it strictly simple...</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://www.lepton-cms.org" title="LEPTON CMS" target="_blank">LEPTON Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="LEPTON Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://www.lepton-cms.org" title="LEPTON Package" target="_blank">LEPTON CMS Package</a> is released under several different licenses.
    </div>
  </div>
  </body>
</html>
';
}   // end function pre_installation_error()