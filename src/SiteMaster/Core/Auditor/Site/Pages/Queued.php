<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Queued extends All
{
    public function getWhere()
    {
        $where = "WHERE status = 'QUEUED'";
        
        if (isset($this->options['scans_id'])) {
            $where .= " AND scans_id = " . (int)$this->options['scans_id'];
        }
        
        return $where;
    }
    
    public function getLimit()
    {
        return 'LIMIT 1';
    }

    public function getOrderBy()
    {
        return 'ORDER BY priority ASC, start_time ASC';
    }
}
