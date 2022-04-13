{% if not user_logged_in() and VISIBILITY not 'private' %}
<div id="login_box" class="shadow_small">
	<span id="toggleLogin" class="icon-switch br_left gradient_gray shadow"></span>
	<form name="login" action="{{CAT_URL}}/account/login.php?redirect={{redirect_url}}" method="post" id="loginForm" class="gradient_gray br_left shadow">
		<input type="hidden" name="redirect" value="{{redirect_url}}" >
		<input type="hidden" name="username_fieldname" value="{{username_fieldname}}" >
		<input type="hidden" name="password_fieldname" value="{{password_fieldname}}" >
		<p {% if otp %}style="display:none;"{% endif %}>
			{{translate('Login')}}:<br>
			<input type="text" name="{{username_fieldname}}" placeholder="{{translate('Username')}}" id="loginInput" value="{{user}}">
		</p>
		<p>
			{{translate('Password')}}:<br>
			<input type="password" name="{{password_fieldname}}" placeholder="{{translate('Password')}}">
		</p>
		<p {%if not otp %}style="display:none;"{% if %}>
			<label class="account_label" for="{{password_fieldname}}_1">{{translate('New password')}}:</label><br>
			<input type="password" class="account_input" name="{{password_fieldname}}_1" id="{{password_fieldname}}_1" maxlength="30"  placeholder="{{translate('New password')}}"><br>
			<label class="account_label" for="{{password_fieldname}}_2">{{translate('Retype new password')}}:</label><br>
			<input type="password" class="account_input" name="{{password_fieldname}}_2" id="{{password_fieldname}}_2" maxlength="30"  placeholder="{{translate('Retype new password')}}"><br>
		</p>
		<button type="submit" class="icon-switch gradient_blue dr_hover" id="loginButton"> {{translate('Login')}}</button><br><br>
		<a href="{{CAT_URL}}/account/forgot.php" class="forgotPW"> {{translate('Forgot your details?')}}</a>
	</form>
</div>
{% else %}
<div id="login_box" class="shadow_small">
	<span id="toggleLogin" class="icon-switch br_left gradient_gray shadow"></span>
	<form name="logout" action="{{redirect_url}}" method="post" id="loginForm" class="gradient_gray br_left shadow">
		<p id="toggle_login"> {{translate('Welcome back')}}, {{display_name}}!</p>
		<button type="submit" class="icon-switch gradient_blue dr_hover"> {{translate('Sign me out')}}</button>
	</form>
</div>
{% endif %}