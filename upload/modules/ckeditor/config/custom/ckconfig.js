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
 
/*
* WARNING: Clear the cache of your browser cache after you modify this file!
* If you don't do this, you may notice that your browser is ignoring all your changes.
*
* --------------------------------------------------
*
* Note: Some CKEditor configs are set in _yourwb_/modules/ckeditor/include.php
* 
* Example: "$ckeditor->config['toolbar']" is PHP code in include.php. The very same here in the 
* wb_ckconfig.js would be: "config.toolbar" inside CKEDITOR.editorConfig = function( config ). 
* 
* Please read "readme-faq.txt" in the wb_config folder for more information about customizing.
* 
*/

CKEDITOR.editorConfig = function( config )
{
    // The standard color of CKEditor. Can be changed in any hexadecimal color you like. Use the     
    // UIColor Plugin in your CKEditor to pick the right color.
    config.uiColor                  = '#bcd5eb';
    
    // Both options are for XHTML 1.0 strict compatibility
    // config.indentClasses = [ 'indent1', 'indent2', 'indent3', 'indent4' ];
    // [ Left, Center, Right, Justified ] 
    // config.justifyClasses = [ 'left', 'center', 'right', 'justify' ];
    
    // Nops, set this to false as this default behavior prooved more usefull in testing.
    config.templates_replaceContent =   false;
    // Define all extra CKEditor plugins in _yourwb_/modules/ckeditor/ckeditor/plugins here
    config.extraPlugins             = 'dropleps,pagelink,GeSHi,autosave';
    config.autosaveTargetUrl = WB_URL + '/modules/ckeditor/ckeditor/plugins/autosave/autosave.php';
    
    // Different Toolbars. Remove, add or move 'SomeButton', with the quotes and following comma 
    config.toolbar_Full	= [['Source','-','Preview','Templates'],['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker','Scayt'],['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],['Maximize','ShowBlocks','-','About'],'/',['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['dropleps','pagelink','Link','Unlink','Anchor'],['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar'],'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor'],['GeSHi'],['Autosave']];
    config.toolbar_Smart = [['Source','Preview'],['Cut','Copy','Paste','PasteText','PasteFromWord'],['Image','Flash','Table','HorizontalRule'],['dropleps','pagelink','Link','Unlink','Anchor'],['Undo','Redo','-','SelectAll','RemoveFormat'],['Maximize','ShowBlocks','-','About'],'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor'],['Bold','Italic','Underline','Strike'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv']['Autosave']];
    config.toolbar_Simple = [['Bold','Italic','-','NumberedList','BulletedList','-','Image','-','dropleps','pagelink','Link','Unlink','-','Scayt','-','About']];
    
    // The default toolbar. Default: Full
    config.toolbar          = 'Full';
    
    // Explanation: _P: new <p> paragraphs are created; _BR: lines are broken with <br> elements;
    //              _DIV: new <div> blocks are created.
    // Sets the behavior for the ENTER key. Default is _P allowed tags: _P | _BR | _DIV
    config.enterMode        = CKEDITOR.ENTER_P; 
    // Sets the behavior for the Shift + ENTER keys. allowed tags: _P | _BR | _DIV
    config.shiftEnterMode   = CKEDITOR.ENTER_BR; 
    
    /* Allows to force CKEditor not to localize the editor to the user language. 
    * Default: Empty (''); Example: ('fr') for French. 
    * Note: Language configuration is based on the backend language of WebsiteBaker. 
    * It's defined in include.php
    * config.language         = ''; */
    // The language to be used if config.language is empty and it's not possible to localize the editor to the user language.
    config.defaultLanguage  = 'en';
    config.docType          = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    
    /* The skin to load. It may be the name of the skin folder inside the editor installation path,  
    * or the name and the path separated by a comma. 
    * Available skins: kama (default), office2003, office2007, v2 */
    config.skin             = 'kama';
    
    // The standard height and width of CKEditor in pixels.
    config.height           = '250';
    config.width            = '900';
    config.toolbarLocation  = 'top';
    
    // Define possibilities of automatic resizing in pixels. Set config.resize_enabled to false to 
    // deactivate resizing.
    config.resize_enabled   = true;
    config.resize_minWidth  = 500;
    config.resize_maxWidth  = 1500;
    config.resize_minHeight = 200;
    config.resize_maxHeight = 1200;
    
    // Nops, i really prefer having this on B and I button. Having Strong and EM  confuses most of my customers.
    // B and I are still valid whith XHTML strict so i use them for less code overhead (instead of <span style="....>)
    config.coreStyles_bold = { element : 'b', overrides : 'strong' };
    config.coreStyles_italic = { element : 'i', overrides : 'em' };
    
    // Nops, default was false, but that led do a lot of frustration for my customers as they tried to create 
    // some empty places inside of their pages. Emty paragraphs aren't a clean solution, but they are user friendly. 
    config.ignoreEmptyParagraph = true;
    
    /* Protect PHP code tags (<?...?>) so CKEditor will not break them when switching from Source to WYSIWYG.
    *  Uncommenting this line doesn't mean the user will not be able to type PHP code in the source.
    *  This kind of prevention must be done in the server side, so just leave this line as is. */ 
    config.protectedSource.push(/<\?[\s\S]*?\?>/g); // PHP Code
};

CKEDITOR.on( 'instanceReady', function( ev )
{
    var writer = ev.editor.dataProcessor.writer;
    // The character sequence to use for every indentation step.
    writer.indentationChars = '\t';
    // The way to close self closing tags, like <br />.
    writer.selfClosingEnd   = ' />';
    // The character sequence to be used for line breaks.
    writer.lineBreakChars   = '\n';
    // Setting rules for several HTML tags.
    
    var dtd = CKEDITOR.dtd;
    for (var e in CKEDITOR.tools.extend( {}, dtd.$block ))
    {
        writer.setRules( e,
        {
            // Indicates that this tag causes indentation on line breaks inside of it.
            indent : true,
            // Insert a line break before the <h1> tag.
            breakBeforeOpen : true,
            // Insert a line break after the <h1> tag.
            breakAfterOpen : false,
            // Insert a line break before the </h1> closing tag.
            breakBeforeClose : false,
            // Insert a line break after the </h1> closing tag.
            breakAfterClose : true
        });
    };
    writer.setRules( 'p',
    {
        // Indicates that this tag causes indentation on line breaks inside of it.
        indent : true,
        // Insert a line break before the <p> tag.
        breakBeforeOpen : true,
        // Insert a line break after the <p> tag.
        breakAfterOpen : false,
        // Insert a line break before the </p> closing tag.
        breakBeforeClose : false,
        // Insert a line break after the </p> closing tag.
        breakAfterClose : true
    });
    writer.setRules( 'br',
    {
        // Indicates that this tag causes indentation on line breaks inside of it.
        indent : false,
        // Insert a line break before the <br /> tag.
        breakBeforeOpen : false,
        // Insert a line break after the <br /> tag.
        breakAfterOpen : false
    });
});