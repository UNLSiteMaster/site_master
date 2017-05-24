<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use DB\RecordList;
use InvalidArgumentException;

class Expired extends All
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE expires < NOW()';
    }
}
