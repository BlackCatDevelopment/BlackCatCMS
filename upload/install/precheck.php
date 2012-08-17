<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project <info@lepton-cms.org>
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @category        LEPTON_Core
 * @package         Installation
 *
 */

$PRECHECK = array(
	'PHP_VERSION'  => array( 'VERSION' => '5.2.2', 'OPERATOR' => '>=' ),
	'PHP_SETTINGS' => array(
		'register_globals' => 0,
		'safe_mode'        => 0,
	)
);

// custom
// Check if AddDefaultCharset is set
$e_adc = false;
$sapi  = php_sapi_name();
if ( strpos( $sapi, 'apache' ) !== FALSE || strpos( $sapi, 'nsapi' ) !== FALSE ) {
	flush();
	$apache_rheaders = apache_response_headers();
	foreach ( $apache_rheaders AS $h ) {
		if ( strpos( $h, 'html; charset' ) !== FALSE && (!strpos(strtolower($h), 'utf-8')) ) {
			preg_match( '/charset\s*=\s*([a-zA-Z0-9- _]+)/', $h, $match );
			$apache_charset = $match[1];
			$e_adc          = $apache_charset;
		}
	}
}

$PRECHECK['CUSTOM_CHECKS']  = array(
    'AddDefaultCharset unset' => array( 'REQUIRED' => 0, 'ACTUAL' => ( !$e_adc ? 0 : $e_adc ), 'STATUS' => ( !$e_adc ? true : false ) )
);
