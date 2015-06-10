<?php
namespace SiteMaster\Core\Auditor\Site\History\MetricHistoryList;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ForSiteHistory extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['site_scan_history_id'])) {
            throw new InvalidArgumentException('A site_scan_history_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE site_scan_history_id = ' .(int)$this->options['site_scan_history_id'];
    }
}
