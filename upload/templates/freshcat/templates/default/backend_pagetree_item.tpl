                <dl class="fc_page_tree_search_dl">
					<dt>PageID</dt>
					<dd class="fc_search_PageID">{$page_id}</dd>
					<dt>MenuTitle</dt>
					<dd class="fc_search_MenuTitle">{$menu_title}</dd>
					<dt>PageTitle</dt>
					<dd class="fc_search_PageTitle">{$page_title}</dd>
				</dl>
				<div class="fc_page_link{if !$is_editable} fc_page_tree_not_editable{/if}">
					{if $is_parent}<span class="fc_toggle_tree">+</span>{/if}
					<a href="{if !$is_editable}#{else}{$CAT_ADMIN_URL}/pages/{$action}.php?page_id={$page_id}{/if}" title="{translate('Page title')}: {$page_title}" class="{if $current}fc_gradient3{/if}">
						<span class="fc_page_tree_menu_title icon-{if $visibility == 'public'}screen{elseif $visibility == 'private'}key{elseif $visibility == 'registered'}users{elseif $visibility == 'hidden'}eye-2{elseif $visibility == 'deleted'}remove{else}eye-blocked{/if}"> {$menu_title}</span> <span class="fc_page_tree_menu_ID">(ID: {$page_id})</span>
					</a>
					{if $permission.pages_settings}<span class="fc_page_tree_options_open fc_pages_tree_options_button icon-tools fc_gradient1 fc_gradient_hover flag-{$language}"></span>{/if}
				</div>
				<input type="hidden" name="page_id" value="{$page_id}" />