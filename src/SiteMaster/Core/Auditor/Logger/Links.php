<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;
use SiteMaster\Core\Auditor\Site\Page;

class Links extends \Spider_LoggerAbstract
{
    /**
     * @var bool|\Spider
     */
    protected $spider = false;

    /**
     * @var bool|Page
     */
    protected $page = false;

    function __construct(\Spider $spider, Page $page)
    {
        $this->spider = $spider;
        $this->page   = $page;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {
        $uris = \Spider::getUris(\Spider::getUriBase($uri), $uri, $xpath);
        
        foreach ($uris as $uri) { 
            Page\Link::createNewPageLink($this->page->id, $uri);
        }
    }
}
