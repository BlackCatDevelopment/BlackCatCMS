<?php

/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Michael Tenschert, Dietrich Roland Pehlke
 *  @copyright      2010-2011 Michael Tenschert, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

// Include the config file
require_once('../../../../../../config.php');

/**
 *	JavaScript handels LF/LB in another way as PHP, even inside an array.
 *	So we're in the need of pre-parse the entries.
 *	
 */
if (!function_exists('wbdroplet_clean_str')) {
	function wbdroplet_clean_str( &$aStr) {
		$vars = array(
			'"' => "\\\"",
			'\'' => "",
			"\n" => "<br />",
			"\r" => ""
		);
		
		return str_replace( array_keys($vars), array_values($vars), $aStr);
	}
}

global $database;
$get_droplet = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_droplets` where `active`=1 ORDER BY `name`");

$list = "[";
$desc_list = "var info = new Array();";

if($get_droplet->numRows() > 0) {
	
	/**
	 *	Loop through ...
	 *
	 */
	while(false != ($droplet = $get_droplet->fetchRow( MYSQL_ASSOC ) ) ) {
		
		$title	= wbdroplet_clean_str( $droplet['name'] );
		$desc	= wbdroplet_clean_str( $droplet['description'] );
		$comm	= wbdroplet_clean_str( $droplet['comments'] );
		
		$list .= "\n[\"".$title."\", \"[[".$title."]]\"],";
		$desc_list .= "\ninfo[\"[[".$title."]]\"]=\"".trim($desc)."<br /><br />".trim($comm)."\";"; 
		
	}
	$list = substr( $list, 0, -1 );
}
$list .= "]";
$desc_list .= "\n";

?>
CKEDITOR.dialog.add( 'droplepsDlg', function( editor ) {
    return { 
        title: editor.lang.dropleps.name,
        minWidth: 280,
        minHeight: 120,
        contents: [ 
            {
                id: 'tab1',
                label: 'Tab1',		
                title: 'Tab1',
                elements : [{
                        id: 'dropleps',
                        type: 'select',
                        label: "Droplets",
						labelLayout:'horizontal',
						widths:['20%','80%'],
						style: 'width: 150px; margin-left: 10px; margin-top:-3px;',
                        validate : function() { },
						items: <?php echo $list; ?>,
						onMouseUp: function() { 
							/** 
							 *	code to display the description of the droplets ... 
							 *
							 */
							<?php echo $desc_list; ?>;
							var droplet_name = this.getValue();
							var ref = document.getElementById("droplet_info");
							ref.innerHTML = info[droplet_name];
						},
						onShow: function() { 
							this.onMouseUp();
						}
                    } , {
                    	id: 'droplet_info_box',
                    	type: 'html',
                    	label: 'Info',
                    	labelLayout:'horizontal',
                    	widths:['20%','80%'],
                    	style:	"display: inline; " +
                    			"float: left; " +
                    			"margin-left: 0; " +
                    			"padding-left: 10px; " +
                    			"width: 250px !important; " +
                    			"height: 100px;" +
                    			"overflow: auto;" +
                    			"white-space: normal !important;",
                    	html: "<div id='droplet_info'>Hello</div>"
                    } ] 
            }
            ],
         onOk: function() {
         	
         	/**
         	 *	Getting the value of our droplet-select
         	 *
         	 */
         	var droplet_name = this.getContentElement("tab1", "dropleps").getInputElement().getValue();
         	
         	editor = this.getParentEditor();
         	editor.fire('paste', { 'text' : droplet_name } );
         	
			return true;
         },
       
         resizable: 2
    };
} );