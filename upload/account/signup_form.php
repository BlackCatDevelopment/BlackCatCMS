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
 * @reformatted     2011-10-04
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

require_once( WB_PATH . '/include/captcha/captcha.php' );

?>

<h1>&nbsp;<?php echo $TEXT[ 'SIGNUP' ]; ?></h1>

<?php
if ( isset( $_GET[ 'err' ] ) && (int) ( $_GET[ 'err' ] ) == ( $_GET[ 'err' ] ) )
{
	$err_msg = '';
	switch ( (int) $_GET[ 'err' ] )
	{
		case 1:
			$err_msg = $MESSAGE[ 'USERS_NO_GROUP' ];
			break;
		case 2:
			$err_msg = $MESSAGE[ 'USERS_NAME_INVALID_CHARS' ] . ' / ' . $MESSAGE[ 'USERS_USERNAME_TOO_SHORT' ];
			break;
		case 3:
			$err_msg = $MESSAGE[ 'USERS_INVALID_EMAIL' ];
			break;
		case 4:
			$err_msg = $MESSAGE[ 'SIGNUP_NO_EMAIL' ];
			break;
		case 5:
			$err_msg = $MESSAGE[ 'MOD_FORM_INCORRECT_CAPTCHA' ];
			break;
		case 6:
			$err_msg = $MESSAGE[ 'USERS_USERNAME_TAKEN' ];
			break;
		case 7:
			$err_msg = $MESSAGE[ 'USERS_EMAIL_TAKEN' ];
			break;
		case 8:
			$err_msg = $MESSAGE[ 'USERS_INVALID_EMAIL' ];
			break;
		case 9:
			$err_msg = $MESSAGE[ 'FORGOT_PASS_CANNOT_EMAIL' ];
			break;
	}
	if ( $err_msg != '' )
	{
		echo "<p style='color:red'>$err_msg</p>";
	}
}
?>

<form name="user" action="<?php echo WB_URL . '/account/signup.php'; ?>" method="post">

<?php
if ( ENABLED_ASP ) // add some honeypot-fields
{
?>
    <div style="display:none;">
	<input type="hidden" name="submitted_when" value="<?php
	$t = time();
	echo $t;
	$_SESSION[ 'submitted_when' ] = $t;
?>" />
	<p class="nixhier">
	email-address:
	<label for="email-address">Leave this field email-address blank:</label>
	<input id="email-address" name="email-address" size="60" value="" /><br />
	username (id):
	<label for="name">Leave this field name blank:</label>
	<input id="name" name="name" size="60" value="" /><br />
	Full Name:
	<label for="full_name">Leave this field full_name blank:</label>
	<input id="full_name" name="full_name" size="60" value="" /><br />
	</p>
    </div>
	<?php
}
?>
<table cellpadding="5" cellspacing="0" border="0" width="90%">
<tr>
	<td width="180"><?php echo $TEXT[ 'USERNAME' ]; ?>:</td>
	<td class="value_input">
		<input type="text" name="username" maxlength="30" style="width:300px;"/>
	</td>
</tr>
<tr>
	<td><?php echo $TEXT[ 'DISPLAY_NAME' ]; ?> (<?php echo $TEXT[ 'FULL_NAME' ]; ?>):</td>
	<td class="value_input">
		<input type="text" name="display_name" maxlength="255" style="width:300px;" />
	</td>
</tr>
<tr>
	<td><?php echo $TEXT[ 'EMAIL' ]; ?>:</td>
	<td class="value_input">
		<input type="text" name="email" maxlength="255" style="width:300px;"/>
	</td>
</tr>
<?php
// Captcha
if ( ENABLED_CAPTCHA )
{
?><tr>
		<td class="field_title"><?php echo $TEXT[ 'VERIFICATION' ]; ?>:</td>
		<td><?php call_captcha(); ?></td>
		</tr>
<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" name="submit" value="<?php echo $TEXT[ 'SIGNUP' ]; ?>" />
		<input type="reset" name="reset" value="<?php echo $TEXT[ 'RESET' ]; ?>" />
	</td>
</tr>
</table>

</form>

<br />
&nbsp;
