<?php
namespace SiteMaster\Core\Auditor\Site\History\SiteHistoryList;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ForSite extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['sites_id'])) {
            throw new InvalidArgumentException('A sites_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE sites_id = ' .(int)$this->options['sites_id'];
    }
}
