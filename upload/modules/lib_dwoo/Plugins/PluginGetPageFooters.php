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

function PluginGetPageFooters(Dwoo\Core $core,$page_id=false,$ignore_inc=false,$print_output=true)
{
    if(defined('CAT_FOOTERS_SENT')) return false;
    $output = \CAT\Helper\Assets::renderAssets('footer',$page_id,$ignore_inc,$print_output);
    define('CAT_FOOTERS_SENT',true);
	return $output;
}
