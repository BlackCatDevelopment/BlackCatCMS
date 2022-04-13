<p class="fc_gradient1" style="text-align:right;border-bottom:1px solid #000;">
    {% if catalog_version %}{{translate('Catalog version')}}: <span id="fc_addons_catalog_version">{{catalog_version}}</span>{% endif %}
    <button id="fc_addons_update_catalog">{{translate('Update')}}</button>
</p><div class="clear_sp"></div>

<table class="fc_table">
    <thead>
        <tr class="fc_gradient1">
            <th></th>
            <th>{{translate('Addon')}}</th>
            <th>{{translate('Avail. since')}}</th>
            <th>{{translate('Current version')}}</th>
            <th>{{translate('Installed version')}}</th>
            <th>{{translate('Action')}}</th>
        </tr>
    </thead>
    <tbody>
    {% for addon in addons %}
        <tr>
            <td>
                {% if addon.installed_data.update is same as (true) %}
                <span class="icon icon-alarm fc_addon_icon-alarm" title="{{translate('Update available!')}}"></span>
                {% endif %}
            </td>
            <td>
                <span class="fc_addon_name">{{addon.name}}</span><br />
                {% if addon.description.en %}{{addon.description.en.title|raw}}{% endif %}
            </td>
            <td>{% if addon.since %}{{addon.since}}{% endif %}</td>
            <td><span class="fc_addon_version{% if addon.installed_data.update is same as (true) %} fc_addon_update{% endif %}">{{addon.version}}</span></td>
            <td>
                {% if addon.is_installed %}
                    <span class="fc_addon_version{% if addon.installed_data.update is same as (true) %} fc_addon_old{% endif %}">{{addon.installed_data.version}}</span><br />
                    <span class="fc_addon_installdate">{{addon.installed_data.install_date}}</span><br />
                {% endif %}
            </td>
            <td class="fc_addon_buttons">
                <span class="fc_addon_directory" style="display:none;">{{addon.directory}}</span>
                {% if not addon.is_installed and permissions.MODULES_INSTALL is same as (true) %}
                <button class="fc_catalog_install fc_gradient1" style="min-width:85px;">{{translate('Install')}}</button>
                {% endif %}
                {% if addon.installed_data.update is same as (true) and permissions.MODULES_INSTALL is same as (true) %}
                <button class="fc_catalog_update fc_gradient_blue" style="min-width:85px;">{{translate('Update')}}</button>
                {% endif %}
                {% if addon.is_removable and addon.is_installed and permissions.MODULES_UNINSTALL %}
                <button class="fc_catalog_uninstall fc_gradient_red" style="min-width:85px;">{{translate('Uninstall')}}</button>
                {% endif %}
            </td>
        </tr>
    {% endfor %}
    </tbody>
</table>

<script charset=iso-8859-1 type="text/javascript">
    jQuery(document).ready(function($) {
        $('#fc_addons_update_catalog').unbind('click').bind('click',function() {
            var old_version = $('span#fc_addons_catalog_version').text();
            $.ajax({
                type:       'GET',
                url:        CAT_ADMIN_URL + '/addons/ajax_update_catalog.php',
                dataType:   'json',
                cache:      false,
                beforeSend: function( data )
                {
                    data.process    = set_activity('Updating catalog...');
                },
                success:    function( data, textStatus, jqXHR )
            {
                    if ( data.success === true )
                    {
                        $('div#addons_main_content').html(data.content);
                        $('span#fc_addons_catalog_version').text(data.catalog_version);
                        jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
                        if(old_version != data.catalog_version)
                    {
                            $.ajax(
                    		{
                    			type:		'POST',
                    			url:		CAT_ADMIN_URL + '/addons/ajax_get_catalog.php',
                                data:       { _cat_ajax: 1 },
                    			dataType:	'json',
                    			cache:		false,
                    			success:	function( data, textStatus, jqXHR )
                    			{
                    				if ( data.success === true )
                    				{
                                        $('div#addons_main_content').html(data.content);
                    				}
                    				else {
                    					return_error( jqXHR.process , data.message);
                    				}
                    			}
                    		});
                        }
                    }
                    else {
                        return_error( jqXHR.process , data.message);
                    }
                }
            });
        });
        $('.fc_catalog_update').unbind('click').bind('click',function() {
            var addon = $(this).parent().parent().find('.fc_addon_directory').text();
            $('<div></div>').appendTo('body')
                .html(cattranslate('Do you really want to upgrade this addon?'))
                .dialog({
                    modal: true
                    ,width: 600
                    ,title: $(this).parent().parent().find('.fc_addon_name').text()
                    ,buttons: [
                        {
                            text: cattranslate('Yes'),
                            click: function() {
                                $(this).dialog("close");
                                $.ajax({
                                    type:        'GET',
                                    url:        CAT_ADMIN_URL + '/addons/ajax_catalog_install.php',
                                    dataType:    'json',
                                    data:       { directory: addon, action: 'update' },
                                    cache:        false,
                                    beforeSend:    function( data )
                                    {
                                        data.process    = set_activity('Update...');
                                    },
                                    success:    function( data, textStatus, jqXHR )
                                    {
                                        if ( data.success === true )
                                        {
                                            //location.reload();
                                            jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
                                            $("#fc_addons_catalog").trigger('click');
                                        }
                                        else {
                                            return_error( jqXHR.process , data.message);
                                        }
                                    }
                                });
                            }
                        },{
                            text: cattranslate('No'),
                            click: function() {
                                $(this).dialog("close");
                            }
                        }
                    ]
                });
        });
        $('.fc_catalog_install').unbind('click').bind('click',function() {
            var addon = $(this).parent().parent().find('.fc_addon_directory').text();
            $('<div></div>').appendTo('body')
                .html(cattranslate('Do you really want to install this addon?'))
                .dialog({
                    modal: true
                    ,width: 600
                    ,title: $(this).parent().parent().find('.fc_addon_name').text()
                    ,buttons: [
                        {
                            text: cattranslate('Yes'),
                            click: function() {
                                $(this).dialog("close");
                                $.ajax({
                                    type:        'GET',
                                    url:        CAT_ADMIN_URL + '/addons/ajax_catalog_install.php',
                                    dataType:    'json',
                                    data:       { directory: addon, action: 'install' },
                                    cache:        false,
                                    beforeSend:    function( data )
                                    {
                                        data.process    = set_activity('Install...');
                                    },
                                    success:    function( data, textStatus, jqXHR )
                                    {
                                        if ( data.success === true )
                                        {
                                            //location.reload();
                                            jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
                                            $("#fc_addons_catalog").trigger('click');
                                        }
                                        else {
                                            return_error( jqXHR.process , data.message);
                                        }
                                    }
                                });
                            }
                        },{
                            text: cattranslate('No'),
                            click: function() {
                                $(this).dialog("close");
                            }
                        }
                    ]
                });
        });
        $('.fc_catalog_uninstall').unbind('click').bind('click',function() {
            var addon = $(this).parent().parent().find('.fc_addon_directory').text();
            $('<div></div>').appendTo('body')
                .html(cattranslate('Do you really want to uninstall this addon?'))
                .dialog({
                    modal: true
                    ,width: 600
                    ,title: $(this).parent().parent().find('.fc_addon_name').text()
                    ,buttons: [
                        {
                            text: cattranslate('Yes'),
                            click: function() {
                                $(this).dialog("close");
                                $.ajax({
                                    type:        'GET',
                                    url:        CAT_ADMIN_URL + '/addons/ajax_catalog_install.php',
                                    dataType:    'json',
                                    data:       { directory: addon, action: 'uninstall' },
                                    cache:        false,
                                    beforeSend:    function( data )
                                    {
                                        data.process    = set_activity('Uninstall...');
                                    },
                                    success:    function( data, textStatus, jqXHR )
                                    {
                                        if ( data.success === true )
                                        {
                                            //location.reload();
                                            jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
                                            $("#fc_addons_catalog").trigger('click');
                                        }
                                        else {
                                            return_error( jqXHR.process , data.message);
                                        }
                                    }
                                });
                            }
                        },{
                            text: cattranslate('No'),
                            click: function() {
                                $(this).dialog("close");
                            }
                        }
                    ]
                });
        });
    });
</script>