<?php

define('__XSITE_ROOT', dirname(realpath(__FILE__)).'/' );
define('__XSITE_XSL',     __XSITE_ROOT.'xsl/');
define('__XSITE_SITES',   __XSITE_ROOT.'sites/');
define('__XSITE_WORKERS', __XSITE_ROOT.'workers/');
define('__XSITE_MAP',     __XSITE_ROOT.'www.xml');

require_once __XSITE_ROOT.'lib/XMLGuide.php';
require_once __XSITE_ROOT.'lib/XMLSite.php';

class XSite
{
    const WORKER_NAMESPACE = 'urn:xsite-data';
    const VIEW_XML_OPTION  = 'toxml';
    
    const XSL_PATH    = __XSITE_XSL;
    const SITE_PATH   = __XSITE_SITES;
    const WORKER_PATH = __XSITE_WORKERS;
    const MAP_PATH    = __XSITE_MAP;
}