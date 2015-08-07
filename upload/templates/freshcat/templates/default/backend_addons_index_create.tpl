<br /><br />
        <form class="fc_list_forms" style="margin-left:10px;" id="fc_add_new_module" action="ajax_create.php" method="post">
            <div class="fc_info">
                {translate('Please fill out the form to create a new addon. A new directory with the basic files will be created to start with.')}<br />
                {translate("If you're adding a language, a language file will be created in the <tt>languages</tt> subfolder.")}
            </div><br /><br />
            <input type="hidden" name="_cat_ajax" value="1" />
            <label class="fc_label_200" for="fc_new_moduletype">{translate('Module type')}</label>
                <select name="new_moduletype" id="fc_new_moduletype">
                    <option value="page">{translate('Module (Page)')}</option>
                    <option value="tool">{translate('Admin-Tool')}</option>
                    <option value="template">{translate('Template')}</option>
                    <option value="library">{translate('Library')}</option>
                    <option value="language">{translate('Language')}</option>
                    <option value="wysiwyg">{translate('WYSIWYG-Editor')}</option>
                </select><br />
            <label class="fc_label_200" for="fc_new_modulename">{translate('Module / language name')}</label>
                <input type="text" class="fc_input_large" id="fc_new_modulename" name="new_modulename" /><br />
            <label class="fc_label_200" for="fc_new_moduledir">{translate('Module directory / language code')}</label>
                <input type="text" class="fc_input_large" id="fc_new_moduledir" name="new_moduledir" /><br />
            <label class="fc_label_200" for="fc_new_moduledesc">{translate('Module description')}</label>
                <input type="text" class="fc_input_large" id="fc_new_moduledesc" name="new_moduledesc" /><br />
            <label class="fc_label_200" for="fc_new_modulename">{translate('Author')}</label>
                <input type="text" class="fc_input_large" id="fc_new_moduleauthor" name="new_moduleauthor" value="{$username}" /><br />
            <label class="fc_label_200" for="fc_new_precheck">{translate('Create precheck.php')}</label>
                <input type="checkbox" id="fc_new_precheck" name="new_precheck" value="Y" /><br />
            <label class="fc_label_200" for="fc_new_headersinc">{translate('Create headers.inc.php')}</label>
                <input type="checkbox" id="fc_new_headersinc" name="new_headersinc" value="Y" /><br />
            <label class="fc_label_200" for="fc_new_footersinc">{translate('Create footers.inc.php')}</label>
                <input type="checkbox" id="fc_new_footersinc" name="new_footersinc" value="Y" /><br />
            <label class="fc_label_200" for="fc_new_usejquery">{translate('Use jQuery')}</label>
                <input type="checkbox" id="fc_new_usejquery" name="new_usejquery" value="Y" /><br />
            <label class="fc_label_200" for="fc_new_usejqueryui">{translate('Use jQuery UI')}</label>
                <input type="checkbox" id="fc_new_usejqueryui" name="new_usejqueryui" value="Y" /><br />
            <br /><br />
            <p class="submit_settings fc_gradient1" style="margin-left:-10px;text-align:left;">
                <input type="submit" id="fc_new_submit" value="{translate('Save')}" />
                <input type="reset" name="reset" value="{translate('Reset')}" />
            </p>
        </form>
        <script type="text/javascript">
        //<![CDATA[
        dialog_form(jQuery('form#fc_add_new_module'));
        //]]>
        </script>