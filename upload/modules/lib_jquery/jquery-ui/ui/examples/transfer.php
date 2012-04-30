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
	<script type="text/javascript" src="../jquery.effects.transfer.min.js"></script>
	<?php echo _loadFile( '../presets/transfer.preset' ); ?>
	<style type="text/css">
  .ui-effects-transfer { border: 2px solid black; background-color: #ccc; }
  </style>
</head>
<body style="font-size:62.5%;">

Click on the left (white) div to see the transfer effect.<br /><br />

<div class="transfer" style="border: 1px solid #000; padding: 15px; background-color: #fff; width: 150px; float: left;">
    <strong>DIV to transfer</strong><br /><br />
		Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
		ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
		amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
		odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
</div><br /><br />

<div class="transferto" style="border: 1px solid #000; padding: 15px; background-color: #CCCCFF; width: 100px; float: right;">
    <strong>DIV to transfer to</strong><br /><br />
		Lorem ipsum dolor sit amet consectetuer Ut sit malesuada mi Nunc.
    Urna tempor Vestibulum porttitor lobortis semper quis auctor sed Nulla fames.
    Pede Nulla Donec in pellentesque odio a lorem eget aliquam consequat.
    Morbi vel ut pharetra convallis Donec semper elit ligula Morbi eu.
    Sagittis vel tincidunt elit lorem sem Nam Maecenas id.
</div><br /><br style="clear: both;" />

To use this effect, add<br /><br />

<tt>class="transfer"</tt><br /><br />

to a &lt;div&gt; to be transfered and<br /><br />

<tt>class="transferto"</tt><br /><br />

to another one to transfer to. You will also need to add some CSS styling to
class<br /><br />

<tt>.ui-effects-transfer</tt><br /><br />

which is the element that creates the effect. In this example, the styling is:<br /><br />

<div style="text-align: left;">
&lt;style type="text/css"&gt;<br />
&nbsp;&nbsp;.ui-effects-transfer {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border: 2px solid black;<br />
&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ccc;<br />
&nbsp;&nbsp;}<br />
&lt;/style&gt;
</div>

See <a href="http://docs.jquery.com/UI/Effects/Transfer">http://docs.jquery.com/UI/Effects/Transfer</a> for more details.

</body>
</html>
