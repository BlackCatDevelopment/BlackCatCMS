{include file="header.tpl"}
  <br /><br /><a href="{$CAT_ADMIN_URL}/admintools/tool.php?tool=droplets">&laquo; {translate('Back to overview')} &laquo;</a><br />
  {if $problem}<div class="problem ui-corner-all">{$problem}</div>{/if}
  {if $info}<div class="info ui-corner-all">{$info}</div>{/if}
  <form method="post" action="{$action}">
    <input type="hidden" name="tool" value="droplets" />
    <input type="hidden" name="edit" value="{$id}" />
    <fieldset>
      <legend>{translate('Edit Droplet')}: {$name}</legend>
        <label class="label" for="name">{translate('Name')}:</label>
          <input type="text" name="name" id="name" value="{$data.name|escape}" /><br />
        <label class="label" for="comment">{translate('Description')}:</label>
          <input type="text" name="description" id="description" value="{$data.description|escape}" /><br /><b></b>
        <label class="label" for="comment">{translate('Comments')}:</label>
          <textarea name="comments" id="comments">{$data.comments|escape}</textarea><br />
        <span class="label">{translate('Active')}:</span>
          <input type="radio" checked="checked" id="active_y" name="active" value="1" />
            <label for="active_y">{translate('Yes')}</label>
          <input type="radio" id="active_n" name="active" value="0" />
            <label for="active_n">{translate('No')}</label><br /><br />
        <label class="label" for="code">{translate('Code')}:</label>
          {show_edit_area( 'code', 'code', $data.code, '800px' )}<br style="clear:both;" /><br />
        <input type="submit" name="save" value="{translate('Save')}" />
        <input type="submit" name="save_and_back" value="{translate('Save and Back')}" />
        <input type="submit" name="cancel" value="{translate('Cancel')}" />
    </fieldset>
  </form>
{include file="footer.tpl"}