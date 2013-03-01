			<label class="fc_label_300" for="fc_default_template">{translate('Backend theme')}:</label>
			<select name="default_theme" id="fc_default_theme">
				{foreach $backends backend}
				<option value="{$backend.VALUE}"{if $backend.SELECTED} selected="selected"{/if}>{$backend.NAME}</option>
				{/foreach}
			</select>
			<hr />

			<label class="fc_label_300" for="fc_wysiwyg_editor">{translate('WYSIWYG Editor')}:</label>
			<select name="wysiwyg_editor" id="fc_wysiwyg_editor">
				{foreach $wysiwyg module}
				<option value="{$module.VALUE}"{if $module.SELECTED} selected="selected"{/if}>{$module.NAME}</option>
				{/foreach}
			</select>

			{if $DISPLAY_ADVANCED}
			<hr />
			<label class="fc_label_300" for="fc_er_level">{translate('PHP Error Reporting Level')}:</label>
			<select name="er_level" id="fc_er_level">
				<option value="">{translate('Please select')}...</option>
				{foreach $er_levels er}
				<option value="{$er.VALUE}"{if $er.SELECTED} selected="selected"{/if}>{$er.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_redirect_timer">{translate('Redirect after')}:</label>
			<input type="text" name="redirect_timer" id="fc_redirect_timer"  value="{$values.redirect_timer}" />ms
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_token_lifetime">{translate('Token lifetime')}:</label>
			<input type="text" name="token_lifetime" id="fc_token_lifetime"  value="{$values.token_lifetime}" /> s
			<p>
				{translate('0 means default, which is 7200s = 2 hours')}
			</p>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_max_attempts">{translate('Allowed wrong login attempts')}:</label>
			<input type="text" name="max_attempts" id="fc_max_attempts"  value="{$values.max_attempts}" />
			<p>
				{translate('When reaching this number, more login attempts are not possible for this session.')}
			</p>
			<div class="clear_sp"></div>
			{else}
			<input type="hidden" name="er_level" value="{$ER_LEVEL}" />
			<input type="hidden" name="redirect_timer" value="{$values.redirect_timer}" />
			<input type="hidden" name="token_lifetime" value="{$values.token_lifetime}" />
			<input type="hidden" name="max_attempts" value="{$values.max_attempts}" />
			{/if}
			<div class="clear_sp"></div>
			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>