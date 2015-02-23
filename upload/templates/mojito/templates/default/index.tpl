{**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         mojito
 *
 *}
<!doctype html>
<html>
<head>
	<link rel="shortcut icon" href="{template_dir}/css/default/images/favicon.ico" type="image/x-icon">
	{get_page_headers}

	<!--[if lte IE 8]>
		<style type="text/css">
			#sidebar {
				float: left;
				width: 189px;
			}
		</style>
	<![endif]-->

</head>
<body>

	<header id="main_header">
			{if $SHOW_SEARCH}
			<div id="search_box" class="br_left">
				<span id="toggleSearch" class="icon-search br_left gradient_blue shadow dr_hover"> </span>
			    <form name="search" action="{$CAT_URL}/search/index.php" method="post" class="gradient_gray br_left shadow">
			    	<input type="hidden" name="page_id" value="{$PAGE_ID}">
			    	<input type="text" name="string" placeholder="{translate('Search ...')}" id="searchInput">
			    	<input type="submit" class="icon-search" value="{translate('Search...')}">
			    </form>
			</div>
			{/if}
		<a href="{cat_url}" id="logo">{page_title}</a>

		<nav id="main_nav">
			{show_menu(1, SM2_ROOT, SM2_START, SM2_ALL|SM2_XHTML_STRICT, '<li>[ac][menu_title]</a>', '</li>', '<ul>', '</ul>')}
		</nav>
	</header>
	<section id="content" class="gradient_gray br_all">
		{if check_block(2)}
		<header id="content_header" class="gradient_gray br_top">
			<div class="right">{language_menu()}</div>
			{page_header()}
			{page_content(2)}
		</header>
		{/if}

		<aside id="sidebar" class="{if !check_block(2)}br_left{else}br_bottomleft{/if}">
			{show_menu(1, SM2_ROOT, SM2_ALL, SM2_ALL|SM2_XHTML_STRICT, '<li class="sib_[sib] [class]">[ac][menu_title]</a>', '</li>', '<ul class="hauptnavigation menu-[level]">', '</ul>')}
            <div id="langmenu">{language_menu()}</div>
		</aside>

		<section id="content_main" class="{if !check_block(2)}br_right{else}br_bottomright{/if}">
			{if !check_block(2)}{page_header}{/if}
			{page_content(1)}
		</section>

		<div class="clear"></div>
	</section>


	<footer id="main_footer">
		<nav id="meta_nav">
			{show_menu(2, SM2_ROOT, SM2_ALL)}
		</nav>

		<section id="footer_content">
			{page_footer}
			<p>{translate('Letzte Aktualisierung')}: {last_modified}</p>
		</section>
		<div class="clear"></div>
	</footer>

	{get_page_footers}

</body>
</html>
