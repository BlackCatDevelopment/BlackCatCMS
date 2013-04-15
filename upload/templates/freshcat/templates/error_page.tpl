<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>Black Cat CMS Error Message</title>
  </head>
  <body>
<div id="fc_content_header">
	<a class="fc_button_back ui-corner-right" target="_top" href="{$LINK}" title="{translate('Next')}">{translate('Back')}</a>
	{translate('An error occured')}
</div>
<div id="fc_main_content">
	<div class="fc_error_box warning ui-corner-all ui-shadow">
		<p>{$MESSAGE}</p>
		<div class="fc_fallback">
			<a target="_top" class="fc_button_back ui-corner-right" href="{$LINK}">{translate('Back')}</a>
		</div>
	</div>
</div>
</body>
</html>