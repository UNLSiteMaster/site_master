<?php
namespace SiteMaster\Core\Auditor\Metric\Marks;

use SiteMaster\Core\Auditor\Metric\MarkUsage;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class FrequentForMetric extends \ArrayIterator
{
    public $options = array();

    public function __construct($options = array())
    {
        $this->options = $this->options + $options;

        if (!isset($this->options['metrics_id'])) {
            throw new InvalidArgumentException('a metrics_id must be set', 500);
        }

        $data = $this->query();

        parent::__construct($data);
    }

    protected function query()
    {
        //Get all marks from and the number of times they have been found for the most recent scan of all sites
        $sql = '
          SELECT COUNT(marks_id) as count, page_marks.marks_id as marks_id
            FROM page_marks
              JOIN scanned_page ON (scanned_page.id = page_marks.scanned_page_id)
              JOIN (SELECT MAX(scans.id) as id
                    FROM scanned_page
                      JOIN scans on (scanned_page.scans_id = scans.id)
                    WHERE scans.status = "' . Scan::STATUS_COMPLETE .'"
                    GROUP BY scans.sites_id
                   ) as completed_scans ON completed_scans.id = scanned_page.scans_id
              JOIN marks ON (page_marks.marks_id = marks.id)
              WHERE marks.metrics_id = ' . (int)$this->options['metrics_id'] . '
            GROUP BY page_marks.marks_id
            ORDER BY count DESC
        ';

        $db = Util::getDB();

        if (!$result = $db->query($sql)) {
            return array();
        }

        $marks = $result->fetch_all(MYSQLI_ASSOC);
        
        return $marks;
    }

    function current() {
        $row = parent::current();
        return new MarkUsage($row['marks_id'], $row['count']);
    }
}
