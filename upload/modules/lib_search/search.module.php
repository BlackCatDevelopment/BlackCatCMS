<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 * @link          http://blackcat-cms.org
 * @license       http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

global $lang;
$lang = CAT_Helper_I18n::getInstance();

/**
 * create the URL parameters for highlighting the search results within the
 * displayed page
 * 
 * @param string $search_match
 * @param array $search_url_array
 * @return string - 
 */
function make_url_searchstring($search_match, $search_url_array) {
	$link = "";
	if ($search_match != SEARCH_TYPE_EXACT) {
		$str = implode(" ", $search_url_array);
		$link = "?searchresult=1&amp;sstring=".urlencode($str);
	} else {
		$str = str_replace(' ', '_', $search_url_array[0]);
		$link = "?searchresult=2&amp;sstring=".urlencode($str);
	}
	return $link;
} // make_url_searchstring()

/**
 * Return the formatted date and time strings for the
 * "last modified by ... on ..." output in the search results
 *   
 * @param integer $page_modified_when - unix timestamp
 * @return array with date and time
 */
function get_page_modified($page_modified_when) {
	global $lang;
	if($page_modified_when > 0) {
		$date = gmdate(DATE_FORMAT, $page_modified_when);
		$time = gmdate(TIME_FORMAT, $page_modified_when);
	} else {
		$date = $lang->translate('- unknown date -');
		$time = $lang->translate('- unknown time -');
	}
	return array($date, $time);
} // get_page_modified()

/**
 * Return the formatted username and displayname for the
 * "last modified by ... on ..." output in the search results
 * 
 * @param integer $page_modified_by - unix timestamp
 * @param array $users - contains the user data if available
 * @return array with username and displayname
 */
function get_page_modified_by($page_modified_by, $users) {
    global $lang;
	if ($page_modified_by > 0) {
		$username = $users[$page_modified_by]['username'];
		$displayname = $users[$page_modified_by]['display_name'];
	} else {
		$username = "";
		$displayname = $lang->translate('- unknown user -');
	}
	return array($username, $displayname);
} // get_page_modified_by()

/**
 * Checks if really all search words matches
 * 
 * @param string $text
 * @param array $search_words
 * @return boolean
 */
function is_all_matched($text, $search_words) {
	$all_matched = true;
	foreach ($search_words AS $word) {
		if (!preg_match('/'.$word.'/i', $text)) {
			$all_matched = false;
			break;
		}
	}
	return $all_matched;
} // is_all_matched()

/**
 * Checks if any of the search words matches
 * 
 * @param string $text
 * @param array $search_words
 * @return boolean
 */
function is_any_matched($text, $search_words) {
	$word = '('.implode('|', $search_words).')';
	if (preg_match('/'.$word.'/i', $text)) {
		return true;
	}
	return false;
} // is_any_matched()

// collects the matches from text in excerpt_array
/**
 * Collects the matches from text in the excerpt_array
 * 
 * @param string $text
 * @param array $search_words
 * @param integer $max_excerpt_num
 * @return array $excerpt_array
 */
function get_excerpts($text, $search_words, $max_excerpt_num) {
	$excerpt_array = false;
	$word = '('.implode('|', $search_words).')';
	// start-sign: .!?; + INVERTED EXCLAMATION MARK - INVERTED QUESTION MARK - DOUBLE EXCLAMATION MARK - INTERROBANG - EXCLAMATION QUESTION MARK - QUESTION EXCLAMATION MARK - DOUBLE QUESTION MARK - HALFWIDTH IDEOGRAPHIC FULL STOP - IDEOGRAPHIC FULL STOP - IDEOGRAPHIC COMMA
	$p_start = ".!?;"."\xC2\xA1"."\xC2\xBF"."\xE2\x80\xBC"."\xE2\x80\xBD"."\xE2\x81\x89"."\xE2\x81\x88"."\xE2\x81\x87"."\xEF\xBD\xA1"."\xE3\x80\x82"."\xE3\x80\x81";
	// stop-sign: .!?; + DOUBLE EXCLAMATION MARK - INTERROBANG - EXCLAMATION QUESTION MARK - QUESTION EXCLAMATION MARK - DOUBLE QUESTION MARK - HALFWIDTH IDEOGRAPHIC FULL STOP - IDEOGRAPHIC FULL STOP - IDEOGRAPHIC COMMA
	$p_stop = ".!?;"."\xE2\x80\xBC"."\xE2\x80\xBD"."\xE2\x81\x89"."\xE2\x81\x88"."\xE2\x81\x87"."\xEF\xBD\xA1"."\xE3\x80\x82"."\xE3\x80\x81";
	
	// jump from match to match, get excerpt, stop if $max_excerpt_num is reached
	$match_array = $matches = array();
	// although preg_match with u-switch handles unicode correctly, the ...pos-variables will count bytes (not chars)
	$startpos = $wordpos = $endpos = 0;
	  
	while (preg_match("/$word/i", $text, $match_array, PREG_OFFSET_CAPTURE, $startpos)) {
		$wordpos = $match_array[0][1];
		$startpos = ($wordpos-200 < $endpos) ? $endpos : $wordpos-200;
		$endpos = $wordpos+200;
		// look for better start position
		if (preg_match_all("/[$p_start]/u", substr($text, $startpos, $wordpos-$startpos), $matches, PREG_OFFSET_CAPTURE)) {
		    // set startpos at last punctuation before word
			$startpos += $matches[0][count($matches[0])-1][1]; 
		}
		// look for better end position
		if (preg_match_all("/[$p_stop]/u", substr($text, $wordpos, $endpos-$wordpos), $matches, PREG_OFFSET_CAPTURE)) {
		    // set endpos at first punctuation after word
			$endpos = $wordpos+$matches[0][0][1]; 
		}
		$match = substr($text, $startpos+1, $endpos-$startpos);
		if (!preg_match('/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\./', $match)) {
		    // skip excerpts with email-addresses
			$excerpt_array[] = trim($match);
		}
		if (count($excerpt_array) >= $max_excerpt_num) {
			$excerpt_array = array_unique($excerpt_array);
			if (count($excerpt_array) >= $max_excerpt_num) break;
		}
		// restart at last endpos
		$startpos = $endpos;
	}
	return $excerpt_array;
} // get_excerpts()

// makes excerpt_array a string ready to print out
/**
 * Create a result string from excerpt array, ready for output
 * 
 * @param array $excerpt_array
 * @param array $search_words
 * @param integer $max_excerpt_num
 * @return string result
 */
function prepare_excerpts($excerpt_array, $search_words, $max_excerpt_num) {
	// excerpts: text before and after a single excerpt, html-tag for markup
	$EXCERPT_BEFORE =       '...&nbsp;';
	$EXCERPT_AFTER =        '&nbsp;...<br />';
	$EXCERPT_MARKUP_START = '<b>';
	$EXCERPT_MARKUP_END =   '</b>';
	// remove duplicate matches from $excerpt_array, if any.
	$excerpt_array = array_unique($excerpt_array);
	// use the first $max_excerpt_num excerpts only
	if(count($excerpt_array) > $max_excerpt_num) {
		$excerpt_array = array_slice($excerpt_array, 0, $max_excerpt_num);
	}
	// prepare search-string
	$string = "(".implode("|", $search_words).")";
	// we want markup on search-results page,
	// but we need some 'magic' to prevent <br />, <b>... from being highlighted
	$excerpt = '';
	foreach($excerpt_array as $str) {
		$excerpt .= '#,,#'.preg_replace("/($string)/i","#,,,,#$1#,,,,,#",$str).'#,,,#';
	}
	$excerpt = str_replace(array('&','<','>','"','\'',"\xC2\xA0"), 
	    array('&amp;','&lt;','&gt;','&quot;','&#039;',' '), $excerpt);
	$excerpt = str_replace(array('#,,,,#','#,,,,,#'), 
	    array($EXCERPT_MARKUP_START, $EXCERPT_MARKUP_END), $excerpt);
	$excerpt = str_replace(array('#,,#','#,,,#'), 
	    array($EXCERPT_BEFORE, $EXCERPT_AFTER), $excerpt);
	// prepare to write out
	if(DEFAULT_CHARSET != 'utf-8') {
		$excerpt = umlauts_to_entities($excerpt, 'UTF-8');
	}
	return $excerpt;
} // prepare_excerpts()

/**
 * Try to work out the possibel link anchor for the result URL.
 * Possible actions:
 *    1. e.g. $page_link_target=="&monthno=5&year=2007" - module-dependent 
 *       target. Do nothing.
 *    2. $page_link_target=="#!wb_section_..." - the user wants the 
 *       section-target, so do nothing.
 *    3. $page_link_target=="#wb_section_..." - try to find a better target, 
 *       use the section-target as fallback.
 *    4. $page_link_target=="" - do nothing
 *     
 * @param string $page_link_target
 * @param string $text
 * @param array $search_words
 * @return string
 */
function make_url_target($page_link_target, $text, $search_words) {
	if (version_compare(PHP_VERSION, '4.3.3', ">=") && 
	    substr($page_link_target,0,12)=='#wb_section_') {
		$word = '('.implode('|', $search_words).')';
		preg_match('/'.$word.'/i', $text, $match, PREG_OFFSET_CAPTURE);
		if ($match && is_array($match[0])) {
		    // position of first match
			$x = $match[0][1]; 
			// is there an anchor nearby?
			if (preg_match_all('/<\s*(?:a[^>]+?name|[^>]+?id)\s*=\s*"([^"]+)"/i', 
			    substr($text,0,$x), $match)) {
				$page_link_target = '#'.$match[1][count($match[1])-1];
			}
		}
	}
	elseif (substr($page_link_target,0,13)=='#!wb_section_') {
		$page_link_target = '#'.substr($page_link_target, 2);
	}
	
	// since wb 2.7.1 the section-anchor is configurable - SEC_ANCHOR holds the anchor name
	if (substr($page_link_target,0,12) == '#wb_section_') {
		if (defined('SEC_ANCHOR') && SEC_ANCHOR != '') {
			$sec_id = substr($page_link_target, 12);
			$page_link_target = '#'.SEC_ANCHOR.$sec_id;
		} else { 
		    // section-anchors are disabled
			$page_link_target = '';
		}
	}	
	return $page_link_target;
} // make_url_target

// wrapper for compatibility with old print_excerpt()
/**
 * Compatibillity wrapper for the old function print_excerpt()
 * This function is no longer supported!
 * 
 * @deprecated - use print_excerpt2() instead!
 * @todo - remove at LEPTON 2.1.x!
 * @param string $page_link
 * @param string $page_link_target
 * @param string $page_title
 * @param string $page_description
 * @param integer $page_modified_when
 * @param string $page_modified_by
 * @param string $text
 * @param integer $max_excerpt_num
 * @param array $func_vars
 * @param string $pic_link
 */
function print_excerpt($page_link, $page_link_target, $page_title, 
    $page_description, $page_modified_when, $page_modified_by, $text, 
    $max_excerpt_num, $func_vars, $pic_link="") {
	
    // print_excerpt() is deprecated!
    trigger_error('The function print_excerpt() is no longer supported, use print_excerpt2() instead!', E_USER_ERROR);
    
    $mod_vars = array(
		'page_link' => $page_link,
		'page_link_target' => $page_link_target,
		'page_title' => $page_title,
		'page_description' => $page_description,
		'page_modified_when' => $page_modified_when,
		'page_modified_by' => $page_modified_by,
		'text' => $text,
		'max_excerpt_num' => $max_excerpt_num,
		'pic_link' => $pic_link
	);
	print_excerpt2($mod_vars, $func_vars);
} // print_excerpt()

/**
 * This is the main function for all module search functions.
 * 
 * @param array $search_result - variables given from the module or droplep
 * @param array $search_parameter - variables given from the LEPTON search to the module
 * @return boolean true if the search result of the module match or false in all other cases
 */
function print_excerpt2($search_result, $search_parameter) {
    
	// check the search result variables
	if ($search_result['text'] == "") return false;
	if (!isset($search_result['page_link']))          $search_result['page_link'] = $search_parameter['page_link'];
	if (!isset($search_result['page_link_target']))   $search_result['page_link_target'] = "";
	if (!isset($search_result['page_title']))         $search_result['page_title'] = $search_parameter['page_title'];
	if (!isset($search_result['page_description']))   $search_result['page_description'] = $search_parameter['page_description'];
	if (!isset($search_result['page_modified_when'])) $search_result['page_modified_when'] = $search_parameter['page_modified_when'];
	if (!isset($search_result['page_modified_by']))   $search_result['page_modified_by'] = $search_parameter['page_modified_by'];
	if (!isset($search_result['text']))               $search_result['text'] = "";
	if (!isset($search_result['max_excerpt_num']))    $search_result['max_excerpt_num'] = $search_parameter['default_max_excerpt'];

	// special: image links
	if (!isset($search_result['pic_link']))           $search_result['pic_link'] = '';
	if (!isset($search_result['image_link']))         $search_result['image_link'] = $search_result['pic_link'];
	
	if (!isset($search_result['no_highlight']))       $search_result['no_highlight'] = false;
	if (isset($search_result['ext_charset'])) {
	    $search_result['ext_charset'] = strtolower($search_result['ext_charset']);
	}
	else {
	    $search_result['ext_charset'] = '';
	}
	
	if($search_result['no_highlight']) {
	    // suppress highlighting of search results
		$search_result['page_link_target'] = "&amp;nohighlight=1".$search_result['page_link_target']; 
	}
	
	// clean the text:
	$search_result['text'] = preg_replace('#<(br|dt|/dd|/?(?:h[1-6]|tr|table|p|li|ul|pre|code|div|hr))[^>]*>#i', '.', $search_result['text']);
	$search_result['text'] = preg_replace('#<(!--.*--|style.*</style|script.*</script)>#iU', ' ', $search_result['text']);
	$search_result['text'] = preg_replace('#\[\[.*?\]\]#', '', $search_result['text']); //Filter droplets from the page data
	// strip_tags() is called below
	if($search_result['ext_charset'] != '') { 
	    // data from external database may have a different charset
		require_once(CAT_PATH.'/framework/functions-utf8.php');
		switch($search_result['ext_charset']) {
		case 'latin1':
		case 'cp1252':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'CP1252');
			break;
		case 'cp1251':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'CP1251');
			break;
		case 'latin2':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'ISO-8859-2');
			break;
		case 'hebrew':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'ISO-8859-8');
			break;
		case 'greek':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'ISO-8859-7');
			break;
		case 'latin5':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'ISO-8859-9');
			break;
		case 'latin7':
			$search_result['text'] = charset_to_utf8($search_result['text'], 'ISO-8859-13');
			break;
		case 'utf8':
		default:
			$search_result['text'] = charset_to_utf8($search_result['text'], 'UTF-8');
		}
	} 
	else {
		$search_result['text'] = entities_to_umlauts($search_result['text'], 'UTF-8');
	}
	
	$content_locked = '';
	$add_anchor = true;
	if (isset($_SESSION[SESSION_SEARCH_NON_PUBLIC_CONTENT])) {
	    // show non-public contents, so add some extra informations
	    if (isset($_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT]) && 
	        !empty($_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT])) {
	        // link to a special page, defined in search as CFG_LINK_NON_PUBLIC_CONTENT
	        $search_result['page_link'] = CAT_URL.$_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT];
	    }
	    else {
	        $search_result['page_link'] = '';
	    }
	    // $_SESSION reset
	    unset($_SESSION[SESSION_SEARCH_NON_PUBLIC_CONTENT]);
	    unset($_SESSION[SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT]);
	    $add_anchor = false;
	}
	
	$anchor_text = $search_result['text']; // make an copy containing html-tags
	
	$search_result['text'] = strip_tags($search_result['text']);
	$search_result['text'] = str_replace(array('&gt;','&lt;','&amp;','&quot;','&#039;','&apos;','&nbsp;'), 
	    array('>','<','&','"','\'','\'',' '), $search_result['text']);
	$search_result['text'] = '.'.trim($search_result['text']).'.';
	// create empty image array
	$images = array();
	if ($search_parameter['settings'][CFG_SEARCH_IMAGES] || $search_parameter['settings'][CFG_CONTENT_IMAGE]) {
	    // we need a image array for the search
	    preg_match_all('/<img[^>]*>/', $anchor_text, $matches);
	    foreach ($matches as $match) {
	        foreach ($match as $img_tag) {
	            // <img ...> zerlegen
	            preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $img_tag, $attr);
	            foreach ($attr as $attributes) {
	                $img = array();
	                foreach ($attributes as $attribut) {
	                    if (strpos($attribut, "=") !== false) {
	                        list ($key, $value) = explode("=", $attribut);
	                        $value = trim($value);
	                        $value = substr($value, 1, strlen($value) - 2);
	                        $img[strtolower(trim($key))] = trim($value);
	                    }
	                }
	                if (isset($img['src'])) $images[] = $img;
	            }
	        }
	    }	     
	}
	
	if (!$search_parameter['settings'][CFG_SEARCH_IMAGES] && ($search_parameter['settings'][CFG_CONTENT_IMAGE] == CONTENT_IMAGE_NONE)) {
    	// Do a fast scan over the search result first. This may speedup things a lot.
    	if ($search_parameter['search_match'] == SEARCH_TYPE_ALL) {
    	    if (!is_all_matched($search_result['text'], $search_parameter['search_words']))	return false;
    	}
    	elseif (!is_any_matched($search_result['text'], $search_parameter['search_words'])) return false;
	}
	else {
	    // create a dummy string to check matches in the images
	    $divider = '.';
	    $image_text = '';
	    foreach ($images as $image) {
	        $file = basename($image['src']);
	        $file = urldecode(substr($file, 0, strrpos($file, '.')));
	        $alt = (isset($image['alt']) && !empty($image['alt'])) ? $image['alt'] : '';
	        $title = (isset($image['title']) && !empty($image['title'])) ? $image['title'] : '';
	        $image_text .= ($alt == $title) ? $divider.$alt.$divider.$file.$divider : $divider.$alt.$divider.$title.$divider.$file.$divider;
	    }
	    if ($search_parameter['search_match'] == SEARCH_TYPE_ALL) {
	        if (!is_all_matched($search_result['text'].$image_text, $search_parameter['search_words']))	return false;
	    }
	    elseif (!is_any_matched($search_result['text'].$image_text, $search_parameter['search_words'])) return false;
	}
	
	// search for an better anchor - this have to be done before strip_tags() (may fail if search-string contains <, &, amp, gt, lt, ...)
	$anchor =  make_url_target($search_result['page_link_target'], $anchor_text, $search_parameter['search_words']);
	// make the link from $mod_page_link, add anchor
	$link = "";
	if (!empty($search_result['page_link'])) {
    	$link = page_link($search_result['page_link']);
    	if (strpos($search_result['page_link'], 'http:') === false)
    		$link .= make_url_searchstring($search_parameter['search_match'], $search_parameter['search_url_array']);
    	
    	// add anchor only if content is not locked!
    	if ($add_anchor) $link .= $anchor;
	}
	
	// now get the excerpt
	$excerpt = "";
	$excerpt_array = array();
	// dont create excerpts if we are only searching for images!
    if (($search_parameter['search_match'] != SEARCH_TYPE_IMAGE) && ($search_result['max_excerpt_num'] > 0)) {
	    if (false !== ($excerpt_array = get_excerpts($search_result['text'], $search_parameter['search_words'], $search_result['max_excerpt_num']))) {
	        $excerpt = prepare_excerpts($excerpt_array, $search_parameter['search_words'], $search_result['max_excerpt_num']);
	    }
	}
		
	// no image matches now ...
	$image_match = false;
	$image_array = array();
	
	if ($search_parameter['settings'][CFG_SEARCH_IMAGES]) {
	    // ok - now we are looking for matching images ...
	    foreach ($images as $image) {
	        $file = urldecode(basename($image['src']));
	        $file = substr($file, 0, strrpos($file, '.'));
	        $alt = (isset($image['alt']) && !empty($image['alt'])) ? $image['alt'] : '';
	        $title = (isset($image['title']) && !empty($image['title'])) ? $image['title'] : '';
	        $image_text = ($alt == $title) ? $divider.$alt.$divider.$file.$divider : $divider.$alt.$divider.$title.$divider.$file.$divider;
	        if (false !== ($excerpt_array = get_excerpts($image_text, $search_parameter['search_words'], $search_result['max_excerpt_num']))) {
	            // image match!
	            $image_excerpt = prepare_excerpts($excerpt_array, $search_parameter['search_words'], $search_result['max_excerpt_num']);
	            // accept only images with complete URL
	            if (false === strpos($image['src'], CAT_URL)) continue;
	            $src = str_ireplace(CAT_URL, CAT_PATH, urldecode($image['src']));
	            $target = CAT_PATH.'/temp/search/'.urldecode(basename($image['src']));
	            makeThumbnail($src, $target, $search_parameter['settings'][CFG_THUMBS_WIDTH]);
	            $image_array[] = array(        
	                'excerpt' => $image_excerpt,
	                'src' => CAT_URL.'/temp/search/'.urldecode(basename($image['src'])),
	                'alt' => $image['alt'],
	                'title' => $image['title'],
	                'width' => $search_parameter['settings'][CFG_THUMBS_WIDTH]
	            );
	            $image_match = true;
	        }
	    }	    
	}
	
	// leave here if nothing matches ...
	if (empty($excerpt) && !$image_match) return false;
	
	$thumb_array = array();
	
	// if no images are matching to the search it's possible to show a image
	// of the content or to use a desired image_link from the module 
	$use_thumb = 0;
	if (!$image_match && (($search_parameter['settings'][CFG_CONTENT_IMAGE] != CONTENT_IMAGE_NONE) || !empty($search_result['image_link']))) {
	    if (!empty($search_result['image_link'])) { 
	        if (strpos($search_result['image_link'], CAT_URL) === false) {
	            $src = CAT_PATH.MEDIA_DIRECTORY.DIRECTORY_SEPARATOR.$search_result['image_link'];
	        }
	        else {
	            $src = str_ireplace(CAT_URL, CAT_PATH, $search_result['image_link']);
	        }
	        // the path to the temporary thumbnail
	        $target = CAT_PATH.'/temp/search/'.basename($search_result['image_link']);
	        // create a thumbnail and place it in the temporary directory
	        if (makeThumbnail($src, $target, $search_parameter['settings'][CFG_THUMBS_WIDTH])) {
                $thumb_array = array(        
                    'src' => CAT_URL.'/temp/search/'.urldecode(basename($search_result['image_link'])),
                    'alt' => $search_result['page_title'],
                    'title' => $search_result['page_title'],
                    'width' => $search_parameter['settings'][CFG_THUMBS_WIDTH]
                );
                $use_thumb = 1;    
	        }
	        else {
	            $use_thumb = 0;
	        }
	    }
	    else {
	        switch($search_parameter['settings'][CFG_CONTENT_IMAGE]):
	        case CONTENT_IMAGE_FIRST:
	            $i = 0; 
	            break;
	        case CONTENT_IMAGE_LAST:
	            $i = count($images)-1; 
	            break;
	        case CONTENT_IMAGE_RANDOM:
	            $i = rand(0, count($images)-1); 
	            break;
	        default:
	            $i = 0;
	        endswitch;
	        if (isset($images[$i]['src']) && (false !== strpos($images[$i]['src'], CAT_URL))) {
	            $src = str_ireplace(CAT_URL, CAT_PATH, urldecode($images[$i]['src']));
	            // the path to the temporary thumbnail
	            $target = CAT_PATH.'/temp/search/'.urldecode(basename($images[$i]['src']));
	            // create a thumbnail and place it in the temporary directory
	            if (makeThumbnail($src, $target, $search_parameter['settings'][CFG_THUMBS_WIDTH])) {
    	            $thumb_array = array(        
    	                'src' => CAT_URL.'/temp/search/'.urldecode(basename($images[$i]['src'])),
    	                'alt' => isset($images[$i]['alt']) ? $images[$i]['alt'] : '',
    	                'title' => isset($images[$i]['title']) ? $images[$i]['title'] : '',
    	                'width' => $search_parameter['settings'][CFG_THUMBS_WIDTH]
    	            );
    	            $use_thumb = 1;
	            }
	            else {
	                $use_thumb = 0;
	            }
	        }
	        else {
	            $use_thumb = 0;
	        }
	    }
	}
	else {
	    $use_thumb = 0;
	}
	
	list($date, $time) = get_page_modified($search_result['page_modified_when']);
	list($username, $displayname) = get_page_modified_by($search_result['page_modified_by'], $search_parameter['users']);
	
	$item = array(
	    'page' => array(
	        'link' => $link,
	        'title' => $search_result['page_title'],
	        'description' => $search_result['page_description'],
	        'excerpt' => $excerpt,
	        'images' => array(
	            'items' => $image_array,
	            'count' => count($image_array)
	            ),
	        'thumb' => array(
	            'active' => $use_thumb,
	            'image' => $thumb_array
	            ),
	        'last_changed' => array(
	            'unix_time' => $search_result['page_modified_when'],
	            'date_formatted' => $date,
	            'time_formatted' => $time
	        ),
	        'visibility' => $search_parameter['page_visibility']
	    ),
	    'user' => array(
	        'name' => $username,
	        'display_name' => $displayname
	    )
	);
	
	// all search results are temporary saved in the $_SESSION['SEARCH_RESULT_ITEMS']
	if (!isset($_SESSION[SESSION_SEARCH_RESULT_ITEMS])) $_SESSION[SESSION_SEARCH_RESULT_ITEMS] = array();
	$_SESSION[SESSION_SEARCH_RESULT_ITEMS][] = $item;
	
	return true;
} // print_excerpt2()

/**
 * Create a Thumbnail from the image $source with the maximum width or height of
 * $size and save it at $target
 * 
 * @param string $source - path to the source image
 * @param string $target - path to the target image
 * @param integer $size - max. size or width
 * @return boolean
 */
function makeThumbnail($source, $target, $size) {
    if (extension_loaded('gd') && function_exists('imagecreatefromjpeg')
        && function_exists('imagecreatefromgif') && function_exists('imagecreatefrompng')) {
        
        if (!file_exists($source)) return false;
        
        // get informations about the image
        $pi = pathinfo($source);
        $extension = strtolower($pi['extension']);
    
        switch ($extension) :
        case 'gif':
            $origin_image = imagecreatefromgif($source);
        break;
        case 'jpeg':
        case 'jpg':
            $origin_image = imagecreatefromjpeg($source);
            break;
        case 'png':
            $origin_image = imagecreatefrompng($source);
            break;
        default:
            // unsupported image type
            return false;
        endswitch;
    
        list ($original_x, $original_y) = getimagesize($source);
        
        if ($original_x > $original_y) {
            $new_width = $size;
            $new_height = $original_y * ($size / $original_x);
        }
        if ($original_x < $original_y) {
            $new_width = $original_x * ($size / $original_y);
            $new_height = $size;
        }
        if ($original_x == $original_y) {
            $new_width = $size;
            $new_height = $size;
        }
        
        // create new image of $new_width and $new_height
        $new_image = imagecreatetruecolor($new_width, $new_height);
        // Check if this image is PNG or GIF, then set if Transparent
        if (($extension == 'gif') or ($extension == 'png')) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }
        else {
            // resample image
            imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $original_x, $original_y);
        }
    
        switch ($extension) :
        case 'gif':
            imagegif($new_image, $target);
            imagedestroy($origin_image);
            imagedestroy($new_image);
            break;
        case 'jpg':
        case 'jpeg':
            imagejpeg($new_image, $target);
            imagedestroy($origin_image);
            imagedestroy($new_image);
            break;
        case 'png':
            imagepng($new_image, $target);
            imagedestroy($origin_image);
            imagedestroy($new_image);
            break;
        default:
            // unsupported image type
            return false;
        endswitch;
    
        @chmod($target, 0755);
        return true;
    }
    else {
        return false;
    }
} // makeThumbnail()


/* These functions can be used in module-supplied search_funcs
 * -----------------------------------------------------------
* print_excerpt2() - the main-function to use in all search_funcs
* print_excerpt() - wrapper for compatibility-reason. Use print_excerpt2() instead.
* list_files_dirs() - lists all files and dirs below a given directory
* clear_filelist() - keeps only wanted or removes unwanted entries in file-list.
*/



// list all files and dirs in $dir (recursive), omits '.', '..', and hidden files/dirs
// returns an array of two arrays ($files[] and $dirs[]).
// usage: list($files,$dirs) = list_files_dirs($directory);
//        $depth: get subdirs (true/false)
function list_files_dirs($dir, $depth=true, $files=array(), $dirs=array()) {
	$dh=opendir($dir);
	while(($file = readdir($dh)) !== false) {
		if($file{0} == '.' || $file == '..') {
			continue;
		}
		if(is_dir($dir.'/'.$file)) {
			if($depth) {
				$dirs[] = $dir.'/'.$file;
				list($files, $dirs) = list_files_dirs($dir.'/'.$file, $depth, $files, $dirs);
			}
		} else {
			$files[] = $dir.'/'.$file;
		}
	}
	closedir($dh);
	natcasesort($files);
	natcasesort($dirs);
	return(array($files, $dirs));
}

// keeps only wanted entries in array $files. $str have to be an eregi()-compatible regex
function clear_filelist($files, $str, $keep=true) {
	// options: $keep = true  : remove all non-matching entries
	//          $keep = false : remove all matching entries
	$c_filelist = array();
	if($str == '')
		return $files;
	foreach($files as $file) {
		if($keep) {
			if(preg_match("~$str~i", $file)) {
				$c_filelist[] = $file;
			}
		} else {
			if(!preg_match("~$str~i", $file)) {
				$c_filelist[] = $file;
			}
		}
	}
	return($c_filelist);
}

/**
 * @deprecated - not supported in LEPTON CMS
 */
function search_make_sql_part($words, $match, $columns) {
    trigger_error(sprintf('[%s - %s] This function is deprecated and not supported by LEPTON CMS!', __FUNCTION__, __LINE__), E_USER_ERROR);
}
