    <div id="jquery_plugin_mgr">
        <h1>
            {translate('jQuery Plugin Manager')}
        </h1>
        <form method="post" action="{$CAT_ADMIN_URL}/admintools/tool.php?tool=jquery_plugin_mgr" enctype="multipart/form-data" name="upload">
            <input type="hidden" name="upload" value="1" />
            <fieldset class="fieldset">
                <img src="{$CAT_URL}/modules/jquery_plugin_mgr/images/upload.png" style="float:left;margin-right:15px;" />
                <legend>{translate('Upload/install plugin')}</legend>
                <input type="file" name="userfile" />
                <script type="text/javascript">document.upload.userfile.focus();</script>
                <input type="submit" class="submit button" value="{translate('Upload')}" name="submit" />
                <div>
                <strong>{translate('Please note')}:</strong>
                {translate('In general, you can add any jQuery Plugin here (ZIP format only!).')}
                {translate('Some plugins are available especially packed for BlackCat CMS, so you should prefer these over common ones.')}
                {translate('Of course, we cannot guarantee that plugins uploaded here will work in general or with BlackCat CMS especially.')}
                </div>

            </fieldset>
        </form>
        {if is_array($plugins)}
        <br /><br />
        <h2>
            {translate('Already installed plugins')}
        </h2>
        <ul id="tiles">
        {foreach $plugins p}
            <li class="tile">{$p}{if $readmes.$p} <a href="{$readmes.$p}" class="readmedlg"><img src="{$CAT_URL}/modules/jquery_plugin_mgr/images/info.png" alt="info.png" title="{translate('Open Readme')}" /></a>{/if}</li>
        {/foreach}
        </ul>
        {/if}
        <div class="dialog" style="display:none">
         asdfasdf
        </div>
    </div>
{literal}
    <script charset=windows-1250 type="text/javascript">
        if(typeof jQuery != 'undefined') {
            jQuery(document).ready(function($) {
                $("div.dialog").dialog({
                    width: 960,
                    hide: 'clip',
                    show: 'blind',
                    autoOpen: false
                });
                $('a.readmedlg').click(function(e) {
                    e.stopPropagation();
                    var url = $(this).attr('href');
                    $('div.dialog').load(url, function() {
                        $('div.dialog').dialog('open');
                    });
                    return false;
                });
            });
        }
    </script>
{/literal}