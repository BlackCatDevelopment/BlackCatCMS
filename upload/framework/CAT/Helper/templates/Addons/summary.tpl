    <h1>{translate('Precheck result')}</h1>
	<h2{if $fail} style="color:#c00;"{/if}>{$heading}</h2>
	<p>{$message}</p>

	<table style="width:100%">
	<thead>
	  <tr>
		<th>&nbsp;</th>
		<th class="col1" style="text-align:left;">{translate('Requirement')}</th>
		<th class="col2" style="text-align:left;">{translate('Required')}</th>
		<th class="col3" style="text-align:left;">{translate('Current')}</th>
	  </tr>
	</thead>
	<tbody>
	{foreach $summary line}
	{if $line.key && $line.key == 'PHP_SETTINGS' && $seen == false}
	{$seen=true}
	<tr>
      <td>&nbsp;</td>
      <td colspan="3">{translate('PHP Settings')}</td>
	</tr>
	{/if}
    {if $line.key && $line.key == 'ADDONS' && $addonsseen == false}
	{$addonsseen=true}
	<tr>
      <td>&nbsp;</td>
      <td style="{$line.style}" colspan="3">{translate('Required Addons')}</td>
	</tr>
	{/if}
 	<tr>
	  <td class="{if $line.status}ok{else}fail{/if}">&nbsp;</td>
      <td class="col1">{$line.check}</td>
      <td style="{$line.style}" class="col2">{$line.required}</td>
      <td style="{$line.style}" class="col3">{$line.actual}</td>
	</tr>
	{/foreach}
	</tbody>
	</table>
