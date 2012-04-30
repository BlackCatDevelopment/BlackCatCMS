tinyMCEPopup.requireLangPack('dropleps');

var droplepsDialog = {
	init : function() {


	},

	insert : function() {
		// Insert the contents from the input into the document
        var sDropletStr = "[[" + document.forms[0].cmbDroplets.value  + "]]";
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, sDropletStr);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(droplepsDialog.init, droplepsDialog);
