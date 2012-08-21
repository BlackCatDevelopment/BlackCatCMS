<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id: index.php 1374 2011-11-15 11:22:48Z creativecat $
 *
 */

@include dirname(__FILE__).'/../../../../framework/LEPTON/Helper/I18n.php';
$lang = new LEPTON_Helper_I18n();
$attr = ( isset($_POST['attr']) ? $_POST['attr'] : NULL );

if ( is_object($lang) ) {
	echo '<data>'.$lang->translate( $_POST['msg'] ).'</data>';
}
else {
	echo "Error<br />";
}

?>