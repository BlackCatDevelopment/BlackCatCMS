//:Shows Search Box in the frontend
//:Usage: [[LEPTON_SearchBox]]
 This Droplet will show a search box. You can use it within your template or at any WYSIWYG page section
if (SHOW_SEARCH) {
    global $wb;
    global $parser;
    
    // load the CSS file search.box.css
    $wb->get_helper('DropLEP')->register_css(PAGE_ID, 'LEPTON_SearchBox', '', 'search.box.css', '/modules/lib_search/templates/default/');
    // load the JS file search.box.js
    $wb->get_helper('DropLEP')->register_js(PAGE_ID, 'LEPTON_SearchBox', '', 'search.box.js', '/modules/lib_search/templates/default/');
    
    // add the actual needed language file (needed by the template parser)
    $wb->get_helper('I18n')->addFile(LANGUAGE.'.php', LEPTON_PATH.'/modules/lib_search/languages/');
    
    // set the template path and enable custom templates
    $parser->setPath(LEPTON_PATH.'/modules/lib_search/templates/custom');
    $parser->setFallbackPath(LEPTON_PATH.'/modules/lib_search/templates/default');
    
    // parse the search.box template
    return $parser->get('search.box.lte', array('action' => LEPTON_URL.'/search/index.php'));
}
else {
    // the LEPTON search function is not enabled!
    return false;
}