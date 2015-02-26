<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;

class Links extends \Spider_LoggerAbstract
{
    /**
     * @var \Spider
     */
    protected $spider;

    /**
     * @var Page
     */
    protected $page;

    function __construct(\Spider $spider, Page $page)
    {
        $this->spider = $spider;
        $this->page   = $page;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {

        $links = \Spider::getUris(\Spider::getUriBase($uri), $uri, $xpath);
        
        $filters = array(
            '\\SiteMaster\\Core\\Auditor\\Filter\\Scheme',
            '\\SiteMaster\\Core\\Auditor\\Filter\\InvalidURI',
        );

        //Filter the links
        foreach ($filters as $filter_class) {
            $links = new $filter_class($links);
        }

        $links_array = array();
        foreach ($links as $link) {
            $links_array[] = Util::stripURIFragment($link);
        }
        
        foreach ($links_array as $uri) { 
            Page\Link::createNewPageLink($this->page->id, $uri);
        }
    }
}
