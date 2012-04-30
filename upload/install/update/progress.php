<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @reformatted     2011-11-20
 * @version         $Id: update.php 1775 2012-02-20 09:22:46Z erpe $
 *
 */

// open progress file
if ( ! file_exists( dirname(__FILE__).'/progress.tmp') ) {
	echo "no progress file";
}
else {
	$step  = ( isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 0 );
	$file  = implode( "\n", file( dirname(__FILE__).'/progress.tmp' ) );
    $steps = unserialize( $file );
    end($steps);
    $last = key($steps);
    
	$msg  = $steps[$step]['msg'];

 	if ( $step == $last && $steps[$step]['done'] ) {
 	    echo '<div class="info success">', $msg , '</div>';
 	    echo '<script charset="windows-1250" type="text/javascript">jQuery("#progress").hide();</script>';
 	}
 	else {
		echo $msg, "<br />";
	}
}

?>