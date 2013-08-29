<div id="bcstats">
    <div>
        <span class="fc_label_200">{translate('Installed')}:</span>
        <span>{$installation_time}</span><br />
        <span class="fc_label_200" style="float:left;">{translate('Page statistics')}:</span>
        <span style="display:inline_block;width:50%;float:left;">
            <table style="width:100%" class="fc_table">
                <thead>
                    <tr>
                        <th class="fc_gradient2">{translate('Visibility')}</th>
                        <th class="fc_gradient2">{translate('Count')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $visibility key value}
                    <tr><td>{translate($key)}</td><td>{$value}</td></tr>
                    {/foreach}
                </tbody>
            </table>
        </span><br style="clear:left;" />
        <span class="fc_label_200">{translate('Latest changed pages')}:</span>
        <span style="display:inline_block;width:50%;float:left;">
            <table style="width:100%" class="fc_table">
                <thead>
                    <tr>
                        <th class="fc_gradient2">{translate('Title')}</th>
                        <th class="fc_gradient2">{translate('Last edited')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $latest item}
                    <tr>
                        <td><a href="{$CAT_ADMIN_URL}/pages/modify.php?page_id={$item.page_id}">{$item.menu_title}</a></td>
                        <td>{format_date($item.modified_when)}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </span><br />
    </div>
</div>
