			{if $DISPLAY_ADVANCED}
            <div class="fc_gradient_red" style="width:300px;float:right;">
                {translate('If you enable maintenance mode, your complete site will be OFFLINE!')}
            </div>

            <div class="fc_settings_max">
                <strong>{translate('Maintenance mode')}</strong><br /><strong></strong>
                <div class="fc_settings_label" id="fc_page_down">
                    <input type="checkbox" class="fc_checkbox_jq" name="maintenance_mode" id="fc_maintenance_mode" value="on" {if $values.maintenance_mode == 'on'} checked="checked"{/if} />
                    <label for="fc_maintenance_mode">{translate('Maintenance mode')}</label>
                </div>
            </div>

            <label class="fc_label_250" for="fc_maintenance_page">{translate('Page to show in maintenance mode')}</label>
            {$PAGES_LIST}
            <div class="clear_sp"></div>

            <hr />

			<label class="fc_label_250" for="fc_page_level_limit">{translate('Page level limit')}:</label>
			<select name="page_level_limit" id="fc_page_level_limit">
				{for count 0 10 1}
				<option value="{$count}"{if $count == $values.page_level_limit} selected="selected"{/if}>{$count}</option>
				{/for}
			</select>
			<hr />

			<div class="fc_settings_max">
				<strong>{translate('Page trash')}:</strong>
				<div class="fc_settings_label" id="fc_page_trash">
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_disabled" value="disabled"{if $values.page_trash == 'disabled'} checked="checked"{/if}/>
					<label for="fc_page_trash_disabled">{translate('Disabled')}</label>
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_inline" value="inline"{if $values.page_trash == 'inline'} checked="checked"{/if}/>
					<label for="fc_page_trash_inline">{translate('Inline')}</label>
					{if $values.page_trash == 'separate'}
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_separate" value="separate"{if $values.page_trash == 'separate'} checked="checked"{/if}/>
					<label for="fc_page_trash_separate">{translate('Separate')}</label>
					{/if}
				</div><!-- fc_page_trash -->
			</div>
			<hr />

			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="manage_sections" id="fc_manage_sections" value="true" {if $values.manage_sections} checked="checked"{/if}/>
				<label for="fc_manage_sections">{translate('Manage sections')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="section_blocks" id="fc_section_blocks" value="true" {if $values.section_blocks} checked="checked"{/if}/>
				<label for="fc_section_blocks">{translate('Sections blocks')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="multiple_menus" id="fc_multiple_menus" value="true" {if $values.multiple_menus} checked="checked"{/if}/>
				<label for="fc_multiple_menus">{translate('Multiple menus')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="page_languages" id="fc_page_languages" value="true" {if $values.page_languages} checked="checked"{/if}/>
				<label for="fc_page_languages">{translate('Page languages')}</label>

			{else}
            <div class="fc_settings_max">
				<input type="hidden" name="page_level_limit" value="{$values.page_level_limit}" />
				<input type="hidden" name="page_trash" value="{$values.page_trash}" />
				<input type="hidden" name="manage_sections" value="{$values.manage_sections}" />
				<input type="hidden" name="section_blocks" value="{$values.section_blocks}" />
				<input type="hidden" name="multiple_menus" value="{$values.multiple_menus}" />
				<input type="hidden" name="page_languages" value="{$values.page_languages}" />
			{/if}

				<input type="checkbox" class="fc_checkbox_jq" name="intro_page" id="fc_intro_page" value="true" {if $values.intro_page} checked="checked"{/if}/>
				<label for="fc_intro_page">{translate('Intro page')}</label>

			{if $DISPLAY_ADVANCED}
				<input type="checkbox" class="fc_checkbox_jq" name="homepage_redirection" id="fc_homepage_redirection" value="true" {if $values.homepage_redirection} checked="checked"{/if}/>
				<label for="fc_homepage_redirection">{translate('Homepage redirection')}</label>
			{else}
				<input type="hidden" name="homepage_redirection" value="{if $values.homepage_redirection}true{else}false{/if}" />
			{/if}
			</div>
			<div class="clear_sp"></div>
