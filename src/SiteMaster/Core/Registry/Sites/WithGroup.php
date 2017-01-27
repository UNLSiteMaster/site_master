<?php
namespace SiteMaster\Core\Registry\Sites;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class WithGroup extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($this->options['group_name'])) {
            throw new InvalidArgumentException('group_name is required', 500);
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
        $options['itemClass'] = '\SiteMaster\Core\Registry\Site';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT sites.id
                FROM sites
                WHERE group_name = '" . self::escapeString($this->options['group_name']) . "'
                ORDER BY sites.base_url ASC";

        return $sql;
    }
}
