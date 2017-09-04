<div id="bclogs">
    {if $logs}
    <table style="width:90%;margin:0 auto;">
    {foreach $logs item}
    <tr>
        <td><a style="margin-right:20px" class="bcshowlog" href="{$CAT_URL}/modules/blackcat/widgets/logs.php?file={$item.date}" data-file="{$item.date}">{$item.file}</a></td>
        <td>{$item.size}</td>
        <td>
            <a href="{$CAT_URL}/modules/blackcat/widgets/logs.php?dl={$item.date}" class="bclogdl icon-download" data-file="{$item.date}"><span style="display:none">{$item.file}</span></a>
            {if $item.removable === true}<a href="{$CAT_URL}/modules/blackcat/widgets/logs.php?remove={$item.date}" class="bclogremove icon-remove" data-file="{$item.date}"><span style="display:none">{$item.file}</span></a>{/if}
        </td>
    </tr>
    {/foreach}
    </table>
    {/if}
    <div id="bclogdialog" title="{translate('Log')}:" style="display:none;max-height:400px;overflow:auto;font-size:10px;"></div>
</div>

<script charset=windows-1250 type="text/javascript">
    jQuery(document).ready(function($) {


         /**
         *
         * jquery.binarytransport.js
         *
         * @description. jQuery ajax transport for making binary data type requests.
         * @version 1.0
         * @author Henry Algus <henryalgus@gmail.com>
         *
         */

        // use this transport for "binary" data type
        $.ajaxTransport("+binary", function(options, originalOptions, jqXHR) {
            // check for conditions and support for blob / arraybuffer response type
            if (window.FormData && ((options.dataType && (options.dataType == 'binary')) || (options.data && ((window.ArrayBuffer && options.data instanceof ArrayBuffer) || (window.Blob && options.data instanceof Blob)))))
            {
                return {
                    // create new XMLHttpRequest
                    send: function(headers, callback){
                    // setup all variables
                    var xhr = new XMLHttpRequest(),
                        url = options.url,
                        type = options.type,
                        async = options.async || true,
                        // blob or arraybuffer. Default is blob
                        dataType = options.responseType || "blob",
                        data = options.data || null,
                        username = options.username || null,
                        password = options.password || null;

                        xhr.addEventListener('load', function(){
                            var data = { };
                            data[options.dataType] = xhr.response;
                            // make callback and send data
                            callback(xhr.status, xhr.statusText, data, xhr.getAllResponseHeaders());
                        });

                        xhr.open(type, url, async, username, password);

                        // setup custom headers
                        for (var i in headers ) {
                            xhr.setRequestHeader(i, headers[i] );
                        }

                        xhr.responseType = dataType;
                        xhr.send(data);
                    },
                    abort: function(){
                        jqXHR.abort();
                    }
                };
            }
        });

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
        $('.bclogdl').click(function(e) {
            e.preventDefault();
            $.ajax({
                type:     'POST',
                dataType: "binary",
                url:     CAT_URL + '/modules/blackcat/widgets/logs.php',
                data:     {
                    dl: $(e.target).data("file"),
                    _cat_ajax: true
                },
                cache:     false,
                success: function(response, status, xhr)
                {
                    // https://stackoverflow.com/a/23797348
                    var filename = "";
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    var type = xhr.getResponseHeader('Content-Type');
                    var blob = response; 
                    if (typeof window.navigator.msSaveBlob !== 'undefined') {
                        // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                        window.navigator.msSaveBlob(blob, filename);
                    } else {
                        var URL = window.URL || window.webkitURL;
                        var downloadUrl = URL.createObjectURL(blob);

                        if (filename) {
                            // use HTML5 a[download] attribute to specify filename
                            var a = document.createElement("a");
                            // safari doesn't support this yet
                            if (typeof a.download === 'undefined') {
                                window.location = downloadUrl;
                            } else {
                                a.href = downloadUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                            }
                        } else {
                            window.location = downloadUrl;
                        }
                        setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
                    }
                }
            });
        });

        $('.bclogremove').click(function(e) {
            e.preventDefault();
            $.ajax(
			{
				type:	 'POST',
				url:	 CAT_URL + '/modules/blackcat/widgets/logs.php',
				data:	 {
                    remove: $(e.target).data("file"),
                    _cat_ajax: true
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
                    file: $(e.target).data("file"),
                    _cat_ajax: true
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