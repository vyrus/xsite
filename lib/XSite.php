<?php
/**XSite project
 *@author danyastuff
 */
 
define ('__XSITE_ROOT', dirname(realpath(__FILE__)).'/../' );
define ('__XSITE_XSL',           __XSITE_ROOT.'../../xsl/');
define ('__XSITE_SITES',         __XSITE_ROOT.'sites/');
define ('__XSITE_WORKERS',       __XSITE_ROOT.'workers/');
define ('__XSITE_MAP',           __XSITE_ROOT.'www/');
define ('__XSITE_CACHE',         __XSITE_ROOT.'cache/');

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
    const PATH_CACHE  = __XSITE_CACHE;
    
    private static $map;
    private static $url;    
    
    #Main method, displays page for URL
    public static function displayPage ($url)
    {
        self::$url = '/'.trim($url, '/');
        
        $guide = new XMLGuide (self::$map);
        $site  = new XMLSite ();
        
        $site->load( 
            $guide->getSitePath(self::$url)
        );
        
        if (!$site->getAttribute('api'))
            $site->appendNode(self::commonNode());
        
        $site->display();
    }
    
    public static function setMap ($map) 
    { 
        self::$map = $map; 
    }
    
    #Current URL    
    public static function getUrl ($index = null)
    {
        if ($index == null) return self::$url;
        
        $parts = explode('/', trim(self::$url, '/'));
        return isset($parts[$index]) ? $parts[$index] : '';
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
		
		#Request
		function toChildren ($doc, $node, $array) 
		{
		    foreach ($array as $key => $value)
		    {		        
		        if (is_array($value))
		        {
		            $item = $doc->createElement('item');
		            toChildren ($doc, $item, $value);
		        }
		        else
		        {
		            $item = $doc->createElement('item', $value);
		        }
		        
		        $item->setAttribute('name', $key);
		        $node->appendChild($item);
		    }
		}
		
	    $request = $doc->createElement('request');
	    toChildren($doc, $request, $_REQUEST);
	    $common->appendChild($request);	    
		
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
	    $siteMap->load(XSITE::PATH_MAP.self::$map);	    
		$includes->appendChild(
		    $doc->importNode($siteMap->documentElement, true)
		);				
		
		$common->appendChild($includes);
		
		return $common;
    }
}