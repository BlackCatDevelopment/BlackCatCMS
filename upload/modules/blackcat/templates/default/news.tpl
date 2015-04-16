{foreach $news item}
    <a href="{$item.link}">{$item.title}</a> <span style="font-size:smaller;">({$item.published})</span><br />
{/foreach}