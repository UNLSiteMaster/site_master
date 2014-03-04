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

    public function getWhere()
    {
        return 'WHERE scanned_page.scans_id = ' .(int)$this->options['scans_id']
        . ' AND page_metric_grades.metrics_id = ' . (int)$this->options['metrics_id']
        . ' AND page_metric_grades.point_grade != page_metric_grades.points_available'
        . ' AND page_metric_grades.incomplete = "NO"';
    }
    
    public function getLimit()
    {
        $limit = '5';
        if (isset($options['scans_id'])) {
            $limit = $options['scans_id'];
        }
        
        return 'LIMIT ' . (int)$limit;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT page_metric_grades.id, max(scanned_page.id)
                FROM page_metric_grades
                  JOIN scanned_page ON (page_metric_grades.scanned_page_id = scanned_page.id)
                " . $this->getWhere() . "
                GROUP BY scanned_page.uri_hash
                ORDER BY page_metric_grades.point_grade ASC 
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
