			<label class="fc_label_200" for="fc_website_title" title="{translate('Used for the title tag in the HTML header.')}">{translate('Website title')}:</label>
			  <input type="text" name="website_title" id="fc_website_title"  value="{$values.website_title}" />
			<hr />

            <div class="fc_settings_max settings_label">
                <input type="checkbox" class="fc_checkbox_jq" name="use_short_urls" id="fc_use_short_urls" value="true" {if $values.use_short_urls} checked="checked"{/if}/>
    			<label for="fc_use_short_urls" title="{translate('This will allow to use SEO friendly URLs like http://www.yourdomain.com/path/to/page instead of http://www.yourdomain.com/page/path/to/page.php')}">{translate('Use short URLs (Apache webserver only, requires mod_rewrite!)')}</label><br />
            </div>
            <div class="clear_sp"></div>

			<label class="fc_label_200" for="fc_website_description" title="{translate('Used for the description META attribute.')}">{translate('Website description')}:</label>
			<textarea name="website_description" id="fc_website_description" cols="80" rows="6"  class="fc_input_300">{$values.website_description}</textarea>
			<div class="clear_sp"></div>

			<label class="fc_label_200" for="fc_website_keywords" title="{translate('Used for the keywords META attribute.')}">{translate('Website keywords')}:</label>
			<textarea name="website_keywords" id="fc_website_keywords" cols="80" rows="6" class="fc_input_300">{$values.website_keywords}</textarea>
			<div class="clear_sp"></div>
