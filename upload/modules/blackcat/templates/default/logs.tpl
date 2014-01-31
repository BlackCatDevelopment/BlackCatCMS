<div id="bclogs">
    {if $logs}
    <table>
    {foreach $logs item}
    <tr>
        <td><a style="margin-right:20px" class="bcshowlog" href="{$CAT_URL}/modules/blackcat/widgets/logs.php?file={$item}">{$item}</a></td>
        <td><a href="{$CAT_URL}/modules/blackcat/widgets/logs.php?remove={$item}" class="bclogremove icon-remove"><span style="display:none">{$item}</span></a></td>
    </tr>
    {/foreach}
    </table>
    {/if}
    <div id="bclogdialog" title="{translate('Log')}:" style="display:none;max-height:400px;overflow:auto;"></div>
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