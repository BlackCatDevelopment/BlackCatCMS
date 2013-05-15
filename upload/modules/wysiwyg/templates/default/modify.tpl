<form name="wysiwyg{$section_id}" action="{$action}" method="post">
  <input type="hidden" name="page_id" value="{$page_id}" />
  <input type="hidden" name="section_id" value="{$section_id}" />
  {$WYSIWYG}
  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-bottom: 10px;">
  <tr>
	<td align="left">
		<input type="submit" value="{translate('Save')}" style="width: 100px; margin-top: 5px;" />
	</td>
	<td align="right">
		<input type="button" value="{translate('Cancel')}" onclick="javascript:window.location='index.php';" style="width: 100px; margin-top: 5px;" />
	</td>
  </tr>
  </table>
</form>
