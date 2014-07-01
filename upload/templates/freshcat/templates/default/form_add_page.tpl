<form name="add_page" action="#" method="post" id="fc_add_page" class="fc_gradient1 shadow_small" style="display:none;">
    <div id="fc_add_page_header" class="fc_gradient1 fc_border fc_gradient_hover">
        <span><a href="#" id="fc_add_page_close" class="icon-cancel-2" title="{translate('Close')}"></a></span>
    </div>
	<nav>
		<ul id="fc_add_page_nav">
			<li><a href="#fc_tabs_general" class="fc_gradient1 fc_gradient_hover fc_first fc_active icon-file" title="{translate('General Settings')}"></a></li>
			<li><a href="#fc_tabs_settings" class="fc_gradient1 fc_gradient_hover icon-tools" title="{translate('Preferences')}"></a></li>
			<li><a href="#fc_tabs_seo" class="fc_gradient1 fc_gradient_hover icon-search" title="{translate('SEO Settings')}"></a></li>
			<li><a href="#fc_tabs_privacy" class="fc_gradient1 fc_gradient_hover icon-shield" title="{translate('Security Settings')}"></a></li>
		</ul>
	</nav>
	<ul id="fc_add_page_ul">
		<li id="fc_tabs_general" class="fc_active">
			<label for="fc_addPage_title">{translate('Menu title')}:</label>
			<input type="text" name="menu_title" id="fc_addPage_title" value="" />

			<label for="fc_addPage_page_title">{translate('Title')}:</label>
			<input type="text" name="page_title" value="" id="fc_addPage_page_title" />

            <label for="fc_addPage_page_link">{translate('URL')}:</label>
			<input type="text" name="page_link" value="" id="fc_addPage_page_link" />{$PAGE_EXTENSION}

			<div class="fc_addPageOnly">
				<label for="fc_addPage_type">{translate('Type')}:</label>
				<select name="type" id="fc_addPage_type">
					{foreach $modules as module}
					<option value="{$module.VALUE}"{if $module.SELECTED} selected="selected"{/if}>{$module.NAME}</option>
					{/foreach}
				</select>
			</div>
			<input type="hidden" name="parent_page_id" id="fc_addPage_parent_page_id" value=""/>
			<label for="fc_addPage_parent">{translate('Parent')}:</label>
			<select name="parent" id="fc_addPage_parent">
				<option></option>
			</select>

		</li>
		<li id="fc_tabs_settings">
			{if $DISPLAY_MENU_LIST}
			<label for="fc_addPage_menu">{translate('Menu')}:</label>
			<select name="menu" id="fc_addPage_menu">
				{foreach $TEMPLATE_MENU as menu}
				<option value="{$menu.VALUE}"{if $menu.SELECTED} selected="selected"{/if}>{$menu.NAME}</option>
				{/foreach}
			</select>
			{/if}
			<label for="fc_addPage_target">{translate('Target')}:</label>
			<select name="target" id="fc_addPage_target">
				<option value="_blank">{translate('New window')}</option>
				<option value="_self" selected="selected">{translate('Same window')}</option>
				<option value="_top">{translate('Top frame')}</option>
			</select>

			<label for="fc_addPage_template">{translate('Template')}:</label>
			<select name="template" id="fc_addPage_template">
				<option value="" selected="selected">{translate('System default')}</option>
				<option value="" disabled="disabled">----------------------</option>
				{foreach $templates as template}
				<option value="{$template.VALUE}">{$template.NAME}</option>
				{/foreach}
            </select><br />

            <div id="fc_div_template_variants" style="display:{if $variants || $template_variant}inline-block{else}none{/if}">
            <label for="fc_default_template_variant">{translate('Variant')}:</label>
            <select name="default_template_variant" id="fc_default_template_variant">
                {foreach $variants variant}
                <option value="{$variant}"{if $variant == $template_variant} selected="selected"{/if}>{$variant}</option>
                {/foreach}
			</select>
            </div>

			{if $DISPLAY_LANGUAGE_LIST}
			<label for="fc_addPage_language">{translate('Language')}:</label>
			<select name="language" id="fc_addPage_language">
				{foreach $languages as language}
				<option value="{$language.VALUE}"{if $language.SELECTED} selected="selected"{/if}>{$language.NAME}</option> {*$language.FLAG_LANG_ICONS*}
				{/foreach}
			</select>
			{/if}
	
		</li>
	
		<li id="fc_tabs_seo">
			<label for="fc_addPage_description">{translate('Description')}:</label>
			<textarea name="description" id="fc_addPage_description" rows="10" cols="1" ></textarea>

			<label for="fc_addPage_keywords">{translate('Keywords')}:</label>
			<input name="keywords" type="hidden" id="fc_addPage_keywords" value="" />
		</li>
	
		<li id="fc_tabs_privacy">
			{*need to include settings of searching - if searching is generally disabled, don't show this option*}
			<input type="checkbox" class="fc_checkbox_jq" name="searching" id="fc_addPage_Searching" value="1" {if !$SEARCHING_DISABLED} checked="checked"{/if} />
			<label for="fc_addPage_Searching">{translate('Searching')}</label>

			<label for="fc_addPage_visibility">{translate('Visibility')}:</label>
			<select name="visibility" id="fc_addPage_visibility" class="fc_toggle_element">
				<option value="public" class="hide___fc_addPage_allowed_viewers">{translate('Public')}</option>
				<option value="private" class="show___fc_addPage_allowed_viewers">{translate('Private')}</option>
				<option value="registered" class="show___fc_addPage_allowed_viewers">{translate('Registered')}</option>
				<option value="hidden" class="hide___fc_addPage_allowed_viewers">{translate('Hidden')}</option>
				<option value="none" class="hide___fc_addPage_allowed_viewers">{translate('None')}</option>
			</select>

			<div id="fc_addPage_allowed_admins">
                <hr />
				<strong>{translate('Administrators')}:</strong>
				<div class="fc_settings_label" id="fc_addPage_admin_groups">
					{foreach $groups.admins group}
					<input type="checkbox" class="fc_checkbox_jq" name="admin_groups[]" id="fc_admin_group_{$group.VALUE}" value="{$group.VALUE}"{if $group.CHECKED} checked="checked"{/if}{if $group.DISABLED} disabled="disabled"{/if} />
					<label for="fc_admin_group_{$group.VALUE}" class="buttonset">{$group.NAME}</label>
					{/foreach}
				</div>
			</div>

			<div id="fc_addPage_allowed_viewers" class="{if $VISIBILITY == 'private' || $VISIBILITY == 'registered'}active_element{else}inactive_element{/if}">
                <hr />
				<strong>{translate('Registered viewers')}:</strong>
				<div class="fc_settings_label" id="fc_addPage_viewers_groups">
					{foreach $groups.viewers viewer}
					<input type="checkbox" class="fc_checkbox_jq" name="viewing_groups[]" id="fc_viewing_group_{$viewer.VALUE}" value="{$viewer.VALUE}"{if $viewer.CHECKED} checked="checked"{/if}{if $viewer.DISABLED} disabled="disabled"{/if}/>
					<label for="fc_viewing_group_{$viewer.VALUE}" class="buttonset">{$viewer.NAME}</label>
					{/foreach}
				</div>
			</div>
		</li>
	</ul>
	<p>
		<button type="submit" name="add_page" class="icon-checkmark fc_addPageOnly" id="fc_addPageSubmit"> {translate('Add page')}</button>
		<button type="submit" name="save_page" class="icon-checkmark fc_changePageOnly" id="fc_savePageSubmit"> {translate('Save page')}</button>
		<button type="submit" name="restore_page" class="icon-checkmark fc_restorePageOnly" id="fc_restorePageSubmit"> {translate('Restore page')}</button>
		<button type="submit" name="add_child_page" class="icon-file-add fc_changePageOnly" id="fc_addPageChildSubmit"> {translate('Add child page')}</button>
		<button type="reset" name="reset" class="fc_gradient1 fc_gradient_hover" id="fc_addPageReset">{translate('Close & Reset')}</button>
	</p>
	<hr class="fc_changePageOnly fc_restorePageOnly"/>
	<p>
		<button type="submit" name="remove_page" class="icon-remove fc_gradient_red fc_changePageOnly fc_restorePageOnly" id="fc_removePageSubmit"> {translate('Remove page')}</button>
	</p>
</form>
