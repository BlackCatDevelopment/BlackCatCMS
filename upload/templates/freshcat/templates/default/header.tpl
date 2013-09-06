<!DOCTYPE html>
<html lang="{$META.LANGUAGE}">
<head>
	<title>{$META.WEBSITE_TITLE} &raquo; {translate('Administration')} - {$HEAD.SECTION_NAME}</title>

	<link rel="shortcut icon" href="{$CAT_THEME_URL}/images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="content-type" content="text/html; charset={$META.CHARSET}" />
	<meta name="description" content="{translate('Administration')}" />
	<meta name="keywords" content="{translate('Administration')}" />
	<meta name="author" content="Matthias Glienke, creativecat" />

	<script type="text/javascript">
		var CAT_URL                           = '{$CAT_URL}',
            DATE_FORMAT						  = '{$DATE_FORMAT}',
			TIME_FORMAT						  = '{$TIME_FORMAT}',
			SESSION							  =	'{$SESSION}';
		var CAT_TEXT						  = new Array();
		CAT_TEXT["SEARCH"]					  = '{translate('Search')}';
		CAT_TEXT["BACK"]					  = '{translate('Back')}';
		CAT_TEXT["YES"]						  = '{translate('Yes')}';
		CAT_TEXT["NO"]						  = '{translate('No')}';
		CAT_TEXT["DEFAULT_MESSAGE_TITLE"]	  = '{translate('Notification')}';
		CAT_TEXT["GROUPS_CONFIRM_DELETE"]	  = '{translate('Are you sure you want to delete the selected group (and any users that belong to it)?')}';
		CAT_TEXT["USERS_CONFIRM_DELETE"]	  = '{translate('Are you sure you want to delete the selected user?')}';
		CAT_TEXT["MANAGE_GROUPS"]			  = '{translate('Manage groups')}';
		CAT_TEXT["MANAGE_USERS"]			  = '{translate('Manage users')}';
		CAT_TEXT["MEDIA_CONFIRM_DELETE_FILE"] = '{translate('Are you sure you want to delete file {name}')}';
		CAT_TEXT["MEDIA_CONFIRM_DELETE_DIR"]  = '{translate('Are you sure you want to delete the directory {name}')}';
		CAT_TEXT["SECTION_CONFIRM_DELETE"]	  = '{translate('Are you sure you want to delete this section?')}';
		CAT_TEXT["SECTION_DELETE"]			  = '{translate('Delete section')}';
	</script>
	{get_page_headers( "backend" , true , "$section_name")}
</head>
<body class="fc_gradient1">

	<header id="fc_admin_header" class="fc_gradient1 fc_border">
		{*Here the selection of different sides will be added*}
		<span id="fc_side_choose"><a href="http://blackcat-cms.org/" title="Visit Black Cat CMS Homepage" target="_blank">Black Cat CMS</a></span>
		{*main navigation*}
		<nav>
			<ul id="fc_menu">
				{foreach $MAIN_MENU as menu}
				{if $menu.permission == true}
				<li>
					<a href="{$menu.link}" target="_self" class="icon-fc_{$menu.permission_title}{if $menu.current == true} fc_current{/if}">{$menu.title}</a>
				</li>
				{/if}
				{/foreach}
			</ul>
		</nav>

		{*user/displayname and link to userpreferences*}
		<div id="fc_account">
			<a href="{$PREFERENCES.link}" id="fc_user_preferences" class="icon-user fc_gradient1 fc_gradient_hover{if $PREFERENCES.current == true} fc_current{/if}" title="{$PREFERENCES.title}">
				<strong id="fc_display_name">
					{$USER.display_name}
					<span id="fc_username">{$USER.username}</span>
				</strong>
			</a>
			{*logout*}
			<a href="{$CAT_ADMIN_URL}/logout/" id="fc_logout" title=" {translate('Logout')}" class="icon-switch fc_gradient1 fc_gradient_hover"></a>
		</div>

	</header>

{*if user is allowed to see pages, sidebar will be shown - I will have to think about that, as the activity-bar is inside this div, so it is also needed for users, who can't see pages*}
{if $permission.pages}
<div id="fc_sidebar" class="fc_gradient3">
	<div id="fc_sidebar_header" class="fc_gradient1 fc_border">
		{translate('Pages')}
        <span class="icon-home fc_gradient1 fc_gradient_hover fc_side_home" title="{translate('Open frontend')}" style="right:24px;"></span>
        <span class="icon-plus fc_gradient1 fc_gradient_hover fc_side_add fc_page_tree_options_open" title="{translate('Add page')}"></span>
	</div>
	<div id="fc_sidebar_content">
		{include('backend_pageTree.tpl')}
	</div>
	<div id="fc_activity"></div>
</div>
	{if $permission.pages_add}
	{include('form_add_page.tpl')}
	{/if}
{/if}
<div id="fc_content_container"{if !$pages} class="fc_no_sidebar"{/if}>