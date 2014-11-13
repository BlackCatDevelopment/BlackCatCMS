<div id="bclogs">
    {if $logs}
    <table style="width:90%;margin:0 auto;">
    {foreach $logs item}
    <tr>
        <td><a style="margin-right:20px" class="bcshowlog" href="{$CAT_URL}/modules/blackcat/widgets/logs.php?file={$item.file}">{$item.file}</a></td>
        <td>{$item.size}</td>
        <td>
            <a href="{$CAT_URL}/modules/blackcat/widgets/logs.php?dl={$item.file}" class="bclogdl icon-download"><span style="display:none">{$item.file}</span></a>
            {if $item.removable === true}<a href="{$CAT_URL}/modules/blackcat/widgets/logs.php?remove={$item.file}" class="bclogremove icon-remove"><span style="display:none">{$item.file}</span></a>{/if}
        </td>
    </tr>
    {/foreach}
    </table>
    {/if}
    <div id="bclogdialog" title="{translate('Log')}:" style="display:none;max-height:400px;overflow:auto;font-size:10px;"></div>
</div>

<script charset=windows-1250 type="text/javascript">
    jQuery(document).ready(function($) {
        $( "#bclogdialog" ).dialog({
            modal: true,
            autoOpen: false,
            height: 400,
            width: 800,
            buttons:
            [
                { text: cattranslate('Close'), click: function() { $( this ).dialog( "close" ); } }
            ]
        });
        $('.bclogremove').click(function(e) {
            e.preventDefault();
            $.ajax(
			{
				type:	 'POST',
				url:	 CAT_URL + '/modules/blackcat/widgets/logs.php',
				data:	 {
                    remove: $(e.target).text()
                },
				cache:	 false,
                success: function( data, textStatus, jqXHR )
				{
                    if(data !== "") {
                        $('#bclogdialog').html(data);
                        $('#bclogdialog').dialog("open");
                    }
                    $(e.target).parent().parent().remove();
                }
            });
        });
        $('.bcshowlog').click(function(e) {
            e.preventDefault();
            $.ajax(
			{
				type:	 'POST',
				url:	 CAT_URL + '/modules/blackcat/widgets/logs.php',
				data:	 {
                    file: $(e.target).text()
                },
				cache:	 false,
                success: function( data, textStatus, jqXHR )
				{
                    $('#bclogdialog').html(data);
                    $('#bclogdialog').dialog("option", "title", cattranslate('Log') + ': ' + $(e.target).html() );
                    $('#bclogdialog').dialog("open");
                }
            });
        });
    });
</script>