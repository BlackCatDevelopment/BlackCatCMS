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
                    <input type="submit" name="submit" value="{translate('Install addon')}" />
                    <input type="reset" name="reset" value="{translate('Reset')}" />
                </p>
            </form>