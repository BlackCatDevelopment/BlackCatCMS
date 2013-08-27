<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          show_menu2
 * @author          Brofield,LEPTON Project
 * @copyright       2006-2010 Brofield
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org/sm2/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php


 
class SM2_ShowMenuFormatter
{
    var $output;
    var $itemTemplate;
    var $itemFooter;
    var $menuHeader;
    var $menuFooter;
    var $defaultClass;
    var $currentClass;
    
    function output($aString) {
        if ($this->flags & SM2_BUFFER) {
            $this->output .= $aString;
        }
        else {
            echo $aString;
        }
    }
    function initialize() { $this->output = ''; }
    function startList($aPage, $aUrl) { 
        echo $this->menuHeader;
    }
    function startItem($aPage, $aUrl, $aCurrSib, $aSibCount) { 
        // determine the class string to use
        $thisClass = $this->defaultClass;
        if ($aPage['page_id'] == PAGE_ID) {
            $thisClass = $this->currentClass;
        }
        
        // format and display this item
        $item = str_replace( 
                array(
                    '[a]','[/a]','[menu_title]','[page_title]','[url]',
                    '[target]','[class]'
                    ),
                array(
                    "<a href='$aUrl' target='".$aPage['target']."'>", '</a>',
                    $aPage['menu_title'], $aPage['page_title'], $aUrl, 
                    $aPage['target'], $thisClass
                    ),
                $this->itemTemplate);
        echo $item;
    }
    function finishItem() { 
        echo $this->itemFooter;
    }
    function finishList() { 
        echo $this->menuFooter;
    }
    function finalize() { }
    function getOutput() {
        return $this->output;
    }
}


?>