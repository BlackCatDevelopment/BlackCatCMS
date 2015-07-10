<div id="fc_content_header">
	<span class="left">{translate('Modify intro page')}</span>
	<div class="clear"></div>
</div>
<div class="fc_info" style="margin:5px">
{translate('The intro page will be created automatically if you enable one or more options.')}<br />
{translate('Please note: At the moment, the two global options (by language / by sub domain) are mutually exclusive.')}<br />
{translate('If you choose the option "Disabled" and save, the intro.php will be deleted!')}
</div>
<div id="fc_main_content">
	<div class="fc_module_block fc_active">
		<form action="{$CAT_ADMIN_URL}/pages/intro2.php" method="post">
            <div class="fc_settings_max">
			    <input type="radio" class="fc_radio_jq fc_toggle_element" name="intro_forward_by" id="fc_intro_forward_by_disabled" value="disabled" {if $values.intro_forward_by_disabled === true} checked="checked"{/if}/>
			    <label for="fc_intro_forward_by_disabled" title="{translate('This will delete the intro page!')}">{translate('Disabled (no forwarding)')}</label>
			    <input type="radio" class="fc_radio_jq fc_toggle_element show___fc_forward_by_domain" name="intro_forward_by" id="fc_intro_forward_by_domain" value="domain" {if $values.intro_forward_by_domain === true} checked="checked"{/if}/>
			    <label for="fc_intro_forward_by_domain" title="{translate('The visitor will be forwarded by analyzing the subdomain')}">{translate('Forward user by sub domain')}</label>
			    <input type="radio" class="fc_radio_jq fc_toggle_element show___fc_forward_by_lang" name="intro_forward_by" id="fc_intro_forward_by_lang" value="lang" {if $values.intro_forward_by_lang === true} checked="checked"{/if}/>
			    <label for="fc_intro_forward_by_lang" title="{translate('The visitor will be forwarded depending on his browser language')}">{translate('Forward user by browser language')}</label>
            </div>

            <div id="fc_forward_by_domain">
            <br />{translate('Enter the name of the subdomain and set the page from the list of pages.')}<br /><br />
            {foreach $domains domain page}
                <div>
                    <label class="fc_label_200" for="domain_{$.foreach.default.index}">{translate('Domain')}:</label>
                    <input type="text" id="domain_{$.foreach.default.index}" name="domains[]" value="{$domain}" />
                    <button id="forward_by_domain_del" type="button">-</button><br />
                    <label class="fc_label_200" for="page_{$.foreach.default.index}">{translate('Seite')}:</label>
                    <select name="pages[]" id="fc_pages_{$.foreach.default.index}">
                    {foreach $pages lang items}
                        <optgroup label="{$lang}">
                        {foreach $items item}
                            <option value="{$item.page_id}"{if $item.page_id == $page} selected="selected"{/if}>{$item.menu_title}</option>
                        {/foreach}
                        </optgroup>
                    {/foreach}
                    </select><br /><hr />
                </div>
            {/foreach}
                <label class="fc_label_200" for="fc_page_default">{translate('Default page')}:</label>
                <select name="pages[]" id="fc_page_default">
                {foreach $pages lang items}
                    <optgroup label="{$lang}">
                    {foreach $items item}
                        <option value="{$item.page_id}"{if $item.page_id == $default} selected="selected"{/if}>{$item.menu_title}</option>
                    {/foreach}
                    </optgroup>
                {/foreach}
                </select><br /><hr />
                <button id="forward_by_domain_add" type="button">+</button><hr />
            </div>

            <div id="fc_forward_by_lang"{if $values.intro_forward_by_lang == 'false'} style="display:none;"{/if}>
            {foreach $pages lang items}
                <label class="fc_label_200" for="fc_intro_page_for_{$lang}"><span class="flag-{$lang}" style="display:inline-block;width:18px;height:18px;"></span> {translate('Language')} {$lang}</label>
                <select name="intro_page_for_{$lang}" id="fc_intro_page_for_{$lang}">
                {foreach $items item}
                    <option value="{$item.page_id}">{$item.menu_title}</option>
                {/foreach}
                </select><br />
            {/foreach}

            {* default page *}
                <label class="fc_label_200" for="fc_intro_page_default" title="{translate('This page is used if no page is found for the browser language, or the browser language cannot be determined')}">{translate('Default page')}</label>
                <select name="intro_page_for_default" id="fc_intro_page_for_default">
                {foreach $pages lang items}
                    <optgroup label="{$lang}">
                    {foreach $items item}
                        <option value="{$item.page_id}">{$item.menu_title}</option>
                    {/foreach}
                    </optgroup>
                {/foreach}
                </select><br /><hr />
            </div>

			<div class="fc_confirm_bar ui-corner-bottom">
				<input type="submit" value="{translate('Save')}" />
				<input type="reset" value="{translate('Cancel')}" onclick="javascript: window.location = 'index.php';" />
			</div>
		</form>
	</div>
    {* template for new domain mapping *}
    <div id="fc_forward_by_domain_fieldset" style="display:none;">
        <label class="fc_label_200" for="domain_#">{translate('Domain')}:</label>
        <input type="text" id="domain_#" name="domains[]" value="" /><br />
        <label class="fc_label_200" for="page_#">{translate('Seite')}:</label>
        <select name="pages[]" id="fc_page_#">
        {foreach $pages lang items}
            <optgroup label="{$lang}">
            {foreach $items item}
                <option value="{$item.page_id}">{$item.menu_title}</option>
            {/foreach}
            </optgroup>
        {/foreach}
        </select><br /><hr />
    </div>
</div>
<script charset=windows-1250 type="text/javascript">
    $('#fc_intro_forward_by_disabled').click( function()
    {
        $('#fc_forward_by_domain').removeClass('fc_active_element').addClass('fc_inactive_element hidden').hide();
        $('#fc_forward_by_lang').removeClass('fc_active_element').addClass('fc_inactive_element hidden').hide();
    });
    $('#fc_intro_forward_by_lang').click( function()
    {
        $('#fc_forward_by_domain').removeClass('fc_active_element').addClass('fc_inactive_element hidden').hide();
    });
    $('#fc_intro_forward_by_domain').click( function()
    {
        $('#fc_forward_by_lang').removeClass('fc_active_element').addClass('fc_inactive_element hidden').hide();
    });
    $('button#forward_by_domain_add').unbind('click').click( function(e)
    {
        e.preventDefault();
        $('button#forward_by_domain_add').before( $('div#fc_forward_by_domain_fieldset').html() );
    });
    $('button#forward_by_domain_del').unbind('click').click( function(e)
    {
        e.preventDefault();
        $(this).parent().remove();
    });
</script>