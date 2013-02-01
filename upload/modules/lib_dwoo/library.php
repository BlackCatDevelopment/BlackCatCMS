<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          Dwoo Template Engine
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	if (defined('LEPTON_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

include(CAT_PATH.'/modules/'.basename(dirname(__FILE__)).'/dwoo/dwooAutoload.php');

$cache_path = CAT_PATH.'/temp/cache';
if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
$compiled_path = CAT_PATH.'/temp/compiled';
if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);

global $parser,
       $HEADING,
       $TEXT,
       $MESSAGE,
       $MENU;

if (!is_object($parser))
{
  include CAT_PATH.'/modules/'.basename(dirname(__FILE__)).'/dwoo/LepDwoo.php';
  $parser = new LepDwoo($compiled_path, $cache_path);
  foreach ( array( 'TEXT', 'HEADING', 'MESSAGE', 'MENU' ) as $global ) {
      if ( isset(${$global}) && is_array(${$global}) ) {
          $parser->setGlobals( $global, ${$global} );
      }
  }
  $parser->setGlobals(
      array(
              'CAT_ADMIN_URL' => CAT_ADMIN_URL,
              'CAT_URL' => CAT_URL,
              'CAT_PATH' => CAT_PATH,
              'LEPTON_URL' => CAT_URL,
        	  'CAT_PATH' => CAT_PATH,
              'CAT_PATH' => CAT_PATH,
              'CAT_URL' => CAT_URL,
        	  'CAT_THEME_URL' => CAT_THEME_URL,
        	  'URL_HELP' => 'http://blackcat-cms.org/'
      )
  );
  // initialize template search path
  $parser->setPath(CAT_THEME_PATH . '/templates');
  $parser->setFallbackPath(CAT_THEME_PATH . '/templates');
}

?>