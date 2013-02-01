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
@include dirname(__FILE__).'/../../../../framework/LEPTON/Helper/I18n.php';
@include dirname(__FILE__).'/../../../../framework/LEPTON/Helper/Directory.php';
$lang = new CAT_Helper_I18n();
$attr = ( isset($_POST['attr']) ? $_POST['attr'] : NULL );

if ( isset($_POST['mod']) ) {
    $mod  = $_POST['mod'];
    $d    = new CAT_Helper_Directory();
    $path = $d->sanitizePath(dirname(__FILE__).'/../../../../modules/'.$mod);
    if( is_dir($path) ) {
        if( file_exists($path.'/languages/'.$lang->getLang().'.php') ) {
            $lang->addFile( $lang->getLang().'.php', $path.'/languages/' );
        }
    }
}

if ( is_object($lang) ) {
	echo '<data>'.$lang->translate( $_POST['msg'], $attr ).'</data>';
}
else {
	echo "Error<br />";
}

?>