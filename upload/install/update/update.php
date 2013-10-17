<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

require_once dirname(__FILE__).'/../../config.php';

// Try to guess installer URL
$installer_uri = 'http://' . $_SERVER[ "SERVER_NAME" ] . ( ( $_SERVER['SERVER_PORT'] != 80 ) ? ':'.$_SERVER['SERVER_PORT'] : '' ) . $_SERVER[ "SCRIPT_NAME" ];
$installer_uri = dirname( $installer_uri );
$installer_uri = str_ireplace('update','',$installer_uri);
$lang          = CAT_Helper_I18n::getInstance();
$lang->addFile( $lang->getLang().'.php', dirname(__FILE__).'/../languages' );

if(!CAT_Helper_Addons::versionCompare( CAT_VERSION, '0.11.0Beta' ))
    pre_update_error($lang->translate(
        'You need to have <strong>BlackCat CMS v0.11.0Beta</strong> installed to use the Update.<br />You have <strong>{{version}}</strong> installed.',
        array( 'version' => CAT_VERSION )
    ));

if(!CAT_Helper_Validate::getInstance()->sanitizeGet('do'))
{
    update_wizard_header();
    echo '
        <h1>BlackCat CMS Update Wizard</h1>
        <h2>Welcome!</h2>
        <form method="get" action="'.$installer_uri.'/update/update.php">
          <input type="hidden" name="do" value="1" />
          <input type="submit" value="'.$lang->translate('To start the update, please click here').'" />
        </form>
    ';
    update_wizard_footer();
}

/*******************************************************************************
 * DO THE UPDATE
 ******************************************************************************/

/*
    BETA TO RELEASE 1.0
*/

// remove captcha_control module
if(file_exists(CAT_PATH.'/modules/captcha_control/index.php'))
{
    CAT_Helper_Directory::removeDirectory(CAT_PATH.'/modules/captcha_control');
    $lang->db()->query(sprintf(
        "DELETE FROM `%saddons` WHERE directory = '%s' AND type = '%s'",
        CAT_TABLE_PREFIX, 'captcha_control', 'module'
    ));
}
// moved to widgets subdir; in fact, this change was made before Beta, but anyway...
if(file_exists(CAT_PATH.'/modules/blackcat/widget.php'))
    unlink(CAT_PATH.'/modules/blackcat/widget.php');
// add to class_secure table
$lang->db()->query(sprintf(
    "REPLACE INTO `%sclass_secure` VALUES ( 0, '%s' )",
    CAT_TABLE_PREFIX, '/backend/pages/ajax_recreate_af.php'
));

/*
    1.0 TO 1.0.1
*/
// remove beta png
if(file_exists(CAT_PATH."/templates/freshcat/css/images/login/beta_state.png"))
    unlink(CAT_PATH."/templates/freshcat/css/images/login/beta_state.png");


$installer_uri = str_replace('/update','',$installer_uri);
update_wizard_header();
    echo '
        <h2>'.$lang->translate('Update done').'</h2>
        <form method="get" action="'.CAT_ADMIN_URL.'">
          <input type="submit" value="'.$lang->translate('Click here to enter the backend').'" />
        </form>
    ';
update_wizard_footer();

function pre_update_error( $msg ) {
    global $installer_uri, $lang;
    update_wizard_header();
    echo'
        <div style="float:left">
          <img src="templates/default/images/fail.png" alt="Fail" title="Fail" />
        </div>
        <h1>BlackCat CMS Update Prerequistes Error</h1>
        <h2>'.$lang->translate('Sorry, the BlackCat CMS Update prerequisites check failed.').'</h2>
        <span style="display:inline-block;background-color:#343434;color:#ff3030;font-size:1.5em;border:1px solid #ff3030;padding:15px;width:100%;margin:15px auto;-webkit-border-radius: 8px;-moz-border-radius: 8px;-khtml-border-radius: 8px;border-radius: 8px;">'.$msg.'</span><br /><br />
        <h2>'.$lang->translate('You will need to fix the errors quoted above to start the installation.').'</h2>';
    update_wizard_footer();
}   // end function pre_update_error()

function update_wizard_header() {
    global $installer_uri, $lang;
    echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>BlackCat CMS Update Prerequistes Error</title>
    <link rel="stylesheet" href="'.$installer_uri.'/templates/default/index.css" type="text/css" />
   </head>
  <body>
  <div style="width:800px;min-width:800px;margin:auto;margin-top:20%;text-align:center;">
    <div style="float:left;width:100%;">';
}

function update_wizard_footer() {
    echo '
    </div>
  </div>
  <div id="header">
    <div>Update Wizard</div>
  </div>
  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>enjoy the difference!</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="BlackCat CMS" target="_blank">BlackCat CMS Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="BlackCat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="BlackCat CMS Bundle" target="_blank">BlackCat CMS Bundle</a> is released under several different licenses.
    </div>
  </div>
  </body>
</html>
';
    exit;
}