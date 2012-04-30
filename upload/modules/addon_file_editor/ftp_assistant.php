<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file checks the FTP connection to the server specified in config.inc.php
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.1.2
 * @platform	Website Baker 2.8
*/

// include WB configuration file (restarts sessions) and WB admin class
require_once('../../config.php');
require_once('../../framework/class.admin.php');

// include module configuration and function file
require_once('config.inc.php');
require_once('functions.inc.php');

// load module language file
$lang = (dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php';
require_once(!file_exists($lang) ? (dirname(__FILE__)) . '/languages/EN.php' : $lang );

// work out link to language specific help file
$help_file = 'help_' . (file_exists(dirname(__FILE__) . '/help/help_' . strtolower(LANGUAGE) . '.html') ? strtolower(LANGUAGE) : 'en') . '.html';

/**
 * Ensure that only users with permissions to Admin-Tools section can access this file
 */
// check user permissions for admintools (redirect users with wrong permissions)
$admin = new admin('Admintools', 'admintools', false, false);
if ($admin->get_permission('admintools') == false) die(header('Location: ../../index.php'));

// create new instance this time showing the admin panel (no headers possible anymore)
$admin = myAdminHandler('addon_file_editor', 'Admintools', 'admintools', true, false);

/**
 * Prepare the template class
 */
// include template class and set template directory
require_once(WB_PATH . '/include/phplib/template.inc');
$tpl = new Template(dirname(__FILE__) . '/htt');
$tpl->set_file('page', 'ftp_connection_check.htt');
$tpl->set_block('page', 'settings_block', 'settings_replace');

// remove the comment block
$tpl->set_block('page', 'comment_block', 'comment_replace');
$tpl->set_block('comment_replace', '');

// replace template placeholder with data from language file
$tpl_vars = array_merge($LANG[1], array('URL_ADMIN_TOOL' => $url_admintools, 'CLASS_SHOW_FTP_INFO' => 'hidden'));
foreach($LANG[10] as $key => $value) {
	$tpl->set_var($key, $value);
}

/**
 * Read or update FTP settings from or to databse
 */
// clean POST values and store settings in database if required
if (isset($_POST['ftp_save_settings'])) {
	updateFtpSettings($_POST);
}

// read FTP settings from database
$ftp_settings = readFtpSettings();

// fetch the name of the addon editor from the database
$editor_info = getAddonInfos($module_folder);

// fill some template variables
$tpl->set_var(array(
	'TXT_HEADING_ADMINTOOLS'=> $HEADING['ADMINISTRATION_TOOLS'],
	'NAME_FILE_EDITOR'		=> $editor_info['name'],
	'TXT_BACK'				=> $TEXT['BACK'],
	'TXT_SAVE'				=> $TEXT['SAVE'],
	'TXT_HELP'				=> $LANG[1]['TXT_HELP'],
	'URL_HELP_FILE'			=> $url_mod_path . '/help/' . $help_file,
	'URL_FILEMANAGER'		=> $url_admintools,
	'URL_WB_ADMIN_TOOLS'	=> ADMIN_URL . '/admintools/index.php',
	'CLASS_HIDDEN'			=> ($ftp_settings['ftp_enabled'] == 1) ? '' : 'hidden',
	'URL_FORM_SUBMIT'		=> $url_ftp_assistant,
	'DISABLED_CHECKED'		=> ($ftp_settings['ftp_enabled'] == 0) ? 'checked="checked"' : '',
	'ENABLED_CHECKED'		=> ($ftp_settings['ftp_enabled'] == 1) ? 'checked="checked"' : '',
	'FTP_SERVER'			=> htmlspecialchars($ftp_settings['ftp_server']),
	'FTP_USER'				=> htmlspecialchars($ftp_settings['ftp_user']),
	'FTP_PASSWORD'			=> htmlspecialchars($ftp_settings['ftp_password']),
	'FTP_PORT'				=> htmlspecialchars($ftp_settings['ftp_port']),
	'FTP_START_DIR'			=> htmlspecialchars($ftp_settings['ftp_start_dir']),
	)
);

// check FTP connection status
if (isset($_POST['ftp_connection_check'])) {
	$status = ftpLogin();
	$tpl->set_var('STATUS_MESSAGE',	writeStatusMessage(
		is_resource($status) ? $LANG[10]['TXT_FTP_LOGIN_OK'] : $LANG[10]['TXT_FTP_LOGIN_FAILED'],
		$url_admintools, is_resource($status), false));
}
		
// ouput the final template
$tpl->pparse('output', 'page');

// print admin template footer
$admin->print_footer();

?>