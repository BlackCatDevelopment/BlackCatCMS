            <form name="install" enctype="multipart/form-data" action="install.php" method="post" id="fc_install_new" class="fc_list_forms">
                <p class="submit_settings fc_gradient1">
                    <strong>{translate('Install addon')}</strong>
                    <input type="submit" name="submit" value="{translate('Install addon')}" />
                    <input type="reset" name="reset" value="{translate('Reset')}" />
                </p>
                <div class="clear_sp"></div>
                <p>
                    <div class="fallback">
                        <input type="file" name="userfile" />
                    </div>
                    <div id="fc_dropzone" style="display:none;">
                        <span>{translate('Click or drag & drop your file to the area below')}</span>
                        <div class="dropzone"></div>
                    </div>
                </p>
                {if $groups.viewers}
                <hr />
                <h3>{translate('Addon permissions')}</h3>
                <p>
                    {translate('You can set permissions for each group to use this addon.')}<br />
                    {translate('You can customize permissions later on group administration.')}<br />
                    {translate('If you upgrade a module, those settings will have no effect on current permissions.')}
                </p>
                <button class="fc_gradient1 fc_gradient_hover" id="fc_mark_all">
                    <span class="fc_mark">{translate('Mark all groups')}</span>
                    <span class="fc_unmark hidden">{translate('Unmark all groups')}</span>
                </button>
                <div class="clear_sp"></div>
                <div id="fc_perm_groups" class="fc_settings_max">
                    {foreach $groups.viewers as group}
                    <input type="checkbox" class="fc_checkbox_jq" name="group_id[]" id="fc_group_{$group.VALUE}" value="{$group.VALUE}" />
                    <label for="fc_group_{$group.VALUE}">{$group.NAME}</label>
                    {/foreach}
                </div>
                <div class="clear_sp"></div>
                {/if}
                <p class="submit_settings fc_gradient1">
                    <input type="submit" name="submit" value="{translate('Install addon')}" />
                    <input type="reset" name="reset" value="{translate('Reset')}" />
                </p>
            </form>
            <script type="text/javascript">
            //<![CDATA[
                if(typeof Dropzone != 'undefined') {
                    Dropzone.autoDiscover = false;
                    var fcDropzone = new Dropzone("div.dropzone", {
                        url: "ajax_install.php",
                        params: {
                            _cat_ajax: 1,
                            __csrf_magic: $('input[name="__csrf_magic"]').val()
                        },
                        maxFiles: 1,
                        acceptedFiles: '.zip',
                        autoProcessQueue: false,
                        addRemoveLinks: true,
                        dictRemoveFile: cattranslate('Remove file'),
                        dictDefaultMessage: '',
                        paramName: "userfile",
                        init: function() {
                            jQuery("input[type=submit]").unbind('click').on("click", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                fcDropzone.processQueue();
                            });
                            this.on('success', function (file, response) {
                                if(response.success === false) {
                                    file.previewElement.classList.add("dz-error");
                                    var jQ = jQuery(jQuery.parseHTML(response.message));
                                    jQuery('div.dz-error-message').html(jQuery(jQ).find('div.fc_error_box').html());
                                    return false;
                                }
                            });
                        }
                    });
                    jQuery("div#fc_dropzone").show();
                    jQuery("div.fallback").hide();
                }
            //]]>
            </script>