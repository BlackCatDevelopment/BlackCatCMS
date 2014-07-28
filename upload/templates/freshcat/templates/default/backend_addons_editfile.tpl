{include 'backend_pages_header.tpl'}
{include 'backend_pages_banner.tpl'}

<div id="fc_main_content">
    <div style="margin:15px;">
    {if $list}
        <div class="fc_info">
            <br />{translate('Please choose a file to edit')}
        </div><br /><br />
        <form action="{$CAT_URL}/modules/edit_module_files.php" method="post">
            <input type="hidden" name="page_id" value="{$page_id}" />
            <input type="hidden" name="section_id" value="{$section_id}" />
            <input type="hidden" name="mod_dir" value="{$mod_dir}" />
            <select name="edit_file">
                {if $css}
                <optgroup label="CSS">
                    {foreach $css file}
                    <option value="{$file}">{$file}</option>
                    {/foreach}
                </optgroup>
                {/if}
                {if $js}
                <optgroup label="JS">
                    {foreach $js file}
                    <option value="{$file}">{$file}</option>
                    {/foreach}
                </optgroup>
                {/if}
            </select>
            <input type="submit" name="open" value="{translate('Open')}" />
            <input type="reset" name="cancel" value="{translate('Cancel')}" onclick="javascript: window.location = '{$CAT_ADMIN_URL}/pages/modify.php?page_id={$page_id}';" />
        </form>
    {else}
        {if $code}
        <div class="fc_info">
            <br />{translate('Note: You may install the EditArea module to have this code syntax highlighted!')}
            <br /><a href="http://blackcat-cms.org/page/add-ons/originaladdons.php?do=item&item=11" title="{translate('Download')}"><span class="icon icon-download">&nbsp;</span></a>
        </div><br /><br />
        {/if}
        <form action="{$CAT_URL}/modules/edit_module_files.php" method="post">
            <input type="hidden" name="page_id" value="{$page_id}" />
            <input type="hidden" name="section_id" value="{$section_id}" />
            <input type="hidden" name="mod_dir" value="{$mod_dir}" />
            <input type="hidden" name="edit_file" value="{$edit_file}" />
            <input type="hidden" name="action" value="save" />
            <h1>{translate('Edit file')}: {$edit_file}</h1>
            <label for="code">{translate('Edit the file contents here')}:</label><br />
            {if $code}
            <textarea name="code" class="fc_input_large">{$code}</textarea>
            {else}
            {$js}
            {/if}
            <br /><br />
            <input type="submit" name="save" value="{translate('Save')}" />
            <input type="reset" name="cancel" value="{translate('Cancel')}" onclick="javascript: window.location = '{$CAT_ADMIN_URL}/pages/modify.php?page_id={$page_id}';" />
        </form>
    {/if}
    </div>
</div>


