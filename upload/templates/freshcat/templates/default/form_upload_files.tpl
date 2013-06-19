<ul class="fc_br_bottom fc_br_topleft fc_gradient1 fc_shadow_small" id="fc_media_index_upload_ul">
	<li>
		<div id="fc_media_upload_not_writable" class="hidden">{translate('Please note: This folder is not writable! You cannot upload files to it!')}</div>
		<form name="upload" action="{$CAT_ADMIN_URL}/media/ajax_upload.php" method="post" enctype="multipart/form-data" id="fc_media_index_upload">
			<div class="clear">
				<input type="hidden" name="form_title" value="{translate('Upload File(s)')}" />
				<input type="hidden" name="folder_path" value="{$initial_folder}" />
                <input type="hidden" name="_cat_ajax" value="1" />
			</div>
			<p>
				<input type="checkbox" class="fc_checkbox_jq" name="overwrite" id="fc_overwrite" value="true" />
				<label for="fc_overwrite">{translate('Overwrite existing files')}</label>
			</p>
			<hr />
			<div class="fc_upload_fields">
				<div class="fc_upload_field fc_gradient4 fc_br_all">
					<input type="file" name="upload_0" maxlength="{$maxUploadFiles}" accept="{$allowed_file_types}" />
					<input type="text" name="test" value="{translate('Choose a file...')}" />
					<span class="fc_upload_close icon-cancel fc_gradient1 fc_gradient_hover fc_br_right fc_border_all_light"></span>
				</div>
				<div class="fc_upload_zip hidden">
					<input type="checkbox" class="fc_checkbox_jq fc_toggle_element show___fc_delete_zip_div_0" name="unzip_0" id="unzip_0" value="true" />
					<label for="unzip_0">{translate('Unpack zip archive')}</label>
					<div id="fc_delete_zip_div_0">
						<input type="checkbox" class="fc_checkbox_jq" name="delete_zip_0" id="delete_zip_0" value="true" />
						<label for="delete_zip_0">{translate('Delete zip archive after unpacking')}</label>
					</div>
					<hr />
				</div>
				<input type="hidden" name="upload_counter[]" value="0" />
			</div>
			{*<span id="fc_add_upload_field" class="icon-plus fc_gradient1 fc_gradient_hover fc_border_all_light"></span>*}
			<hr />
			<div class="clear_sp"></div>
			<p>
				<input type="submit" name="upload_files" id="fc_upload_submit" value="{translate('Upload File(s)')}" />
				<input type="reset" name="reset_upload" id="fc_close_media" value="{translate('Close & Reset')}" />
			</p>
		</form>
		<div id="fc_upload_field_add" class="hidden">
			<div class="fc_upload_fields">
				<div class="fc_upload_field fc_gradient4 fc_br_all">
					<input type="file" name="upload_" maxlength="{$maxUploadFiles}" accept="{$allowed_file_types}" />
					<input type="text" name="test" value="{translate('Choose a file...')}" />
					<span class="fc_upload_close icon-cancel fc_gradient1 fc_gradient_hover fc_br_right fc_border_all_light"></span>
				</div>
				<div class="fc_upload_zip hidden">
					<input type="checkbox" class="fc_checkbox_jq fc_toggle_element" name="unzip_" id="unzip_" value="true" />
					<label for="unzip_">{translate('Unpack zip archive')}</label>
					<div id="fc_delete_zip_div_">
						<input type="checkbox" class="fc_checkbox_jq" name="delete_zip_" id="delete_zip_" value="true" />
						<label for="delete_zip_">{translate('Delete zip archive after unpacking')}</label>
					</div>
					<hr />
				</div>
				<input type="hidden" name="upload_counter[]" value="" />
			</div>
		</div>
	</li>
</ul>