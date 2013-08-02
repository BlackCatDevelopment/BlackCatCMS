<div class="fc_blocks_header">
{if $filters}
<table class="fc_table">
    <thead>
        <tr class="fc_gradient1">
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
            <td>{$filter.module_name}</td>
            <td>{$filter.filter_name}</td>
            <td>{translate($filter.filter_description)}</td>
            <td>{$filter.filter_active}</td>
            <td><span class="{if $filter.filter_code}code{else}file{/if}">&nbsp;</span></td>
        </tr>
        {/foreach}
    </tbody>
</table><br /><br />
<div style="float:right;">
    <span class="file">&nbsp;</span>
        {translate('The code is located in a PHP file which resides in <tt>./&lt;Module&gt;/filter</tt>')}<br />
    <span class="code">&nbsp;</span>
        {translate('The code is located in the database table <tt>&lt;Prefix&gt;mod_filter</tt>')}<br />
</div>
{else}
<div class="highlight">
 
</div>
{/if}
</div>