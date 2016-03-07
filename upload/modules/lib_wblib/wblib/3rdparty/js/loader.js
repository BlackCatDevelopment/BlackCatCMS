require.config({
    baseUrl: WBLIB_URL + '/3rdparty/js/',
    paths: {
        jquery:   'jquery/jquery.min',
        jqueryui: 'jqueryui/jquery-ui.min',
        colpick:  'colpick/colpick',
        egg:      'egg/egg',
        select2:  'select2/select2.min',
        validate: 'validate/jquery.validate.min'
    }
});



function loadCss(url) {
    var link  = document.createElement("link");
    link.type = "text/css";
    link.rel  = "stylesheet";
    link.href = url;
    document.getElementsByTagName("head")[0].appendChild(link);
}

//jQuery already loaded, just use that
if (typeof jQuery === 'function') {
    define('jquery', function() { return jQuery; });
}
if (typeof jQuery.ui === 'object') {
    define('jqueryui', function() { return jQuery.ui; });
}

require(['jquery'], function($) {
    require(['jqueryui'], function($) {
        // hide honeypot fields (should be hidden anyway, just to be sure)
        jQuery('div.fbform .fbhide').hide();
        // add star icon to required
        jQuery('.fbrequired').addClass('ui-icon ui-icon-star');
        // ----- load UI theme -----
        if(typeof wbforms_disable_ui == 'undefined' || !wbforms_disable_ui === true) {
            loadCss(wbforms_ui_css.replace(/\%s/,wbforms_ui_theme));
        }

        // ----- attach select2 -----
        if(jQuery('div.fbform select').not('.fbimageselect').not('.uidisabled').not('.fbleave').length) {
            require(['select2'], function($) {
                loadCss(WBLIB_URL+'/3rdparty/js/select2/select2.css');
                jQuery('div.fbform select').not('.fbimageselect').not('.uidisabled').not('.fbleave').select2();
            });
        }

        // ----- attach color picker -----
        if(jQuery('.fbcolorpicker').length) {
            jQuery('.fbcolorpicker').each( function() {
                var color_code = jQuery(this).val();
                jQuery(this).after('<span class="fbcolpick" style="border:1px solid #000;background-color:' + color_code + ';display:inline-block;width:25px;height:25px;margin-bottom:-7px;">');
            });
            require(['colpick'], function($) {
                loadCss(WBLIB_URL+'/3rdparty/js/colpick/colpick.css');
                jQuery('.fbcolorpicker,span.fbcolpick').colpick({
                    onShow:function(el) {
                        if(jQuery(this).is('span')) {
                            jQuery(this).colpickSetColor(jQuery(this).prevUntil('.fbcolorpicker').prev().val());
                        } else {
                            jQuery(this).colpickSetColor(jQuery(this).val());
                        }
                    },
                	onChange:function(hsb,hex,rgb,el,bySetColor) {
                		if(bySetColor) {
                            if(jQuery(el).is('span')) {
                                jQuery(el).css('background-color','#'+hex);
                            } else {
                                jQuery(el).nextUntil('span.fbcolpick').next().css('background-color','#'+hex);
                            }
                        }
                	},
                    onSubmit:function(hsb,hex,rgb,el,bySetColor) {
                        jQuery(el).colpickHide();
                        if(jQuery(el).is('span')) {
                            jQuery(el).prevUntil('.fbcolorpicker').prev().val('#'+hex);
                            jQuery(el).css('background-color','#'+hex);
                        } else {
                            jQuery(el).val('#'+hex);
                            jQuery(el).nextUntil('span.fbcolpick').next().css('background-color','#'+hex);
                        }
                    }
                }).keyup(function(){
                	jQuery(this).colpickSetColor(this.value);
                });
            });
        }

        // ----- attach buttons -----
        jQuery('.radiogroup').buttonset();
        jQuery('.checkboxgroup').buttonset();
        jQuery('.buttonset').buttonset();

        // ----- AJAX Buttons -----
        jQuery('button.fbajax').on('click',function(e) {
            e.preventDefault();
            var url    = jQuery(this).data('url');
            var field  = jQuery(this).data('field');
            var result = jQuery(this).data('result');
            var obj    = document.getElementById(field);
            var data   = {};
            if(field.length) {
                data = { data: jQuery(obj).val() };
            }
            jQuery.ajax({
                type:     'POST',
                url:      url,
                cache:    false,
                data:     data,
                success:  function( data, textStatus, jqXHR )
                {
                    var r_obj = document.getElementById(result);
                    jQuery(r_obj).text(data);
                    jQuery(r_obj).val(data);
                }
            });
        });

        // ----- add tooltips -----
        if(typeof wbforms_disable_tooltips == 'undefined' || !wbforms_disable_tooltips === true) {
            jQuery('form.ui-widget [title]').each( function() {
                if(!jQuery(this).next('span').hasClass('fbinfo') && !jQuery(this).find('span.fbinfo').length) // don't add twice
                {
                    if(jQuery(this).is('div')) {
                        jQuery(this).append(
                            '<span class="fbinfo ui-icon ui-icon-info" style="display:inline-block;vertical-align:top;width:20px;margin-left:5px;" title="' + jQuery(this).attr('title') + '">&nbsp;<\/span>'
                        );
                    }
                    else {
                        if(!jQuery(this).is('span') && !jQuery(this).is('button')) {
                            jQuery(this).after(
                                '<span class="fbinfo ui-icon ui-icon-info" style="display:inline-block;vertical-align:top;width:20px;margin-left:5px;" title="' + jQuery(this).attr('title') + '">&nbsp;<\/span>'
                            );
                        }
                    }
                }
            });
            jQuery('span.fbinfo').tooltip({
                position: {
                    my: 'left-70 bottom-20',
                    at: 'left+15 top',
                    using: function(position, feedback) {
                         jQuery(this).css(position);
                         jQuery('<div>')
                         .addClass('arrow')
                         .addClass(feedback.vertical)
                         .addClass(feedback.horizontal)
                         .appendTo(this);
                    }
                }
            });
            jQuery('span.fbrequired').tooltip({
                position: {
                    my: 'center bottom-20',
                    at: 'center top',
                    using: function(position, feedback) {
                         jQuery(this).css(position);
                         jQuery('<div>')
                         .addClass('arrow')
                         .addClass(feedback.vertical)
                         .addClass(feedback.horizontal)
                         .appendTo(this);
                    }
                }
            });
        }
    });

});
