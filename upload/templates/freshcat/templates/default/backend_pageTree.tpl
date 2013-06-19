<div id="fc_pages">
	{if $pages}
	<div id="fc_search_tree">
		<div id="fc_search_page_tree_default"><span class="icon-search"> {translate('Search...')}</span></div>
		<input type="text" name="search_page_tree" id="fc_search_page_tree" value="" />
		<span class="fc_close"></span>
		<div id="fc_search_page_options">
			<ul>
				<li title="{translate('Check all')}" id="{translate('Any')}" class="fc_activeSearchOption">{translate('Check all')}</li>
				<li title="{translate('Menu title contains')}" id="{translate('Menu')}">{translate('Menu title contains')}</li>
				<li title="{translate('Page title contains')}" id="{translate('Title')}">{translate('Page title contains')}</li>
				<li title="{translate('Section title contains')}" id="{translate('Section')}">{translate('Section name contains')}</li>
				<li title="{translate('Page ID is')}" id="{translate('PID')}">{translate('Page ID is')}</li>
				<li title="{translate('Section ID is')}" id="{translate('SID')}">{translate('Section ID is')}</li>
			</ul>
		</div>
	</div>
	{/if}
	{if $permission.pages_intro}
	<div class="fc_page_link fc_page_intro">
		<a href="{$CAT_ADMIN_URL}/pages/intro.php" title="{translate('Modify intro page')}" class="icon-file fc_gradient1 fc_gradient_hover"> {translate('Intro page')}</a>
	</div>
	{/if}
	{if $pages_editable != 0}
	<div id="fc_page_tree_top">
    {$pages}
	</div>
	{else}
	<ul class="fc_no_page_found">
		<li>{translate('No editable pages were found')}</li>
	</ul>
	{/if}
</div>