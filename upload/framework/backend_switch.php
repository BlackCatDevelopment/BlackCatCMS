<?php

	// -------------------------------------------------------------------------
	// experimental backend theme switch
	// -------------------------------------------------------------------------

global $old_settings, $settings, $js_back;

if ( strcasecmp( $old_settings['default_theme'], $settings['default_theme'] ) )
{
	global $template_engine;
	
	// load old template engine
	include CAT_PATH.'/templates/'.$old_settings['default_theme'].'/info.php';
	$old_engine      = isset($template_engine) ? $template_engine : NULL;
	$template_engine = NULL; // reset
	
	// load new template engine
	include CAT_PATH.'/templates/'.$settings['default_theme'].'/info.php';
	
	// new TE differs from old TE
    if ( strcasecmp( $old_engine, $template_engine ) )
	{
        // get config.php
        $config = file( CAT_PATH.'/config.php' );
        // define('LEPTON_BACKEND_PATH', LEPTON_BACKEND_FOLDER ); LEPTON_ADMINS_FOLDER
        foreach ( $config as $i => $line )
		{
		    if ( preg_match( '~define\(\'LEPTON_BACKEND_PATH\'.*$~i', $line ) )
		    {
		        $config[$i] = "define('LEPTON_BACKEND_PATH', "
							. ( ! strcasecmp( $template_engine, 'dwoo' )
							    ? 'LEPTON_BACKEND_FOLDER'
								: 'LEPTON_ADMINS_FOLDER'
							  )
							. " );\n";
		        break;
		    }
		}
		$fh = fopen( CAT_PATH.'/config.php', 'w' );
		if ( is_resource($fh) )
		{
		    fwrite($fh, implode('', $config));
		    fclose($fh);
		}
		// TE changed to dwoo
		if ( ! strcasecmp( $template_engine, 'dwoo' ) )
		{
		    $js_back = LEPTON_URL.'/backend/settings/index.php';
		}
		else {
		    $js_back = LEPTON_URL.'/admins/settings/index.php';
		}
    }
}

?>