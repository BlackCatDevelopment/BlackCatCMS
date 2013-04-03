<?php

/**
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_jquery
 *
 */

@include dirname(__FILE__).'/../../../../config.php';

$lang = CAT_Helper_I18n::getInstance(LANGUAGE);
$val  = CAT_Helper_Validate::getInstance();
$attr = $val->get('_REQUEST','attr');

if( file_exists(CAT_PATH.'/languages/'.$lang->getLang().'.php') ) {
    $lang->addFile( $lang->getLang().'.php', CAT_PATH.'/languages/' );
}

$mod  = $val->get('_REQUEST','mod');
if ( $mod ) {
    $d    = CAT_Helper_Directory::getInstance();
    $path = $d->sanitizePath(dirname(__FILE__).'/../../../../modules/'.$mod);
    if( is_dir($path) ) {
        if( file_exists($path.'/languages/'.$lang->getLang().'.php') ) {
            $lang->addFile( $lang->getLang().'.php', $path.'/languages/' );
        }
    }
}

if ( is_object($lang) ) {
	echo '<data>'.$lang->translate( $val->get('_REQUEST','msg'), $attr ).'</data>';
}
else {
	echo '<error>Unable to create I18n instance!</error>';
}

?>