            
            <span class="fc_label_200">{translate('Security settings')}:</span>
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
