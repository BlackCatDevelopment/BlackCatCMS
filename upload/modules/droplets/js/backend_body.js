if ( typeof jQuery != 'undefined' ) {
	// check / uncheck all checkboxes
	jQuery('[type="checkbox"]#checkall').click( function() {
    	jQuery("input[id^=markeddroplet_][type='checkbox']").attr('checked', jQuery(this).is(':checked'));
	});
    jQuery(document).ready(function($) {
    $("div.dialog").dialog({
        width: 960,
        height: $(window).height() - 100,
        hide: 'clip',
        show: 'blind',
        autoOpen: false,
        title: cattranslate('Droplets help')
    });
    $('a.readmedlg').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('href');
        var path = url.substr(0,url.lastIndexOf('/'));
        $('div.dialog').load(url, function() {
            $('div.dialog').find('img').each(function() {
                $(this).attr('src', $(this).attr('src').replace('../', path + "/../"));
            });
            $('div.dialog').dialog('open').css('overflow','auto');
        });
        return false;
    });
});
}
