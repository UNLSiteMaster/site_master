<?php
namespace SiteMaster\Core\Auditor\Group\History\MetricHistoryList;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ForGroupHistory extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['group_scan_history_id'])) {
            throw new InvalidArgumentException('A group_scan_history_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE group_scan_history_id = ' .(int)$this->options['group_scan_history_id'];
    }
}
