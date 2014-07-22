{*each class .advanced_label should be checked for advanced permission if we keep it in the Core*}

<div id="fc_content_header">
    {translate('Modify groups')}
    <div class="fc_header_buttons">
        {if $permissions.USERS}<a href="{$CAT_ADMIN_URL}/users/index.php" class="fc_br_left fc_gradient1 fc_gradient_hover">{translate('Manage users')}</a>{/if}
        <a href="{$CAT_ADMIN_URL}/groups/index.php" class="{if !$permissions.USERS}fc_br_all {else}fc_br_right {/if}fc_gradient1 fc_gradient_hover fc_active">{translate('Manage groups')}</a>
    </div>
    <div class="clear"></div>
</div>

<div class="highlight" style="width:100%">
{translate('Please note: The BlackCat CMS permissions system is going to be reengineered with version 2.0. We recommend to keep your permissions as simple as possible until next version. We kindly ask for your understanding.')}
</div>

<div id="fc_main_content">
    <div id="fc_lists_overview">
        <div id="fc_list_search">
            <div class="fc_input_fake">
                <input type="text" name="fc_list_search" id="fc_list_search_input" value="{translate('Search...')}" />
                <label class="fc_close" for="fc_list_search_input"></label>
            </div>
        </div>

        <div class="fc_gradient1 fc_border">
            {if $permissions.GROUPS_ADD}<button id="fc_list_add" class="icon-plus fc_cell_one fc_gradient1 fc_gradient_hover" title="{translate('Add group')}"></button>{/if}
            <div class="clear"></div>
        </div>

        <ul id="fc_list_overview" class="fc_group_list">
            {foreach $groups.viewers as group}
            <li class="fc_group_item icon-users fc_border fc_gradient1 fc_gradient_hover">
                <span class="fc_groups_name">{$group.NAME}</span>
                <input type="hidden" name="group_id" value="{$group.VALUE}" />
            </li>
            {/foreach}
        </ul>
    </div>
    {if $permissions.GROUPS_MODIFY}
    <div class="fc_all_forms">
        <form action="{$CAT_ADMIN_URL}/groups/ajax_save_group.php" method="post" id="fc_Group_form" class="fc_list_forms">
            <p class="submit_settings fc_gradient1">
                <strong class="fc_addGroup">{translate('Add group')}</strong>
                <strong class="fc_modifyGroup">{translate('Modify group')}</strong>
                <input type="submit" name="addGroup" value="{translate('Add group')}" class="fc_addGroup" />
                <input type="submit" name="saveGroup" value="{translate('Save group')}" class="fc_modifyGroup" />
                <input type="reset" name="reset" value="{translate('Reset')}">
                <input type="hidden" name="group_id" id="fc_Group_group_id" value="" />
            </p>
            <div class="clear_sp"></div>
            <label for="fc_Group_name">{translate('Group name')}:</label>
            <input type="text" name="name" id="fc_Group_name" value="">
            <div class="clear_sp"></div>
            <ul class="fc_groups_tabs fc_gradient1 fc_border clearfix">
                <li><a class="fc_gradient1 fc_gradient_hover fc_active" href="#fc_tabs_system_permissions"><span class="icon-cog"></span> {translate('General System')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_modules"><span class="icon-puzzle"></span> {translate('Modules')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_admintools"><span class="icon-wrench"></span> {translate('Admintools')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_templates"><span class="icon-color-palette"></span> {translate('Templates')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_media"><span class="icon-pictures"></span> {translate('Media')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_groups"><span class="icon-users"></span> {translate('Permissions')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_languages"><span class="icon-comments"></span> {translate('Languages')}</a></li>
                <li><a class="fc_gradient1 fc_gradient_hover" href="#fc_tabs_members"><span class="icon-users"></span> {translate('Members')}</a></li>
            </ul>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_system_permissions">
            {include backend_groups_index_system.tpl}
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_modules">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_modules" name="modules" id="fc_Group_modules" value="1" />
                <label for="fc_Group_modules">{translate('Modules')}</label>
                <div id="fc_modules" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="modules_view" id="fc_Group_modules_view" value="1" />
                    <label for="fc_Group_modules_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="modules_install" id="fc_Group_modules_install" value="1" />
                    <label for="fc_Group_modules_install">{translate('Add')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="modules_uninstall" id="fc_Group_modules_uninstall" value="1" />
                    <label for="fc_Group_modules_uninstall">{translate('Delete')}</label>
                </div>
                <hr />
                <strong>{translate('Installed modules')}</strong>
                <div class="clear_sp"></div>
                <ul>
                    {foreach $modules as module}
                    <li>
                        <input type="checkbox" class="fc_checkbox_jq" name="module_permissions[]" id="fc_Group_m_{$module.VALUE}" value="{$module.VALUE}">
                        <label for="fc_Group_m_{$module.VALUE}">{$module.NAME}</label>
                    </li>
                    {/foreach}
                </ul>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_admintools">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_Group_admintools_div" name="admintools" id="fc_Group_admintools" value="1" />
                <label for="fc_Group_admintools">{translate('Admintools')}</label>
                <div id="fc_Group_admintools_div" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="admintools_settings" id="fc_Group_admintools_settings" value="1" />
                    <label for="fc_Group_admintools_settings">{translate('Modify settings')}</label>
                </div>
                <hr />
                <strong>{translate('Installed admintools')}</strong>
                <div class="clear_sp"></div>
                <ul>
                    {foreach $admintools as admintool}
                    <li>
                        <input type="checkbox" class="fc_checkbox_jq" name="module_permissions[]" id="fc_Group_m_{$admintool.VALUE}" value="{$admintool.VALUE}">
                        <label for="fc_Group_m_{$admintool.VALUE}">{$admintool.NAME}</label>
                    </li>
                    {/foreach}
                </ul>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_templates">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_templates" name="templates" id="fc_Group_templates" value="1" />
                <label for="fc_Group_templates">{translate('Templates')}</label>
                <div id="fc_templates" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="templates_view" id="fc_Group_templates_view" value="1" />
                    <label for="fc_Group_templates_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="templates_install" id="fc_Group_templates_install" value="1" />
                    <label for="fc_Group_templates_install">{translate('Add')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="templates_uninstall" id="fc_Group_templates_uninstall" value="1" />
                    <label for="fc_Group_templates_uninstall">{translate('Delete')}</label>
                </div>
                <hr />
                <strong>{translate('Installed templates')}</strong>
                <div class="clear_sp"></div>
                <ul>
                    {foreach $templates as template}
                    <li>
                        <input type="checkbox" class="fc_checkbox_jq" name="template_permissions[]" id="fc_Group_t_{$template.VALUE}" value="{$template.VALUE}">
                        <label for="fc_Group_t_{$template.VALUE}">{$template.NAME}</label>
                    </li>
                    {/foreach}
                </ul>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_media">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_media" name="media" id="fc_Group_media" value="1" />
                <label for="fc_Group_media">{translate('Media')}</label>
                <div id="fc_media" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="media_view" id="fc_Group_media_view" value="1" />
                    <label for="fc_Group_media_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="media_upload" id="fc_Group_media_upload" value="1" />
                    <label for="fc_Group_media_upload">{translate('Upload files')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="media_rename" id="fc_Group_media_rename" value="1" />
                    <label for="fc_Group_media_rename">{translate('Rename')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="media_delete" id="fc_Group_media_delete" value="1" />
                    <label for="fc_Group_media_delete">{translate('Delete')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="media_create" id="fc_Group_media_create" value="1" />
                    <label for="fc_Group_media_create">{translate('Create folder')}</label>
                </div>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_groups">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_users" name="users" id="fc_Group_users" value="1" />
                <label for="fc_Group_users">{translate('Users')}</label>
                <div id="fc_users" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="users_view" id="fc_Group_users_view" value="1" />
                    <label for="fc_Group_users_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="users_add" id="fc_Group_users_add" value="1" />
                    <label for="fc_Group_users_add">{translate('Add')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="users_modify" id="fc_Group_users_modify" value="1" />
                    <label for="fc_Group_users_modify">{translate('Modify')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="users_delete" id="fc_Group_users_delete" value="1" />
                    <label for="fc_Group_users_delete">{translate('Delete')}</label>
                </div>
                <hr />
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_groups" name="groups" id="fc_Group_groups" value="1" />
                <label for="fc_Group_groups">{translate('Groups')}</label>
                <div id="fc_groups" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="groups_view" id="fc_Group_groups_view" value="1" />
                    <label for="fc_Group_groups_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="groups_add" id="fc_Group_groups_add" value="1" />
                    <label for="fc_Group_groups_add">{translate('Add')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="groups_modify" id="fc_Group_groups_modify" value="1" />
                    <label for="fc_Group_groups_modify">{translate('Modify')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="groups_delete" id="fc_Group_groups_delete" value="1" />
                    <label for="fc_Group_groups_delete">{translate('Delete')}</label>
                </div>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_languages">
                <div class="clear_sp"></div>
                <input type="checkbox" class="fc_advanced_groups fc_checkbox_jq set_advanced___fc_languages" name="languages" id="fc_Group_languages" value="1" />
                <label for="fc_Group_languages">{translate('Languages')}</label>
                <div id="fc_languages" class="fc_settings_max">
                    <div class="clear_sp"></div>
                    <input type="checkbox" class="fc_checkbox_jq" name="languages_view" id="fc_Group_languages_view" value="1" />
                    <label for="fc_Group_languages_view">{translate('View')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="languages_install" id="fc_Group_languages_install" value="1" />
                    <label for="fc_Group_languages_install">{translate('Add')}</label>
                    <input type="checkbox" class="fc_checkbox_jq" name="languages_uninstall" id="fc_Group_languages_uninstall" value="1" />
                    <label for="fc_Group_languages_uninstall">{translate('Delete')}</label>
                </div>
            </div>

            <div class="fc_toggle_tabs fc_settings_max_large" id="fc_tabs_members">
                <div class="clear_sp"></div>
                <div id="fc_members" class="fc_settings_max">
                    {if $members}{$members}{else}{translate('No members')}{/if}
                </div>
            </div>


            <div class="clear_sp"></div>
            <p class="submit_settings fc_gradient1">
                {if $permissions.GROUPS_DELETE}<input type="submit" id="fc_removeGroup" class="fc_modifyGroup fc_list_remove fc_gradient_red" name="removeGroup" value="{translate('Delete group')}" />{/if}
                <input type="submit" name="addGroup" value="{translate('Add group')}" class="fc_addGroup">
                <input type="submit" name="saveGroup" value="{translate('Save group')}" class="fc_modifyGroup">
                <input type="reset" name="reset" value="{translate('Reset')}">
            </p>
        </form>
    </div>
    {/if}
</div>