<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Installer
 *
 */

$PRECHECK = array(
	'PHP_VERSION'  => array( 'VERSION' => '5.3.1', 'OPERATOR' => '>=' ),
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
