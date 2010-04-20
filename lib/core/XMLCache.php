<?php

class XMLCache 
{
    private $url;
    private $itemForUrl;
    private $sitesXML;
    private $sitemapXML;
    
    private $mapPath;
    private $sitesPath;
    private $sitemapPath;
    
    public function __construct ($map)
    {
        $this->mapPath = XSite::PATH_MAP.$map;
        $this->sitesPath = XSite::PATH_CACHE.$map;
        $this->sitemapPath = XSite::PATH_CACHE.'sitemap.xml';
    }     
    
    public function getSite ($url)
    {        
        $this->url = '/'.trim($url, '/');
        
        $sites = $this->getSites();
        $items = $sites->xpath("item[@url = '{$this->url}']");
        
        if (count($items) == 0) 
        {
            $this->itemForUrl = $sites->addChild('item');
            $this->itemForUrl['url'] = $this->url;
            $this->itemForUrl['changed'] = 0;
            $this->setSitemapUrl();
            return false;
        }
        $this->itemForUrl = $items[0];
        
        if (filemtime($this->mapPath) > (int) $this->itemForUrl['changed'])
            return false;
        
        return $this->itemForUrl['site'];
    }
    
    public function setSite ($site)
    {
        #Warning: $this->itemForUrl defined in getSite()        
        $this->itemForUrl['site'] = $site;
        $this->itemForUrl['changed'] = time();
        
        fwrite(
            fopen($this->sitesPath, 'w+'),
            $this->getSites()->asXML()
        );
    }
    
    private function setSitemapUrl ()
    {
        $sitemap = $this->getSitemap();
        
        #Google sitemap format
        $url = $sitemap->addChild('url');
        $url->addChild('loc', $this->url);
        $url->addChild('lastmod', date('Y-m-d'));
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.5');
        
        fwrite(
            fopen($this->sitemapPath, 'w+'),
            $sitemap->asXML()
        );
    }
    
    #TODO: Is it necessary to update?
    private function updateSitmapURL () #There does nothing
    {
        $sitemap = $this->getSitemap();
                
        $urls = $sitemap->xpath("url[loc = '{$this->url}']");
        $url = $urls[0];
    }                
    
    private function getSites ()
    {
        if (!file_exists($this->sitesPath))
            $this->createXMLFile($this->sitesPath, 'sites');
        
        if (!$this->sitesXML)
            $this->sitesXML = simplexml_load_file ($this->sitesPath);
        
        return $this->sitesXML;
    }
    
    private function getSitemap ()
    {
        if (!file_exists($this->sitemapPath))
            $this->createXMLFile(
                $this->sitemapPath, 
                'urlset',
                'http://www.google.com/schemas/sitemap/0.84'
            );
        
        if (!$this->sitemapXML)
            $this->sitemapXML = simplexml_load_file ($this->sitemapPath);
        
        return $this->sitemapXML;
    }
    
    private function createXMLFile ($path, $rootName, $xmlns = null)
    {
        $xml = new SimpleXMLElement ("<$rootName/>");        
        if ($xmlns) 
            $xml['xmlns'] = $xmlns;
            
        fwrite(
            fopen($path, 'w+'), 
            $xml->asXML()
        );
    }        
}