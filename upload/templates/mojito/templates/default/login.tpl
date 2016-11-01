{if !user_logged_in() && $VISIBILITY != 'private'}
<div id="login_box" class="shadow_small">
	<span id="toggleLogin" class="icon-switch br_left gradient_gray shadow"></span>
	<form name="login" action="{$CAT_URL}/account/login.php?redirect={$redirect_url}" method="post" id="loginForm" class="gradient_gray br_left shadow">
		<input type="hidden" name="redirect" value="{$redirect_url}" >
		<input type="hidden" name="username_fieldname" value="{$username_fieldname}" >
		<input type="hidden" name="password_fieldname" value="{$password_fieldname}" >
		{translate('Login')}:<br>
		<input type="text" name="{$username_fieldname}" placeholder="email@domain.de" id="loginInput"><br>
		{translate('Password')}:<br>
		<input type="password" name="{$password_fieldname}" placeholder="Passwort"><br>
		<button type="submit" class="icon-switch gradient_blue dr_hover" id="loginButton"> {translate('Login')}</button><br><br>
		<a href="{$CAT_URL}/account/forgot.php" class="forgotPW"> {translate('Forgot your details?')}</a>
	</form>
</div>
{else}
<div id="login_box" class="shadow_small">
	<span id="toggleLogin" class="icon-switch br_left gradient_gray shadow"></span>
	<form name="logout" action="{$redirect_url}" method="post" id="loginForm" class="gradient_gray br_left shadow">
		<p id="toggle_login"> {translate('Welcome back')}, {$display_name}!</p>
		<button type="submit" class="icon-switch gradient_blue dr_hover"> {translate('Sign me out')}</button>
	</form>
</div>
{/if}