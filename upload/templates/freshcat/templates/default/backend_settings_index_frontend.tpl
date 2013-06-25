            <label class="fc_label_200" for="fc_default_template">{translate('Template')}:</label>
			<select name="default_template" id="fc_default_template">
				{foreach $templates template}
				<option value="{$template.VALUE}"{if $template.SELECTED} selected="selected"{/if}>{$template.NAME}</option>
				{/foreach}
			</select>
			<hr />

			<label class="fc_label_200" for="fc_website_header">{translate('Website header')}:</label>
			<textarea name="website_header" id="fc_website_header" cols="80" rows="6"  class="fc_input_300">{$values.website_header}</textarea>
			<div class="clear_sp"></div>

			<label class="fc_label_200" for="fc_website_footer">{translate('Website footer')}:</label>
			<textarea name="website_footer" id="fc_website_footer" cols="80" rows="6"  class="fc_input_300">{$values.website_footer}</textarea>
			<div class="clear_sp"></div>
