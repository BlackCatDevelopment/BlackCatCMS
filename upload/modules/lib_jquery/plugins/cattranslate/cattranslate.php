<?php

/**
 *   @author          Black Cat Development
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_jquery
 *
 */

@include dirname(__FILE__).'/../../../../config.php';

$lang = CAT_Helper_I18n::getInstance(LANGUAGE);
$val  = CAT_Helper_Validate::getInstance();
$attr = $val->get('_REQUEST','attr');
$msg  = $val->get('_REQUEST','msg');

if( version_compare(phpversion(),'5.4','<') )
{
    $msg  = empty($msg)  ? '' : htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    $attr = empty($attr) ? '' : htmlspecialchars($attr, ENT_QUOTES, 'UTF-8');
}
else
{
    $msg  = empty($msg)  ? '' : htmlspecialchars($msg, ENT_XHTML, 'UTF-8');
    $attr = empty($attr) ? '' : htmlspecialchars($attr, ENT_XHTML, 'UTF-8');
}

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
	echo '<data>'.$lang->translate($msg,$attr).'</data>';
}
else {
	echo '<data>'.$msg.'</data>';
}