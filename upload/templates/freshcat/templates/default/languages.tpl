<div class="langmenu">
	{% for item in items %}
	<a href="{{item.href}}" title="{{item.menu_title}}"><img src="{{CAT_URL}}/languages/{{item.language|lower)}}.png" alt="{{item.language}}"></a>
	{% endfor %}
</div>