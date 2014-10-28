            <label class="fc_label_200" for="fc_default_template" title="{translate('Select the frontend template you wish to use as default. You can choose different templates on a per-page-level.')}">{translate('Template')}:</label>
			<select name="default_template" id="fc_default_template">
				{foreach $templates template}
				<option value="{$template.VALUE}"{if $template.SELECTED} selected="selected"{/if}>{$template.NAME}</option>
				{/foreach}
			</select><br />

            <div id="div_template_variants" style="display:{if $variants}inline-block{else}none{/if}">
            <label class="fc_label_200" for="fc_default_template_variant" title="{translate('Choose a template variant here. Available variants are defined in the template\'s info.php. (For example, a variant containing a slider for homepage / showcase pages and a variant without for normal pages.)')}">{translate('Variant')}:</label>
            <select name="default_template_variant" id="fc_default_template_variant">
				{foreach $variants variant}
				<option value="{$variant}"{if $variant == $values.default_template_variant} selected="selected"{/if}>{$variant}</option>
				{/foreach}
			</select>
            </div>

			<hr />

			<label class="fc_label_200" for="fc_website_header" title="{translate('The template may use this as a global header.')}">{translate('Website header')}:</label>
			<textarea name="website_header" id="fc_website_header" cols="80" rows="6"  class="fc_input_300">{$values.website_header}</textarea>
			<div class="clear_sp"></div>

			<label class="fc_label_200" for="fc_website_footer" title="{translate('The template may use this as a global footer.')}">{translate('Website footer')}:</label>
			<textarea name="website_footer" id="fc_website_footer" cols="80" rows="6"  class="fc_input_300">{$values.website_footer}</textarea>
			<div class="clear_sp"></div>

<script charset="windows-1250" type="text/javascript">
	$('select[name=default_template]').change( function()
	{
		//$(this).closest('form').removeClass('ajaxForm').unbind();
        var dates	= {
			'_cat_ajax': 1,
            'template':  $('#fc_default_template').val()
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
                    $("#fc_default_template_variant").empty();
                    if( $(data.variants).size() > 0 )
                    {
    					$.each(data.variants, function(index, value)
    					{
                            $("<option/>").val(value).text(value).appendTo("#fc_default_template_variant");
					    });
                        $('#div_template_variants').show();
                    }
                    else {
                        $('#div_template_variants').hide();
                    }
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
	});
</script>
