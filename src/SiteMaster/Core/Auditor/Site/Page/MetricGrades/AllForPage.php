<?php
namespace SiteMaster\Core\Auditor\Site\Page\MetricGrades;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class AllForPage extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['scanned_page_id'])) {
            throw new InvalidArgumentException('A scanned_page_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE scanned_page_id = ' .(int)$this->options['scanned_page_id'];
    }
}
