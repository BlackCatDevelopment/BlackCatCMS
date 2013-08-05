/*

Search Term Highlighter
Author: Dave Lemen
Version: 0.6

This script highlights searched words on your web page. Since it's implemented
in JavaScript that runs in the browser, you don't need to do anything special
on the server. It works in static HTML files as well as dynamically generated
pages.

Here's how to implement it:
1. (Optional) Change the items in the Configuration Section that you want to change.
   There are examples to show you how.
2. Put this file on your web server.
3. Add a script tag to your web page, like this:
	<script type="text/javascript" src="/highlighter.js"></script>
4. Add an "onload" attribute to the body tag, like this:
	<body onload="highlighter.highlight()">
*/

window.highlighter = new SearchTermHighlighter();

// ---------------------------------------------------------------------------
// Configuration Section:
// ---------------------------------------------------------------------------

// Uncomment the following line to hide the legend.
// highlighter.legend = false;

// List the URL parameters that contain search terms.
// Defaults: q, as_q, as_epq, as_oq, query, search
// Example:
// highlighter.parameters = 'q, as_q, as_epq, as_oq, query, search';

// Use addStyle(color, background, fontWeight) to change the highlighting style.
// This is optional. A set of 10 default styles will be used automatically.
// Example:
// highlighter.addStyle('#000', '#FF4', 'bold'); // bold, black text on yellow background.

// ---------------------------------------------------------------------------

highlighter.init();

function SearchTermHighlighter()
{
	var ok = ((document.createElement) ? true : false);
	var isInitialized = false;
	var searchTerms = new Array();
	var foundCount = 0;
	var styles = new Array();
	var usingDefaultStyles = true;
	var DEBUG = false;

	this.parameters = 'q as_q as_epq as_oq query search';
	this.legend = true;
	this.addStyle = addStyle;
	this.init = init;
	this.loadSearchTerms = loadSearchTerms;
	this.highlight = highlight;
	this.highlightTerm = highlightTerm;
	this.writeLegend = writeLegend;
	this.hideLegend = hideLegend;
	this.unhighlight = unhighlight;

	if (!ok) return;
	loadDefaults();

	function loadDefaults()
	{
		var liteBG = '#FF6 #BFF #9F9 #F99 #F6F'.split(/ /);
		var darkBG = '#800 #0A0 #860 #049 #909'.split(/ /);
		for (var i = 0; i < liteBG.length; i++){styles.push(new HighlightStyle('black', liteBG[i], 'bold'));}
		for (i = 0; i < darkBG.length; i++){styles.push(new HighlightStyle('white', darkBG[i], 'bold'));}
	}

	function addStyle(color, background, fontWeight)
	{
		if (!ok) return;
		if (usingDefaultStyles) { styles = new Array(); usingDefaultStyles = false; }
		styles.push(new HighlightStyle(color, background, fontWeight));
	}

	function init()
	{
		if (!ok) return;
		this.loadSearchTerms();
		var style = 0;
		document.write('<style type="text/css">\n');
		for (i = 0; i < searchTerms.length; i++)
		{
			document.write('.' + searchTerms[i].cssClass + '{' + styles[style] + '}\n');
			style++;
			if (style >= styles.length) style = 0;
		}
		if (this.legend) document.write('#sth_legend{border:1px solid #CCC;background:white;margin:0px;padding:5px;'
			+ 'font-family:verdana,helvetica,arial,sans-serif;font-size:x-small;}\n');
		document.write('</style>\n');
		isInitialized = true;
	}

	function loadSearchTerms()
	{
		var a = new Array();
		var params = getParamValues(document.referrer, this.parameters);
		var terms;
		var index = 0;
		for (i = 0; i < params.length; i++)
		{
			terms = parseTerms(params[i]);
			for (j = 0; j < terms.length; j++)
			{
				if (terms[j] != '')
				{
					a.push(new SearchTerm(index++, terms[j].toLowerCase()));
				}
			}
		}
		a.sort(function(t1, t2){return ((t1.term == t2.term) ? 0 : ((t1.term < t2.term) ? -1 : 1));});
		var prev = new SearchTerm(0, '');
		for (i = a.length - 1; i >= 0; i--)
		{
			if (a[i].term != prev.term)
			{
				searchTerms.push(a[i]);
				prev = a[i];
			}
		}
		searchTerms.sort(function(t1, t2){return t1.index - t2.index;});
		debugAlert('Search Terms:\n' + searchTerms.join('\n'));
	}

	function parseTerms (query)
	{
		var s = query + '';
		s = s.replace(/(^|\s)(site|related|link|info|cache):[^\s]*(\s|$)/ig, ' ');
		s = s.replace(/[^a-z0-9_-]/ig, ' '); // word chars only.
		s = s.replace(/(^|\s)-/g, ' '); // +required -excluded ~synonyms
		s = s.replace(/\b(and|not|or)\b/ig, ' ');
		s = s.replace(/\b[a-z0-9]\b/ig, ' '); // one char terms
		return s.split(/\s+/);
	}

	function getParamValues(url, parameters)
	{
		var params = new Array();
		var p = parameters.replace(/,/, ' ').split(/\s+/);
		if (url.indexOf('?') > 0)
		{
			var qs = url.substr(url.indexOf('?') + 1);
			var qsa = qs.split('&');
			for (i = 0; i < qsa.length; i++)
			{
				nameValue = qsa[i].split('=');
				if (nameValue.length != 2) continue;
				for (j = 0; j < p.length; j++)
				{
					if (nameValue[0] == p[j])
					{
						params.push(unescape(nameValue[1]).toLowerCase().replace(/\+/g, ' '));
					}
				}
			}
		}
		return params;
	}

	function highlight()
	{
		if (!ok) return;
		if (!isInitialized) this.init();
		searchTerms.sort(function(term1, term2){return(term2.term.length-term1.term.length)});
		for (var i = 0; i < searchTerms.length; i++)
		{
			this.highlightTerm(document.getElementsByTagName("body")[0], searchTerms[i]);
		}
		if (this.legend)
		{
			this.writeLegend();
		}
	}

	function highlightTerm(node, term)
	{
		// this little step is required to prevent IE from getting wrapped around the axle on
		// certain types of malformed HTML.
		if (node.nodeType == 1) // element
		{
			if (node.getAttribute("sth_x") == term.term) return;
			else node.setAttribute("sth_x", term.term);
		}

		if (node.hasChildNodes())
		{
			for (var i = 0; i < node.childNodes.length; i++)
			{
				this.highlightTerm(node.childNodes[i], term);
			}
		}

		if (node.nodeType == 3)
		{
			var p = node.parentNode;
			if (p.nodeName != 'TEXTAREA' && p.nodeName != 'SCRIPT' && p.className.substr(0, term.CSS_CLASS_PREFIX.length) != term.CSS_CLASS_PREFIX)
			{
				var result = term.pattern.exec(node.nodeValue);
				if (result != null)
				{
					term.found = true;
					foundCount++;
					var v = node.nodeValue;
					var lt = document.createTextNode(v.substr(0, result.index));
					var rt = document.createTextNode(v.substr(result.index + result[0].length));
					var span = document.createElement('SPAN');
					span.className = term.cssClass;
					span.appendChild(document.createTextNode(result[0]));
					p.insertBefore(lt, node);
					p.insertBefore(span, node);
					p.replaceChild(rt, node);
				}
			}
		}
	}

	function writeLegend()
	{
		if (foundCount > 0)
		{
			var body = document.getElementsByTagName("body")[0];
			var legend = body.insertBefore(document.createElement('DIV'), body.childNodes[0]);
			legend.id = 'sth_legend';
			var s = 'These search terms have been highlighted:';
			searchTerms.sort(function(t1, t2){return t1.index - t2.index;});
			for (var i = 0; i < searchTerms.length; i++)
			{
				if (searchTerms[i].found == true) s += ' <span class="' + searchTerms[i].cssClass + '">'
					+ searchTerms[i].term + '</span>';
			}
			s += ' | <a href="javascript:void(0);" onclick="highlighter.hideLegend()">hide this legend</a>';
			s += ' | <a href="javascript:void(0);" onclick="highlighter.unhighlight()">remove highlighting</a>';
			legend.innerHTML = s;
		}
	}

	function hideLegend()
	{
		var legend = document.getElementById('sth_legend');
		if (legend) legend.parentNode.removeChild(legend);
	}

	function unhighlight()
	{
		var prefix = searchTerms[0].CSS_CLASS_PREFIX;
		var spans = document.getElementsByTagName('SPAN');
		var elts = new Array();
		var parent, i, j;
		for (i = 0; i < spans.length; i++)
		{
			if (spans[i].className.substr(0, prefix.length) == prefix)
			{
				elts.push(spans[i]);
			}
		}
		for (i = 0; i < elts.length; i++)
		{
			parent = elts[i].parentNode;
			for (j = 0; j < elts[i].childNodes.length; j++)
			{
				parent.insertBefore(elts[i].childNodes[j], elts[i]);
			}
			parent.removeChild(elts[i]);
		}
		this.hideLegend();
	}

	function debugAlert(msg){if(DEBUG)alert(msg);}

	function SearchTerm(index, term)
	{
		this.CSS_CLASS_PREFIX = 'sth_';
		this.index = index;
		this.term = term.toLowerCase();
		this.cssClass = this.CSS_CLASS_PREFIX + this.term.replace(/[^a-z0-9_ ]/g, '').replace(/ /g, '_');
		this.pattern = new RegExp('\\b' + this.term + '\\b', 'i');
		this.found = false;
		this.toString = function(){return this.term};
	}

	function HighlightStyle(color, background, fontWeight)
	{
		this.color = color;
		this.background = background;
		this.fontWeight = fontWeight;
		this.toString = function(){return 'color:' + this.color + ';background:'
			+ this.background + ';font-weight:' + this.fontWeight + ';'};
	}
}

