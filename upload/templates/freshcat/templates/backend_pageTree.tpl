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
		<ul>
		{foreach $pages page}
			{if $page.close_parent}
			{for close 0 $page.close_parent}
			{if $close > 0}
				</ul>
			</li>
			{/if}
			{/for}
			{/if}
			<li id="pageid_{$page.page_id}" class="fc_page_tree_item{if $page.is_parent} fc_expandable{if $page.cookie} fc_tree_open{else} fc_tree_close{/if}{/if}">
				<dl class="fc_page_tree_search_dl">
					<dt>PageID</dt>
					<dd class="fc_search_PageID">{$page.page_id}</dd>
					<dt>MenuTitle</dt>
					<dd class="fc_search_MenuTitle">{$page.menu_title}</dd>
					<dt>PageTitle</dt>
					<dd class="fc_search_PageTitle">{$page.page_title}</dd>
					{foreach $page.sections as section}
					<dt>SectionID</dt>
					<dd class="fc_search_SectionID">{$section.section_id}</dd>
					<dt>SectionName</dt>
					<dd class="fc_search_SectionName">{$section.name}</dd>
					{/foreach}
				</dl>
				<div class="fc_page_link{if !$page.editable} fc_page_tree_not_editable{/if}">
					{if $page.is_parent}<span class="fc_toggle_tree">+</span>{/if}
					<a href="{if !$page.editable}#{else}{$CAT_ADMIN_URL}/pages/modify.php?page_id={$page.page_id}{/if}" title="{translate('Page title')}: {$page.page_title}"{if $page.page_id == $page_id} class="fc_gradient3"{/if}>
						<span class="fc_page_tree_menu_title icon-{if $page.visibility == 'public'}screen{elseif $page.visibility == 'private'}key{elseif $page.visibility == 'registered'}users{elseif $page.visibility == 'hidden'}eye-2{elseif $page.visibility == 'deleted'}remove{else}eye-blocked{/if}"> {$page.menu_title}</span> <span class="fc_page_tree_menu_ID">(ID: {$page.page_id})</span>
					</a>
					<span class="fc_page_tree_options_open fc_pages_tree_options_button icon-tools fc_gradient1 fc_gradient_hover"></span>
				</div>
				<input type="hidden" name="pageid" value="{$page.page_id}" />
				{if $page.is_parent}
				<ul>
				{else}
			</li>
			{/if}
			{$last_level = $page.level}
		{/foreach}
		{if $last_level > 0}
			{for close 0 $last_level}
			{if $close > 0}
				</ul>
			</li>
			{/if}
			{/for}
		{/if}
		</ul>
	</div>
	{else}
	<ul class="fc_no_page_found">
		<li>{translate('No editable pages were found')}</li>
	</ul>
	{/if}
</div>