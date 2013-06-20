<form id="wysiwyg_admin" method="post" action="{$action}">
    <h2>{translate('Manage settings for editor')} -{$id}-</h2>
    <input type="hidden" name="job" value="save" />
    <input type="hidden" name="id" value="{$id}" />
    <fieldset>
        <legend>{translate('Common options')}</legend>
        <label for="skin">{translate('Skin')}</label>
        {if is_array($skins)}
              <select name="skin" id="skin">
            {foreach $skins as skin}
                <option value="{$skin}"{if $skin == $current_skin} selected="selected"{/if}>{$skin}</option>
            {/foreach}
          </select>
              <div id="wysiwyg_admin_skin_preview">
                  {translate('Skin preview')}<br />
                  {$preview}
              </div>
            {/if}<br />
        <label for="toolbar">{translate('Editor Toolbar')}</label>
        {if is_array($toolbars)}
          <select name="toolbar" id="toolbar">
            {foreach $toolbars as toolbar}
            <option value="{$toolbar}"{if $toolbar == $current_toolbar} selected="selected"{/if}>{$toolbar}</option>
            {/foreach}
          </select>
        {else}
          {if is_scalar($toolbars)}{$toolbars}{/if}
            {/if}<br />
        <label for="width">{translate('Editor width')}</label>
            <input type="text" name="width" value="{$width}" />
            <input type="radio" id="width_unit_em" name="width_unit" value="em" {$width_unit_em} /> em
            <input type="radio" id="width_unit_px" name="width_unit" value="px" {$width_unit_px} /> px
            <input type="radio" id="width_unit_proz" name="width_unit" value="%" {$width_unit_proz} /> %
            <br />
            {if $errors.width}<span class="error">{$errors.width}</span><br />{/if}
        <label for="height">{translate('Editor height')}</label>
            <input type="text" name="height" value="{$height}" />
            <input type="radio" id="height_unit_em" name="height_unit" value="em" {$height_unit_em} /> em
            <input type="radio" id="height_unit_px" name="height_unit" value="px" {$height_unit_px} /> px
            <input type="radio" id="height_unit_proz" name="height_unit" value="%" {$height_unit_proz} /> %
            <br />
            {if $errors.height}<span class="error">{$errors.height}</span><br />{/if}
        {if $htmlpurifier == true}
        <label for="enable_htmlpurifier">{translate('Enable HTMLPurifier')}</label>
            <input type="checkbox" id="enable_htmlpurifier" name="enable_htmlpurifier" value="true"{if isset($enable_htmlpurifier) && $enable_htmlpurifier == true} checked="checked"{/if} /><br />
        <span class="indent">
        {translate('If this option is enabled, all WYSIWYG content will be cleaned by using HTMLPurifier before it is stored. Users that are members of group "Administrators" are still allowed to use all HTML, including forms and script.')}
        </span>
        {/if}
    </fieldset>

    {if is_array($settings) && count($settings)}
    <fieldset>
        <legend>{translate('Additional options')}</legend>
        {foreach $settings as set}{assign "$set.name" val}
        {if ! isset($set.requires} || isset($plugins_checked[$set.requires])}
        <label for="{$set.name}">{$set.name}</label>
            <span class="settings">
            {if $set.type == 'boolean'}
            <input type="radio" name="{$set.name}" value="true" {if $config.$val == 'true' || ( $config.$val == '' && $set.default == 'true' )}checked="checked"{/if} /> true
            <input type="radio" name="{$set.name}" value="false" {if $config.$val == 'false' || ( $config.$val == '' && $set.default == 'false' )}checked="checked"{/if} /> false
            {else}
            {if $set.type == 'select'}{assign $set.options options}
            <select name="{$set.name}" id="{$set.name}">
            {foreach $options as opt}<option value="{$opt}"{if $opt == $config.$val} selected="selected"{/if}>{$opt}</option>{/foreach}
            </select>
          {else}
            <input type="text" name="{$set.name}" value="{if $config.$val == ''}{$set.default}{else}{$config.$val}{/if}" />
            {/if}{/if}
            </span>
            <span class="infotext">{translate($set.name)}</span><br />
        {/if}
        {/foreach}
    </fieldset>
    {/if}

    {if is_array($plugins) && count($plugins)}
    <fieldset id="plugins">
        <legend>{translate('Additional plugins')}</legend>
        {foreach $plugins as plugin}
        <input type="checkbox" name="plugins[]" id="plugins" value="{$plugin}" {if isset($plugins_checked[$plugin])}checked="checked"{/if}/> {$plugin}<br />
        {/foreach}
    </fieldset>
    {/if}

    {if is_array($filemanager) && count($filemanager)}
    <fieldset id="filemanager">
        <legend>{translate('Available Filemanager')}</legend>
        {foreach $filemanager as filemgr}
        <input type="radio" name="filemanager" id="filemanager_{$filemgr.dir}" value="{$filemgr.dir}" {if isset($filemanager_checked[$filemgr.dir])}checked="checked"{/if}/>
        <label for="filemanager_{$filemgr.dir}">{$filemgr.name}</label><br />
        {/foreach}
    </fieldset>
    {/if}

    <br clear="all" />

    <div class="buttons">
        <input type="submit" value="{translate('Save')}" />
        <input type="button" value="{translate('Cancel')}" onclick="document.location='{$CAT_ADMIN_URL}/admintools/index.php';" />
    </div>
</form>