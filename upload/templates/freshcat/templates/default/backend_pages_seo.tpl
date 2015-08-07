{include('backend_pages_header.tpl')}
    <div id="fc_main_content">
    	<div class="fc_modified_header">
    		<div class="fc_current_page">
    			{translate('Current page')}: <strong>{$PAGE_TITLE}</strong> (<strong>ID: {$PAGE_ID}</strong>)
    		</div><div class="clear"></div>
    	</div>

        <div class="fc_info" style="margin:5px;">
        {translate('Please note: These are options that need some Know-How about Search Engine Optimization. If you don\'t know what to do here, just leave the default settings.')}
        </div>
        <div class="ui-corner-top" id="fc_set_form_content">
            <div style="float:right;margin-top:15px;margin-right:15px;">
                <strong>{translate('SEO Check')}</strong><br /><br />
                <div><div class="switch{if $keyword_in_url} on{/if}"><div class="inner"></div></div>{translate('Keywords appear in the URL for this page')}</div>
                <div><div class="switch{if $keyword_in_title} on{/if}"><div class="inner"></div></div>{translate('Keywords appear in the title attribute')}</div>
                <div><div class="switch{if $keyword_in_meta} on{/if}"><div class="inner"></div></div>{translate('Keywords appear in the META description')}</div>
                <div><div class="switch{if $title_length} on{/if}"><div class="inner"></div></div>{translate('Title length <= 55 characters')}</div>
                <div><div class="switch{if $descr_length} on{/if}"><div class="inner"></div></div>{translate('Description length <= 156 characters')}</div>
            </div>
            {$form}
        </div><div class="clear"></div>

    </div>
</div>
