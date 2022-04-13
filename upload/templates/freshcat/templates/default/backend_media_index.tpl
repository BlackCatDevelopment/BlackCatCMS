<div id="fc_content_header">
	{{translate('Media')}}
	<div class="fc_header_buttons fc_br_all">
		{% if permissions.media_upload %}
		<ul id="fc_header_button_dropdown_toggle">
			<li><a href="#" class="fc_gradient1 fc_gradient_hover icon-upload fc_toggle_element fc_br_right show___fc_media_index_upload_ul"> {{translate('Upload')}}</a></li>
            <li id="fc_media_upload_not_writable" style="display:none;">
                <div class="fc_br_bottom fc_br_topleft fc_gradient4 fc_shadow_small">{{translate('Please note: This folder is not writable! You cannot upload files to it!')}}</div>
            </li>
			<li id="fc_media_upload_is_writable">
				{% include 'form_upload_files.tpl' %}
			</li>
		</ul>
		{% endif %}
		{% if permissions.media_create or permissions.media_rename or permissions.media_delete %}
		<ul class="fc_header_button_dropdown">
			<li><a href="#" class="fc_gradient1 fc_gradient_hover icon-tools fc_br_left"> {{translate('Options')}}</a><br>
				<ul id="fc_media_options" class="fc_br_bottom fc_br_topleft fc_gradient4 fc_shadow_small">
					{% if permissions.media_create %}<li class="fc_create_new_folder icon-plus fc_gradient4 fc_gradient_hover"> {{translate('Create new folder')}}</li>{% endif %}
					{% if permissions.media_rename %}<li class="fc_rename_file icon-pencil fc_gradient4 fc_gradient_hover"> {{translate('Rename folder/file')}}</li>{% endif %}
					{% if permissions.media_create %}<li class="hidden fc_inactive_button fc_gradient4 fc_gradient_hover"> {{translate('Duplicate folder/file')}}</li>{% endif %}
					{% if permissions.media_delete %}<li class="fc_delete_file icon-remove fc_gradient4 fc_br_bottom fc_gradient_hover"> {{translate('Delete folder/file')}}</li>{% endif %}
					<li class="hidden">{{translate('Change settings')}}</li>
				</ul>
			</li>
		</ul>
		{% endif %}
	</div>
	<div class="clear"></div>
</div>

<div id="fc_main_content">
	<div id="fc_media_browser">
		<ul class="fc_media_folder fc_media_folder_active fc_clickable">
			<input type="hidden" name="folder_path" value="{{initial_folder}}">
			{% if count(folders)==0 and count(files)==0}
			<li class="fc_filetype_file fc_no_content">{{translate('No files available')}}</li>
			{% else %}
			{% for folder in folders %}
			<li class="fc_filetype_folder" title="{{folder.NAME}}">
				<div class="fc_name_short">
					<p class="icon-folder"> {{folder.NAME}}</p>
				</div>
				<input type="hidden" name="load_url" value="{{folder.NAME}}">
			</li>
			{% endfor %}
			{% for file in files %}
			<li class="fc_filetype_file" title="{{file.FULL_NAME}}">
				<div class="fc_name_short">
					<p class="icon-file-{{file.FILETYPE}}"> {{file.FULL_NAME}}</p>
				</div>
				<input type="hidden" name="load_url" value="{{file.FULL_NAME}}">
			</li>
			{% endfor %}
			{% endif %}
		</ul>
		<div id="fc_media_info">
			<div class="fc_file_info">
				<p class="fc_no_preview icon-info"> {{translate('No preview available')}}</p>
				<span class="fc_filename"> 
				</span>
				<div class="fc_file_more_info fc_border_all_light fc_br_all fc_gradient1">
					<span class="fc_file_label">{{translate('File type')}}:</span><span class="fc_file_type"></span><br>
					<span class="fc_file_label">{{translate('File size')}}:</span><span class="fc_file_size"></span><br>
					<span class="fc_file_label">{{translate('Created at')}}:</span><span class="fc_file_date"></span> {{translate('at')}} <span class="fc_file_time"></span>
				</div>
				<div class="fc_file_options">
                    {% if permissions.media_rename %}
					<button type="submit" class="left fc_rename_file">{{translate('Rename')}}</button>
                    {% endif %}
                    {% if permissions.media_delete %}
					<button type="submit" class="right fc_delete_file fc_gradient_red">{{translate('Delete')}}</button>
                    {% endif %}
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>