<div id="fc_content_header">
	{$PAGE_HEADER}
	<div class="fc_header_buttons">
		<a href="{$CAT_ADMIN_URL}/pages/modify.php?page_id={$PAGE_ID}" class="fc_br_left fc_gradient1 fc_gradient_hover{if $CUR_TAB == 'modify'} fc_active{/if}">{translate('Modify page')}</a>
        <a href="{$CAT_ADMIN_URL}/pages/seo.php?page_id={$PAGE_ID}" class="fc_gradient1 fc_gradient_hover{if $CUR_TAB == 'seo'} fc_active{/if}">{translate('SEO')}</a>
		<a href="{$CAT_ADMIN_URL}/pages/lang_settings.php?page_id={$PAGE_ID}" class="fc_gradient1 fc_gradient_hover{if $CUR_TAB == 'lang'} fc_active{/if}">{translate('Language Mappings')}</a>
        <a href="{$CAT_ADMIN_URL}/pages/modify_headers.php?page_id={$PAGE_ID}" class="fc_gradient1 fc_gradient_hover{if $CUR_TAB == 'headers'} fc_active{/if}">{translate('Header files')}</a>
		<a href="{$PAGE_LINK}?preview=1" target="_blank" class="fc_br_right fc_gradient1 fc_gradient_hover">{translate('View page')}</a>
	</div>
	<div class="clear"></div>
</div>