  <table>
  	<thead>
	  <tr>
		<th></th>
		<th style="text-align:left;" class="col1">{translate('Folder')}</th>
		<th style="text-align:left;" class="col2">{translate('Required')}</th>
		<th style="text-align:left;" class="col3">{translate('Current')}</th>
	  </tr>
	</thead>
    <tbody>
{foreach $dirs dir}
	  <tr>
		<td class="{if $dir.ok}ok{else}fail{/if}">&nbsp;</td>
	    <td>{$dir.name}</td>
	    <td class="success">{translate('Writable')}</td>
	    <td class="{if $dir.ok}success{else}fail{/if}">{if $dir.ok}{translate('Writable')}{else}{translate('Not writable!')}{/if}</td>
	  </tr>
{/foreach}
	</tbody>
  </table>
  <div id="result" class="info {if $ok}success{else}fail{/if}">
  {$result}
  </div>