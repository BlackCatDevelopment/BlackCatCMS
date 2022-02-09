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

/*
    What to check:
    1. User is logged in
    2. User has page perms
    3. User has section perms
*/

function PluginUserCanEdit(Dwoo\Core $core)
{
    try {
        $page_id    = func_get_args()[1];
        $section_id = func_get_args()[2];
        if(!is_numeric($page_id) || !is_numeric($section_id)) return false;

        if(\CAT\Helper\Page::exists(intval($page_id)) && \CAT\Sections::exists(intval($section_id)))
        {
            $u = \CAT\User::getInstance();
            if($u->is_authenticated())
            {
                if($u->hasPagePerm($page_id,'pages_edit'))
                {
                    // get module for page and section
                    if($u->hasModulePerm(\CAT\Sections::getSection(intval($section_id))))
                    {
                        return true;
                    }
                }
            }
        }
    } catch( Exception $e ) {
    }
    return false;
}