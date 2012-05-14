<?php
header('Content-Type: text/html; charset=UTF-8');
//*****************************************************************//
// Pixie: The Small, Simple, Site Maker.                           //
// ----------------------------------------------------------------//
// Licence: GNU General Public License v3                          //
// Title:   GeSHi CKEditor plugin dialog.                          //
//*****************************************************************//
/* GeSHi output parser script */
/* Original author : Nigel McNie */
/* Modified by T White */
/* Modified for LEPTON by B. Martinovic (WebBird) */

/* Check for GeSHi */
if (is_readable('../geshi.php')) {
    $path = '../';
} elseif (is_readable('geshi.php')) {
    $path = '../';
} else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>GeSHi</title>
<style type="text/css">
body{font-family:'Lucida Grande',Verdana,Arial,Sans-Serif;font-size:11pt;line-height:14pt;padding-left:1%;padding-right:1%;}
#center{text-align:center;}
#right{text-align:right;}
</style>
</head>
<body>
<p id="center">To activate this plugin you must do the following first :</p>
<p>
<ol>
<li>Download GeSHi from <a href="http://sourceforge.net/projects/geshi/files/" target="_blank">here</a> (Version 1.0.8.6 or higher recommended)</li>
<li>Extract the downloaded archive</li>
<li>Copy the folder geshi/geshi/ into Pixie's lib directory admin/lib/</li>
<li>Copy the file geshi/geshi.php into Pixie's lib directory admin/lib/</li>
<li>Close this dialogue by clicking cancel</li>
<li>Finally, click the "Post syntax highlighted code" Icon again to re-launch this dialogue.</li>
</ol>
</p>
<p id="right">Enjoy!<p>
</body>
</html>

    <?php die();
}

if ( ! defined('WB_URL') ) {
    include dirname(__FILE__).'/../../../../../../config.php';
}
if ( ! defined('LANGUAGE') ) {
    define( 'LANGUAGE', 'EN' );
}
global $GeSHiLang;
$langfile = dirname(__FILE__).'/../lang/'.LANGUAGE.'.php';
if ( ! file_exists($langfile) ) { $langfile = dirname(__FILE__).'/../lang/EN.php'; }
include $langfile;

require_once $path . 'geshi.php';
$fill_source = FALSE;

if (isset($_POST['submit'])) {
    if (get_magic_quotes_gpc()) { $_POST['source'] = stripslashes($_POST['source']); }

    if (!strlen(trim($_POST['source']))) {
        $_POST['language'] = preg_replace('#[^a-zA-Z0-9\-_]#', '', $_POST['language']);
        $_POST['source']   = implode('', @file($path . 'geshi/' . $_POST['language'] . '.php'));
        $_POST['language'] = 'php';
    } else { $fill_source  = TRUE; }

    /* Set GeSHi options */

    $geshi = new GeSHi($_POST['source'], $_POST['language']);
    if (($_POST['container-type']) == 1) {
    	$geshi->set_header_type(GESHI_HEADER_DIV);
    }
    if (($_POST['container-type']) == 2) {
    	$geshi->set_header_type(GESHI_HEADER_PRE_VALID);
    }
    if (($_POST['line_numbers']) == 2) {
    	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
    	$geshi->set_line_style('background: #F0F5FE;', 'background: #FFFFFF;', TRUE);
    }
    if (($_POST['line_numbers']) == 3) {
    	$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    }
    if (($_POST['style_type']) == 2) {
    	$geshi->enable_classes();
    }
    if (isset($_POST['submit'])) {
    	$geshi_out = $geshi->parse_code();
    }
} else { /* Don't pre-select any language */ $_POST['language'] = NULL; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>GeSHi</title>
<?php if (isset($_POST['submit'])) { ?>
<script type="text/javascript">    //<![CDATA[

var CKEDITOR = window.parent.CKEDITOR;

var okListener = function(ev) {
   this._.editor.insertHtml('<?php echo preg_replace("/\r?\n/", "\\n", addslashes($geshi_out)); ?>');
   CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
};

CKEDITOR.dialog.getCurrent().on("ok", okListener);

                                //]]></script>
<?php } ?>

<style type="text/css">
body{font-family:Arial,'Lucida Grande',Verdana,Sans-Serif;font-size:12px;padding-left:1%;padding-right:1%;color:#676666;}
h3{color:#676666;font-weight:400;max-width:59%;margin:0;}
#footer{text-align:center;font-size:80%;color:#BBBABA;clear:both;padding-top:16px;}
a{color:#0497D3;text-decoration:none;}
a:hover{color:#191919;}
textarea{border:1px solid #BBBABA;font-size:90%;color:#676666;width:53%;margin-bottom:6px;}
p{font-size:90%;}
#clear{text-align:right;width:100px;float:left;padding-right:1%;}
#submit{width:100px;float:left;}
#style-radio{float:right;padding-right:2%;margin:0;}
#language{text-align:left;width:31%;color:#676666;background-color:#FFF;border:1px solid #BBBABA;height:24px;margin-bottom:12px;}
#source{height:300px;width:400px;}
.ui_button{font-size:12px;text-align:center;width:86px;border:1px solid #BBBABA;color:#4F4E4E;background-color:#FFF;padding:4px 12px;}
.ui_button:hover{background-color:#DBDADA;background-image:none;}
<?php if ( (isset($_POST['submit'])) && ($_POST['style_type'] === 2) ) {
      /* Output the stylesheet. Note it doesn't output the <style> tag */
      echo $geshi->get_stylesheet(TRUE); } ?>
</style>
</head>
<body>
<?php if (isset($_POST['submit'])) { print $geshi_out; }
      if (!(isset($_POST['submit']))) { ?>
<form accept-charset="UTF-8" action="<?php echo basename($_SERVER['SCRIPT_NAME']); ?>" method="post">
<h3 id="lang"><?php echo $GeSHiLang['Choose a language']; ?></h3>
<div style="float:right;">
	<div id="style-radio">
		<input type="radio" name="style_type" value="1" checked="checked" /> <?php echo $GeSHiLang['Use inline syles']; ?> (<a href="http://qbnz.com/highlighter/geshi-doc.html#using-css-classes" target="_blank">?</a>)<br />
		<input type="radio" name="style_type" value="2" /> <?php echo $GeSHiLang['Use your own css']; ?><br /><br />
		<input type="radio" name="line_numbers" value="1" checked="checked" /> <?php echo $GeSHiLang['No Line numbers']; ?> (<a href="http://qbnz.com/highlighter/geshi-doc.html#enabling-line-numbers" target="_blank">?</a>)<br />
		<input type="radio" name="line_numbers" value="2" /> <?php echo $GeSHiLang['Use Line numbers']; ?><br /><br />
		<input type="radio" name="container-type" value="1" checked="checked" /> <?php echo $GeSHiLang['Use a div container']; ?> (<a href="http://qbnz.com/highlighter/geshi-doc.html#the-code-container" target="_blank">?</a>)<br />
		<input type="radio" name="container-type" value="2"> <?php echo $GeSHiLang['Use a (Valid) pre container']; ?>
	</div>
</div>
<select name="language" id="language">
<?php
$languages = array();
$selected  = array();
if (!($dir = @opendir(dirname(__FILE__) . '/' . $path .'/geshi'))) {
	echo '<option>No languages available!</option>';
}
else {
	while ($file = readdir($dir)) {
	    if ( $file[0] == '.' || strpos($file, '.', 1) === FALSE) { continue; }
	    $lang = substr($file, 0,  strpos($file, '.'));
	    $languages[] = $lang;
	    $selected[$lang] = NULL;
	}
	closedir($dir);
}
sort($languages);
if ( isset( $_POST['language'] ) ) {
	$selected[$_POST['language']] = true;
}
else {
    $selected['html4strict'] = true;
}
//echo "<option selected=\"selected\" value=\"javascript\">javascript</option>";
foreach ($languages as $lang) {
    echo '<option value="' . $lang . '"' . ( ( $selected[$lang] ) ? ' selected="selected"' : NULL ) . '>' . $lang . "</option>\n";
}

?>
</select>
</p>
<h3 id="src"><?php echo $GeSHiLang['Code to highlight']; ?></h3>
<textarea name="source" id="source"><?php echo $fill_source ? htmlspecialchars($_POST['source']) : '' ?></textarea>

<span id="submit"><input class="ui_button" type="submit" name="submit" value="Highlight" /></span>
<span id="clear"><input class="ui_button" type="submit" name="clear" onclick="document.getElementById('source').value='';document.getElementById('language').value='';return false" value="clear" /></span>
</form>

<div id="footer"><p><a href="http://qbnz.com/highlighter/" target="_blank">GeSHi</a> &copy; Nigel McNie, 2004, released under the <a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU GPL</a></p></div>
<?php /* End isset post submit */ } ?>
</body>
</html>

