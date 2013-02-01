<?php
  /**
   *  @template       blank
   *  @version        see info.php of this template
   *  @author         erpe
   *  @copyright      2010-2011 erpe
   *  @license        GNU General Public License
   *  @license terms  see info.php of this module
   *  @platform       see info.php of this module
   *  @requirements   PHP 5.2.x and higher
   */
  
  // include class.secure.php to protect this file and the whole CMS!
  if (defined('CAT_PATH'))
  {
      include(CAT_PATH . '/framework/class.secure.php');
  }
  elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php'))
  {
      include($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php');
  }
  else
  {
      $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));
      $dir = $_SERVER['DOCUMENT_ROOT'];
      $inc = false;
      foreach ($subs as $sub)
      {
          if (empty($sub))
              continue;
          $dir .= '/' . $sub;
          if (file_exists($dir . '/framework/class.secure.php'))
          {
              include($dir . '/framework/class.secure.php');
              $inc = true;
              break;
          }
      }
      if (!$inc)
          trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
  // end include class.secure.php
  
  // OBLIGATORY WEBSITE BAKER VARIABLES
  $template_directory = 'blank';
  $template_name = 'Blank';
  $template_function = 'template';
  $template_version = '1.1.0';
  $template_platform = 'Lepton 1.x';
  $template_author = 'erpe';
  $template_license = '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
  $template_license_terms = '-';
  $template_description = 'This template is for use on page where you do not want anything wrapping the content.';
  $template_guid = '8f6b513e-ee82-47d8-a0d2-415a06ec8f0a';
  
  // OPTIONAL VARIABLES FOR ADDITIONAL MENUES AND BLOCKS
  // $menu[1]                = '';
  // $menu[2]                = '';
  // $block[1]              = '';
  // $block[2]              = '';
?>