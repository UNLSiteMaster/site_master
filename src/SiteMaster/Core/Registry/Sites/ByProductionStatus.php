<?php
namespace SiteMaster\Core\Registry\Sites;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ByProductionStatus extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
        
        if (!isset($this->options['production_status'])) {
            throw new InvalidArgumentException('production_status is required', 500);
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
                WHERE production_status = '" . self::escapeString($this->options['production_status']) . "'
                ORDER BY sites.base_url ASC";

        return $sql;
    }
}
