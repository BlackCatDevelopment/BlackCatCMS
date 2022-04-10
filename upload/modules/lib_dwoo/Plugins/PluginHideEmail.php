<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2018 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Modules
   @package         dwoo

*/

/**
 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 *
 * @return string Trimmed string.
 */
function PluginHideEmail(Dwoo\Core $core, $email, $class = "")
{
  $character_set =
    "+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz";
  $key = str_shuffle($character_set);
  $cipher_text = "";
  $id = "e" . rand(1, 999999999);
  for ($i = 0; $i < strlen($email); $i += 1) {
    $cipher_text .= $key[strpos($character_set, $email[$i])];
  }
  $script =
    'var a="' .
    $key .
    '";var b=a.split("").sort().join("");var c="' .
    $cipher_text .
    '";var d="";';
  $script .= "for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));";
  $script .=
    'document.getElementById("' .
    $id .
    '").innerHTML="<a href=\\"mailto:"+d+"\\" class=\\"' .
    $class .
    '\\">"+d+"</a>"';
  $script =
    "eval(\"" . str_replace(["\\", '"'], ["\\\\", '\"'], $script) . "\")";
  $script =
    '<script type="text/javascript">/*<![CDATA[*/' .
    $script .
    "/*]]>*/</script>";
  return '<span id="' .
    $id .
    '">[javascript protected email address]</span>' .
    $script;
  // end hide_email
}
