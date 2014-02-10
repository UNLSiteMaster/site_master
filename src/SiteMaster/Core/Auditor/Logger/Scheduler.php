<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;
use Monolog\Logger;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;

class Scheduler extends \Spider_LoggerAbstract
{
    /**
     * @var bool|\Spider
     */
    protected $spider = false;

    /**
     * @var bool|Scan
     */
    protected $scan = false;

    /**
     * @var bool|Site
     */
    protected $site = false;

    function __construct(\Spider $spider, Scan $scan, Site $site)
    {
        $this->spider = $spider;
        $this->scan = $scan;
        $this->site = $site;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {
        $pages = $this->spider->getCrawlableUris($this->site->base_url, \Spider::getURIBase($uri), $uri, $xpath);

        foreach ($this->spider->getFilters() as $filter_class) {
            $pages = new $filter_class($pages);
        }

        foreach ($pages as $uri) {
            if ($page_scan = Page::getByScanIDAndURI($this->scan->id, $uri)) {
                //Looks like it already exists... skip
                continue;
            }
            
            $page_scan = Page::createNewPage($this->scan->id, $this->scan->sites_id, $uri);
            
            $page_scan->scheduleScan();
        }
    }
}
