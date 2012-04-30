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
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

// TEMPLATE CODE STARTS BELOW
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php 
	echo defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8'; ?>" />
	<meta name="description" content="<?php page_description(); ?>" />
	<meta name="keywords" content="<?php page_keywords(); ?>" />
	<?php 
    get_page_headers();
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/css/template.css" media="screen,projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/css/print.css" media="print" />

	<title><?php page_title(); ?></title>
</head>
<body>
<div id="site">
	<div id="head">
	<a href="<?php echo WB_URL;?>/"><img class="head_img" src="<?php echo TEMPLATE_DIR;?>/img/1.jpg" width="900" height="180" alt="image" /></a>
	</div>
	<div id="headtitle"><?php page_header(); ?></div>

	<!-- Left Column -->
	<div id="side">	
		<div id="navi1">
		<!-- Navigation linke Seite (Hauptnavigation) -->
        	<?php
		show_menu2(1, SM2_ROOT, SM2_ROOT+2, SM2_TRIM|SM2_PRETTY|SM2_XHTML_STRICT) ; ?>
		</div>
		
<!-- OPTIONAL: display frontend search -->
	<div id="search">
	<?php 
	if (SHOW_SEARCH) { ?>
	<form action="<?php echo WB_URL; ?>/search/index.php" method="get">
		<p><input type="hidden" name="referrer" value="<?php
			echo defined('REFERRER_ID') ? REFERRER_ID : PAGE_ID; ?>" /></p>
		<fieldset>
			<legend><?php echo $TEXT['SEARCH']; ?>:</legend>
			<input type="text" name="string" class="search_string" />
			<input type="submit" name="wb_search" id="wb_search" value="<?php
			echo $TEXT['SEARCH']; ?>" />
		</fieldset>
	</form>
	<?php 
	} ?>
	</div>
<!-- END frontend search -->

<!-- OPTIONAL: display frontend login -->
<div id="login">
<?php
if (FRONTEND_LOGIN == 'enabled' && VISIBILITY != 'private' 
	&& $wb->get_session('USER_ID') == '') { ?>
	<!-- login form -->
	<form name="login" id="login1" action="<?php 
		echo LOGIN_URL; ?>" method="post">
		<fieldset>
			<legend><?php echo $TEXT['LOGIN']; ?>:</legend>
			<label for="username" accesskey="1">
			<?php echo $TEXT['USERNAME']; ?>:</label>
			<input type="text" name="username" id="username" 
				style="text-transform: lowercase;" /><br />
			<label for="password" accesskey="2"><?php echo $TEXT['PASSWORD']; ?>:</label>
			<input type="password" name="password" id="password" /><br />
			<input type="submit" name="wb_login" id="wb_login" value="<?php 
			echo $MENU['LOGIN']; ?>"/><br />
	
			<!-- forgotten details link -->
			<a href="<?php echo FORGOT_URL; ?>"><?php echo $TEXT['FORGOT_DETAILS']; ?></a>

			<!-- frontend signup -->
			<?php
if (is_numeric(FRONTEND_SIGNUP) && (FRONTEND_SIGNUP != 0 )) { ?>
			<a href="<?php echo SIGNUP_URL; ?>"><?php echo $TEXT['SIGNUP']; ?></a>
			<?php } ?>
		</fieldset>
	</form>
<?php 
} elseif (FRONTEND_LOGIN == 'enabled' && is_numeric($wb->get_session('USER_ID'))) { ?>
	<!-- logout form -->
	<form name="logout" id="logout1" action="<?php echo LOGOUT_URL; ?>" method="post">
		<fieldset>
			<legend><?php echo $TEXT['LOGGED_IN']; ?>:</legend>
			<p><?php echo $TEXT['WELCOME_BACK']; ?>, <?php echo $wb->get_display_name(); ?></p>
			<input type="submit" name="wb_login" id="wb_login" value="<?php 
			echo $MENU['LOGOUT']; ?>" />
			<!-- edit user preferences -->
			<p><a href="<?php echo PREFERENCES_URL; ?>"><?php echo $MENU['PREFERENCES']; ?></a></p>
		</fieldset>
	</form>
<?php 
} ?>
</div>
<!-- END frontend login -->
	<div id="frontedit">[[editthispage]]</div>
</div>    <!-- END left column -->   

	<!-- Content -->
	
	<div id="cont">
	<?php page_content(1); ?>	
	</div>
	<br style="clear: both;" />
	<div id="foot">
	<?php show_menu2(2, SM2_ROOT, SM2_ALL, SM2_TRIM|SM2_PRETTY|SM2_XHTML_STRICT);?>
	</div>


<!-- Block Bottom -->
	<div id="basic">
	<div id="links"><?php page_footer(); ?></div>
	<div id="design"><a href='http://cms-lab.com'>Design by CMS-LAB</a></div>
	</div>
</div>
</body>
</html>

