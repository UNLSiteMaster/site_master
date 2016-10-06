<?php
namespace SiteMaster\Core\Auditor\Metric\Marks;

use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class UniqueValueFound extends \ArrayIterator
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
    
    protected function getWhere()
    {
        $where = 'WHERE marks.metrics_id = ' . (int)$this->options['metrics_id'];
        
        if (isset($this->options['scans_id'])) {
            $where .= ' AND scanned_page.scans_id = ' . (int)$this->options['scans_id'];
        }
        
        return $where;
    }
    
    protected function query()
    {
        $db = Util::getDB();

        $sql = "SELECT marks.id, value_found
                FROM page_marks
                    LEFT JOIN marks ON (page_marks.marks_id = marks.id)
                    LEFT JOIN scanned_page ON (page_marks.scanned_page_id = scanned_page.id)
                " . $this->getWhere() . "
                GROUP BY value_found, marks.id
                ORDER BY id DESC";

        if (!$result = $db->query($sql)) {
            return array();
        }

        $marks = array();
        while ($row = $result->fetch_assoc()) {
            $marks[$row['value_found']] = $row['id'];
        }

        return $marks;
    }
}
