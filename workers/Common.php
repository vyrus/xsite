<?php

class Common
{
    public static function commonNode ()
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
		$localURL = Handler::getURL();
		$url = $doc->createElement('url', Handler::getURL());
		$url->setAttribute('tail', Handler::urlTail());
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
	    $siteMap->load(XSITE::MAP_PATH);
	    
		$includes->appendChild(
		    $doc->importNode($siteMap->documentElement, true)
		);
		
		$common->appendChild($includes);
		
		return $common;
    }
    
    public function news () {}
}