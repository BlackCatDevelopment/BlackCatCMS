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
           "Close": function() { $(this).dialog("close"); }
        }
      }
    );
  });
  </script>
</head>
<body style="font-size:62.5%;">

<div id="dialog" title="Dialogtitel">
    Dialogtext<br /><br />
    Klicken Sie mit der linken Maustaste auf den Titel und ziehen Sie mit gehaltener
    Maustaste, um mich zu verschieben.<br />
    Sie können meine Größe verändern, indem Sie mit der linken Maustaste auf
    die untere rechte Ecke klicken und ziehen.<br />
    Sie können mich schließen, indem Sie auf das X oben rechts klicken.<br /><br />
</div>

</body>
</html>
