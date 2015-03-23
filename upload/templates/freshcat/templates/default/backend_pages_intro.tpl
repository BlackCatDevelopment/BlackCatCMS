<div id="fc_content_header">
	<span class="left">{translate('Modify intro page')}</span>
	<div class="clear"></div>
</div>
<div class="fc_info" style="margin:5px">{translate('Please note: You will have to define the complete HTML for the intro page! It is not served by the CMS!')}</div>
<div id="fc_main_content">
	<div class="fc_module_block fc_active">
		<form action="{$CAT_ADMIN_URL}/pages/intro2.php" method="post">
			<div class="fc_module_content ui-shadow ui-corner-all">
				<div class="fc_blocks_header ui-corner-top">
				<span class="fc_section_header_block">{translate('Intro page contents')}</span>
				</div>
				{$intro_page_content}
				<div class="fc_confirm_bar ui-corner-bottom">
					<input type="submit" value="{translate('Save')}" />
					<input type="reset" value="{translate('Cancel')}" onclick="javascript: window.location = 'index.php';" />
				</div>
			</div>
		</form>
	</div>
</div>