<?php
    include_once '../../../helper.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <link href="../../../frontend.css" rel="stylesheet" type="text/css"/>
  <link type="text/css" href="../../themes/base/jquery-ui.css" rel="stylesheet" />
 	<script type="text/javascript" src="../../../jquery-core/jquery-core.min.js"></script>
	<script type="text/javascript" src="../../external/jquery.bgiframe-2.1.2.js"></script>
	<script type="text/javascript" src="../jquery.ui.core.min.js"></script>
	<script type="text/javascript" src="../jquery.ui.widget.min.js"></script>
	<script type="text/javascript" src="../jquery.ui.button.min.js"></script>
  <?php echo _loadFile( '../presets/dialog.preset' ); ?>
  <script src="../jquery.ui.dialog.min.js" type="text/javascript"></script>
  <script>
  $(document).ready(function() {
    $("#dialog").dialog(
      {
        buttons: {
           "Close": function() { $j(this).dialog("close"); }
        }
      }
    );
  });
  </script>
</head>
<body style="font-size:62.5%;">

<div id="dialog" title="Dialog Title">
    Dialog text.<br /><br />
    You can drag me around by clicking left on the title.<br />
    You can resize me by clicking left on the bottom right corner.<br />
    You can close me by clicking on the X on the upper right corner.<br /><br />
</div>

</body>
</html>
