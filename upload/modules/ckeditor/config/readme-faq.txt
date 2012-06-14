/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Michael Tenschert, Dietrich Roland Pehlke
 *  @copyright      2010-2012 Michael Tenschert, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

################################################

--- 	LEPTON CKEditor module 		---
--- 	FAQ: How to use customization 		--- 

################################################
################################################


# Question: #

What files are there to customize CKEditor for LEPTON?

# Answer:   #

All default files in CKEditor modules are in the folder: _yourwbinstallation_/modules/ckeditor/wb_config/default

There are four different files. Please look inside each file with your FTP-Browser / AddonFileEditor / on your local computer:

+ ckconfig.js: 		Here are most of the configuration issues defined. 
						Toolbar-Configuration, behavior on Enter / Shift+Enter, default language and so on.

+ editor.css: 			The default look of the WYSIWYG textarea and the preview. 
						You can also put an editor.css in any frontend template, 
						it will be loaded automatically for each page instead of the default one.

+ editor.styles.js: 		The default styles you can choose from a dropdown in the CKEditor toolbar. 
						You can also put an editor.styles.js in any frontend template, 
						it will be loaded automatically for each page instead of the default one.

+ editor.templates.js: 		The default CKE templates you can choose of a button in CKEditor toolbar. 
						Please note: We recommend not to use CKE templates, because the LEPTON template 
						should define the different blocks and the template. 
						
Furthermore this files and some other configurations (we recommend you shouldn't change unless you really know what you are doing) are stored in:
_yourwbinstallation_/modules/ckeditor/include.php


################################################


# Question: #

Why are there different folders?

# Answer:   #

All four configuration files (ckconfig.js, editor.css, editor.styles.js, editor.templates.js) are both available in 
				_yourwbinstallation_/modules/ckeditor/config/default
				_yourwbinstallation_/modules/ckeditor/config/custom

The custom files all have the file prefix "custom.". 

They are not loaded and recognized by the CKEditor module unless you remove the "custom."!
As an example in config/custom: 
				Change the filename "custom.editor.styles.js" with your FTP-Browser / AddonFileEditor / ... into "editor.styles.js".

It is necessary to have this custom files - you should never change the files in default folder, as they are overwritten with any update of CKEditor module and / or LEPTON! If you have any problems when upgrading CKEditor module to another version and problems are occuring, just change the filename back to "custom." and then have a look at the files.


################################################


# Question: #

How is the workflow of reading CKEditor files?

# Answer:   #

Note: The workflow is defined inside _yourwbinstallation_/modules/ckeditor/include.php

1) is called as first if available, 2) is the next one, and so on.


+ ckconfig.js: 			1) _yourwbinstallation_/modules/ckeditor/wb_config/custom/ckconfig.js 	[If filename prefix "custom." removed]
				2) _yourwbinstallation_/modules/ckeditor/wb_config/defaultckconfig.js

+ editor.css: 			1) _yourwbinstallation_/templates/_yourdefaulttemplate_/editor.css
				2) _yourwbinstallation_/templates/_yourdefaulttemplate_/css/editor.css
				3) _yourwbinstallation_/modules/ckeditor/wb_config/custom/editor.css 		[If filename prefix "custom." removed]
				4) _yourwbinstallation_/modules/ckeditor/wb_config/default/editor.css

+ editor.styles.js: 		1) _yourwbinstallation_/templates/_yourdefaulttemplate_/editor.styles.js
				2) _yourwbinstallation_/templates/_yourdefaulttemplate_/js/editor.styles.js
				3) _yourwbinstallation_/modules/ckeditor/wb_config/custom/editor.styles.js 	[If filename prefix "custom." removed]
				4) _yourwbinstallation_/modules/ckeditor/wb_config/default/editor.styles.js

+ editor.templates.js: 		1) _yourwbinstallation_/modules/ckeditor/wb_config/custom/editor.templates.js 	[If filename prefix "custom." removed]
				2) _yourwbinstallation_/modules/ckeditor/wb_config/default/editor.templates.js


################################################


# Question: #

Which StylesSet name can I use in editor-styles.js?

# Answer:   #

You can only use StylesSet of the name "wb".
	CKEditor will recognize: 	"CKEDITOR.addStylesSet( 'wb',[ .... styles .... ]);"
	CKEditor will not recognize: 	"CKEDITOR.addStylesSet( 'mywb',[ .... styles .... ]);"


################################################


The LEPTON CKEditor module.


