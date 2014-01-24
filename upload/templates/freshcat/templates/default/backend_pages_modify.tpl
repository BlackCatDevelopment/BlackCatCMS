{include('backend_pages_header.tpl')}
{include backend_pages_banner.tpl}
<div id="fc_main_content">
	<div class="fc_modified_header fc_br_bottom fc_gradient1 fc_border">
		<form action="{$CAT_ADMIN_URL}/pages/sections_save.php" id="fc_add_module_form" name="add_section" method="post">
			<div id="fc_add_module">
				<select name="add_module">
					{foreach $modules as module}
					<option value="{$module.VALUE}">{$module.NAME}</option>
					{/foreach}
				</select>
				<input type="hidden" name="page_id" value="{$PAGE_ID}" />
				<input type="submit" name="submit_add" value="{translate('Add')}" />
			</div>
		</form>
		<button class="icon-eye-2 fc_gradient1 fc_gradient_hover" id="show_modules"> {translate('Show all sections')}</button>
		<button class="icon-eye-blocked-2 fc_gradient1 fc_gradient_hover" id="hide_modules"> {translate('Hide all sections')}</button>
        <button class="icon-file fc_gradient1 fc_gradient_hover" id="recreate_af"> {translate('Re-create access file')}</button>
	</div>
	{if $blocks_counter > 0}
	<ul id="fc_all_blocks">
		{foreach $blocks as block}
		<li class="fc_module_block fc_active" id="sectionid_{$block.section_id}">
			{if $MANAGE_SECTIONS}<div class="fc_section_drag fc_border_all_light fc_gradient1 fc_gradient_hover icon-move fc_br_top"></div>{/if}
			<div class="fc_module_content fc_shadow_small fc_br_all">
				<div class="fc_blocks_header fc_gradient1 fc_border fc_br_top">
					<table>
						<tbody>
							<tr>
								<td class="fc_section_header_block">
									{translate('Block')}: <strong>{$block.current_block_name}</strong>
								</td>
								{if $block.name}
								<td class="fc_section_header_name">
									{translate('Name')}: <strong>{$block.name}</strong>
								</td>
								{/if}
								<td class="fc_section_header_module">
									{translate('Modul')}: <strong>{$block.module}</strong>
								</td>
								<td class="fc_section_header_id">
									ID: <strong>{$block.section_id}</strong>
								</td>
								{if $MANAGE_SECTIONS}
								<td class="fc_sections_options">
									<span class="fc_section_button icon-eye-2 fc_toggle_section_block" title="{translate('show/hide section')}"></span>
									<div class="fc_section_modify_div_parent">
										<span class="fc_section_button fc_open_section_modify icon-calendar" title="{translate('Modify section')}"></span>
										<div class="fc_section_modify_div fc_gradient1 fc_border_all_light fc_shadow_small fc_br_all">
											<div class="fc_arrow_up"></div>
											<form method="post" action="{$CAT_ADMIN_URL}/pages/ajax_sections_save.php" name="modify_section" class="fc_modify_section">
												<div>
                                                    <input type="hidden" name="_cat_ajax" value="1" />
													<input type="hidden" name="update_section_id" value="{$block.section_id}" />
													<span class="fc_section_modify_label">{translate('Name')}:</span>
													<input type="text" name="blockname" value="{$block.name}" />
													<div class="clear"></div>
													<hr />
                                                    {if $block.template_blocks > 0}
													<span class="fc_section_modify_label left">{translate('Block')}:</span>
													<select name="set_block" class="left">
													{foreach $block.template_blocks as template_block}
														<option value="{$template_block.VALUE}"{if $template_block.SELECTED} selected="selected"{/if}>{$template_block.NAME}</option>
													{/foreach}
													</select>
													<div class="clear"></div>
													<hr />
                                                    {/if}
													<span class="fc_section_modify_label">{translate('From')}:</span>
													<input type="text" name="day_from" value="{$block.date_day_from}" class="fc_date_two" maxlength="2" title="{translate('Day')}" /> . 
													<input type="text" name="month_from" value="{$block.date_month_from}" class="fc_date_two" maxlength="2" title="{translate('Month')}" /> . 
													<input type="text" name="year_from" value="{$block.date_year_from}" class="fc_date_four" maxlength="4" title="{translate('Year')}" /> - 
													<input type="text" name="hour_from" value="{$block.date_hour_from}" class="fc_date_two" maxlength="2" title="{translate('Hour')}" /> : 
													<input type="text" name="minute_from" value="{$block.date_minute_from}" class="fc_date_two" maxlength="2" title="{translate('Minute')}" />
													<div class="clear"></div>
													<span class="fc_section_modify_label">{translate('To')}:</span>
													<input type="text" name="day_to" value="{$block.date_day_to}" class="fc_date_two" maxlength="2" title="{translate('Day')}" /> . 
													<input type="text" name="month_to" value="{$block.date_month_to}" class="fc_date_two" maxlength="2" title="{translate('Month')}" /> . 
													<input type="text" name="year_to" value="{$block.date_year_to}" class="fc_date_four" maxlength="4" title="{translate('Year')}" /> - 
													<input type="text" name="hour_to" value="{$block.date_hour_to}" class="fc_date_two" maxlength="2" title="{translate('Hour')}" /> : 
													<input type="text" name="minute_to" value="{$block.date_minute_to}" class="fc_date_two" maxlength="2" title="{translate('Minute')}" />
													<div class="clear"></div>
												</div>
                                                <input type="submit" name="save_section" value="{translate('Save & Close')}" />
											</form>
										</div>
									</div>
									<div class="fc_section_button fc_delete_section icon-remove" title="{translate('delete section')}">
                                        <form method="post" action="{$CAT_ADMIN_URL}/pages/ajax_sections_save.php" name="modify_section" class="fc_modify_section">
										    <input type="hidden" name="delete_section_id" value="{$block.section_id}" />
                                        </form>
									</div>
								</td>
								{/if}
							</tr>
						</tbody>
					</table>
				</div>
				<div class="fc_blocks_content">
					{$block.content}
					<div class="clear"></div>
				</div>
				<div class="fc_blocks_footer fc_br_bottom fc_gradient1 fc_border">
                    {if $block.modified_by}{translate('Last modification by')} {$block.modified_by}{/if}
                    {if $block.modified_when}- {$block.modified_when}{/if}
                </div>
			</div>
		</li>
		{/foreach}
	</ul>
	{else}
	<div class="fc_module_block"><span class="highlight">{translate('No sections were found for this page')}</span></div>
	{/if}
	<div id="fc_main_content_footer"></div>
</div>