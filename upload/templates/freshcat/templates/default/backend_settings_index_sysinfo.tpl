			<span class="fc_label_200">{translate('Install date and time')}:</span>
			{$values.installation_time}
			<div class="clear_sp"></div>

            <span class="fc_label_200">{translate('Version')}:</span>
            {$values.cat_version} {if $values.cat_build}(Build {$values.cat_build}){/if}
            <div class="clear_sp"></div>

            <span class="fc_label_200">{translate('Installation path')}:</span>
            {$CAT_PATH}
            <div class="clear_sp"></div>

            <span class="fc_label_200">{translate('PHP version')}:</span>
            <?=phpversion()?>
            <div class="clear_sp"></div>

            {if isset($values.pages_count) && is_array($values.pages_count)}
            <span class="fc_label_200">{translate('Page statistics')}:</span>
            <table style="width:50%;margin-left:200px;" class="fc_table">
                <thead>
                    <tr>
                        <th class="fc_gradient2">{translate('Visibility')}</th>
                        <th class="fc_gradient2">{translate('Count')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $values.pages_count line}
                    <tr><td>{translate($line.visibility)}</td><td>{$line.count}</td></tr>
                    {/foreach}
                </tbody>
            </table>
            {/if}
            <div class="clear_sp"></div>
