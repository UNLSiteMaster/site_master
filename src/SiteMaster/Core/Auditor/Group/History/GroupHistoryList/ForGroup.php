<?php
namespace SiteMaster\Core\Auditor\Group\History\GroupHistoryList;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ForGroup extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['group_name'])) {
            throw new InvalidArgumentException('A group_name must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE group_name = "'.self::escapeString($this->options['group_name']).'"';
    }
}
