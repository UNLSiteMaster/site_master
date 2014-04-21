<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Site\Page\MetricGrades\ForScanAndMetric;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Scan;

class HotSpots implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var \SiteMaster\Core\Registry\Site
     */
    public $site = false;

    /**
     * @var bool|\SiteMaster\Core\Auditor\Scan
     */
    public $scan = false;

    /**
     * @var ForScanAndMetric
     */
    public $hot_spots = array();

    /**
     * @var bool|Metric
     */
    public $metric = false;

    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['scans_id'])) {
            throw new InvalidArgumentException('a scan id is required', 400);
        }

        if (!isset($this->options['metrics_id'])) {
            throw new InvalidArgumentException('a metric id is required', 400);
        }
        
        if (!$this->metric = Metric::getByID($this->options['metrics_id'])) {
            throw new InvalidArgumentException('Could not find a metric with the given id.', 500);
        }

        if (!$this->scan = Scan::getByID($this->options['scans_id'])) {
            throw new InvalidArgumentException('Could not find a scan for the given page.', 400);
        }

        if (!$this->site = $this->scan->getSite()) {
            throw new InvalidArgumentException('Could not find a site with the given id', 400);
        }
        
        $this->hot_spots = new ForScanAndMetric(array(
            'scans_id' => $this->scan->id,
            'metrics_id' => $this->options['metrics_id']
        ));
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->scan->getURL();
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        $metric_object = $this->metric->getMetricObject();
        return 'Hot Spots for ' . $metric_object->getName();
    }
}
