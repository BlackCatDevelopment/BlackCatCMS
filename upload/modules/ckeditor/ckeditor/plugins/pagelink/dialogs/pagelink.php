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

global $database;

$debug = true;

if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

// Include the config file
require_once('../../../../../../config.php');

$list = "[";
$page_titles = "var pagelink_titles = new Array(); ";

// Function to generate page list
function gen_page_list($parent) {
	global $database, $admin, $list, $page_titles;
	
	$get_pages = $database->query("SELECT `page_id`,`menu_title`,`level` FROM `".TABLE_PREFIX."pages` WHERE `parent`= '".$parent."' order by `position`");
	
	while( !false == ($page = $get_pages->fetchRow( MYSQL_ASSOC ) ) ) {
					
		$title = str_replace(
			array("\"", "'". "&quote;"),
			array("'", "\'", "\\'"),
			$page['menu_title']
		);
		
		// Add leading -'s so we can tell what level a page is at
		$leading_dashes = '';
		for($i = 0; $i < $page['level']; $i++) $leading_dashes .= '- ';
		
		$list .= "[ \"".$leading_dashes." ".$title."\",'[wblink".$page['page_id']."]'],";
		$page_titles .= " pagelink_titles['[wblink".$page['page_id']."]']=\"".$title."\";\n";
		
		gen_page_list($page['page_id']);
	}
}

$get_pages = $database->query("SELECT `page_id`,`menu_title`,`level` FROM `".TABLE_PREFIX."pages` WHERE `parent`= '0' order by `position`");

if($get_pages->numRows() > 0) {
	// Loop through pages
	while(!false == ($page = $get_pages->fetchRow( MYSQL_ASSOC ) ) ) {
				
		$title = str_replace(
			array("\"", "'". "&quote;"),
			array("'", "\'", "\\'"),
			$page['menu_title']
		);
		
		$list .= "[ \"".$title."\",'[wblink".$page['page_id']."]'],";
		$page_titles .= " pagelink_titles['[wblink".$page['page_id']."]']=\"".$title."\";\n";
		
		gen_page_list($page['page_id']);
	}
	$list = substr($list, 0, -1);
	
} else {
	/**
	 *	None found
	 *
	 */
	 $list .= "['non found', 'none']";
}

$list .= "]";
$page_titles .= "";

/**
 *	Bugfix for IE, as the IE hasn't found the list in the class-scope,
 *	so we've to try it to declare the list global here.
 *
 */
echo $page_titles;

?>
CKEDITOR.dialog.add( 'pagelinkDlg', function( editor ) {
    return { 
        title: editor.lang.pagelink.title,
        minWidth: 380,
        minHeight: 130,
        contents: [ 
            {
                id: 'tab1',
                label: 'Tab1',
                title: 'Tab1',
                elements : [{
                        id: 'pagelinks',
                        type: 'select',
                        label: editor.lang.pagelink.page,
                        labelLayout:'horizontal',
						widths:['20%','80%'],
						style: 'width: 150px; margin-left: 10px; margin-top:-3px;',
                        validate : function() {},
                        items: <?php echo $list; ?>
                    }, {
                    	id: 'pagelinkclass',
                    	type: 'text',
                    	label: editor.lang.pagelink.cssclass,
                    	labelLayout:'horizontal',
						widths:['20%','80%'],
						style: 'width: 200px; margin-left: 10px, padding-left: 30px;',
						validate: function() {}
                    }, {
                    	id: 'pagelinkusepagename',
                    	type: 'checkbox',
                    	label: editor.lang.pagelink.usepagetitle,
						labelLayout:'horizontal',
						widths:['20%','80%'],
						value: 1,
                    	validate: function() {}
                    
                    },{
						/**
						 *	Add "rel" pop-up-select to the ui
						 */
						id: 'cmbRel',
						type: 'select',
						label: editor.lang.pagelink.advrel,
						widths:['20%','80%'],
						style: 'width: 150px; margin-left: 10px; margin-top:-3px;',
						labelLayout:'horizontal',
						items: 
						[
							[ editor.lang.pagelink.notset,	0 ],
							[ "Fancybox",	"fancybox" ],
							[ "Lightbox",	"lightbox" ],
							[ "PrettyPhoto","prettyPhoto" ],
							[ "Alternate",	"alternate" ],
							[ "Copyright",	"copyright" ],
							[ "Designates",	"designates" ],
							[ "No follow",	"nofollow" ],
							[ "Stylesheet",	"stylesheet" ],
							[ "Thumbnail",	"thumbnail" ]
						],
						setup: function ( obj ) {
							if ( obj.adv['advRel'] ) this.setValue( obj.adv['advRel'] );
							// alert("call");
						},								
						commit: function ( list_ref ) {
							// list_ref['rel'] = this.getValue();
						}
					}
                    ] 
            }
            ],
         onOk: function() {
         	
         	/**
         	 *	Getting the value of our page-select
         	 *
         	 */
         	var ref = this.getContentElement("tab1", "pagelinks").getInputElement();
         	var wb_link = ref.getValue();
         	
         	var class_name = this.getContentElement("tab1", "pagelinkclass").getInputElement().getValue();
			if (class_name.length > 0 ) class_name = " class='"+class_name+"' ";
			
			var re = this.getContentElement("tab1", "cmbRel").getInputElement().getValue();
			if (re != 0) {
				rel = " rel='"+re+"'";
			} else {
				rel = "";
			}
			
			/**
			 *	Should we use the selected page-title instead of "[[pagelinkxxx]]"?
			 *
			 */
			var link_text = wb_link;
			var use_title = false;
			
			var ref_c = this.getContentElement("tab1", "pagelinkusepagename").getInputElement();
			if (ref_c) {
				if (ref_c.$.checked == true) {
					/**
					 *	In the hidden ui-element "pagelinkhiddenhtml"
					 *	the array "pagelink_titles" is defined.
					 *
					 */
					if (pagelink_titles) link_text = pagelink_titles[wb_link];
					use_title = true;
				}
			}

         	if (wb_link != "none") {
				editor = this.getParentEditor();
				
				var selection = editor.getSelection();
				var	ranges = selection.getRanges();
				
				if ( ranges.length == 1 && ranges[0].collapsed ) {
					
					/**	***********************************************
					 *	Nothing selected ... so we simple append a link
					 *
					 */
					wb_link = "<a href='"+wb_link+"' "+ class_name + rel +">"+link_text+"</a>";

				} else {
					
					if (use_title == false) {
						var c_ref = ranges[0].cloneContents(); // **!!
						var text = c_ref.$.textContent;
						
						if (text == "") {
							/**
							 *	Image?
							 */
							var s = c_ref.$.firstChild['src'];
							if (s) {

								text = "<img src='" + s + "' ";
								
								var atts = new Array('style', 'class', 'id', 'alt', 'width', 'height', 'border', 'title', 'longdesc', 'usemap');
								var temp = "";
								
								for(var i=0; i< atts.length; i++) {
									temp = c_ref.$.firstChild.getAttribute( atts[i] );
									if (temp) text += " "+atts[i]+"='"+temp+"'";
								}
								
								text += " />";
							}
						}
						
						
						wb_link = "<a href='"+wb_link+"' "+ class_name + rel +">"+text+"</a>";
										
						selection.selectRanges( ranges );
						
					} else {
					
						wb_link = "<a href='"+wb_link+"' "+ class_name + rel +">"+link_text+"</a>";
					}
				}
				
				setTimeout( function() {
						editor.fire( 'paste', { 'html': wb_link } );
					}, 
					0
				);
			}
			
			return true;
         },
         resizable: 3
    };
} );