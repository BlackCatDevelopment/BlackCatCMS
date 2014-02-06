<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>{translate('Login')}</title>

<link rel="shortcut icon" href="{$CAT_THEME_URL}/css/images/favicon.ico" type="image/x-icon" />

<meta http-equiv="content-type" content="text/html; charset={$meta.CHARSET}" />
<meta http-equiv="content-language" content="{$meta.LANGUAGE}" />
<meta name="description" content="{translate('Login')}" />
<meta name="keywords" content="{translate('Login')}" />
<meta name="robots" content="noindex, nofollow" />

<meta http-equiv="Content-Encoding" content="gzip" />
<meta http-equiv="Accept-Encoding" content="gzip, deflate" />

<meta name="author" content="Black Cat CMS" />

<link href="{$CAT_THEME_URL}/css/{$DEFAULT_THEME_VARIANT}/login.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
	var CAT_URL								= '{$CAT_URL}';
	var CAT_THEME_URL						= '{$CAT_THEME_URL}';
	var CAT_ADMIN_URL						= '{$CAT_ADMIN_URL}';
</script>

<script src="{$CAT_URL}/modules/lib_jquery/jquery-core/jquery-core.min.js" type="text/javascript"></script>
<script src="{$CAT_URL}/modules/lib_jquery/jquery-ui/ui/jquery-ui.min.js" type="text/javascript"></script>

<script src="{$CAT_THEME_URL}/js/login.js" type="text/javascript"></script>

</head>
<body class="fc_gradient3">
	<img src="{$CAT_THEME_URL}/css/images/login/radial.png" alt="radial" class="fc_login_radial" />
	<div class="fc_img_no_save fc_start_black"></div>
	<noscript>
		<img src="{$CAT_THEME_URL}/css/images/login/js_required.png" alt="JavaScript is required" />
		<div id="fc_JS_needed" class="fc_br_all fc_shadow_big fc_gradient1 fc_border_all_light">
			<h1>{translate('JavaScript is required')}</h1>
			<p>{translate('To use <span class="icon-logo">Black Cat CMS</span>, please enable JavaScript in your browser and try again.')}</p>
		</div>
	</noscript>
	<div id="shake_wrapper">
		<div id="fc_forms" class="fc_br_all fc_shadow_big">
			<div id="fc_login_header" class="fc_br_top fc_gradient4">
				<a href="{$CAT_URL}" class="icon-home fc_border_all fc_border_all_light fc_gradient1 fc_gradient_hover" id="fc_home_site" title="{translate('Home')}"></a>
			</div>
			<form name="login" action="{$ACTION_URL}" method="post" id="fc_login_form" class="fc_gradient1 fc_br_bottom fc_border">
				<p>
					{*Currently not active <input type="hidden" name="url" value="{$REDIRECT}" />*}
					<input type="hidden" name="username_fieldname" value="{$USERNAME_FIELDNAME}" />
					<input type="hidden" name="password_fieldname" value="{$PASSWORD_FIELDNAME}" />
		
					<label for="fc_login_username">{translate('Username')}</label>
					<input type="text" maxlength="{$MAX_USERNAME_LEN}" name="{$USERNAME_FIELDNAME}" value="{$USERNAME}" id="fc_login_username" />
				</p>
				<p>
					<label for="fc_login_password">{translate('Password')}</label>
					<input type="password" maxlength="{$MAX_PASSWORD_LEN}" name="{$PASSWORD_FIELDNAME}" id="fc_login_password" />
				</p>
				<p>
					<button type="submit" name="submit_login" class="fc_login_button icon-switch"> {translate('Login')}</button>
				</p>
				<p class="fc_loader"></p>
				<p id="fc_message_login"></p>
			</form>
			<form name="forgot_pass" action="{$CAT_ADMIN_URL}/login/forgot/ajax_forgot.php" method="post" id="fc_login_forgot_form" class="fc_gradient1 fc_br_bottom fc_border">
				<p>
					<label for="fc_forgot">{translate('Email')}</label>
					<input type="text" maxlength="255" name="email" value="{if $EMAIL}{$EMAIL}{/if}" id="fc_forgot" />
					<button type="submit" name="submit_email" class="fc_forgot_button icon-mail"> {translate('Send details')}</button>
				</p>
				<p class="fc_loader"></p>
				<p id="fc_message"></p>
				<span class="fc_br_top icon-help fc_gradient1 fc_gradient_hover fc_border_all_light" id="fc_home_login"> {translate('Need to log-in?')}</span>
			</form>
		</div>
	</div>
	<div class="fc_gradient4 fc_license">
		<p class="right"><a href="http://blackcat-cms.org" title="Black Cat CMS Core" target="_blank">Black Cat CMS Core</a> is released under the
			<a href="http://www.gnu.org/licenses/gpl.html" title="Black Cat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
			<a href="http://blackcat-cms.org" title="Black Cat CMS Bundle" target="_blank">Black Cat CMS Bundle</a> is released under several different licenses.
		</p>
		<p class="left">
			<a href="{$FORGOTTEN_DETAILS_APP}" class="icon-help" id="fc_login_forgot" title="{translate('Forgotten your details?')}">
				<span class="fc_br_all fc_show_description"> {translate('Forgotten your details?')}</span>
			</a>
		</p>
		<div class="fc_error_box hidden">
			<p>Session expired. Please login again after reloading the page!<br />
			This happens if there was no valid token or your session expired by time.</p>
		</div>
	</div>
</body>
</html>
