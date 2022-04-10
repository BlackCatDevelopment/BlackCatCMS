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

function PluginShowWysiwygEditor(
  Dwoo\Core $core,
  $name,
  $id,
  $content,
  $width = "100%",
  $height = "350px"
) {
  if (!function_exists("show_wysiwyg_editor")) {
    @require_once CAT_PATH . "/modules/" . WYSIWYG_EDITOR . "/include.php";
    $wysiwyg_editor_loaded = true;
  }
  ob_start();
  show_wysiwyg_editor($name, $id, $content, $width, $height);
  $content = ob_get_clean();
  echo $content;
}
