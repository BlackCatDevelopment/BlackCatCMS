            
            <span class="fc_label_300">{{translate('Security settings')}}:</span>
            <div class="clear"></div>
            <div class="fc_settings_max settings_label">
                <input type="checkbox" class="fc_checkbox_jq" name="auto_disable_users" id="fc_auto_disable_users" value="true" {% if values.auto_disable_users %} checked="checked"{% endif %}/>
    			<label for="fc_auto_disable_users">{{translate('Disable user accounts when max login attempts is reached')}}</label><br>
                <input type="checkbox" class="fc_checkbox_jq" name="enable_htmlpurifier" id="fc_enable_htmlpurifier" value="true" {% if values.enable_htmlpurifier %} checked="checked"{% endif %}/>
    			<label for="fc_enable_htmlpurifier">{{translate('Use HTML Purifier to protect WYSIWYG content')}}</label><br>
            </div>
            <label class="fc_label_300" for="fc_cookie_samesite">{{translate('Cookie SameSite directive')}}:</label>
            <select name="cookie_samesite" id="fc_cookie_samesite">
                <option value="Strict"{% if values.cookie_samesite=='Strict' %} selected="selected"{% endif %}>Strict</option>
                <option value="Lax"{% if values.cookie_samesite=='Lax' %} selected="selected"{% endif %}>Lax</option>
                <option value="None"{% if values.cookie_samesite=='None' %} selected="selected"{% endif %}>None</option>
            </select>
            <p>
                {{translate('In &quot;Strict&quot; mode, the cookie is not sent with absolutely no cross-site request. The &quot;lax&quot; mode allows the cookie to be sent with some &quot;secure&quot; cross-site requests. None&quot; disables any security.')|raw}}<br>
                {{translate('<strong>Note:</strong> Changing this setting does not affect already existing cookies.')}}
            </p>

            <div class="clear_sp"></div>

            <span class="fc_label_300">{{translate('Upload security settings')}}:</span>
            <div class="clear"></div>

            <div class="fc_settings_max settings_label">
                <input type="checkbox" class="fc_checkbox_jq" name="upload_enable_mimecheck" id="fc_upload_enable_mimecheck" value="true" {% if values.upload_enable_mimecheck %} checked="checked"{% endif %}/>
    			<label for="fc_upload_enable_mimecheck">{{translate('Check mime type of uploaded files')}}</label>
            </div>
            <div class="clear_sp"></div>

            <label class="fc_label_300" for="fc_upload_mime_default_type">{{translate('Default MIME type')}}</label>
            <select name="upload_mime_default_type" id="fc_upload_mime_default_type">
                <option value="application/octet-stream" {% if values.upload_mime_default_type=="application/octet-stream" %}selected="selected"{% endif %}>application/octet-stream (best security)</option>
                <option value="text/plain"{% if values.upload_mime_default_type=="text/plain" %}selected="selected"{% endif %}>text/plain (weak security)</option>
            </select><br>
            <p class="fc_important">{{translate('The default MIME type is used if the real MIME type cannot be encountered.')}}</p>
            <div class="clear_sp"></div>

            <label class="fc_label_300" for="fc_upload_allowed">{{translate('Allowed filetypes on upload')}}</label>
            <input class="fc_input_large" type="text" name="upload_allowed" id="fc_upload_allowed" value="{{values.upload_allowed}}">
            <div class="clear_sp"></div>
            
            <span class="fc_label_300">{{translate('Captcha and Advanced Spam Protection (ASP)')}}:</span>
            <div class="clear"></div>
            <p>{{translate('Please note: These settings only concern the old Captcha derived from WebsiteBaker. At the moment, there are no settings for the SecurImage library here.')}}</p>

		    <label class="fc_label_300" for="fc_captcha_type">{{translate('Type of CAPTCHA')}}:</label>
    		<select name="captcha_type" id="fc_captcha_type" onchange="load_captcha_image()" style="vertical-align:top;">
    			{% for key, value in useable_captchas %}
    			<option value="{{key}}" {% if captcha_type==key %} selected="selected"{% endif %}>{{translate(value)}}</option>
    			{% endfor %}
    		</select>
            <img alt="captcha_example" id="captcha_example" src="{{CAT_URL}}/framework/CAT/Helper/Captcha/WB/captchas/{{captcha_type}}.png"><br>
            <div class="clear"></div>

            <div id="fc_text_qa_div" style="display:{% if captcha_type != 'text' %}none{% else %}block{% endif %};">
    		    <label class="fc_label_300" for="fc_text_qa">{{translate('Questions and Answers')}}:</label>
    			<textarea name="text_qa" id="fc_text_qa" cols="60" rows="10">{{text_qa}}</textarea>
            </div>
            <div class="clear_sp"></div>

            <div class="fc_settings_max settings_label">
			    <input type="checkbox" class="fc_checkbox_jq" name="enabled_captcha" id="fc_enabled_captcha" {% if enabled_captcha=='1' %}checked="checked"{% endif %} value="1">
                <label for="fc_enabled_captcha">{{translate('Activate CAPTCHA for signup')}}</label>
                <p>{{translate('CAPTCHA settings for modules are located in the respective module settings')}}</p>
                <input type="checkbox" class="fc_checkbox_jq" name="enabled_asp" id="fc_enabled_asp" {% if enabled_asp=='1' %}checked="checked"{% endif %} value="1">
                <label for="fc_enabled_asp">{{translate('Activate ASP (if available)')}}</label>
                <p>{{translate('ASP tries to determine if a form-input was originated from a human or a spam-bot.')}}</p>
                <p>{{translate('To make ASP work with modules, modifications in the module itself are necessary.')}}</p>
            </div>
            
            <div class="clear_sp"></div>

<script type="text/javascript">
	var pics = new Array();

	pics["ttf_image"] = new Image();
	pics["ttf_image"].src = "{{ttf_image}}";

	pics["calc_image"] = new Image();
	pics["calc_image"].src = "{{calc_image}}";

	pics["calc_ttf_image"] = new Image();
	pics["calc_ttf_image"].src = "{{calc_ttf_image}}";

	pics["old_image"] = new Image();
	pics["old_image"].src = "{{old_image}}";

	pics["calc_text"] = new Image();
	pics["calc_text"].src = "{{calc_text}}";

	pics["text"] = new Image();
	pics["text"].src = "{{text}}";

	function load_captcha_image() {
        if ( pics[jQuery('#fc_captcha_type').val()].src.length )
        {
		    jQuery('#captcha_example').prop('src', pics[jQuery('#fc_captcha_type').val()].src );
        }
		toggle_text_qa();
	}

	function toggle_text_qa() {
		if(jQuery('#fc_captcha_type').val() == 'text' ) {
			jQuery('#fc_text_qa_div').show();
		} else {
			jQuery('#fc_text_qa_div').hide();
		}
	}

</script>