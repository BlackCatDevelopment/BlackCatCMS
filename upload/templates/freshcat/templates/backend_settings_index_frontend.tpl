            <label class="fc_label_120" for="fc_default_template">{translate('Template')}:</label>
			<select name="default_template" id="fc_default_template">
				{foreach $templates template}
				<option value="{$template.VALUE}"{if $template.SELECTED} selected="selected"{/if}>{$template.NAME}</option>
				{/foreach}
			</select>
			<hr />

			<label class="fc_label_200" for="fc_website_header">{translate('Website header')}:</label><br />
			<textarea name="website_header" id="fc_website_header" cols="80" rows="6" >{$values.website_header}</textarea>
			<div class="clear_sp"></div>

			<label class="fc_label_200" for="fc_website_footer">{translate('Website footer')}:</label><br />
			<textarea name="website_footer" id="fc_website_footer" cols="80" rows="6" >{$values.website_footer}</textarea>
			<div class="clear_sp"></div>

			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>

