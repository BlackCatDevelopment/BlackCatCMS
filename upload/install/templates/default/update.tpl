<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>{translate('Black Cat CMS Update')}</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="{$installer_uri}/../modules/lib_jquery/plugins/jquery.cookies/jquery.cookies.js" type="text/javascript"></script>
	<script charset="windows-1250" type="text/javascript">
	    var URL = '{$installer_uri}/update/';
 	</script>
	<script type="text/javascript" src="{$installer_uri}/progress.js"></script>
 	<link rel="stylesheet" href="{$installer_uri}/templates/default/index.css" type="text/css" />
  </head>
  <body>
    <div id="radial">
      <img src="{$installer_uri}/templates/default/images/radial.png" alt="radial" />
	</div>
    <div id="container">
      <div style="width:900px;margin:30px auto 0 auto;border:0;height:100px;background-color:#0e1115;height:60px;padding:0;text-align:center;">
	    <h1>{translate('Welcome to your Black Cat CMS Upgrade!')}</h1>
	  </div><br />

	  <div id="content" style="margin-top:-16px;min-height:300px;">
	  {if $error}
		<div id="error" class="fail">{$error}</div>
	  {else}
		{if ! $progress}
		<div style="text-align:center;">
		{translate('This wizard will help you to upgrade your current Black Cat CMS Version')}<br />
		<span style="font-weight:bold;color:#f00;">v{$cat_version}</span><br />
		{translate('to Version')}<br />
		<span style="font-weight:bold;color:#f00;">v{$new_cat_version}</span>
		</div>
		<form method="post" action="{$installer_uri}/index.php">
		  <input type="hidden" name="update" value="y" />
		  <div style="text-align:center;"><button type="submit">{translate('Press button to start')}</button></div>
		</form>
		{else}
        <div id="progress"></div>
        <div id="progress_msg"></div>
		{/if}
      {/if}
      {if $debug}
	  <br /><br /><span style="color:#f00;font-weight:bold;">Debugging enabled</span><br />
	  Dumping vars: $_REQUEST<br />
	  <textarea cols="100" rows="20" style="width: 100%;font-size:9px;">
	  {$dump}
	  </textarea>
	  {/if}
	  </div>
	<div id="black"></div>
	<img src="{$installer_uri}/templates/default/images/radial.png" alt="radial" id="radial" />
  </div> <!-- end of container -->

  <div id="header">
   <div>{translate('Black Cat CMS Update')}</div>
  </div>

  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>enjoy the difference!</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="Black Cat CMS" target="_blank">Black Cat CMS Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="Black Cat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="Black Cat CMS Bundle" target="_blank">Black Cat CMS Bundle</a> is released under several different licenses.
    </div>
  </div>

  <script src="{$installer_uri}/installer.js" type="text/javascript"></script>
  </body>
</html>