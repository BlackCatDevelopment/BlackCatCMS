/**
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
  *
 */

jQuery(document).ready(function(){

    // toggle list of not yet installed modules
    // current_button is the clicked button
    function toggle_uninstalled(current_button)
    {
        // first, hide all
        var items = $('#fc_list_overview').children('li.fc_not_installed');
        items.addClass('fc_no_search').hide();
        // if current button is the 'show not installed' button
        // and action is 'show'...
        if ( $('.icon-folder-add').hasClass('fc_active') )
	    {
            // show heading
            $('#fc_list_overview').children('li.fc_not_installed.fc_type_heading').show();
            // for active buttons...
            $('button.fc_active').each( function() {
                var current = $(this);
                // show templates
                if ( current.hasClass('icon-color-palette') ) {
                    var ch = $('#fc_list_overview').children('li.fc_type_templates.fc_not_installed');
                    ch.removeClass('fc_no_search').show();
                }
                // show modules
                if ( current.hasClass('icon-puzzle') ) {
                    var ch = $('#fc_list_overview').children('li.fc_type_modules.fc_not_installed');
                    ch.removeClass('fc_no_search').show();
                }
                // show languages
                if ( current.hasClass('icon-comments') ) {
                    var ch = $('#fc_list_overview').children('li.fc_type_languages.fc_not_installed');
                    ch.removeClass('fc_no_search').show();
                }
            });
        }

        if ( current_button.hasClass('icon-folder-add') )
        {
            if ( current_button.hasClass('fc_active') )
            {
                // scroll
                $('#fc_list_overview').animate({
    				scrollTop: $('li.fc_type_heading').offset().top
    			}, 1000);
            }
            else
            {
                $('#fc_list_overview').animate({
    				scrollTop: 0
    			}, 1000);
            }
        }
    }

	$('#fc_list_overview li').fc_set_tab_list();
	$('#fc_mark_all').click( function(e)
	{
		e.preventDefault();
		var current		= $(this),
			input_div	= $('#fc_perm_groups');
		current.toggleClass( 'fc_marked' );
		if ( current.hasClass( 'fc_marked' ) )
		{
			input_div.children( 'input' ).prop( 'checked', true).change();
			current.children( '.fc_mark' ).addClass('hidden');
			current.children( '.fc_unmark' ).removeClass('hidden');
		}
		else
		{
			input_div.children( 'input' ).prop( 'checked', false).change();
			current.children( '.fc_unmark' ).addClass('hidden');
			current.children( '.fc_mark' ).removeClass('hidden');
		}
	});
	
    // hide all but modules
	$('#fc_list_overview').children('li').not('.fc_type_modules').addClass('fc_no_search').slideUp(0);
    $('.fc_not_installed').addClass('fc_no_search').slideUp(0);

	$('#fc_list_search_input').blur();
	$('#fc_lists_overview button').not('#fc_list_add').click( function()
	{
		var current_button	= $(this),
			modules			= $('#fc_list_overview').children('li.fc_type_modules').not('.fc_not_installed'),
			templates		= $('#fc_list_overview').children('li.fc_type_templates').not('.fc_not_installed'),
			languages		= $('#fc_list_overview').children('li.fc_type_languages').not('.fc_not_installed'),
            not_installed   = $('#fc_list_overview').children('li.fc_not_installed');

		current_button.toggleClass('fc_active');
		if ( current_button.hasClass('icon-puzzle') )
		{
			var slide	= modules;
		}
		else if ( current_button.hasClass('icon-color-palette') )
		{
			var slide	= templates;
		}
		else if ( current_button.hasClass('icon-comments') )
		{
			var slide	= languages;
		}
        else if ( current_button.hasClass('icon-folder-add') )
        {
            var slide   = not_installed;
        }

		if ( current_button.hasClass('fc_active') )
		{
			slide.removeClass('fc_no_search').stop().slideDown(300);
		}
		else
		{
			slide.addClass('fc_no_search').stop().slideUp(300);
		}

// for not installed addons, get active buttons
        //if ( current_button.hasClass('icon-folder-add') )
        //{
            toggle_uninstalled(current_button);
        //}

	});
    $('.fc_module_item').not('.fc_type_heading').click( function(e) {
        var current	= $(this);
        var dates	= {
			'_cat_ajax': 1,
            'module': current.find('input[name="addon_directory"]').val(),
            'type': current.find('input[name="addon_type"]').val()
		};
		$.ajax(
		{
			type:		'POST',
			url:		CAT_ADMIN_URL + '/addons/ajax_get_details.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
            beforeSend:	function( data )
    		{
    			data.process	= set_activity();
    		},
			success:	function( data, textStatus, jqXHR )
			{
				if ( data.success === true )
				{
                    $('div#addon_details').html(data.content);
                    jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
    });

    $('#fc_add_new_module_button').click(
        function(e) {
            $('#fc_add_new_module').toggle('slow');
            e.preventDefault();
        }
    );
});