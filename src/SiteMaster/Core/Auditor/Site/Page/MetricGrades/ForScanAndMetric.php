<?php
namespace SiteMaster\Core\Auditor\Site\Page\MetricGrades;

use DB\RecordList;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\InvalidArgumentException;

class ForScanAndMetric extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('A scans_id must be set', 500);
        }

        if (!isset($options['metrics_id'])) {
            throw new InvalidArgumentException('A metrics_id must be set', 500);
        }

        parent::__construct($options);
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
            return 'ORDER BY total_errors DESC';
        }
        
        return 'ORDER BY page_metric_grades.point_grade ASC';
    }
    
    public function getWhere()
    {
        $where = 'page_metric_grades.num_errors > 0';
        if (!isset($this->options['include_notices']) || $this->options['include_notices'] == true) {
            $where .= ' OR page_metric_grades.num_notices > 0';
        }
        
        return $where;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT page_metric_grades.id as id, page_metric_grades.num_errors as total_errors, page_metric_grades.num_notices as total_notices
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
                WHERE
                 " . $this->getWhere() . "
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
