<div id="fc_content_header">
    {translate('Addons')}
</div>
<div id="fc_main_content">
    <div id="fc_lists_overview" class="fc_addons">
        <div id="fc_list_search">
            <div class="fc_input_fake">
                <input type="text" name="fc_list_search" id="fc_list_search_input" value="{translate('Search...')}" />
                <label class="fc_close" for="fc_list_search_input"></label>
            </div>
        </div>
        <div class="fc_gradient1 fc_border">
            <button class="icon-puzzle fc_active fc_gradient1 fc_gradient_hover" title="{translate('Modules')}"></button>
            <button class="icon-color-palette fc_gradient1 fc_gradient_hover" title="{translate('Templates')}"></button>
            <button class="icon-comments fc_gradient1 fc_gradient_hover" title="{translate('Languages')}"></button>
            <button class="icon-folder-add fc_gradient1 fc_gradient_hover" title="{translate('Not installed yet')}"></button>
            {if $permissions.MODULES_INSTALL}<button id="fc_list_add" class="icon-plus fc_gradient1 fc_gradient_hover" title="{translate('Install Addon')}"></button>{/if}
            <div class="clear"></div>
        </div>
        <ul id="fc_list_overview" class="fc_group_list">
{foreach $addons as addon}
            {if $addon.name}
            <li class="fc_module_item fc_type_{$addon.type}s fc_border fc_gradient1 fc_gradient_hover{if $addon.is_installed === false} fc_not_installed{/if}">
                {if $addon.icon}<img src="{$addon.icon}" alt="{$addon.directory}" />
                {elseif $addon.type == 'templates'}<span class="icon-color-palette"></span>{elseif $addon.type == 'languages'}<span class="icon-comments"></span>{else}<span class="icon-puzzle"></span>{/if}
                <span class="fc_groups_name"> {$addon.name}</span>
                <input type="hidden" name="addon_directory" value="{$addon.directory}" />
                <input type="hidden" name="addon_type" value="{$addon.type}" />
            </li>
            {else}
            <li class="fc_uninstalled_addon">
                <span class="fc_groups_name">{$addon.INSTALL.name}</span>
            </li>
            {/if}
{/foreach}
            {if $not_installed_addons}
            <li class="fc_border fc_gradient4 fc_not_installed fc_type_heading">
                <span class="fc_groups_name">{translate('Not installed yet')}</span>
            </li>
{foreach $not_installed_addons type addons}
    {foreach $addons as addon}
            {if $addon.name}
            <li class="fc_module_item fc_border fc_gradient1 fc_gradient_hover fc_type_{$type} fc_not_installed">
                {if $addon.icon}<img src="{$addon.icon}" alt="{$addon.directory}" />
                {else}<span class="icon-puzzle"></span>{/if}
                <span class="fc_groups_name"> {$addon.name}</span>
                <input type="hidden" name="addon_directory" value="{$addon.directory}" />
                <input type="hidden" name="addon_type" value="{$addon.type}" />
            </li>
            {else}
            <li class="fc_uninstalled_addon">
                <span class="fc_groups_name">{$addon.INSTALL.name}</span>
            </li>
            {/if}
    {/foreach}
{/foreach}
            {/if}
        </ul>
    </div>
    <div class="fc_all_forms">
        {if $permissions.MODULES_INSTALL}
        <form name="install" enctype="multipart/form-data" action="install.php" method="post" id="fc_install_new" class="fc_list_forms">
            <p class="submit_settings fc_gradient1">
                <strong>{translate('Install addon')}</strong>
                <input type="submit" name="submit" value="{translate('Install addon')}" />
                <input type="reset" name="reset" value="{translate('Reset')}" />
            </p>
            <div class="clear_sp"></div>
            <p>
                <input type="file" name="userfile" />
            </p>
            {if $groups.viewers}
            <hr />
            <h3>{translate('Addon permissions')}</h3>
            <p>
                {translate('You can set permissions for each group to use this addon.')}<br />
                {translate('You can customize permissions later on group administration.')}<br />
                {translate('If you upgrade a module, those settings will have no effect on current permissions.')}
            </p>
            <button class="fc_gradient1 fc_gradient_hover" id="fc_mark_all">
                <span class="fc_mark">{translate('Mark all groups')}</span>
                <span class="fc_unmark hidden">{translate('Unmark all groups')}</span>
            </button>
            <div class="clear_sp"></div>
            <div id="fc_perm_groups" class="fc_settings_max">
                {foreach $groups.viewers as group}
                <input type="checkbox" class="fc_checkbox_jq" name="group_id[]" id="fc_group_{$group.VALUE}" value="{$group.VALUE}" />
                <label for="fc_group_{$group.VALUE}">{$group.NAME}</label>
                {/foreach}
            </div>
            <div class="clear_sp"></div>
            {/if}
            <p class="submit_settings fc_gradient1">
                <button class="fc_gradient1 fc_gradient_hover" id="fc_add_new_module_button" style="float:left;padding: 0 0.3em;">
                {translate('Create new addon')}
                </button>
                <input type="submit" name="submit" value="{translate('Install addon')}" />
                <input type="reset" name="reset" value="{translate('Reset')}" />
            </p>
        </form>
        {/if}

        <div id="addon_details"></div>

        {if $permissions.MODULES_INSTALL}
        <br /><br />
        <form class="ajaxForm fc_list_forms" style="display:none;margin-left:10px;" id="fc_add_new_module" action="ajax_create.php" method="post">
            <p>{translate('Please fill out the form to create a new addon. An empty directory with an info.php file will be created for you to start your work.')}</p>
            <p>{translate("If you're adding a language, a language file will be created in the languages subfolder.")}</p>
            <input type="hidden" name="_cat_ajax" value="1" />
            <label class="fc_label_200" for="fc_new_moduletype">{translate('Module type')}</label>
                <select name="new_moduletype" id="fc_new_moduletype">
                    <option value="module">{translate('Module (Page)')}</option>
                    <option value="tool">{translate('Admin-Tool')}</option>
                    <option value="language">{translate('Language')}</option>
                    <option value="template">{translate('Template')}</option>
                    <option value="library">{translate('Library')}</option>
                    <option value="wysiwyg">{translate('WYSIWYG-Editor')}</option>
                </select><br />
            <label class="fc_label_200" for="fc_new_modulename">{translate('Module / language name')}</label>
                <input type="text" id="fc_new_modulename" name="new_modulename" /><br />
            <label class="fc_label_200" for="fc_new_moduledir">{translate('Module directory / language code')}</label>
                <input type="text" id="fc_new_moduledir" name="new_moduledir" /><br />
            <label class="fc_label_200" for="fc_new_moduledesc">{translate('Module description')}</label>
                <input type="text" id="fc_new_moduledesc" name="new_moduledesc" /><br />
            <label class="fc_label_200" for="fc_new_modulename">{translate('Author')}</label>
                <input type="text" id="fc_new_moduleauthor" name="new_moduleauthor" value="{$username}" /><br /><br />
            <p class="submit_settings fc_gradient1" style="margin-left:-10px;text-align:left;">
                <input type="submit" id="fc_new_submit" value="{translate('Save')}" />
                <input type="reset" name="reset" value="{translate('Reset')}" />
            </p>
        </form>
        {/if}

    </div>
</div>