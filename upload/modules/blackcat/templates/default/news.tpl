{foreach $news item}
    <a href="{$item.link}" target="_blank">{$item.title}</a> <span style="font-size:smaller;">({$item.published})</span><br />
{/foreach}