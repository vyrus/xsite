<?php

class XMLGuide 
{
    private $xmlMap;
    private $site;
    
    private $map;
    
    public function __construct ($map)
    {
        $this->map = $map;
        $this->xmlMap = simplexml_load_file (XSite::PATH_MAP.$this->map);
        
        $this->site = array(
            'root'    => $this->xmlMap['site'],
            '404'     => $this->xmlMap['site-404']
        );
    }
    
    private $cache;
    
    public function getSitePath ($url)
    {
        $url = trim($url, '/');
        
        $this->cache = new XMLCache ($this->map);        
        if ($site = $this->cache->getSite($url)) return $site;
        
        $urlParts = explode ('/', $url);        
        $branch = $this->xmlMap;
        
        if (!$urlParts[0]) return $this->export($this->site['root']);
        
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
                #if there isn't any static url but regexp found
                array_unshift($urlParts, $part);
                
                if ($items = $branch->xpath('item[@regexp | @func]')) 
                {
                    $branch = $items;
                    break;
                }
                
                $branch = null;
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
                    return $this->export();
            }
            
            if ($branch['site']) 
                return $this->export($branch['site']);
                
            if ($branch['redirect']) 
            {
                header ('Location: '.$branch['redirect']);
                return;
            }
            
            $parent = $branch->xpath('ancestor::*[@subsite]');
            if ($parent[0]['subsite']) 
                return $this->export($parent[0]['subsite']);
        }
        
        return $this->export();    
    }
    
    private function export ($site = null)
    {
        if ($site)
        {
            $this->cache->setSite($site);
            return $site;
        }
        
        return $this->site['404'];
    }
    
}