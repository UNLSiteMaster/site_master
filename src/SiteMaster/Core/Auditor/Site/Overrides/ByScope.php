<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use DB\RecordList;
use InvalidArgumentException;

class ByScope extends All
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['scope'])) {
            throw new InvalidArgumentException('An scope must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getWhere()
    {
        $sql = 'WHERE scope = "' . self::escapeString($this->options['scope']) . '"';
        
        if (isset($this->options['marks_id'])) {
            $sql .= PHP_EOL . ' AND marks_id = ' . (int)$this->options['marks_id'];
        }
        
        return $sql;
    }
}
