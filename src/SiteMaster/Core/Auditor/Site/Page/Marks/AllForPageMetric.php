<?php
namespace SiteMaster\Core\Auditor\Site\Page\Marks;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class AllForPageMetric extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['scanned_page_id'])) {
            throw new InvalidArgumentException('A scanned_page_id must be set', 500);
        }

        if (!isset($options['metrics_id'])) {
            throw new InvalidArgumentException('A metrics_id must be set', 500);
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
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page\Mark';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getWhere()
    {
        return 'WHERE scanned_page_id = ' .(int)$this->options['scanned_page_id'] . '
                   AND marks.metrics_id = ' . (int)$this->options['metrics_id'] ;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT page_marks.id
                FROM page_marks
                JOIN marks ON (page_marks.marks_id = marks.id)
                " . $this->getWhere() . "
                ORDER BY page_marks.marks_id ASC";

        return $sql;
    }
}
