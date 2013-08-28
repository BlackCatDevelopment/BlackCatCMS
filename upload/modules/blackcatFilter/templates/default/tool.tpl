<div class="fc_blocks_header">
{if $filters}
<table class="fc_table">
    <thead>
        <tr class="fc_gradient1">
            <th></th>
            <th>{translate('Provided by (module)')}</th>
            <th>{translate('Filtername')}</th>
            <th>{translate('Description')}</th>
            <th>{translate('Enabled')}</th>
            <th>{translate('Code')}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $filters filter}
        <tr>
            <td>
                <a class="ajaxAction" href="{$CAT_URL}/modules/blackcatFilter/ajax_set.php?filter={$filter.filter_name}&amp;action=delete">
                    <img src="{$CAT_URL}/modules/blackcatFilter/css/images/delete.png" />
                </a>
            </td>
            <td>{$filter.module_name}</td>
            <td>{translate($filter.filter_name)}</td>
            <td>{translate($filter.filter_description)}</td>
            <td>
                <a class="ajaxAction" href="{$CAT_URL}/modules/blackcatFilter/ajax_set.php?filter={$filter.filter_name}&amp;action={if $filter.filter_active == Y}deactivate{else}activate{/if}">
                    <img src="{$CAT_URL}/modules/blackcatFilter/css/images/{if $filter.filter_active == Y}active{else}inactive{/if}.png" />
                </a>
            </td>
            <td><span class="{if $filter.filter_code}code{else}file{/if}">&nbsp;</span></td>
        </tr>
        {/foreach}
    </tbody>
</table><br /><br />
<div style="float:right;margin-right:10px;">
    <span class="file">&nbsp;</span>
        {translate('The code is located in a PHP file which resides in <tt>./&lt;Module&gt;/filter</tt>')}<br />
    <span class="code">&nbsp;</span>
        {translate('The code is located in the database table <tt>&lt;Prefix&gt;mod_filter</tt>')}<br />
</div>
{else}
<div class="highlight">
{translate('No filters found')}
</div>
{/if}
</div>

<button class="button icon-plus fc_gradient_blue fc_gradient_hover left filter_add"> {translate('Add entry')}</button><br /><br /><br />
<div class="filter_form_container" style="display:{if $showit}block{else}none{/if}">
    <h1>{translate('Add new filter')}</h1>
    {if count($errors)}<div class="highlight" style="float:right">{$errors}</div>{/if}
    <form method="post" action="{$CAT_ADMIN_URL}/admintools/tool.php?tool=blackcatFilter" enctype="multipart/form-data">
        <label for="filter_module_name" class="fc_label_120">{translate('Module name')}</label>
            <select name="filter_module_name">
                {foreach $modules module}
                <option value="{$module.directory}" {if $module.SELECTED}selected="selected"{/if}>{$module.name}</option>
                {/foreach}
            </select><br />
        <label for="filter_name" class="fc_label_120">{translate('Filter name')}</label>
            <input type="text" name="filter_name" {if $missing.name}class="missing"{/if} /><br />
        <label for="filter_description" class="fc_label_120">{translate('Filter description')}</label>
            <input type="text" name="filter_description" {if $missing.description}class="missing"{/if} /><br />
        <label for="filter_code" class="fc_label_120">{translate('Code')}</label>
            <textarea name="filter_code" {if $missing.code}class="missing"{/if}></textarea><br />
        <strong>{translate('or')}</strong><br />
        <label for="filter_file" class="fc_label_120">{translate('Upload file')}</label>
            <input type="file" name="filter_file" />
        <div class="fc_settings_max_large">
            <input type="checkbox" class="fc_checkbox_jq" name="filter_active" id="filter_active" checked="checked" value="Y" />
            <label for="filter_active">{translate('Filter is active')}</label>
        </div>
        <div class="clear_sp"></div>
        <input type="submit" name="filter_add" />
        <input type="reset" />
        
    </form>
</div>