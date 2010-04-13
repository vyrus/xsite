<?php

class URLInspector 
{
    public function is_news_title ($uri)
    {
        $titles = array('happy-birthday', 'any-competition', 'new-projects');
        
        return in_array($uri, $titles) ? true : false;
    }
}