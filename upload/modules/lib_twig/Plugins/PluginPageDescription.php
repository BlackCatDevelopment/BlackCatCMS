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

class PluginPageDescription
{
  public static function page_description(bool $mode = true)
  {
    global $page_id;
    $temp = CAT_Page::getInstance($page_id)->getDescription();
    if (true === $mode) {
      return $temp;
    } else {
      echo $temp;
      return true;
    }
  }
}
