<!doctype html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>BlackCat CMS - Fatal Error</title>
    <style type="text/css">
        body{
        font-family: HelveticaNeue,Helvetica,Arial,Verdana,sans-serif;font-size:1.3em;line-height: 1.5em;background-color:#2C2C2C;color:#fff;
        }
        .fc_header{
        background-color:#0e1115;border-bottom:1px dashed #2d2d2d;top:0;color:#900;font-size:.9em;left:0;padding-bottom:5px;padding-top:5px;position:absolute;text-align:center;width:100%;z-index:1;margin:0;
        }
        .fc_error{
        width:100%;height:100%;position:absolute;top:150px;text-align:center;
        }
        .fc_license{
        background-color:#0e1115;border-top:1px dashed #2d2d2d;bottom:0;color:#9e9e9e;font-size:.7em;left:0;padding-bottom:5px;padding-top:5px;position:absolute;text-align:center;width:100%;z-index:1;margin:0;
        }
        a {
        color: #5aa2da;text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="fc_header">
        <h1>BlackCat CMS Fatal Error</h1>
    </div>
    <div class="fc_error">
        {translate('Ooops... A fatal error occured while processing your request!')}<br /><br />
        {translate('Error message')}:<br />
        {translate($message)}<br /><br />
        {if $file}
        {translate('Source')}: <span style="font-size: smaller;">[ {$file} : {$line} : {$function} ]</span><br /><br />
        {/if}
        {translate("We're sorry!")}
    </div>
    <div class="fc_license">
		<p>
            <a target="_blank" title="Black Cat CMS Core" href="http://blackcat-cms.org">Black Cat CMS Core</a> is released under the
			<a target="_blank" title="Black Cat CMS Core is GPL" href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>.<br>
			<a target="_blank" title="Black Cat CMS Bundle" href="http://blackcat-cms.org">Black Cat CMS Bundle</a> is released under several different licenses.
		</p>
	</div>
</body>
</html>