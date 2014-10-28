			<label class="fc_label_300" for="fc_default_template" title="{translate('Choose a backend theme.')}">{translate('Backend theme')}:</label>
			<select name="default_theme" id="fc_default_theme">
				{foreach $backends backend}
				<option value="{$backend.VALUE}"{if $backend.SELECTED} selected="selected"{/if}>{$backend.NAME}</option>
				{/foreach}
			</select><br />

            <div id="div_theme_variants" style="display:{if $variants}inline-block{else}none{/if}">
            <label class="fc_label_300" for="fc_default_theme_variant" title="{translate('Choose a template variant here. Available variants are defined in the template\'s info.php.')}">{translate('Variant')}:</label>
            <select name="default_theme_variant" id="fc_default_theme_variant">
				{foreach $variants variant}
				<option value="{$variant}"{if $variant == $values.default_theme_variant} selected="selected"{/if}>{$variant}</option>
				{/foreach}
			</select>
            </div>
			<hr />

			<label class="fc_label_300" for="fc_wysiwyg_editor" title="{translate('If no editors are listed here, you have to install one first.')}">{translate('WYSIWYG Editor')}:</label>
			<select name="wysiwyg_editor" id="fc_wysiwyg_editor">
				{foreach $wysiwyg module}
				<option value="{$module.VALUE}"{if $module.SELECTED} selected="selected"{/if}>{$module.NAME}</option>
				{/foreach}
			</select>

			{if $DISPLAY_ADVANCED}
			<hr />

			<label class="fc_label_300" for="fc_redirect_timer" title="{translate('After some actions, success or error messages are displayed. This is the time such messages are shown before the backend redirects you back to the calling page.')}">{translate('Redirect after')}:</label>
			<input type="text" name="redirect_timer" id="fc_redirect_timer" value="{$values.redirect_timer}" /> ms
            <p>{translate('allowed values')}: 0 - 10000</p>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_token_lifetime" title="{translate('Tokens are used to protect against CSRF attacks. Too short token lifetimes will cause problems, so change this setting wisely.')}">{translate('Token lifetime')}:</label>
			<input type="text" name="token_lifetime" id="fc_token_lifetime"  value="{$values.token_lifetime}" /> s
			<p>{translate('0 means default, which is 7200s = 2 hours; allowed values')}: 0 - 10000</p>
			<div class="clear_sp"></div>

			<label class="fc_label_300" for="fc_max_attempts" title="{translate('By default, wrong login attempts are only saved in the session. To lock the user account after the max. attempts are reached, use the appropriate security setting. (Security -> Disable user accounts when max login attempts is reached)')}">{translate('Allowed wrong login attempts')}:</label>
			<input type="text" name="max_attempts" id="fc_max_attempts"  value="{$values.max_attempts}" />
			<p>{translate('When reaching this number, more login attempts are not possible for this session.')} ({translate('allowed values')}: 1 - 10)</p>
			<div class="clear_sp"></div>
			{else}
			<input type="hidden" name="er_level" value="{$values.er_level}" />
			<input type="hidden" name="redirect_timer" value="{$values.redirect_timer}" />
			<input type="hidden" name="token_lifetime" value="{$values.token_lifetime}" />
			<input type="hidden" name="max_attempts" value="{$values.max_attempts}" />
			{/if}
			<div class="clear_sp"></div>

<script charset=windows-1250 type="text/javascript">
	$('select[name=default_theme]').change( function()
	{
		$(this).closest('form').removeClass('ajaxForm').unbind();
        var dates	= {
			'_cat_ajax': 1,
            'template':  $('#fc_default_theme').val()
		};
		$.ajax(
		{
			context:	form,
			type:		'POST',
			url:		CAT_ADMIN_URL + '/settings/ajax_get_template_variants.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			success:	function( data, textStatus, jqXHR )
			{
				if ( data.success === true )
				{
					var form	= $(this);
                    // remove old options
                    $("#fc_default_theme_variant").empty();
                    if( $(data.variants).size() > 0 )
                    {
    					$.each(data.variants, function(index, value)
    					{
                            $("<option/>").val(value).text(value).appendTo("#fc_default_theme_variant");
					    });
                        $('#div_theme_variants').show();
                    }
                    else {
                        $('#div_theme_variants').hide();
                    }
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
	});
</script>