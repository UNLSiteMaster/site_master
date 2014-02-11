<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Util;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;

abstract class MetricInterface
{
    public $options;
    public $plugin_name;
    public $metric_record;

    /**
     * @param string $plugin_name (The plugin machine name for this metric)
     * @param array $options an array of options (usually the same options that were passed to the plugin)
     */
    public function __construct($plugin_name, array $options = array())
    {
        $this->plugin_name = $plugin_name;
        $this->options     = $options;
        $this->metric_record = $this->getMetricRecord();
    }
    
    /**
     * Get the human readable name of this metric
     * 
     * @return string The human readable name of the metric
     */
    abstract public function getName();

    /**
     * Get the Machine name of this metric
     * 
     * This is what defines this metric in the database
     * 
     * @return string The unique string name of this metric
     */
    abstract public function getMachineName();

    /**
     * Determine if this metric should be graded as pass-fail
     * 
     * @return bool True if pass-fail, False if normally graded
     */
    abstract public function isPassFail();

    /**
     * Scan a given URI and apply all marks to it.
     *
     * All that this
     *
     * @param string $uri - the uri to scan
     * @param \DOMXPath $xpath - the xpath of the uri
     * @param \Spider $spider - the spider object
     * @param Scan $scan - the current scan record
     * @param Site $site - the current site record
     * @param \SiteMaster\Core\Auditor\Site\Page $page - the current page record
     * @param int $depth - the current depth of the scan
     * @return bool True if there was a successful scan, false if not.  If false, the metric will be graded as incomplete
     */
    abstract public function scan($uri, \DOMXPath $xpath, \Spider $spider, Scan $scan, Site $site, Page $page, $depth);

    /**
     * Get the metric record for this metric
     * 
     * @return bool|Metric
     */
    public function getMetricRecord()
    {
        if ($metric = Metric::getByMachineName($this->getMachineName())) {
            //Found the metric, just return it.
            return $metric;
        }

        //Couldn't find the metric.  Install it.
        return Metric::createNewMetric($this->getMachineName());
    }

    /**
     * Get the plugin class for this metric
     * 
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    public function getPlugin()
    {
        return PluginManager::getManager()->getPluginInfo($this->plugin_name);
    }

    /**
     * Preform a scan on a uri
     *
     * @param string $uri - the uri to scan
     * @param \DOMXPath $xpath - the xpath of the uri
     * @param \Spider $spider - the spider object
     * @param Scan $scan - the current scan record
     * @param Site $site - the current site record
     * @param \SiteMaster\Core\Auditor\Site\Page $page - the current page record
     * @param int $depth - the current depth of the scan
     */
    public function preformScan($uri, \DOMXPath $xpath, \Spider $spider, Scan $scan, Site $site, Page $page, $depth)
    {
        //scan
        $completed = $this->scan($uri, $xpath, $spider, $scan, $site, $page, $depth);
        //grade the metric
        $this->grade($page, $completed);
    }
    
    public function grade(Page $page, $completed)
    {
        $grade = $this->getMetricGrade($page);

        $grade->pass_fail = 'NO';
        if ($this->isPassFail()) {
            $grade->pass_fail = 'YES';
        }
        
        if (!$completed) {
            $grade->incomplete = 'YES';
        }

        $marks = $page->getMarks($this->metric_record->id);
        
        //Compute the changes since the last scan
        $grade->changes_since_last_scan = $this->getChangesSinceLastScan($page, $marks);
        
        //Compute the grade
        $points = 100;
        foreach ($marks as $mark) {
            $points -= $mark->points_deducted;
        }
        
        //Make sure it bottoms out at zero
        if ($points < 0) {
            $points = 0;
        }
        
        $grade->grade = $points;
        
        //TODO: compute the letter grade

        if (!$grade->save()) {
            return false;
        }
        
        return $grade;
    }

    /**
     * Get the number of changes since the last scan
     * 
     * @param Page $page The current page
     * @param Page\Marks\AllForPageMetric $marks A list of marks for the current page
     * @return int The number of changes
     */
    public function getChangesSinceLastScan(Page $page, Page\Marks\AllForPageMetric $marks)
    {
        if (!$last_page_scan = $page->getPreviousScan()) {
            return $marks->count();
        }
        
        $previous_marks = $last_page_scan->getMarks($this->metric_record->id);
        return $previous_marks->count() - $marks->count();
    }

    /**
     * Get the metric grade for this page, create it if it does not exist.
     * 
     * @param $page
     * @return bool|Page\MetricGrade
     */
    public function getMetricGrade($page)
    {
        if ($grade = Page\MetricGrade::getByMetricIDAndScannedPageID($this->metric_record->id, $page->id)) {
            return $grade;
        }
        
        return Page\MetricGrade::CreateNewPageMetricGrade($this->metric_record->id, $page->id);
    }

    /**
     * Get a mark record for a machine name.  This method will create the record if it isn't found.
     * It will also update the record if it needs to
     * 
     * @param string $machine_name
     * @param string $name
     * @param int $point_deduction
     * @param string $description
     * @param string $help_text
     * @return bool|Metric\Mark
     */
    public function getMark($machine_name, $name, $point_deduction, $description = '', $help_text = '')
    {
        if (!$mark = Metric\Mark::getByMachineNameAndMetricID($machine_name, $this->metric_record->id)) {
            return Metric\Mark::createNewMark($this->metric_record->id, $machine_name, $name, array(
                'point_deduction' => $point_deduction,
                'description' => $description,
                'help_text' => $help_text
            ));
        }
        
        //check if we need to update the name and description
        $update = false;
        
        if ($mark->name != $name) {
            $mark->name = $name;
            $update = true;
        }
        
        if ($mark->description != $description) {
            $mark->description = $description;
            $update = true;
        }
        
        if ($update) {
            $mark->update();
        }
        
        return $mark;
    }
}