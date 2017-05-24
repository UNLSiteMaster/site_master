<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use DB\RecordList;
use InvalidArgumentException;

class AllForSite extends All
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['sites_id'])) {
            throw new InvalidArgumentException('An sites_id must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE sites_id = ' . (int)$this->options['sites_id'];
    }
}
