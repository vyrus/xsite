<?php

class XMLSite 
{    
    private $site;
    
    public function load ($sitePath)
    {
        $sitePath = XSite::PATH_SITE.$sitePath;
        if (!file_exists($sitePath)) 
        {
            #TODO: log 'site $sitePath not found'
            return;
        }
        $this->site = new DOMDocument('1.0', 'UTF-8');
		$this->site->load($sitePath);				
		
		#Worker		
		$worker = $this->site->documentElement->getAttribute('worker');
		$workerPath = XSite::PATH_WORKER.str_replace('::','/',$worker).'.php';
		
		if (file_exists($workerPath))
		{
		    require_once($workerPath);
    		$this->processNSNodes($worker);
		}		
    }
    
    protected function processNSNodes ($worker)
    {
        $NSNodes = $this->site->getElementsByTagNameNS(XSite::NAMESPACE_WORKER, '*');
		
		while ($NSNodes->length)
		{			    		    
			$node = $NSNodes->item(0);
			
			$args = array ();
			foreach ($node->attributes as $attr)
			    $args[$attr->name] = $attr->value;
			
			$method = str_replace('-', '_', str_replace('xsite:', '', $node->nodeName));
			
			$w = new $worker ();			
			
			if ($dataNode = $w->$method($args)) 
			{				    			    		    
			    $class = get_class ($dataNode); //SimpleXMLElement or DOMElement
			    
			    if ($class != 'DOMElement') #SimpleXMLElement or descendant
			        $dataNode = dom_import_simplexml ($dataNode);
			    
			    $node->parentNode->replaceChild(
					$this->site->importNode($dataNode, true), 
					$node
				);
			}				
			else
			    $node->parentNode->removeChild($node);
		}
    }
    
    public function getAttribute ($name)
    {
        if (!$this->site) return false;
        return $this->site->documentElement->getAttribute($name);
    }
    
    public function appendNode ($DOMNode)
    {
        if ($this->site)
            $this->site->documentElement->appendChild( 
    			$this->site->importNode($DOMNode, true)
    		);
    }   
    
    public function display () 
    {
        if (!$this->site) return;
        $xslPath = $this->site->documentElement->getAttribute('transform');        
        
        if (isset($_GET[XSite::OPTION_VIEW_XML]) || !$xslPath) 
		{
			header('Content-type: text/xml; charset=utf-8');
			echo $this->site->saveXML();
		}
		else
		{	
		    $xslPath = XSite::PATH_XSL.$xslPath;    		    
		    if (!file_exists($xslPath)) return;
		    
		    $xsl = new DOMDocument('1.0', 'UTF-8');
    		$xsl->load($xslPath);

    		$xslProc = new XSLTProcessor();
    		$xslProc->importStylesheet($xsl);
    				    
			header('Content-type: text/html; charset=utf-8');
			echo $xslProc->transformToXML($this->site);
		}
    }
}