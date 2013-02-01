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
  
  // TEMPLATE CODE STARTS BELOW
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php
  echo defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8';
?>" />
  <meta name="description" content="<?php
  page_description();
?>" />
  <meta name="keywords" content="<?php
  page_keywords();
?>" />
  <link rel="stylesheet" type="text/css" href="<?php
  echo TEMPLATE_DIR;
?>/template.css" media="screen,projection" />
  <title><?php
  page_title('', '[WEBSITE_TITLE]');
?></title>
</head>
<body>
  <?php
  // TEMPLATE CODE STARTS BELOW
  // output only the page content, nothing else
  page_content();
?>
  
</body>
</html>