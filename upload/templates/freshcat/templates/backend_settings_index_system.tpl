			{if $DISPLAY_ADVANCED}
            <div class="fc_gradient_red" style="width:300px;float:right;">
                {translate('If you enable maintenance mode, your complete site will be OFFLINE!')}
            </div>

            <div class="fc_settings_max">
                <strong>{translate('Maintenance mode')}</strong><br /><strong></strong>
                <div class="fc_settings_label" id="fc_page_down">
                    <input type="checkbox" class="fc_checkbox_jq" name="maintenance_mode" id="fc_maintenance_mode" value="on" />
                    <label for="fc_maintenance_mode">{translate('Maintenance mode')}</label>
                </div>
            </div>

            <label class="fc_label_120" for="fc_maintenance_page">{translate('Page to show in maintenance mode')}</label>
            {$PAGES_LIST}
            <div class="clear_sp"></div>

            <hr />

			<label class="fc_label_120" for="fc_page_level_limit">{translate('Page level limit')}:</label>
			<select name="page_level_limit" id="fc_page_level_limit">
				{for count 0 10 1}
				<option value="{$count}"{if $count == $PAGE_LEVEL_LIMIT} selected="selected"{/if}>{$count}</option>
				{/for}
			</select>
			<hr />

			<div class="fc_settings_max">
				<strong>{translate('Page trash')}:</strong>
				<div class="fc_settings_label" id="fc_page_trash">
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_disabled" value="disabled"{if $PAGE_TRASH == 'disabled'} checked="checked"{/if}/>
					<label for="fc_page_trash_disabled">{translate('Disabled')}</label>
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_inline" value="inline"{if $PAGE_TRASH == 'inline'} checked="checked"{/if}/>
					<label for="fc_page_trash_inline">{translate('Inline')}</label>
					{if $PAGE_TRASH == 'separate'}
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_separate" value="separate"{if $PAGE_TRASH == 'separate'} checked="checked"{/if}/>
					<label for="fc_page_trash_separate">{translate('Separate')}</label>
					{/if}
				</div><!-- fc_page_trash -->
			</div>
			<hr />

			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="manage_sections" id="fc_manage_sections" value="true" {if $MANAGE_SECTIONS} checked="checked"{/if}/>
				<label for="fc_manage_sections">{translate('Manage sections')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="section_blocks" id="fc_section_blocks" value="true" {if $SECTION_BLOCKS} checked="checked"{/if}/>
				<label for="fc_section_blocks">{translate('Sections blocks')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="multiple_menus" id="fc_multiple_menus" value="true" {if $MULTIPLE_MENUS} checked="checked"{/if}/>
				<label for="fc_multiple_menus">{translate('Multiple menus')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="page_languages" id="fc_page_languages" value="true" {if $PAGE_LANGUAGES} checked="checked"{/if}/>
				<label for="fc_page_languages">{translate('Page languages')}</label>

			{else}
            <div class="fc_settings_max">
				<input type="hidden" name="page_level_limit" value="{$PAGE_LEVEL_LIMIT}" />
				<input type="hidden" name="page_trash" value="{$PAGE_TRASH}" />
				<input type="hidden" name="manage_sections" value="{$MANAGE_SECTIONS}" />
				<input type="hidden" name="section_blocks" value="{$SECTION_BLOCKS}" />
				<input type="hidden" name="multiple_menus" value="{$MULTIPLE_MENUS}" />
				<input type="hidden" name="page_languages" value="{$PAGE_LANGUAGES}" />
			{/if}

				<input type="checkbox" class="fc_checkbox_jq" name="intro_page" id="fc_intro_page" value="true" {if $INTRO_PAGE} checked="checked"{/if}/>
				<label for="fc_intro_page">{translate('Intro page')}</label>

			{if $DISPLAY_ADVANCED}
				<input type="checkbox" class="fc_checkbox_jq" name="homepage_redirection" id="fc_homepage_redirection" value="true" {if $HOMEPAGE_REDIRECTION} checked="checked"{/if}/>
				<label for="fc_homepage_redirection">{translate('Homepage redirection')}</label>
			{else}
				<input type="hidden" name="homepage_redirection" value="{if $HOMEPAGE_REDIRECTION}true{else}false{/if}" />
			{/if}
			</div>
			<div class="clear_sp"></div>
			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>