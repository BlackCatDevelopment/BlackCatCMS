			{if $DISPLAY_ADVANCED}
            <div class="fc_gradient_red" style="width:300px;float:right;padding:3px 5px;">
                {translate('If you enable maintenance mode, your complete site will be OFFLINE!')}
            </div>

            <div class="fc_settings_max">
                <strong>{translate('Maintenance mode')}</strong><br /><strong></strong>
                <div class="fc_settings_label" id="fc_page_down">
                    <input type="checkbox" class="fc_checkbox_jq" name="maintenance_mode" id="fc_maintenance_mode" value="on" {if $values.maintenance_mode == 'on'} checked="checked"{/if} />
                    <label for="fc_maintenance_mode" title="{translate('In maintenance mode, only the page you choose here will be exposed to the visitor. All other pages are redirected to the maintenance page.')}">{translate('Maintenance mode')}</label>
                </div>
            </div>

            <label class="fc_label_250" for="fc_maintenance_page" title="{translate('This is the only page the visitor sees in maintenance mode. This page should have setting [hidden].')}">{translate('Page to show in maintenance mode')}</label>
            {$PAGES_LIST}
            <div class="clear_sp"></div>

            <hr />

			<label class="fc_label_250" for="fc_er_level" title="{translate('Sets which PHP errors are reported. For development, use E_ALL&E_STRICT. For production, use E_NONE.')}">{translate('PHP Error Reporting Level')}:</label>
			<select name="er_level" id="fc_er_level">
				<option value="">{translate('Please select')}...</option>
				{foreach $er_levels er}
				<option value="{$er.VALUE}"{if $er.SELECTED} selected="selected"{/if}>{$er.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>

            <hr />

			<label class="fc_label_250" for="fc_page_level_limit" title="{translate('Maximum depth of page tree')}">{translate('Page level limit')}:</label>
			<select name="page_level_limit" id="fc_page_level_limit">
				{for count 0 10 1}
				<option value="{$count}"{if $count == $values.page_level_limit} selected="selected"{/if}>{$count}</option>
				{/for}
			</select><br />

            <label class="fc_label_250" for="fc_err_page_404">{translate('Page to show on 404 "Not found" error')}</label>
            {$ERR_PAGES_LIST}
            <div class="clear_sp"></div>
			<hr />

			<div class="fc_settings_max">
				<strong>{translate('Page trash')}:</strong>
				<div class="fc_settings_label" id="fc_page_trash">
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_disabled" value="false"{if $values.page_trash == 'false' || $values.page_trash === false} checked="checked"{/if}/>
					<label for="fc_page_trash_disabled" title="{translate('Pages are deleted at once')}">{translate('Disabled')}</label>
					<input type="radio" class="fc_radio_jq" name="page_trash" id="fc_page_trash_inline" value="true"{if $values.page_trash == 'true' || $values.page_trash === true} checked="checked"{/if}/>
					<label for="fc_page_trash_inline" title="{translate('Pages are marked as \'deleted\' only and can be restored')}">{translate('Enabled')}</label>
				</div><!-- fc_page_trash -->
			</div>
			<hr />

			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="manage_sections" id="fc_manage_sections" value="true" {if $values.manage_sections} checked="checked"{/if}/>
				<label for="fc_manage_sections" title="{translate('Allows to completely disable the [Manage Sections] option of all pages, disabling the capability to add/remove or reorder the sections of any page')}">{translate('Manage sections')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="section_blocks" id="fc_section_blocks" value="true" {if $values.section_blocks} checked="checked"{/if}/>
				<label for="fc_section_blocks" title="{translate('If the template you are using supports multiple blocks, and you wish to use this feature, enable it here; requires [Manage sections] to be enabled, too')}">{translate('Sections blocks')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="multiple_menus" id="fc_multiple_menus" value="true" {if $values.multiple_menus} checked="checked"{/if}/>
				<label for="fc_multiple_menus" title="{translate('If the template you are using supports multiple menus, and you wish to use this feature, enable it here; enabling this feature while using a template with only 1 menu has no effect')}">{translate('Multiple menus')}</label>

				<input type="checkbox" class="fc_checkbox_jq" name="page_languages" id="fc_page_languages" value="true" {if $values.page_languages} checked="checked"{/if}/>
				<label for="fc_page_languages" title="{translate('When enabled, the system automatically hides any page from the website menu that is not in the language of the current logged-in user; guest users will see only the pages in the language chosen as the default for the site')}">{translate('Page languages')}</label>

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
				<label for="fc_intro_page" title="{translate('By default, the default or \'home\' page is the very first page listed in page tree; this options allows to have an introductory page that is totally different to – and outside the rest of – your site')}">{translate('Intro page')}</label>

			{if $DISPLAY_ADVANCED}
				<input type="checkbox" class="fc_checkbox_jq" name="homepage_redirection" id="fc_homepage_redirection" value="true" {if $values.homepage_redirection} checked="checked"{/if}/>
				<label for="fc_homepage_redirection" title="{translate('When a visitor first enters your site, the system \'silently\' redirects them to the default page, without changing the address that is displayed in the location bar. If this option is enabled, the redirection will be visible.')}">{translate('Homepage redirection')}</label>
			{else}
				<input type="hidden" name="homepage_redirection" value="{if $values.homepage_redirection}true{else}false{/if}" />
			{/if}
			</div>
			<div class="clear_sp"></div>
