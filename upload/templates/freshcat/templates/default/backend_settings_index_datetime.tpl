			<label class="fc_label_120" for="fc_default_language">{translate('Language')}:</label>
			<select name="default_language" id="fc_default_language">
				{foreach $languages language}
				<option value="{$language.CODE}"{if $language.SELECTED} selected="selected"{/if}>{$language.NAME} ({$language.CODE})</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

			{if $DISPLAY_ADVANCED}
			<label class="fc_label_120" for="fc_default_charset">{translate('Charset')}:</label>
			<select name="default_charset" id="fc_default_charset">
				<option value="">{translate('Please select')}...</option>
				{foreach $charsets charset}
				<option value="{$charset.VALUE}"{if $charset.SELECTED} selected="selected"{/if}>{$charset.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
			{else}
				<input type="hidden" name="default_charset" value="{$DEFAULT_CHARSET}" />
			{/if}
			<hr />
			<label class="fc_label_120" for="fc_default_timezone_string">{translate('Timezone')}:</label>
			<select name="default_timezone_string" id="fc_default_timezone_string">
				<option value="0">{translate('Please select')}...</option>
				{foreach $timezones timezone}
				<option {if $timezone.SELECTED} selected="selected"{/if}>{$timezone.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_120" for="fc_default_date_format">{translate('Date format')}:</label>
			<select name="default_date_format" id="fc_default_date_format">
				<option value="M d Y">{translate('Please select')}...</option>
				{foreach $dateformats dateformat}
				<option value="{$dateformat.VALUE}"{if $dateformat.SELECTED} selected="selected"{/if}>{$dateformat.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_120" for="fc_default_time_format">{translate('Time format')}:</label>
			<select name="default_time_format" id="fc_default_time_format">
				<option value="g:i A">{translate('Please select')}...</option>
				{foreach $timeformats timeformat}
				<option value="{$timeformat.VALUE}"{if $timeformat.SELECTED} selected="selected"{/if}>{$timeformat.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
