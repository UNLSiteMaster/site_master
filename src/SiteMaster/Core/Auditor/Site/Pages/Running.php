<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Running extends All
{
    public function getWhere()
    {
        return "WHERE status = 'RUNNING'";
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
