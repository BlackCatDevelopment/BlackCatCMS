{include file="header.tpl"}
  <a href="">{translate('Back to overview')}</a><br />
  {if $problem}<div class="problem ui-corner-all">{$problem}</div>{/if}
  {if $info}<div class="info ui-corner-all">{$info}</div>{/if}
  <form action="{$action}" enctype="multipart/form-data" method="post">
    <input type="hidden" name="tool" value="droplets" />
    <input type="hidden" name="import" value="1" />
    <fieldset>
      <legend>{translate('Import')}</legend>
        <label for="file">{translate('Please choose a file')}</label>
        <input type="file" name="file" id="file" />
        <input type="submit" name="save" value="{translate('Save')}" />
        <input type="submit" name="save_and_back" value="{translate('Save and Back')}" />
        <input type="submit" name="cancel" value="{translate('Cancel')}" />
    </fieldset>
  </form>
{include file="footer.tpl"}