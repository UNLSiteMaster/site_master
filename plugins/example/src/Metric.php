<?php
namespace SiteMaster\Plugins\Example;

use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;

class Metric extends MetricInterface
{

    /**
     * Get the human readable name of this metric
     *
     * @return string The human readable name of the metric
     */
    public function getName()
    {
        return 'Example Metric';
    }

    /**
     * Get the Machine name of this metric
     *
     * This is what defines this metric in the database
     *
     * @return string The unique string name of this metric
     */
    public function getMachineName()
    {
        return 'example';
    }

    /**
     * Determine if this metric should be graded as pass-fail
     *
     * @return bool True if pass-fail, False if normally graded
     */
    public function isPassFail()
    {
        if (isset($this->options['pass_fail']) && $this->options['pass_fail'] == true) {
            //Simulate a pass/fail metric grade
            return true;
        }
        
        return false;
    }

    /**
     * Scan a given URI and apply all marks to it.
     *
     * All that this
     *
     * @param string $uri The uri to scan
     * @param \DOMXPath $xpath The xpath of the uri
     * @param int $depth The current depth of the scan
     * @param \SiteMaster\Core\Auditor\Site\Page $page The current page to scan
     * @param \SiteMaster\Core\Auditor\Logger\Metrics $context The logger class which calls this method, you can access the spider, page, and scan from this
     * @return bool True if there was a successful scan, false if not.  If false, the metric will be graded as incomplete
     */
    public function scan($uri, \DOMXPath $xpath, $depth, Page $page, Metrics $context)
    {
        if (isset($this->options['simulate_incomplete']) && $this->options['simulate_incomplete']) {
            //Simulate an incomplete scan
            return false;
        }

        if (isset($this->options['available_points']) && $this->options['available_points']) {
            //The available points defaults to 100.  However, it can be customized line this:
            $grade = $this->getMetricGrade($page);
            $grade->points_available = $this->options['available_points'];
            $grade->save();
        }
          
        $mark = $this->getMark('test', 'Just a test', 10.5);

        $page->addMark($mark);

        $mark = $this->getMark('test2', 'Just a test', 5);

        $page->addMark($mark);

        return true;
    }

    /**
     * Set the options array for this metric.
     * 
     * This is for testing purposes
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}