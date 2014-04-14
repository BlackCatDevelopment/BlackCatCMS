<div id="search_results">
  <h2>{translate('New search')}</h2>
  <div class="search_form">
    <form name="search_form" action="{$form.action}" method="get">
      <table width="100%">
        <colgroup>
          <col width="*" />
          <col width="150" />
        </colgroup>
        <tr>
          <td>
            <input type="hidden" name="{$form.search_path.name}" value="{$form.search_path.value}" />
            <input type="text" class="search_box_input" name="{$form.search_string.name}" value="{translate('Search ...')}"  onfocus="javascript:search_box_onfocus(this, '{translate("Search ...")}');" onblur="javascript:search_box_onblur(this, '{translate("Search ...")}');" />
          </td>
          <td>
            <input type="submit" value="{translate('Submit')}" />
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <input id="search_type_match_all" type="radio" name="{$form.search_type.name}" value="{$form.search_type.match_all.value}"{if $form.search_type.match_all.checked == 1} checked="checked"{/if} />
            <label for="{$form.search_type.match_all.value}">{translate('all words')}</label>
            <input id="search_type_match_any" type="radio" name="{$form.search_type.name}" value="{$form.search_type.match_any.value}"{if $form.search_type.match_any.checked == 1} checked="checked"{/if} />
            <label for="{$form.search_type.match_any.value}">{translate('any word')}</label>
            <input id="search_type_match_exact" type="radio" name="{$form.search_type.name}" value="{$form.search_type.match_exact.value}"{if $form.search_type.match_exact.checked == 1} checked="checked"{/if} />
            <label for="{$form.search_type.match_exact.value}">{translate('exact match')}</label>
            <input id="search_type_match_exact" type="radio" name="{$form.search_type.name}" value="{$form.search_type.match_image.value}"{if $form.search_type.match_image.checked == 1} checked="checked"{/if} />
            <label for="{$form.search_type.match_image.value}">{translate('only images')}</label>
          </td>
        </tr>
      </table>
    </form>
  </div>
  {if $result.count > 0}<br />
    <h2>{translate('Search results')}</h2>
    <div style="margin-bottom:5px">{translate('Search term')}: {$form.search_string.value}</div>
    {foreach $result.items item}
      <div class="search_item">
        <div class="search_item_header">
          {if $item.page.visibility != 'public'}
            <img src="{$images.locked.src}" width="{$images.locked.width}" height="{$images.locked.height}" alt="{translate('Content locked')}" title="{translate('Content locked')}" />
          {/if}
          {if count_characters($item.page.link) > 0}<a href="{$item.page.link}">{$item.page.title}</a>{else}{$item.page.title}{/if}
          <div class="search_item_info">
            {translate('Last modified')}: {$item.page.last_changed.date_formatted} / {translate('by')}: {$item.user.display_name}
          </div>
        </div>
        {if $item.page.thumb.active == 1}
        <div class="content_image">
          {if count_characters($item.page.link) > 0}<a href="{$item.page.link}">{/if}<img src="{$item.page.thumb.image.src}" alt="{$item.page.thumb.image.alt}" title="{$item.page.thumb.image.title}" />{if count_characters($item.page.link) > 0}</a>{/if}
        </div>
        {/if}
        <div class="search_item_description">{$item.page.description}</div>
        {if count_characters($item.page.excerpt) > 0}
          <div class="search_item_excerpt">{$item.page.excerpt}</div>
        {/if}
        {if $item.page.images.count > 0}
          <div class="search_image_thumbs">
            <div class="search_image_thumbs_title">{translate('Matching images')}:</div>
            {foreach $item.page.images.items image}
            <div class="search_image_thumbs_loop">
              <div class="search_image_thumbs_image">
                {if count_characters($item.page.link) > 0}<a href="{$item.page.link}">{/if}<img src="{$image.src}" alt="{$image.alt}" title="{$image.title}" />{if count_characters($item.page.link) > 0}</a>{/if}
              </div>
              {$image.excerpt}
            </div>
            {/foreach}
          </div>
        {/if}
        <div class="search_item_link"></div>
      </div>
    {/foreach}
  {else}
    {translate('No matches!')}
  {/if}
</div>