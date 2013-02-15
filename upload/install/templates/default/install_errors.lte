{if $errors}
<div id="result" class="fail">
{translate('The installation failed! Please see check error information below.')}
</div>
{/if}

{if $errors}
<table style="border: 1px solid #0e1115;">
  <tbody>
	{foreach $errors key value}
	<tr>
      <th colspan="2">{translate($key)}</th>
	</tr>
	{foreach $value skey svalue}
	<tr>
      <td class="row_{if $dwoo.foreach.default.index % 2}a{else}b{/if}">{translate($skey)}</td>
      <td class="row_{if $dwoo.foreach.default.index % 2}a{else}b{/if}">
		{if is_array($svalue)}{foreach $svalue item}{$item}<br />{/foreach}{else}{$svalue}{/if}
	  </td>
	</tr>
	{/foreach}{/foreach}
  </tbody>
</table>
{/if}