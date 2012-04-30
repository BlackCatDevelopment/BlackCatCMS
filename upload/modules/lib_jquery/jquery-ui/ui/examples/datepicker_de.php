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
  <?php echo _loadFile( '../presets/datepicker.preset' ); ?>
  <script src="../jquery.ui.datepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
	$(function() {
		$("#datepicker").datepicker();
	});
</script>
</head>
<body style="font-size:62.5%;">
  <div style="margin-top: 50px;">
    <p>Datum: <input type="text" id="datepicker"></p><br /><br />
    Klicken Sie in das Eingabefeld, um den Datepicker zu &ouml;ffnen.<br /><br />
  </div>
</body>
</html>
