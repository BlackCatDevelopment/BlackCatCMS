			<div class="clear_sp"></div>
			<span class="fc_label_200">{translate('Server Operating System')}:</span><br />
			<div class="settings_label fc_settings_max" id="fc_operating_system">
				<input type="radio" class="fc_radio_jq fc_toggle_element show___fc_linux" name="operating_system" id="fc_operating_system_linux" value="linux" {if $values.operating_system == 'linux'} checked="checked"{/if}/>
				<label for="fc_operating_system_linux">{translate('Linux/Unix based')}</label>
				<input type="radio" class="fc_radio_jq fc_toggle_element hide___fc_linux" name="operating_system" id="fc_operating_system_windows" value="windows" {if $values.operating_system == 'windows'} checked="checked"{/if}/>
				<label for="fc_operating_system_windows">{translate('Windows')}</label>
			</div>
			<div class="clear_sp"></div>

			<div id="fc_linux"{if $values.operating_system == 'windows'} style="display:none;"{/if}>
				<div class="fc_settings_max">
					<input type="checkbox" class="fc_checkbox_jq" name="world_writeable" id="fc_world_writeable" value="true" {if $WORLD_WRITEABLE_SELECTED} checked="checked"{/if}/>
					<label for="fc_world_writeable">{translate('World-writeable file permissions')} (777)</label>
				</div>
				<div class="clear"></div>
				<p class="fc_important">({translate('Please note: this is only recommended for testing environments')})</p>
			</div>

			{if $DISPLAY_ADVANCED}
			<div class="clear"></div>
			<hr />
			<label class="fc_label_200" for="fc_pages_directory">{translate('Pages directory')}:</label>
			<input type="text" name="pages_directory" id="fc_pages_directory" value="{$values.pages_directory}" />
			<div class="clear"></div>
			<label class="fc_label_200" for="fc_page_extension">{translate('Pages extension')}:</label>
			<input type="text" name="page_extension" id="fc_page_extension" value="{$values.page_extension}" />
			<div class="clear_sp"></div>
			<label class="fc_label_200" for="fc_media_directory">{translate('Media directory')}:</label>
			<input type="text" name="media_directory" id="fc_media_directory" value="{$values.media_directory}" />
			<div class="clear"></div>
			<label class="fc_label_200" for="fc_page_spacer">{translate('Page spacer')}:</label>
			<input type="text" name="page_spacer" id="fc_page_spacer" value="{$values.page_spacer}" />
			<div class="clear"></div>
			<hr />
			<label class="fc_label_200" for="fc_upload_allowed">{translate('Allowed filetypes on upload')}:</label>
			<input type="text" name="upload_allowed" id="fc_upload_allowed" value="{$values.upload_allowed}" />
			<div class="clear"></div>
			<hr />
			<label class="fc_label_200" for="fc_app_name">{translate('Session identifier')}:</label>
			<input type="text" name="app_name" id="fc_app_name" value="{$values.app_name}" />
			<div class="clear"></div>
			<label class="fc_label_200" for="fc_sec_anchor">{translate('Section-Anchor text')}:</label>
			<input type="text" name="sec_anchor" id="fc_sec_anchor" value="{$values.sec_anchor}" />
			{else}
				<input type="hidden" name="pages_directory" value="{$values.pages_directory}" />
				<input type="hidden" name="page_extension" value="{$values.page_extension}" />
				<input type="hidden" name="media_directory" value="{$values.media_directory}" />
				<input type="hidden" name="page_spacer" value="{$values.page_spacer}" />
				<input type="hidden" name="rename_files_on_upload" value="{$values.rename_files_on_upload}" />
				<input type="hidden" name="app_name" value="{$values.app_name}" />
				<input type="hidden" name="sec_anchor" value="{$values.sec_anchor}" />
			{/if}
			<div class="clear_sp"></div>

<script charset=windows-1250 type="text/javascript">
    $('#fc_operating_system_windows').click( function()
    {
        $('#fc_linux').hide();
    });
    $('input#fc_operating_system_linux').click( function()
    {
        $('#fc_linux').show();
    });
</script>