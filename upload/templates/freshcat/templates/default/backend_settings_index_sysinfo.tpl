            <label class="fc_label_200" for="fc_default_template">{translate('GUID')}:</label>
			<span id="guid">{$values.guid}</span>
            {if ! isset($values.guid) || $values.guid == ''}
            <button class="icon-shield" dir="ltr" type="submit" id="fc_createguid" name="create_guid" onclick="return false;">{translate('Create GUID')}</button>
            {/if}

			<hr />

			<span class="fc_label_200">{translate('Install date and time')}:</span>
			{$values.installation_time}
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