<?php
namespace SiteMaster\Core\Auditor\Site\Page\MetricGrades;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ChangesForScan extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('A scans_id must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
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
            . ' AND page_metric_grades.changes_since_last_scan != 0';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT page_metric_grades.id
                FROM page_metric_grades
                  JOIN scanned_page ON (page_metric_grades.scanned_page_id = scanned_page.id)
                " . $this->getWhere() . "
                ORDER BY page_metric_grades.weight ASC";

        return $sql;
    }
}
