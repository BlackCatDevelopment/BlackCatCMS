			<label class="fc_label_120" for="fc_default_language" title="{{translate('This is the default language of the system.')}}">{{translate('Language')}}:</label>
			<select name="default_language" id="fc_default_language">
				{% for language in languages %}
				<option value="{{language.CODE}}"{% if language.SELECTED %} selected="selected"{% endif %}>{{language.NAME}} ({{language.CODE}})</option>
				{% endfor %}
			</select>
			<div class="clear_sp"></div>

			{% if DISPLAY_ADVANCED %}
			<label class="fc_label_120" for="fc_default_charset" title="{{translate('This is the charset to be used for both the frontend and the backend. We recommend to use UTF-8.')}}">{{translate('Charset')}}:</label>
			<select name="default_charset" id="fc_default_charset">
				<option value="">{{translate('Please select')}}...</option>
				{% for charset in charsets %}
				<option value="{{charset.VALUE}}"{% if charset.SELECTED %} selected="selected"{% endif %}>{{charset.NAME}}</option>
				{% endfor %}
			</select>
			<div class="clear_sp"></div>
			{% else %}
				<input type="hidden" name="default_charset" value="{{DEFAULT_CHARSET}}">
			{% endif %}
			<hr>
			<label class="fc_label_120" for="fc_default_timezone_string" title="{{translate('This is the default timezone. This setting will be used for guests and as a default for new users.')}}">{{translate('Timezone')}}:</label>
			<select name="default_timezone_string" id="fc_default_timezone_string">
				<option value="0">{{translate('Please select')}}...</option>
				{% for timezone in timezones %}
				<option {% if timezone.SELECTED %} selected="selected"{% endif %}>{{timezone.NAME}}</option>
				{% endfor %}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_120" for="fc_default_date_format" title="{{translate('This is the default date format. This setting will be used for guests and as a default for new users.')}}">{{translate('Date format')}}:</label>
			<select name="default_date_format" id="fc_default_date_format">
				<option value="M d Y">{{translate('Please select')}}...</option>
				{% for dateformat in dateformats %}
				<option value="{{dateformat.VALUE}}"{% if dateformat.SELECTED %} selected="selected"{% endif %}>{{dateformat.NAME}}</option>
				{% endfor %}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_120" for="fc_default_time_format" title="{{translate('This is the default time format. This setting will be used for guests and as a default for new users.')}}">{{translate('Time format')}}:</label>
			<select name="default_time_format" id="fc_default_time_format">
				<option value="g:i A">{{translate('Please select')}}...</option>
				{% for timeformat in timeformats %}
				<option value="{{timeformat.VALUE}}"{% if timeformat.SELECTED %} selected="selected"{% endif %}>{{timeformat.NAME}}</option>
				{% endfor %}
			</select>
			<div class="clear_sp"></div>