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
	<script type="text/javascript" src="../jquery.effects.scale.min.js"></script>
	<?php echo _loadFile( '../presets/scale.preset' ); ?>
</head>
<body style="font-size:62.5%;">

Click on the div below to see the scale effect.<br /><br />

<div class="scale" style="border: 1px solid #000; padding: 15px; background-color: #fff;">
		Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
		ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
		amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
		odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
</div><br /><br />

To use this effect, add<br /><br />

<tt>class="scale"</tt><br /><br />

to a &lt;div&gt;.<br /><br />

See <a href="http://docs.jquery.com/UI/Effects/Scale">http://docs.jquery.com/UI/Effects/Scale</a> for more details.

</body>
</html>
