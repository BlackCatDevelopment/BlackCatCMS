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

class PluginPageTitle
{
  public static function page_title(
    string $template = "[WEBSITE_TITLE][SPACER][PAGE_TITLE]",
    string $spacer = " - ",
    bool $return = true
  ) {
    $vars = ["[WEBSITE_TITLE]", "[PAGE_TITLE]", "[MENU_TITLE]", "[SPACER]"];
    $values = [
      WEBSITE_TITLE,
      CAT_Helper_Page::properties(null, "page_title"),
      MENU_TITLE,
      $spacer,
    ];
    $temp = str_ireplace($vars, $values, $template);
    if (true === $return) {
      return $temp;
    } else {
      echo $temp;
      return true;
    }
  }
}
