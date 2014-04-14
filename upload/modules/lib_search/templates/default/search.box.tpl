<div class="search_box">
  <form name="search" action="{$action}" method="get">
     <input type="hidden" name="page_id" value="{$PAGE_ID}" />
     <input class="search_box_input" type="text" name="string" value="{translate('Search ...')}"  onfocus="javascript:search_box_onfocus(this, '{translate("Search ...")}');" onblur="javascript:search_box_onblur(this, '{translate("Search ...")}');" />
     <input type="submit" value="{translate('Submit')}" />
  </form>
</div>

      