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
 * @reformatted     2011-10-04
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

/* Include  phpLib-template parser */
require_once( CAT_PATH . '/include/phplib/template.inc' );
require_once( CAT_PATH . '/framework/timezones.php' );

// see if there exists a template file in "account-htt" folder  inside the current template
$paths = array(
	CAT_PATH . "/templates/" . TEMPLATE,
	CAT_PATH . "/templates/" . TEMPLATE . "/htt",
	CAT_PATH . "/templates/" . DEFAULT_THEME . "/templates",
	dirname( __FILE__ ) . '/htt'
);

$template_path = NULL;
foreach ( $paths as $p )
{
	$temp = $p . "/preferences_form.htt";
	if ( file_exists( $temp ) )
	{
		$template_path =& $p;
		break;
	}
}

if ( $template_path === NULL )
	die( "Can't find a valid template for this form!" );

$tpl = new Template( $template_path );

$tpl->set_unknowns( 'remove' );

/**
 *	set template file name
 *
 */
$tpl->set_file( 'preferences', 'preferences_form.htt' );

/**	*********
 *	languages
 *
 */
$tpl->set_block( 'preferences', 'languages_values_block', 'languages_values_output' );

$query  = "SELECT `directory`,`name` from `" . CAT_TABLE_PREFIX . "addons` where `type`='language'";
$result = $database->query( $query );
if ( !$result )
{
	die( $database->get_error() );
}

while ( false != ( $data = $result->fetchRow( MYSQL_ASSOC ) ) )
{
	$sel = ( LANGUAGE == $data[ 'directory' ] ) ? " selected='selected'" : "";
	$tpl->set_var( 'LANG_SELECTED', $sel );
	$tpl->set_var( array(
		'LANG_CODE' => $data[ 'directory' ],
		'LANG_NAME' => $data[ 'name' ]
	) );
	$tpl->parse( 'languages_values_output', 'languages_values_block', true );
}


/**	****************
 *	default timezone
 *
 */
global $timezone_table;
$tpl->set_block( 'preferences', 'timezone_values_block', 'timezone_values_output' );
foreach ( $timezone_table as $title )
{
	$tpl->set_var( 'TIMEZONE_NAME', $title );
	$tpl->set_var( 'TIMEZONE_SELECTED', ( $wb->get_timezone_string() == $title ) ? ' selected="selected"' : '' );
	$tpl->parse( 'timezone_values_output', 'timezone_values_block', true );
}


/**	***********
 *	date format
 *
 */
$tpl->set_block( 'preferences', 'date_format_block', 'date_format_output' );

$user_time = true;
require_once( CAT_ADMIN_PATH . '/interface/date_formats.php' );
foreach ( $DATE_FORMATS AS $format => $title )
{
	$format = str_replace( '|', ' ', $format ); // Add's white-spaces (not able to be stored in array key)

	$value = ( $format != 'system_default' ) ? $format : "";

	if ( DATE_FORMAT == $format AND !isset( $_SESSION[ 'USE_DEFAULT_DATE_FORMAT' ] ) )
	{
		$tpl->set_var( 'DATE_FORMAT_SELECTED', "selected='selected'" );
	}
	elseif ( $format == 'system_default' AND isset( $_SESSION[ 'USE_DEFAULT_DATE_FORMAT' ] ) )
	{
		$tpl->set_var( 'DATE_FORMAT_SELECTED', "selected='selected'" );
	}
	else
	{
		$tpl->set_var( 'DATE_FORMAT_SELECTED', '' );
	}
	$tpl->set_var( array(
		'DATE_FORMAT_VALUE' => $value,
		'DATE_FORMAT_TITLE' => $title
	) );

	$tpl->parse( 'date_format_output', 'date_format_block', true );
}

/**	***********
 *	time format
 *
 */
$tpl->set_block( 'preferences', 'time_format_block', 'time_format_output' );

//$user_time = true;
$TIME_FORMATS = CAT_Helper_DateTime::getTimeFormats();
foreach ( $TIME_FORMATS AS $format => $title )
{
	$format = str_replace( '|', ' ', $format ); // Add's white-spaces (not able to be stored in array key)

	$value = ( $format != 'system_default' ) ? $format : "";

	if ( TIME_FORMAT == $format AND !isset( $_SESSION[ 'USE_DEFAULT_TIME_FORMAT' ] ) )
	{
		$tpl->set_var( 'TIME_FORMAT_SELECTED', "selected='selected'" );
	}
	elseif ( $format == 'system_default' AND isset( $_SESSION[ 'USE_DEFAULT_TIME_FORMAT' ] ) )
	{
		$tpl->set_var( 'TIME_FORMAT_SELECTED', "selected='selected'" );
	}
	else
	{
		$tpl->set_var( 'TIME_FORMAT_SELECTED', '' );
	}
	$tpl->set_var( array(
		'TIME_FORMAT_VALUE' => $value,
		'TIME_FORMAT_TITLE' => $title
	) );
	$tpl->parse( 'time_format_output', 'time_format_block', true );
}

/**
 *	Building a hash
 *
 */
$hash                      = sha1( microtime() . $_SERVER[ 'HTTP_USER_AGENT' ] );
$_SESSION[ 'wb_apf_hash' ] = $hash;

$tpl->set_var( array(
	'TEMPLATE_DIR' => TEMPLATE_DIR,
	'CAT_URL' => CAT_URL,
	'PREFERENCES_URL' => PREFERENCES_URL,
	'LOGOUT_URL' => LOGOUT_URL,
	'HEADING_MY_SETTINGS' => $HEADING[ 'MY_SETTINGS' ],
	'HEADING_PREFERENCES' => CAT_Helper_I18n::getInstance()->translate('Preferences'),
	'TEXT_DISPLAY_NAME' => $TEXT[ 'DISPLAY_NAME' ],
	'DISPLAY_NAME' => $wb->get_display_name(),
	'TEXT_LANGUAGE' => $TEXT[ 'LANGUAGE' ],
	'TEXT_TIMEZONE' => $TEXT[ 'TIMEZONE' ],
	'TEXT_PLEASE_SELECT' => $TEXT[ 'PLEASE_SELECT' ],
	'TEXT_DATE_FORMAT' => $TEXT[ 'DATE_FORMAT' ],
	'TEXT_TIME_FORMAT' => $TEXT[ 'TIME_FORMAT' ],
	'HEADING_MY_EMAIL' => $HEADING[ 'MY_EMAIL' ],
	'TEXT_EMAIL' => $TEXT[ 'EMAIL' ],
	'GET_EMAIL' => $wb->get_email(),
	'HEADING_MY_PASSWORD' => $HEADING[ 'MY_PASSWORD' ],
	'TEXT_CURRENT_PASSWORD' => $TEXT[ 'CURRENT_PASSWORD' ],
	'TEXT_NEW_PASSWORD' => $TEXT[ 'NEW_PASSWORD' ],
	'TEXT_RETYPE_NEW_PASSWORD' => $TEXT[ 'RETYPE_NEW_PASSWORD' ],
	'TEXT_LOGOUT' => CAT_Helper_I18n::getInstance()->translate('Logout'),
	'TEXT_SAVE' => $TEXT[ 'SAVE' ],
	'TEXT_RESET' => $TEXT[ 'RESET' ],
	'USER_ID' => ( isset( $_SESSION[ 'USER_ID' ] ) ? $_SESSION[ 'USER_ID' ] : '-1' ),
	'r_time' => TIME(),
	'HASH' => $hash,
	'TEXT_NEED_CURRENT_PASSWORD' => $TEXT[ 'NEED_CURRENT_PASSWORD' ],
	'TEXT_ENABLE_JAVASCRIPT' => $TEXT[ 'ENABLE_JAVASCRIPT' ],
	'RESULT_MESSAGE' => ( isset( $_SESSION[ 'result_message' ] ) ) ? $_SESSION[ 'result_message' ] : "",
	'AUTH_MIN_LOGIN_LENGTH'	=> AUTH_MIN_LOGIN_LENGTH
) );

unset( $_SESSION[ 'result_message' ] );

// for use in template <!-- BEGIN/END comment_block -->
$tpl->set_block( 'preferences', 'comment_block', 'comment_replace' );
$tpl->set_block( 'comment_replace', '' );

// ouput the final template
$tpl->pparse( 'output', 'preferences' );
?>