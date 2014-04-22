<?php
namespace SiteMaster\Core\Auditor\Site\Page\MetricGrades;

use DB\RecordList;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\InvalidArgumentException;

class ForScanAndMetric extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('A scans_id must be set', 500);
        }

        if (!isset($options['metrics_id'])) {
            throw new InvalidArgumentException('A metrics_id must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true,
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page\MetricGrade';
        $options['listClass'] = __CLASS__;

        return $options;
    }
    
    public function getLimit()
    {
        $limit = '-1';
        if (isset($this->options['limit'])) {
            $limit = $this->options['limit'];
        }

        if ($limit == -1) {
            return '';
        }
        
        return 'LIMIT ' . (int)$limit;
    }
    
    public function getOrderBy()
    {
        if (isset($this->options['order_by_marks'])) {
            return 'ORDER BY total_marks DESC';
        }
        
        return 'ORDER BY page_metric_grades.point_grade ASC';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT page_metric_grades.id as id, page_marks.total as total_marks
                FROM page_metric_grades
                /* Grab the newest page_metric_grades record for each uri_hash */
                JOIN (
                    SELECT MAX(page_metric_grades.id) as id
                    FROM page_metric_grades
                    JOIN scanned_page ON (page_metric_grades.scanned_page_id = scanned_page.id)
                    WHERE scanned_page.scans_id = " . (int)$this->options['scans_id'] . "
                    AND page_metric_grades.metrics_id = " . (int)$this->options['metrics_id'] . "
                    AND page_metric_grades.incomplete = 'NO'
                    GROUP BY scanned_page.uri_hash
                ) as grades ON (grades.id = page_metric_grades.id)
                JOIN (
                    SELECT count(pm.id) AS total, sp.id
                    FROM page_marks pm
                    JOIN scanned_page sp ON pm.scanned_page_id = sp.id AND sp.scans_id = " . (int)$this->options['scans_id'] . "
                    JOIN marks m ON pm.marks_id = m.id AND m.metrics_id = " . (int)$this->options['metrics_id'] . "
                    GROUP BY sp.id 
                ) as page_marks ON page_marks.id = page_metric_grades.scanned_page_id
                WHERE
                    #Only select metric grades with marks
                    page_marks.total > 0
                 " . $this->getOrderBy() . "
                 " . $this->getLimit();

        return $sql;
    }

    /**
     * Get the scan
     * 
     * @return false|Scan
     */
    public function getScan()
    {
        return Scan::getByID($this->options['scans_id']);
    }
}
