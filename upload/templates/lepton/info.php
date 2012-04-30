<?php
  /**
   *  @template       Lepton-Start
   *  @version        see info.php of this template
   *  @author         cms-lab
   *  @copyright      2010-2011 CMS-LAB
   *  @license        http://creativecommons.org/licenses/by/3.0/
   *  @license terms  see info.php of this template
   *  @platform       see info.php of this template
   *  @requirements   PHP 5.2.x and higher
   */
  
  // include class.secure.php to protect this file and the whole CMS!
  if (defined('WB_PATH'))
  {
      include(WB_PATH . '/framework/class.secure.php');
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
  
  // OBLIGATORY VARIABLES
  $template_directory = 'lepton';
  $template_name = 'lepton-start';
  $template_function = 'template';
  $template_version = '1.02';
  $template_platform = '1.0';
  $template_author = 'CMS-LAB';
  $template_license = 'http://creativecommons.org/licenses/by/3.0/';
  $template_license_terms = 'you have to keep the frontend-backlink to cms-lab untouched';
  $template_description = 'This template is simply a start';
  $template_guid = '06d11a78-8554-4f77-8f10-4411c4169319';
  
  // OPTIONAL VARIABLES FOR ADDITIONAL MENUES AND BLOCKS
  $menu[1] = 'Main';
  $menu[2] = 'Foot';
  $menu[3] = 'Pseudomenu';
  $block[1] = 'Content';
  $block[2] = 'News';
?>