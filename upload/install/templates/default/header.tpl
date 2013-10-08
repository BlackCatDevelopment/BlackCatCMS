<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>{translate('Black Cat CMS Step by Step Installation Wizard')}</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="{$installer_uri}/../modules/lib_jquery/plugins/cookie/jquery.cookie.js" type="text/javascript"></script>
	<script type="text/javascript" src="{$installer_uri}/../modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.js"></script>
	<link rel="stylesheet" type="text/css" href="{$installer_uri}/../modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.css" media="screen" />
 	<link rel="stylesheet" href="{$installer_uri}/templates/default/index.css" type="text/css" />
   </head>
  <body>
    <div id="radial">
      <img src="{$installer_uri}/templates/default/images/radial.png" alt="radial" />
	</div>
    <div id="container">
	  <div id="nav">
	    <ul id="steps">
	      {foreach $steps step}
	      <li id="{$step.id}" class="{if $step.current}current{/if}{if $step.done} done{/if}{if $step.failed} fail{/if}">
	      {if $step.done}<a href="{$installer_uri}/index.php?goto={$step.id}">{/if}
		  {translate('Step')} {$dwoo.foreach.default.index}<br />
		  <span>{$step.text}</span>
  		  {if $step.done}</a>{/if}
		  </li>
	      {/foreach}
	    </ul>
      </div>

		<form method="post" action="{$installer_uri}/index.php" id="cat_installer_form">
    	<input type="hidden" name="laststep" value="{$this_step}" />
      <div id="content">
		<div>
