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

require_once CAT_PATH . "/framework/functions.php";

function PluginMenutitle(Dwoo\Core $core, $page)
{
  global $page_id;
  // remove all but numbers
  preg_match("/(\d+)/", $page, $match);
  if (!count($match)) {
    if ($page_id > 0) {
      return CAT_Helper_Page::getPageSettings(
        $page_id,
        "internal",
        "menu_title"
      );
    } else {
      return "";
    }
  }
  return CAT_Helper_Page::properties($match[1], "menu_title");
}
