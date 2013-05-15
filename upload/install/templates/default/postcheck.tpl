<div class="info">
{translate('Please check your settings before finishing the installation process.')}
</div>

<table style="border: 1px solid #0e1115;">
  <tbody>
	{foreach $config key value}
	<tr>
      <td class="row_{if $dwoo.foreach.default.index % 2}a{else}b{/if}">{translate($key)}</td>
      <td class="row_{if $dwoo.foreach.default.index % 2}a{else}b{/if}">{$value}</td>
	</tr>
	{/foreach}
  </tbody>
</table>