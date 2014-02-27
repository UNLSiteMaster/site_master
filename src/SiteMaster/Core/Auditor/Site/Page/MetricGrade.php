<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Registry\Site\Member;

class MetricGrade extends Record
{
    public $id;                      //int required
    public $metrics_id;              //fk for metrics.id NOT NULL
    public $scanned_page_id;         //fk for scanned_page.id NOT NULL
    public $points_available;        //DECIMAL(5,2) NOT NULL DEFAULT=100 The total number of points available for the metric
    public $weighted_grade;          //DECIMAL(5,2) NOT NULL DEFAULT=0
    public $point_grade;             //DECIMAL(5,2) NOT NULL DEFAULT=0
    public $changes_since_last_scan; //INT NOT NULL DEFAULT=0
    public $pass_fail;               //ENUM('YES', 'NO') NOT NULL default='NO'
    public $incomplete;              //ENUM('YES', 'NO') NOT NULL DEFAULT='NO'.  'YES' if the metric was unable to complete
    public $letter_grade;            //VARCHAR(2) for historic tracking of the letter grade in case the scale changes
    public $weight;                  //DOUBLE(2,2) NOT NULL default=0, % of total page grade

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'page_metric_grades';
    }

    /**
     * Get a Page Metric grade by the page ID and metric ID
     *
     * @param int $metric_id the id of the metric
     * @param int $scanned_page_id the id of the page
     * @return bool|MetricGrade
     */
    public static function getByMetricIDAndScannedPageID($metric_id, $scanned_page_id)
    {
        return self::getByAnyField(__CLASS__, 'metrics_id', $metric_id, 'scanned_page_id=' . (int)$scanned_page_id);
    }

    /**
     * Create a new Page Metric Grade
     *
     * @param int $metrics_id the id of a metric that this grade belongs to
     * @param int $scanned_page_id the id of the page that this metric belongs to
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|MetricGrade
     */
    public static function createNewPageMetricGrade($metrics_id, $scanned_page_id, array $fields = array())
    {
        $metric_grade = new self();
        $metric_grade->pass_fail = 'NO';
        $metric_grade->incomplete = 'NO';
        $metric_grade->points_available = 100;
        $metric_grade->weighted_grade = 0;
        $metric_grade->point_grade = 0;
        $metric_grade->weight = 0;
        $metric_grade->changes_since_last_scan = 0;

        $metric_grade->synchronizeWithArray($fields);
        $metric_grade->metrics_id      = $metrics_id;
        $metric_grade->scanned_page_id = $scanned_page_id;

        if (!$metric_grade->insert()) {
            return false;
        }

        return $metric_grade;
    }

    /**
     * Get the percent grade for this metric grade
     * 
     * @return float|int
     */
    public function getPercentGrade()
    {
        if ($this->points_available == 0) {
            return 0;
        }
        
        return round(($this->point_grade / $this->points_available) * 100, 2);
    }

    /**
     * Determine if this grade is an incomplete
     * 
     * @return bool
     */
    public function isIncomplete()
    {
        if ($this->incomplete == 'YES') {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if this grade is pass/fail
     * 
     * @return bool
     */
    public function isPassFail()
    {
        if ($this->pass_fail == 'YES') {
            return true;
        }
        
        return false;
    }

    /**
     * Get the page record
     * 
     * @return mixed
     */
    public function getPage()
    {
        return Page::getByID($this->scanned_page_id);
    }

    /**
     * Get the metric record
     *
     * @return mixed
     */
    public function getMetric()
    {
        return Metric::getByID($this->metrics_id);
    }

    /**
     * Get all marks for this grade
     * 
     * @return Marks\AllForPageMetric
     */
    public function getMarks()
    {
        return new Marks\AllForPageMetric(
            array(
                'scanned_page_id' => $this->scanned_page_id,
                'metrics_id' => $this->metrics_id
            )
        );
    }
}
