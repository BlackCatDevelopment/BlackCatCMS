<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON v2.0 Black Cat Edition Development
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 */

if ( !defined( 'LEPTON_PATH' ) &&  !defined( 'LEPTON_INSTALL' ) )
{
		// try to load config.php
	if ( strpos( __FILE__, '/framework/class.secure.php' ) !== false )
		{
		$config_path = str_replace( '/framework/class.secure.php', '', __FILE__ );
		}
		else
		{
		$config_path = str_replace( '\framework\class.secure.php', '', __FILE__ );
		}
	if ( !file_exists( $config_path . '/config.php' ) )
		{
		if ( file_exists( $config_path . '/install/index.php' ) )
			{
			header( "Location: ../install/index.php" );
				exit();
			}
			else
			{
				// Problem: no config.php nor installation files...
			exit( '<p><b>Sorry, but this installation seems to be damaged! Please contact your webmaster!</b></p>' );
			}
		}
		
	require_once( $config_path . '/config.php' );
    $admin_dir             = str_replace( LEPTON_PATH, '', ADMIN_PATH );
		
    //require_once( $config_path . '/framework/class.database.php' );

    $db                    = new database();
    $direct_access_allowed = array();

	// some core files must be allowed to load the config.php by themself!
    $q = $db->query('SELECT * FROM '.TABLE_PREFIX.'class_secure');
    if( $q->numRows()>0 )
    {
        while( false !== ( $row = $q->fetchRow(MYSQL_ASSOC) ) )
        {
            $direct_access_allowed[] = $row['filepath'];
        }
    }

		$allowed = false;
	foreach ( $direct_access_allowed as $allowed_file )
		{
		if ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $allowed_file ) !== false )
			{
				$allowed = true; 
				break;
			}
		}

	if ( !$allowed )
		{
		if ( ( ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $admin_dir . '/media/index.php' ) ) !== false ) || ( ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $admin_dir . '/preferences/index.php' ) ) !== false ) || ( ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $admin_dir . '/support/index.php' ) ) !== false ) )
			{
				// special: do absolute nothing!
			}
		elseif ( ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $admin_dir . '/index.php' ) !== false ) || ( strpos( $_SERVER[ 'SCRIPT_NAME' ], $admin_dir . '/interface/index.php' ) !== false ) )
			{
				// special: call start page of admins directory
			header( "Location: " . ADMIN_URL . '/start/index.php' );
				exit();
			}
		elseif ( strpos( $_SERVER[ 'SCRIPT_NAME' ], '/index.php' ) !== false )
			{
				// call the main page
			header( "Location: ../index.php" );
				exit();
			}
			else
			{
			if ( !headers_sent() )
				{
					// set header to 403
				header( $_SERVER[ 'SERVER_PROTOCOL' ] . " 403 Forbidden" );
				}
				// stop program execution
			exit( '<p><b>ACCESS DENIED!</b> - Invalid call of <i>' . $_SERVER[ 'SCRIPT_NAME' ] . '</i></p>' );
		}
	}
}

/**
 * strip droplets
 **/
if ( !function_exists( '__lep_sec_formdata' ) )
{
	function __lep_sec_formdata( &$arr )
	{
		foreach ( $arr as $key => $value )
		{
			if ( is_array( $value ) )
			{
				__lep_sec_formdata( $value );
			}
			else
			{
				// remove <script> tags
				$value       = str_replace( array(
					 '<script',
					'</script'
				), array(
					 '&lt;script',
					'&lt;/script'
				), $value );
				$value     = preg_replace( '#(\&lt;script.+?)>#i', '$1&gt;', $value );
				$value     = preg_replace( '#(\&lt;\/script)>#i', '$1&gt;', $value );
				//$arr[$key] = preg_replace( '#\[\[.+?\]\]#', '', __strip($value) );
				$arr[ $key ] = str_replace( array(
					 '[',
					']'
				), array(
					 '&#91;',
					'&#93;'
				), $value );
			}
		}
	}
}

// secure form input
if ( isset( $_SESSION ) && !defined( 'LEP_SEC_FORMDATA' ) && !isset( $_SESSION[ 'USER_ID' ] ) )
{
	if ( count( $_GET ) )
	{
		__lep_sec_formdata( $_GET );
	}
	if ( count( $_POST ) )
	{
		__lep_sec_formdata( $_POST );
	}
	if ( count( $_REQUEST ) )
	{
		__lep_sec_formdata( $_REQUEST );
	}
	define( 'LEP_SEC_FORMDATA', true );
}

?>