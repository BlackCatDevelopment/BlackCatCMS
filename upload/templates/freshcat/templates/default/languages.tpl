<div class="langmenu">
	{foreach $items as item}
	<a href="{$item.href}" title="{$item.menu_title}"><img src="{$CAT_URL}/languages/{lower($item.language)}.png" alt="{$item.language}"></a>
	{/foreach}
</div>