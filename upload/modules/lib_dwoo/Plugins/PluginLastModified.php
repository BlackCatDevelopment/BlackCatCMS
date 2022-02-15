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

require_once CAT_PATH.'/framework/functions.php';

function PluginLastModified(Dwoo\Core $core, $page_id=null)
{
    if(is_numeric($page_id)) {
		$sql = "SELECT `modified_when` FROM `:prefix:pages` WHERE `page_id`=:id";
		$t   = CAT_Helper_Page::getInstance()->db()->query(
                   $sql,
                   array('id'=>intval($page_id) )
               )->fetchColumn();
	}
	else {
		$sql = "SELECT `modified_when` FROM `:prefix:pages` WHERE `visibility`='public' OR `visibility`='hidden' ORDER BY `modified_when` DESC LIMIT 0,1";
		$t   = CAT_Helper_Page::getInstance()->db()->query($sql)->fetchColumn();
	}
	return CAT_Helper_DateTime::getDate($t);
}
