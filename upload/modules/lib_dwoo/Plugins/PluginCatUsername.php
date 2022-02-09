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

function PluginCatUsername(Dwoo\Core $core, ?int $userID=null)
{
    if(empty($userID)) {
        return \CAT\Base::user()->get('display_name');
    } else {
        if(\CAT\Base::user()->isAuthenticated()) {
            $user = \CAT\Helper\Users::getDetails($userID);
            return $user['display_name'];
        }
    }
    return $userID;
}