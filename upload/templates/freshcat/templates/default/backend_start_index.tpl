<div id="fc_content_header">
	{translate('Welcome to Black Cat CMS Administration')}
</div>
<div id="fc_main_content">
    <div class="highlight" style="width:100%">
    {translate('This is your dashboard. At the moment, it is not possible to change the widgets shown here or to set permissions. This will be done in next version of BlackCat CMS.')}
    </div>
	<ul id="fc_widget_list_1" class="fc_widgets">
    {foreach $widgets_1 as widget}
		<li class="fc_widget_wrapper">
			<div class="fc_widget_shadow_1"></div>
			<div class="fc_widget_shadow_2"></div>
			<div class="fc_widget_content">
				<span class="fc_close"></span>
				<div class="fc_starter ui-corner-all fc_start_{$widget.module_name}">
					<p class="fc_start_title ui-corner-top">{$widget.module_name}</p>
						{$widget.content}
					<div class="clear"></div>
				</div>
			</div>
		</li>
	{/foreach}
	</ul>
	<ul id="fc_widget_list_2" class="fc_widgets">
	{foreach $widgets_2 as widget}
		<li class="fc_widget_wrapper">
			<div class="fc_widget_shadow_1"></div>
			<div class="fc_widget_shadow_2"></div>
			<div class="fc_widget_content">
				<span class="fc_close"></span>
				<div class="fc_starter ui-corner-all fc_start_{$widget.module_name}">
					<p class="fc_start_title ui-corner-top">{$widget.module_name}</p>
						{$widget.content}
					<div class="clear"></div>
				</div>
			</div>
		</li>
	{/foreach}
	</ul>
	<div class="clear"></div>
</div>