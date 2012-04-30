<?php
    include_once '../../../helper.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <link href="../../../frontend.css" rel="stylesheet" type="text/css"/>
  <link type="text/css" href="../../themes/base/jquery-ui.css" rel="stylesheet" />
 	<script type="text/javascript" src="../../../jquery-core/jquery-core.min.js"></script>
	<script type="text/javascript" src="../jquery.ui.core.min.js"></script>
	<script type="text/javascript" src="../jquery.effects.core.min.js"></script>
	<script type="text/javascript" src="../jquery.effects.highlight.min.js"></script>
  <script>
  jQuery(document).ready(function($) {
    $('#highlight').effect("highlight", {}, 3000);
  });
  </script>
</head>
<body>

<div id="highlight">
		<br /><br /><br />
		Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
		ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
		amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
		odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
		<br /><br /><br />
    To use this effect, add "Highlight" to your preset and <br /><br />
    <tt>id="highlight"</tt><br /><br />
    to a div or other block element to be highlighted.<br /><br />
    
    See <a href="http://docs.jquery.com/UI/Effects/Highlight">http://docs.jquery.com/UI/Effects/Highlight</a> for more details.
    
</div>

</body>
</html>
