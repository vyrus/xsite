<?php

class XMLSite 
{    
    private $site;
    
    public function load ($sitePath)
    {
        $this->site = new DOMDocument('1.0', 'UTF-8');
		$this->site->load(XSite::SITE_PATH.$sitePath);				
		
		#Worker		
		$worker = $this->site->documentElement->getAttribute('worker');
		$workerPath = XSite::WORKER_PATH.str_replace('::','/',$worker).'.php';
		
		if (file_exists($workerPath))
		{
		    require_once($workerPath);
    		$this->processNSNodes($worker);
		}		
    }
    
    protected function processNSNodes ($worker)
    {
        $NSNodes = $this->site->getElementsByTagNameNS(XSite::WORKER_NAMESPACE, '*');
		
		while ($NSNodes->length)
		{			    		    
			$node = $NSNodes->item(0);
			
			$args = array ();
			foreach ($node->attributes as $attr)
			    $args[$attr->name] = $attr->value;
			
			$method = str_replace('-', '_', str_replace('xsite:', '', $node->nodeName));			
			$w = new $worker ();			
			
			if ($dataNode = $w->$method($args)) 
				$node->parentNode->replaceChild(
					$doc->importNode($dataNode, true), 
					$node
				);
			else
			    $node->parentNode->removeChild($node);
		}
    }
    
    public function appendNode ($DOMNode)
    {
        $this->site->documentElement->appendChild( 
			$this->site->importNode($DOMNode, true)
		);
    }   
    
    public function display () 
    {
        $xslPath = $this->site->documentElement->getAttribute('transform');
        
        if (isset($_GET[XSite::VIEW_XML_OPTION]) || !$xslPath) 
		{
			header('Content-type: text/xml; charset=utf-8');
			echo $this->site->saveXML();
		}
		else
		{		    		    		    
		    $xsl = new DOMDocument('1.0', 'UTF-8');
    		$xsl->load(XSite::XSL_PATH.$xslPath);

    		$xslProc = new XSLTProcessor();
    		$xslProc->importStylesheet($xsl);
    				    
			header('Content-type: text/html; charset=utf-8');
			echo $xslProc->transformToXML($this->site);
		}
    }
}