			<label class="fc_label_300" for="fc_search">{translate('Visibility')}:</label>
			<select name="search" id="fc_search">
				<option value="public"{if $search.search == 'public'} selected="selected"{/if}>{translate('Public')}</option>
				<option value="private"{if $search.search == 'private'} selected="selected"{/if}>{translate('Private')}</option>
				<option value="registered"{if $search.search == 'registered'} selected="selected"{/if}>{translate('Registered')}</option>
				<option value="none"{if $search.search == 'none'} selected="selected"{/if}>{translate('None')}</option>
			</select>
			<div class="clear_sp"></div>

			{if $DISPLAY_ADVANCED}
			<label class="fc_label_300" for="fc_search_cfg_search_library">{translate('Search library')}:</label>
			<input type="text" name="search_cfg_search_library" id="fc_search_library" value="{$search.cfg_search_library}" />
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_template">{translate('Standard page: Template for search result')}:</label>
			<select name="search_template" id="fc_search_template">
				{foreach $search_templates search_template}
				<option value="{$search_template.VALUE}"{if $search_template.SELECTED} selected="selected"{/if}>{$search_template.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_cfg_search_use_page_id">{translate('Individual page: PAGE_ID for search result')}:</label>
			{$PAGES_LIST}
			<div class="clear_sp"></div>

            <div class="fc_settings_max_large">
              <input type="checkbox" class="fc_checkbox_jq" name="search_cfg_search_images" id="fc_search_cfg_search_images" value="true" {if $search.cfg_search_images == 'true'} checked="checked"{/if}/>
			  <label for="fc_search_cfg_search_images">{translate('Search for images')}:</label>
            </div>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_cfg_content_image">{translate('Use image from content page in search result')}:</label>
			<select name="search_cfg_content_image" id="fc_searchcfg_content_image">
				<option value="none"{if $search.cfg_content_image == 'none'} selected="selected"{/if}>{translate('None')}</option>
				<option value="first"{if $search.cfg_content_image == 'first'} selected="selected"{/if}>{translate('First')}</option>
				<option value="last"{if $search.cfg_content_image == 'last'} selected="selected"{/if}>{translate('Last')}</option>
				<option value="random"{if $search.cfg_content_image == 'random'} selected="selected"{/if}>{translate('Random')}</option>
			</select>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_cfg_thumbs_width">{translate('Max. width/height of images in search result')}:</label>
			<input type="text" name="search_cfg_thumbs_width" id="fc_search_cfg_thumbs_width" value="{$search.cfg_thumbs_width}" />
			<div class="clear_sp"></div>

            <div class="fc_settings_max_large">
              <input type="checkbox" class="fc_checkbox_jq" name="search_cfg_search_description" id="fc_search_cfg_search_description" value="true" {if $search.cfg_search_description == 'true'} checked="checked"{/if}/>
			  <label for="fc_search_cfg_search_description">{translate('Search for page descriptions')}:</label>
            </div>
			<div class="clear_sp"></div>

            <div class="fc_settings_max_large">
              <input type="checkbox" class="fc_checkbox_jq" name="search_cfg_show_description" id="fc_search_cfg_show_description" value="true" {if $search.cfg_show_description == 'true'} checked="checked"{/if}/>
			  <label for="fc_search_cfg_show_description">{translate('Show page description in search result')}:</label>
            </div>
			<div class="clear_sp"></div>

            <div class="fc_settings_max_large">
              <input type="checkbox" class="fc_checkbox_jq" name="search_cfg_search_keywords" id="fc_search_cfg_search_keywords" value="true" {if $search.cfg_search_keywords == 'true'} checked="checked"{/if}/>
			  <label for="fc_search_cfg_search_keywords">{translate('Search for page keywords')}:</label>
            </div>
			<div class="clear_sp"></div>

            <div class="fc_settings_max_large">
              <input type="checkbox" class="fc_checkbox_jq" name="search_cfg_search_non_public_content" id="fc_search_cfg_search_non_public_content" value="true" {if $search.cfg_search_non_public_content == 'true'} checked="checked"{/if}/>
			  <label for="fc_search_cfg_search_non_public_content">{translate('Search in non-public content')}:</label>
            </div>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_cfg_link_non_public_content">{translate('Redirect link (URL) for non-public content')}:</label>
			<input type="text" name="search_cfg_link_non_public_content" id="fc_search_cfg_non_public_content" value="{$search.cfg_link_non_public_content}" />
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_module_order">{translate('Module-order for searching')}:</label>
			<input type="text" name="search_module_order" id="fc_search_module_order" value="{$search.module_order}" />
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_max_excerpt">{translate('Max lines of excerpt')}:</label>
			<input type="text" name="search_max_excerpt" id="fc_search_max_excerpt" value="{$search.max_excerpt}" />
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_search_time_limit">{translate('Max time to gather excerpts per module')}:</label>
			<input type="text" name="search_time_limit" id="fc_search_time_limit" value="{$search.time_limit}" />
			{else}
			<input type="hidden" name="search_template" value="{$search.template}" />
			<input type="hidden" name="search_cfg_content_image" value="{$search.cfg_content_image}" />
			<input type="hidden" name="search_cfg_search_droplet" value="{$search.cfg_search_droplet}" />
			<input type="hidden" name="search_cfg_search_use_page_id" value="{$search.cfg_search_use_page_id}" />
			<input type="hidden" name="search_cfg_search_images" value="{$search.cfg_search_images}" />
			<input type="hidden" name="search_cfg_show_description" value="{$search.cfg_show_description}" />
			<input type="hidden" name="search_cfg_search_description" value="{$search.cfg_search_description}" />
			<input type="hidden" name="search_cfg_search_keywords" value="{$search.cfg_search_keywords}" />
			<input type="hidden" name="search_cfg_link_non_public_content" value="{$search.cfg_link_non_public_content}" />
			<input type="hidden" name="search_cfg_search_non_public_content" value="{$search.cfg_search_non_public_content}" />
			<input type="hidden" name="search_cfg_thumbs_width" value="{$search.cfg_thumbs_width}" />
			<input type="hidden" name="seach_cfg_search_library" value="{$search.cfg_search_library}" />
			<input type="hidden" name="search_module_order" value="{$search.module_order}" />
			<input type="hidden" name="search_max_excerpt" value="{$search.max_excerpt}" />
			<input type="hidden" name="search_time_limit" value="{$search.time_limit}" />
			{/if}
			<div class="clear_sp"></div>
