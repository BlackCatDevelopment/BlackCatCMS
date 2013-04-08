if ( typeof jQuery != 'undefined' ) {
	try {
		jQuery("a[rel=fancybox]").fancybox({'width':'80%','height':'80%'});
	}
	catch (x) {}
	
	// check / uncheck all checkboxes
	jQuery('[type="checkbox"]#checkall').click( function() {
    	jQuery("input[id^=markeddroplet_][type='checkbox']").attr('checked', jQuery(this).is(':checked'));
	});
}
