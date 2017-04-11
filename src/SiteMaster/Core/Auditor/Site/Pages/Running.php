<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Running extends All
{
    public function getWhere()
    {
        $where = "WHERE status = 'RUNNING'";

        if (isset($this->options['daemon_name'])) {
            $where .= " AND daemon_name = '" . self::escapeString($this->options['daemon_name']). "'";
        }
        
        return $where;
    }

    public function getLimit()
    {
        return '';
    }

    public function getOrderBy()
    {
        return 'ORDER BY priority ASC, start_time ASC';
    }
}
