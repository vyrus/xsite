<?php

class XMLGuide 
{
    private $xmlMap;   
    private $site;    
    
    public function __construct ()
    {
        $this->xmlMap = simplexml_load_file (XSite::MAP_PATH);
        
        $this->site = array(
            'root'    => $this->xmlMap['site'],
            '404'     => $this->xmlMap['site-404']
        );
    }
    
    public function getSitePath ($url)
    {
        $url = trim($url, '/');        
        $urlParts = explode ('/', $url);        
        $branch = $this->xmlMap;
        
        if (!$urlParts) return $this->site['root'];
        
        while ($urlParts)
        {
            $foundBranch = null;
            $part = array_shift($urlParts);
            
            if (isset( $branch->{$part} )) 
            {
                $branch = $branch->{$part};
            }
            else if (isset( $branch->item['name'] ) && $branch->item['name'] == $part)
            {
                $branch = $branch->item;
            } 
            else
            {
                $branch = null;
                break;
            }                                            
            
            #if there isn't any static url but regexp found
            if ($urlParts 
                && !isset($branch->{$urlParts[0]}) 
                && ($items = $branch->xpath('item[@regexp | @func]'))
            ) {
                $branch = $items;
                break;
            }
        }
        
        if ($branch) #anyway page is found
        {
            if (isset($branch[0]['regexp']) || isset($branch[0]['func']) ) 
            {                   
                $tail = implode('/', $urlParts);
                
                foreach ($branch as $item)
                {
                    if (isset($item['regexp']))
                    {
                        $pattern = '{^'.$item['regexp'].'$}';
                        if (preg_match ($pattern, $tail)) 
                        {
                            $branch = $item;
                            break;
                        }
                    } 
                    else if (isset($item['func']))
                    {
                        $method = str_replace('-', '_', $item['func']);
                        
                        if (!isset($inspector)) $inspector = new URLInspector();                        
                        
                        if (@$inspector->{$method}($tail))
                        {
                            $branch = $item;
                            break;
                        }
                    }
                    
                }
                
                if (!$branch['regexp'] && !$branch['func']) 
                    return $this->site['404'];
            }
            
            if ($branch['site']) return $branch['site'];
            
            $parent = $branch->xpath('ancestor::*[@subsite]');
            if ($parent[0]['subsite']) 
                return $parent[0]['subsite'];
            
            #return $this->site['default'];
        }
        
        return $this->site['404'];                 
    }
    
}