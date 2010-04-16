<?php
/**XSite project
 *@author danyastuff
 */
 
define ('__XSITE_ROOT', dirname(realpath(__FILE__)).'/../' );
define ('__XSITE_XSL',           __XSITE_ROOT.'../../xsl/');
define ('__XSITE_SITES',         __XSITE_ROOT.'sites/');
define ('__XSITE_WORKERS',       __XSITE_ROOT.'workers/');
define ('__XSITE_MAP',           __XSITE_ROOT.'www.xml');
define ('__XSITE_CACHE_SITES',   __XSITE_ROOT.'cache/sites.xml');
define ('__XSITE_CACHE_SITEMAP', __XSITE_ROOT.'cache/sitemap.xml');

require_once __XSITE_ROOT.'lib/core/XMLCache.php';
require_once __XSITE_ROOT.'lib/core/XMLGuide.php';
require_once __XSITE_ROOT.'lib/core/XMLSite.php';
require_once __XSITE_ROOT.'lib/URLInspector.php';

class XSite
{
    const NAMESPACE_WORKER = 'urn:xsite-data';
    const OPTION_VIEW_XML  = 'toxml';
    
    const PATH_XSL    = __XSITE_XSL;
    const PATH_SITE   = __XSITE_SITES;
    const PATH_WORKER = __XSITE_WORKERS;
    const PATH_MAP    = __XSITE_MAP;
    const PATH_CACHE_SITES   = __XSITE_CACHE_SITES;
    const PATH_CACHE_SITEMAP = __XSITE_CACHE_SITEMAP;
    
    private static $url;
    
    #Main method, displays page for URL
    public static function displayPage ($url)
    {
        self::$url = '/'.trim($url, '/');
        
        $guide = new XMLGuide ();
        $site  = new XMLSite ();
        
        $site->load( 
            $guide->getSitePath(self::$url)
        );
        
        $site->appendNode(self::commonNode());
        
        $site->display();
    }
    
    #Current URL    
    public static function getUrl ()
    {
        return self::$url;
    }
    
    #Common node, added to every page
    private static function commonNode ()
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->appendChild(
            $doc->createElement('common')
        );
		
		$common = $doc->documentElement;
		
		#Host
		$common->appendChild(
			$doc->createElement('host', $_SERVER['HTTP_HOST'])
		);
		
		#URL
		$localURL = self::getUrl();
		$url = $doc->createElement('url', self::$url);
		$url->setAttribute('tail', array_pop(explode('/', self::$url)));
		$common->appendChild($url);
		
		#Query string
		$queryString = $doc->createElement('query-string');
		$qs = explode('&', $_SERVER['QUERY_STRING']);
		foreach ($qs as $pair)
        {
            $pair = explode('=', $pair);
            $k = $pair[0]; $v = $pair[1];
	        $item = $doc->createElement('item', $v);
	        $item->setAttribute('name', $k);
		    $queryString->appendChild($item);
	    }
	    $common->appendChild($queryString);
		
		#Date
		$date = $doc->createElement('date');
		$date->setAttribute('year',  date('Y'));
		$date->setAttribute('month', date('n'));
		$date->setAttribute('day',   date('j'));
		$date->setAttribute('time',  date('H:i'));
		$common->appendChild($date);
	    
	    #Includes
	    $includes = $doc->createElement('includes');
	    
	    $siteMap = new DOMDocument('1.0', 'UTF-8');
	    $siteMap->load(XSITE::PATH_MAP);
	    
		$includes->appendChild(
		    $doc->importNode($siteMap->documentElement, true)
		);
		
		$common->appendChild($includes);
		
		return $common;
    }
}