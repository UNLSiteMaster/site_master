<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;

class Metrics extends \Spider_LoggerAbstract
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

    /**
     * @var bool|Page
     */
    protected $page = false;

    /**
     * 
     * @var array
     */
    protected $phantomjsResults = [];

    function __construct(\Spider $spider, Scan $scan, Site $site, Page $page, $phantomjsResults)
    {
        $this->spider = $spider;
        $this->scan = $scan;
        $this->site = $site;
        $this->page = $page;
        $this->phantomjsResults = $phantomjsResults;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {
        $metrics = new \SiteMaster\Core\Auditor\Metrics();
        
        foreach ($metrics as $metric) {
            /**
             * @var \SiteMaster\Core\Auditor\MetricInterface $metric
             */
            
            $metricPhantomResults = [];
            
            $plugin = $metric->getPlugin();
            
            if (isset($this->phantomjsResults[$plugin->getMachineName()])) {
                $metricPhantomResults = $this->phantomjsResults[$plugin->getMachineName()];
            }
            
            $metric->performScan($uri, $xpath, $depth, $this->page, $this, $metricPhantomResults);
        }
    }

    /**
     * @return bool|\Spider
     */
    public function getSpider()
    {
        return $this->spider;
    }

    /**
     * @return bool|Scan
     */
    public function getScan()
    {
        return $this->scan;
    }

    /**
     * @return bool|Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return bool|Page
     */
    public function getPage()
    {
        return $this->page;
    }
}
