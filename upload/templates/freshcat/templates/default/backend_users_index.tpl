<div id="fc_content_header">
	{{translate('Manage users')}}
	<div class="fc_header_buttons">
		<a href="{{CAT_ADMIN_URL}}/users/index.php" class="{% if not permissions.GROUPS %}fc_br_all {else}fc_br_left {% endif %}fc_gradient1 fc_gradient_hover fc_active">{{translate('Manage users')}}</a>
		{% if permissions.GROUPS %}<a href="{{CAT_ADMIN_URL}}/groups/index.php" class="fc_br_right fc_gradient1 fc_gradient_hover">{{translate('Manage groups')}}</a>{% endif %}
	</div>
</div>

<div id="fc_main_content">
	<div id="fc_lists_overview">
		<div id="fc_list_search">
			<div class="fc_input_fake">
				<input type="text" name="fc_list_search" id="fc_list_search_input" value="{{translate('Search...')}}">
				<label class="fc_close" for="fc_list_search_input"></label>
			</div>
		</div>

		<div class="fc_gradient1 fc_border">
			{% if permissions.USERS_ADD %}<button id="fc_list_add" class="icon-plus fc_cell_one fc_gradient1 fc_gradient_hover" title="{{translate('Add user')}}"></button>{% endif %}
			<div class="clear"></div>
		</div>

		<ul id="fc_list_overview" class="fc_user_list">
			{% for user in users %}
			<li class="fc_group_item icon-user fc_border fc_gradient1 fc_gradient_hover">
				<span class="fc_display_name">{{user.DISPLAY_NAME}}</span><br>
				<span class="fc_list_name">{{user.USER_NAME}}</span>
				<input type="hidden" name="user_id" value="{{user.VALUE}}">
			</li>
			{% endfor %}
		</ul>
	</div>

	{% if permissions.USERS_MODIFY %}
	<div class="fc_all_forms">
		<form name="add_user" action="{{CAT_ADMIN_URL}}/users/add.php" method="post" id="fc_User_form" class="fc_list_forms">
			<p class="submit_settings fc_gradient1">
				<strong class="fc_addUser">{{translate('Add user')}}</strong>
				<strong class="fc_modifyUser">{{translate('Modify user')}}</strong>
				<input type="submit" name="addUser" value="{{translate('Add user')}}" class="fc_addUser">
				<input type="submit" name="saveUser" value="{{translate('Save user')}}" class="fc_modifyUser">
				<input type="reset" name="reset_user" value="{{translate('Reset')}}">
				<input type="hidden" name="username_fieldname" id="fc_User_fieldname" value="{{USERNAME_FIELDNAME}}">
				<input type="hidden" name="user_id" id="fc_User_user_id" value="">
			</p>
			<div class="clear_sp"></div>

			<div class="fc_input_description">
				<label for="fc_User_name" class="fc_label_200">{{translate('Username')}}:</label>
				<input type="text" name="{{USERNAME_FIELDNAME}}" id="fc_User_name" value="">
				<div class="fc_settings_max fc_br_all icon-notification fc_gradient_red fc_border fc_shadow_big"> {{NEWUSERHINT.0}}</div>
			</div>
			<div class="clear_sp"></div>

			<label for="fc_User_display_name" class="fc_label_200">{{translate('Display name')}}:</label>
			<input type="text" name="display_name" id="fc_User_display_name" maxlength="255" value="">
			<div class="clear_sp"></div>

			<label for="fc_User_email" class="fc_label_200">{{translate('Email')}}:</label>
			<input type="text" name="email" id="fc_User_email" maxlength="255" value="">
			<div class="clear_sp"></div><hr>

            {% if INITIAL_PAGE %}
            <h3>{{translate('Initial page')}}:</h3>
			<label for="fc_init_page" class="fc_label_200">{{translate('Page')}}:</label>
            <select id="fc_init_page" name="init_page">
            {% if frontend_pages|length > 0 %}<optgroup label="{{translate('Frontend page')}}">
            {% for label, page in frontend_pages %}
                <option value="{{page}}"{% if init_page == page %} selected="selected"{% endif %}>{{label}}</option>
            {% endfor %}</optgroup>{% endif %}
            {% if backend_pages|length>0 %}<optgroup label="{{translate('Backend page')}}">
            {% for label, page in backend_pages %}
                <option value="{{page}}"{% if init_page == page %} selected="selected"{% endif %}>{{label}}</option>
            {% endfor %}</optgroup>{% endif %}
            {% if admin_tools|length>0 %}<optgroup label="{{translate('Admin Tool')}}">
            {% for label, page in admin_tools %}
                <option value="{{page}}"{% if init_page == page %} selected="selected"{% endif %}>{{label}}</option>
            {% endfor %}</optgroup>{% endif %}
            </select>

			<div class="clear_sp"></div>
            <label for="fc_init_page_param" class="fc_label_200">{{translate('Optional parameters')}}:</label>
            <input type="text" name="init_page_param" id="fc_init_page_param" value="{{init_page_param}}">
			<div class="clear_sp"></div><hr>
            {% endif %}

			{% if HOME_FOLDERS %}
			<label for="fc_User_home_folder" class="fc_label_200">{{translate('Home folder')}}:</label>
			<select name="home_folder" id="fc_User_home_folder">
				<option value="">{{translate('None')}}</option>
				{% for homefolder in home_folders %}
				<option value="{{homefolder.FOLDER}}">{{homefolder.NAME}}</option>
				{% endfor %}
			</select>
            <div class="clear_sp"></div><hr>
			{% endif %}

			<div class="fc_modifyUser fc_password_notification fc_br_all icon-notification fc_gradient_red fc_input_description">
				{{translate('Please note: You should only enter values in those fields if you wish to change this users password')}}
			</div>
			<div class="clear_sp"></div>
			<div class="fc_input_description">
				<label for="fc_User_password" class="fc_label_200">{{translate('Password')}}:</label>
				<input type="password" name="password" id="fc_User_password" value="">
				<div class="fc_settings_max fc_br_all icon-notification fc_gradient_red fc_border fc_shadow_big"> {{NEWUSERHINT.1}}</div>
			</div>
			<div class="clear_sp"></div>

			<div class="fc_input_description">
				<label for="fc_User_password2" class="fc_label_200">{{translate('Retype password')}}:</label>
				<input type="password" name="password2" id="fc_User_password2" value="">
				<div class="fc_settings_max fc_br_all icon-notification fc_gradient_red fc_border fc_shadow_big"> {{NEWUSERHINT.1}}</div>
			</div>
			<div class="clear_sp"></div>
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="otp" id="fc_User_otp" value="1" checked="checked">
				<label for="fc_User_otp">{{translate('One-time password')}}</label><br>
				<small>{{translate('Recommended: The user must change his password the next time he logs in.')}}</small>
			</div>

			<hr>

			<h3>{{translate('Groups')}}:</h3>
			<div id="fc_User_group" class="fc_settings_max">
				<span class="fc_description">({{translate('You need to choose at least one group')}})</span><br>
				{% for group in groups.viewers %}
				<input type="checkbox" class="fc_checkbox_jq" name="groups[]" id="fc_User_groups_{{group.VALUE}}" value="{{group.VALUE}}"{% if group.VALUE == 1 %}{% if not is_admin %} disabled="disabled"{% endif %}{% endif %}>
				<label for="fc_User_groups_{{group.VALUE}}">{{group.NAME}}</label>
				{% endfor %}
			</div>
			<hr>

			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="active" id="fc_User_active_user" value="1" checked="checked">
				<label for="fc_User_active_user">{{translate('Activate user')}}</label>
			</div>
			<div class="clear_sp"></div>

			<p class="submit_settings fc_gradient1">
				{% if permissions.USERS_DELETE %}<input type="submit" id="fc_removeUser" class="fc_modifyUser fc_list_remove fc_gradient_red" name="removeUser" value="{{translate('Delete user')}}">{% endif %}
				<input type="submit" name="addUser" value="{{translate('Add user')}}" class="fc_addUser">
				<input type="submit" name="saveUser" value="{{translate('Save user')}}" class="fc_modifyUser">
				<input type="reset" name="reset_user" value="{{translate('Reset')}}">
			</p>

		</form>
	</div>
	{% endif %}
</div>