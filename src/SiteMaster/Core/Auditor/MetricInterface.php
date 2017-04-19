<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;

abstract class MetricInterface
{
    const HEADLESS_SCRIPT_NAME = 'headless.js';
    
    public $options;
    public $plugin_name;
    protected $headless_results = false;

    /**
     * @param string $plugin_name (The plugin machine name for this metric)
     * @param array $options an array of options (usually the same options that were passed to the plugin)
     */
    public function __construct($plugin_name, array $options = array())
    {
        $this->plugin_name = $plugin_name;
        $this->options     = $options;
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
     * @param int $depth - the current depth of the scan
     * @param \SiteMaster\Core\Auditor\Site\Page $page - the current page to scan
     * @param Logger\Metrics $logger The logger class which calls this method, you can access the spider, page, and scan from this
     * @return bool True if there was a successful scan, false if not.  If false, the metric will be graded as incomplete
     */
    abstract public function scan($uri, \DOMXPath $xpath, $depth, Page $page, Logger\Metrics $logger);

    /**
     * Get the weight of this metric as defined in the configuration.
     * If it was not defined, return 0.
     * 
     * @return int The weight
     */
    public function getWeight()
    {
        $weight = 0;
        
        if (isset($this->options['weight'])) {
            $weight = $this->options['weight'];
        }
        
        return $weight;
    }

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
        $metric = Metric::createNewMetric($this->getMachineName());
        
        return $metric;
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
     * @param int $depth - the current depth of the scan
     * @param \SiteMaster\Core\Auditor\Site\Page $page - the current page record
     * @param Logger\Metrics $logger
     * @param array $headless_results
     */
    public function performScan($uri, \DOMXPath $xpath, $depth, Page $page, Logger\Metrics $logger, $headless_results)
    {
        try {
            //scan
            $this->headless_results = $headless_results;
            $completed = $this->scan($uri, $xpath, $depth, $page, $logger);
        } catch (\Exception $exception) {
            //Some sort of error occurred.  Mark this metric as incomplete
            $completed = false;
            Util::log(
                \Monolog\Logger::NOTICE,
                'Metric exception thrown for ' . $this->getMachineName(),
                array(
                    'uri', $uri,
                    'exception' => (string)$exception,
                )
            );
        }
        
        //grade the metric
        $this->grade($page, $completed);
    }

    /**
     * Grade this metric
     * 
     * @param Page $page the page record
     * @param bool $completed flag for if the scan was able to finish successfully
     * @return bool|Page\MetricGrade
     */
    public function grade(Page $page, $completed)
    {
        $grade = $this->getMetricGrade($page);

        $grade->pass_fail = 'NO';
        if ($this->isPassFail()) {
            $grade->pass_fail = 'YES';
        }
        
        $scan = $page->getScan();
        if ($scan->isPassFail()) {
            //The grading method for the scan is pass/fail...
            $grade->pass_fail = 'YES';
        }
        
        if (!$completed) {
            $grade->incomplete = 'YES';
        }
        
        $metric_record = $this->getMetricRecord();

        $marks = $page->getMarks($metric_record->id);

        $last_page_scan = $page->getPreviousScan();

        $count_before = 0;
        if ($last_page_scan) {
            $previous_marks = $last_page_scan->getMarks($metric_record->id);
            $count_before = $previous_marks->count();
        }
        
        //Compute the changes since the last scan
        $grade->changes_since_last_scan = $this->getChangesSinceLastScan($count_before, $marks->count());
        
        //Compute percent and weighted grade
        $grade->point_grade = $this->computePointGrade($grade, $marks);
        $grade->weighted_grade = $this->computeWeightedGrade($grade->point_grade, $grade->points_available, $grade->weight);
        
        //Compute the letter grade
        $grade->letter_grade = $this->computeLetterGrade($grade);

        //Store the number of errors and notices to make them easier to access (less complicated and faster sql queries)
        $errors  = $grade->getErrors();
        $notices = $grade->getNotices();

        $grade->num_errors  = (int)$errors->count();
        $grade->num_notices = (int)$notices->count();

        if (!$grade->save()) {
            return false;
        }
        
        return $grade;
    }

    /**
     * Compute the weighted grade for this metric
     *
     * @param double $point_grade the total points earned
     * @param double $points_available the total available points that could be earned
     * @param double $weight the weight of the grade
     * @return double the computed weighted grade
     */
    public function computeWeightedGrade($point_grade, $points_available, $weight)
    {
        if ($points_available == 0) {
            return 0;
        }
        
        return round($weight * ($point_grade / $points_available), 2);
    }

    /**
     * Compute the point grade of the page scan
     * 
     * @param Page\MetricGrade $grade the metric grade record
     * @param Page\Marks\AllForPageMetric $marks the list of marks for the record
     * @return int the point grade
     */
    public function computePointGrade(Page\MetricGrade $grade, Page\Marks\AllForPageMetric $marks)
    {
        //Compute the grade
        $points = $grade->points_available;
        foreach ($marks as $mark) {
            if ($mark->points_deducted < 0) {
                //Skip points awarded, if you want to include the number of awarded points you can dynamically set the `points_available`
                //See the example metric for details
                continue;
            }
            $points -= $mark->points_deducted;
        }

        //Make sure it bottoms out at zero
        if ($points < 0) {
            $points = 0;
        }
        
        //Handle pass/fail
        if ($grade->isPassFail()) {
            if ($points != $grade->points_available) {
                //Return 0 if they did not get 100%
                return 0;
            }
        }
        
        return $points;
    }

    /**
     * Determine the letter grade for a metric grade
     * 
     * @param Page\MetricGrade $grade
     * @return string the letter grade
     */
    public function computeLetterGrade(Page\MetricGrade $grade)
    {
        $grade_helper = new GradingHelper();
        
        if ($grade->isIncomplete()) {
            return GradingHelper::GRADE_INCOMPLETE;
        }
        
        if ($grade->isPassFail()) {
            if ($grade->point_grade != $grade->points_available) {
                return GradingHelper::GRADE_NO_PASS;
            }

            return GradingHelper::GRADE_PASS;
        }

        return $grade_helper->convertPercentToLetterGrade($grade->getPercentGrade());
    }

    /**
     * Get the number of changes since the last scan
     * A positive result means there were that many more marks in the new scan
     * A negative result means there were that many less marks
     * A zero result means there were no changes
     *
     * @param int $count_before the total number of marks from the last page scan
     * @param int $new_count the total number of marks from the new page scan
     * @return int The number of changes
     */
    public function getChangesSinceLastScan($count_before, $new_count)
    {
        //If there were no marks last time, return the new count
        if ($count_before == 0) {
            return $new_count;
        }
        
        //Calculate the changes.  Ensure a positive number
        $changes = abs($count_before - $new_count);
        
        if ($count_before > $new_count) {
            //Change to negative if there are now less changes
            $changes = -1 * $changes;
        }
        
        return $changes;
    }

    /**
     * Get the metric grade for this page, create it if it does not exist.
     * 
     * @param Page $page the apge record
     * @return bool|Page\MetricGrade
     */
    public function getMetricGrade(Page $page)
    {
        $metric_record = $this->getMetricRecord();
        if ($grade = Page\MetricGrade::getByMetricIDAndScannedPageID($metric_record->id, $page->id)) {
            return $grade;
        }
        
        return Page\MetricGrade::createNewPageMetricGrade($metric_record->id, $page->id, array(
            'weight' => $this->getWeight()
        ));
    }

    /**
     * Get a mark record for a machine name.  This method will create the record if it isn't found.
     * It will also update the record if it needs to
     * 
     * @param string $machine_name the machine name of the mark
     * @param string $name the human readable name of the mark
     * @param double $point_deduction the point deduction
     * @param string $description the description of the mark
     * @param string $help_text the help text for the mark
     * @return bool|Metric\Mark the mark
     */
    public function getMark($machine_name, $name, $point_deduction, $description = '', $help_text = '')
    {
        if (!$mark = Metric\Mark::getByMachineNameAndMetricID($machine_name, $this->getMetricRecord()->id)) {
            return Metric\Mark::createNewMark($this->getMetricRecord()->id, $machine_name, $name, array(
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
        
        if ($mark->point_deduction != $point_deduction) {
            $mark->point_deduction = $point_deduction;
            $update = true;
        }
        
        if ($mark->help_text != $help_text) {
            $mark->help_text = $help_text;
            $update = true;
        }
        
        if ($update) {
            $mark->update();
        }
        
        return $mark;
    }

    /**
     * Format a value_found for presentation
     * 
     * @param $machine_name
     * @param $value_found
     * @return mixed
     */
    function formatValueFound($machine_name, $value_found)
    {
        return $value_found;
    }

    /**
     * Get the headless script name
     * 
     * If this metric does not implement it, return false
     * 
     * @return bool|string
     */
    public function getHeadlessScript()
    {
        $file = $this->getPlugin()->getRootDirectory() . '/' . self::HEADLESS_SCRIPT_NAME;
        
        if (file_exists($file)) {
            return $file;
        }
        
        return false;
    }
}