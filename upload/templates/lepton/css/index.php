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
?>