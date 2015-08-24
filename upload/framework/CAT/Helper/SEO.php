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
 *   @author          Black Cat Development
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_SEO'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_SEO extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 8 );

        /**
         *
         * @access public
         * @return
         **/
        public static function updateSitemap()
        {
            // get pages that are visible for guests
            $pagelist = CAT_Helper_Page::getPages();
            // get default settings
            $default_freq = CAT_Registry::get('SITEMAP_UPDATE_FREQ',NULL,'weekly');
            // get default page for highest priority
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!! note: this does not work in combination with language based forwarding!
// !!!!! Especially if the root pages are all invisible!
            $root     = CAT_Helper_Page::getDefaultPage();
            if(count($pagelist))
            {
                $xml       = array();
                foreach($pagelist as $p)
                {
                    $seo_set = array();
                    if(isset($p['settings'])&&isset($p['settings']['seo']))
                        $seo_set = $p['settings']['seo'];
                    if(isset($seo_set['sitemap_include'])&&isset($seo_set['sitemap_include'][0]))
                    {
                        if($seo_set['sitemap_include'][0]=='never') continue;
                    }
                    $prio  = ((isset($seo_set['sitemap_priority'])&&isset($seo_set['sitemap_priority'][0]))
                           ? $seo_set['sitemap_priority'][0]
                           : '0.5');
                    $freq  = ((isset($seo_set['sitemap_update_freq'])&&isset($seo_set['sitemap_update_freq'][0]))
                           ? $seo_set['sitemap_update_freq'][0]
                           : $default_freq);
                    if($p['page_id']==$root)
                        $prio = '1.0';
                    $xml[] = '  <url>
    <loc>'.CAT_Helper_Validate::getURI($p['href']).'</loc>
    <lastmod>'.strftime('%Y-%m-%d',$p['modified_when']).'</lastmod>
    <priority>'.$prio.'</priority>
    <changefreq>'.$freq.'</changefreq>
  </url>';
                }
            }
            if(count($xml))
            {
                $fh = fopen(CAT_PATH.'/sitemap.xml','w');
                fwrite($fh,'<'.'?'.'xml version="1.0" encoding="UTF-8"?>'."\n");
                fwrite($fh,'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
                fwrite($fh,implode("\n",$xml));
                fwrite($fh,"\n".'</urlset>');
                fclose($fh);
            }

/*
 [settings] => Array
                (
                    [seo] => Array
                        (
                            [sitemap_priority] => Array
                                (
                                    [0] => 0.6
                                )

                        )

                )
*/
        }   // end function updateSitemap()
        
        

    } // class CAT_Helper_SEO

} // if class_exists()

/*
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <url>
      <loc>http://www.example.com/</loc>
      <lastmod>2005-01-01</lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
   </url>
</urlset>

Eintrag in robots.txt:
Sitemap: http://www.example.com/sitemap.xml


Submitting your Sitemap via an HTTP request

To submit your Sitemap using an HTTP request (replace <searchengine_URL> with
the URL provided by the search engine), issue your request to the following
URL:

<searchengine_URL>/ping?sitemap=sitemap_url

For example, if your Sitemap is located at http://www.example.com/sitemap.gz, your URL will become:

<searchengine_URL>/ping?sitemap=http://www.example.com/sitemap.gz

URL encode everything after the /ping?sitemap=:

<searchengine_URL>/ping?sitemap=http%3A%2F%2Fwww.yoursite.com%2Fsitemap.gz

You can issue the HTTP request using wget, curl, or another mechanism of your
choosing. A successful request will return an HTTP 200 response code; if you
receive a different response, you should resubmit your request. The HTTP 200
response code only indicates that the search engine has received your Sitemap,
not that the Sitemap itself or the URLs contained in it were valid. An easy
way to do this is to set up an automated job to generate and submit Sitemaps
on a regular basis.
*/