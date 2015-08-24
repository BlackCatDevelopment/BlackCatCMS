<?php

/**
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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

// disable UI theme and tooltips, as the BC Backend already uses qtip2
\wblib\wbFormsJQuery::set('load_ui_theme',false);
\wblib\wbFormsJQuery::set('disable_tooltips',true);
// default class for labels
\wblib\wbFormsElementLabel::setClass('fc_label_250');
// prefix for generated IDs (you can explicitly set the id attribute in inc.forms.php)
\wblib\wbFormsElementLabel::setIDPrefix('fc_');
// override some other wbForms defaults
\wblib\wbFormsElementCheckbox::setClass('fc_checkbox_jq');
\wblib\wbFormsElementFieldset::setClass('');
\wblib\wbFormsElementInfo::setClass('');
\wblib\wbFormsElementButton::setClass('');
\wblib\wbFormsElementRadio::setTemplate('
    <div class="fc_settings_label"%title%>
       %is_required%<input%type%%name%%id%%class%%style%%value%%required%%checked%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />
       %label%
    </div>
'
);
// outer "wrapper" to checkbox groups; also used for radiogroups
\wblib\wbFormsElementCheckboxgroup::setTemplate(
    '
    <span class="fc_label_250">%label_span%</span>
    <div class="fc_settings_max left">
    %options%
    </div><div class="clear"></div>
    '
);

\wblib\wbFormsElementCheckbox::setTemplate('
<div class="fc_settings_max settings_label">
    <div class="fc_settings_label"%title%>
       %is_required%<input%type%%name%%id%%class%%style%%value%%required%%checked%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />
       %label%<br />
    </div>
</div><div class="clear_sp"></div>
'
);

/*
<div class="fc_settings_max settings_label">
    <input type="checkbox" class="fc_checkbox_jq" name="use_short_urls" id="fc_use_short_urls" value="true" {if $values.use_short_urls} checked="checked"{/if}/>
	<label for="fc_use_short_urls" title="{translate('This will allow to use SEO friendly URLs like http://www.yourdomain.com/path/to/page instead of http://www.yourdomain.com/page/path/to/page.php')}">{translate('Use short URLs (Apache webserver only, requires mod_rewrite!)')}</label><br />
</div>
<div class="clear_sp"></div>
*/