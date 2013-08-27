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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         menu_link
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


global $MOD_MENU_LINK, $TEXT;

$backend        = CAT_Backend::getInstance('pages','pages_modify');

// get target page_id
$table          = CAT_TABLE_PREFIX.'mod_menu_link';
$sql_result     = $backend->db()->query("SELECT * FROM $table WHERE section_id = '$section_id'");
$sql_row        = $sql_result->fetchRow(MYSQL_ASSOC);
$target_page_id = $sql_row['target_page_id'];
$r_type         = $sql_row['redirect_type'];
$extern         = $sql_row['extern'];
$anchor         = $sql_row['anchor'];
$sel            = ' selected="selected"';

// Get list of targets (id=... or <a name ...>) from pages in $links
$targets  = array();
$table_mw = CAT_TABLE_PREFIX."mod_wysiwyg";
$table_s  = CAT_TABLE_PREFIX."sections";

$pages    = CAT_Helper_Page::getPages();
$links    = array();
foreach($pages as $page)
    $links[$page['page_id']] = $page['link'];

foreach($links as $pid=>$l) {
	if($query_section = $backend->db()->query("SELECT section_id, module FROM $table_s WHERE page_id = '$pid' ORDER BY position")) {
		while($section = $query_section->fetchRow(MYSQL_ASSOC)) {
			// get section-anchor
			if(defined('SEC_ANCHOR') && SEC_ANCHOR!='') {
				$targets[$pid][] = SEC_ANCHOR.$section['section_id'];
			} else {
				$targets[$pid] = array();
			}
			if($section['module'] == 'wysiwyg') {
				if($query_page = $backend->db()->query("SELECT content FROM $table_mw WHERE section_id = '{$section['section_id']}' LIMIT 1")) {
					$page = $query_page->fetchRow(MYSQL_ASSOC);
					if(preg_match_all('/<(?:a[^>]+name|[^>]+id)\s*=\s*"([^"]+)"/i',$page['content'], $match)) {
						foreach($match[1] AS $t) {
							$targets[$pid][$t] = $t;
						}
					}
				}
			}
		}
	}
}

// get target-window for actual page
$table      = CAT_TABLE_PREFIX."pages";
$query_page = $backend->db()->query("SELECT target FROM $table WHERE page_id = '$page_id'");
$page       = $query_page->fetchRow(MYSQL_ASSOC);
$target     = $page['target'];

// script for target-select-box
?>
<script language="JavaScript" type="text/javascript">
/*<![CDATA[*/
	function populate() {
		o=document.getElementById('menu_link');
		d=document.getElementById('page_target');
		e=document.getElementById('extern');
		if(!d){return;}
		var mitems=new Array();
		mitems['0']=[' ','0'];
		mitems['-1']=[' ','0'];
		<?php
		foreach($links AS $pid=>$link) {
			$str="mitems['$pid']=[";
			$str.="' ',";
			$str.="'0',";
			if(is_array($targets) && is_array($targets[$pid])) {
				foreach($targets[$pid] AS $value) {
					$str.="'#$value',";
					$str.="'$value',";
				}
				$str=rtrim($str, ',');
			}
			$str.="];\n";
			echo $str;
		}
		?>
		d.options.length=0;
		cur=mitems[o.options[o.selectedIndex].value];
		if(!cur){return;}
		d.options.length=cur.length/2;
		j=0;
		for(var i=0;i<cur.length;i=i+2)
		{
			d.options[j].text=cur[i];
			d.options[j++].value=cur[i+1];
		}

    if(o.value=='-1') {
			e.disabled = false;
		} else {
			e.disabled = true;
		}
	}

/*]]>*/
</script>
<form name="menulink" action="<?php echo CAT_URL ?>/modules/menu_link/save.php" method="post" class="ajaxForm">
<input type="hidden" name="page_id" value="<?php echo $page_id ?>" />
<input type="hidden" name="section_id" value="<?php echo $section_id ?>" />
<table>
<tr>
	<td>
		<?php echo $TEXT['LINK'].':' ?>
	</td>
	<td>
		<select name="menu_link" id="menu_link" onchange="populate()" style="width:250px;" >
			<option value="0"<?php echo $target_page_id=='0'?$sel:''?>><?php echo $TEXT['PLEASE_SELECT']; ?></option>
			<option value="-1"<?php echo $target_page_id=='-1'?$sel:''?>><?php echo $MOD_MENU_LINK['EXTERNAL_LINK']; ?></option>
			<?php foreach($links AS $pid=>$link) {
				if ($pid == $page_id)  // Display current page with selection disabled
					echo "<option value=\"$pid\" disabled=\"disabled\">$link *</option>\n";
				else
					echo "<option value=\"$pid\" ".($target_page_id==$pid?$sel:'').">$link</option>\n";
			} ?>
		</select>
		&nbsp;
		<input type="text" name="extern" id="extern" value="<?php echo $extern; ?>" style="width:250px;" <?php if($target_page_id!='-1') echo 'disabled="disabled"'; ?> />
	</td>
</tr>
<tr>
	<td>
		<?php echo $TEXT['ANCHOR'].':' ?>
	</td>
	<td>
		<select name="page_target" id="page_target" onfocus="populate()" style="width:250px;" >
			<option value="<?php echo $anchor ?>" selected="selected"><?php echo $anchor=='0'?' ':'#'.$anchor ?></option>
		</select>
	</td>
</tr>
<tr>
	<td>
		<?php echo $TEXT['TARGET'].':' ?>
	</td>
	<td>
		<select name="target" style="width:250px;" >
			<option value="_blank"<?php if($target=='_blank') echo ' selected="selected"'; ?>><?php echo $TEXT['NEW_WINDOW'] ?></option>
			<option value="_self"<?php if($target=='_self') echo ' selected="selected"'; ?>><?php echo $TEXT['SAME_WINDOW'] ?></option>
			<option value="_top"<?php if($target=='_top') echo ' selected="selected"'; ?>><?php echo $TEXT['TOP_FRAME'] ?></option>
		</select><br />
		<?php echo $MOD_MENU_LINK['XHTML_EXPLANATION'] ?>
	</td>
</tr>
<tr>
	<td style="vertical-align: top;">
		<?php echo $MOD_MENU_LINK['R_TYPE'].':' ?>
	</td>
	<td>
		<select name="r_type" style="width:250px;" >
			<option value="301"<?php if($r_type=='301') echo ' selected="selected"'; ?>>301 (Moved permanently)</option>
			<option value="302"<?php if($r_type=='302') echo ' selected="selected"'; ?>>302 (Moved temporarily)</option>
		</select><br /><br />
		<?php echo $MOD_MENU_LINK['REDIRECT_EXPLANATION']; ?>
	</td>
</tr>
</table>

<br />

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left">
		<input type="submit" value="<?php echo $TEXT['SAVE'] ?>" style="width: 100px; margin-top: 5px;" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $TEXT['CANCEL'] ?>" onclick="javascript: window.location = 'index.php';" style="width: 100px; margin-top: 5px;" />
	</td>
</tr>
</table>

</form>
