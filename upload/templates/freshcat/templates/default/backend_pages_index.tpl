{% include 'backend_pages_header.tpl' %}
<div id="fc_content_header">
	{{translate('Pages')}}
	<div class="clear"></div>
</div>
<div id="fc_main_content">
	<ul id="fc_pages_list">
	{% for  page in pages %}
		{if page.close_parent %}
		{% for close in range(0,page.close_parent) %}
		{% if close > 0 %}
			</ul>
		</li>
		{% endif %}
		{% endfor %}
		{% endif %}
		<li id="fc_pageid_{{page.page_id}}" class="fc_pages_item{% if page.is_parent %} fc_pages_expandable{% endif %}">
			<table class="fc_page_list">
				<tr>
					<td class="fc_page_list_menu">
						{% if page.is_parent %}<span class="fc_toggle_pages">+</span>{% endif %}
						{% if page.editable %}<a href="{{CAT_ADMIN_URL}}/pages/modify.php?page_id={{page.page_id}}" class="fc_pages_type">{{page.menu_title}}</a>
						{% else %}
						{{page.menu_title}}
						{% endif %}
					</td>
					<td class="fc_page_list_title">
						{{page.page_title}}
					</td>
					<td class="fc_page_list_link">
						{{page.link}}
					</td>
					<td class="fc_page_list_page_id">
						{{page.page_id}}
					</td>
				</tr>
			</table>
			{% if page.is_parent %}
			<ul>
			{% else %}
		</li>
		{% endif %}
		{% set last_level = page.level %}
	{% endfor %}
	{% if last_level > 0 %}
		{% for close in range(0,last_level) %}
		{% if close > 0 %}
			</ul>
		</li>
		{% endif %}
		{% endfor %}
	{% endif %}
	</ul>
</div>