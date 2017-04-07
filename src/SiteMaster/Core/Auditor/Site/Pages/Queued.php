<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Queued extends All
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }
    
    public function getWhere()
    {
        $where = "WHERE status = 'QUEUED'";
        
        if (isset($this->options['scans_id'])) {
            $where .= " AND scans_id = " . (int)$this->options['scans_id'];
        }

        if (isset($this->options['not_in_scans']) && is_array($this->options['not_in_scans'])) {
            $ids = array_map(function($val) {
                //Cast to an int to prevent injection attacks
                return (int)$val;
            }, $this->options['not_in_scans']);
            $where .= " AND scans_id NOT IN (" . explode(',', $ids) . ")";
        }
        
        return $where;
    }
    
    public function getLimit()
    {
        if (isset($this->options['limit'])) {
            if ($this->options['limit'] == '-1') {
                //Don't use a limit
                return '';
            }
            return 'LIMIT ' . (int)$this->options['limit'];
        }
        
        //default to 1
        return 'LIMIT 1';
    }

    public function getOrderBy()
    {
        return 'ORDER BY priority ASC, date_created ASC';
    }
}
