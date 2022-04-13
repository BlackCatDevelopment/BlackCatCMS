<!doctype html>
<html>
<head>
    {{get_page_headers()}}
	<!--[if lte IE 8]>
		<style type="text/css">
			#sidebar \{ float: left;width: 189px; }
		</style>
	<![endif]-->
</head>
<body>
	<header id="main_header">
		<a href="{{cat_url()}}" id="logo">{{page_title()}}</a>
	</header>

	<section id="content" class="gradient_gray br_all">
        <header id="content_header" class="gradient_gray br_top">
			{{page_header()}}
		</header>
        <section id="content_main" class="br_all">
			{{page_content(1)}}
		</section>
		<div class="clear"></div>
	</section>

	<footer id="main_footer">
		<section id="footer_content">
			{{page_footer()}}
		</section>
		<div class="clear"></div>
	</footer>

	{{get_page_footers()}}

</body>
</html>