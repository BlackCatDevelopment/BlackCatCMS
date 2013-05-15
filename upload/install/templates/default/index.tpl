{include header.tpl}
{$output}

{if $debug}
<br /><br /><span style="color:#f00;font-weight:bold;">Debugging enabled</span><br />
Dumping vars: $this_step (0), $_REQUEST (1), $prevstep (2), $nextstep (3), $currentstep (4), $steps (5)<br />
<textarea cols="100" rows="20" style="width: 100%;font-size:9px;">
{$dump}
</textarea>
{/if}

{include footer.tpl}