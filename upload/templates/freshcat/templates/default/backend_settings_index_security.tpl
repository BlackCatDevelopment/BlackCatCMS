            
            <span class="fc_label_300">{translate('Security settings')}:</span>
            <div class="clear"></div>
            <div class="fc_settings_max settings_label">

                <input type="checkbox" class="fc_checkbox_jq" name="auto_disable_users" id="fc_auto_disable_users" value="true" {if $values.auto_disable_users} checked="checked"{/if}/>
    			<label for="fc_auto_disable_users">{translate('Disable user accounts when max login attempts is reached')}</label><br />

                <input type="checkbox" class="fc_checkbox_jq" name="enable_csrfmagic" id="fc_enable_csrfmagic" value="true" {if $values.enable_csrfmagic} checked="checked"{/if}/>
    			<label for="fc_enable_csrfmagic">{translate('Use csrf-magic to protect forms (frontend only)')}</label><br />

                <input type="checkbox" value="true" class="fc_checkbox_jq" name="csrfmagic_defer" id="fc_csrfmagic_defer" {if $values.csrfmagic_defer} checked="checked"{/if}/>
                <label for="fc_csrfmagic_defer">{translate('Defer executing csrf_check() until manual call')}</label>

            </div>
            <div class="clear_sp"></div>


            <span class="fc_label_300">{translate('Upload security settings')}:</span>
            <div class="clear"></div>

            <div class="fc_settings_max settings_label">
                <input type="checkbox" class="fc_checkbox_jq" name="upload_enable_mimecheck" id="fc_upload_enable_mimecheck" value="true" {if $values.upload_enable_mimecheck} checked="checked"{/if}/>
    			<label for="fc_upload_enable_mimecheck">{translate('Check mime type of uploaded files')}</label>
            </div>
            <div class="clear_sp"></div>

            <label class="fc_label_300" for="fc_upload_mime_default_type">{translate('Default MIME type')}</label>
            <select name="upload_mime_default_type" id="fc_upload_mime_default_type">
                <option value="application/octet-stream" {if $values.upload_mime_default_type=="application/octet-stream"}selected="selected"{/if}>application/octet-stream (best security)</option>
                <option value="text/plain"{if $values.upload_mime_default_type=="text/plain"}selected="selected"{/if}>text/plain (weak security)</option>
            </select><br />
            <p class="fc_important">{translate('The default MIME type is used if the real MIME type cannot be encountered.')}</p>
            <div class="clear_sp"></div>

            <label class="fc_label_300" for="fc_upload_allowed">{translate('Allowed filetypes on upload')}</label>
            <input class="fc_input_large" type="text" name="upload_allowed" id="fc_upload_allowed" value="{$values.upload_allowed}" />
            
