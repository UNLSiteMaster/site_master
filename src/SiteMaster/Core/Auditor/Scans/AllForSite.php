<?php
namespace SiteMaster\Core\Auditor\Scans;

use SiteMaster\Core\InvalidArgumentException;

class AllForSite extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['sites_id'])) {
            throw new InvalidArgumentException('the sites_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        $where = "WHERE sites_id = " . (int)$this->options['sites_id'];
        
        if (isset($this->options['not_id'])) {
            $where .= " AND id != " . (int)$this->options['not_id'];
        }
        
        return $where;
    }
}
