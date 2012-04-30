tinyMCEPopup.requireLangPack('pagelink');
var pagelinkDialog = {
	init : function() {

	},

	insert : function() {
        /**
         *  Insert the contents from the input into the document
         *
         *  We've to handle four situations:
         *  - nothing is selected - and no pagetitle used: we are using the link itself as innerHTML.
         *  - nothing is selected - and the pagetitle should be used: we are using the page_title.
         *  - something is selected - and the pagetitle is unused: we are using the selected text.
         *  - at last: something is selected, but the pagetitle should be use instead: we overwrite the selected text with the page-title.
         *
         */
        
        tinyMCEPopup.restoreSelection();
        
        var link_html = "";
        var name = "";
        
        if (true == document.forms[0].wbUseTitle.checked) {
            name = document.forms[0].cmbLinks.options[ document.forms[0].cmbLinks.selectedIndex ].text;
        } else {
            var name = tinyMCEPopup.editor.selection.getContent() == "" 
                ? document.forms[0].cmbLinks.value
                : tinyMCEPopup.editor.selection.getContent()
                ;
        }
        
        link_html = "<a href='"+document.forms[0].cmbLinks.value+"'>"+name+"</a>";
        
        tinyMCEPopup.editor.execCommand(
            'mceInsertContent', 
            false,
            link_html
        );
		
        tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(pagelinkDialog.init, pagelinkDialog);
