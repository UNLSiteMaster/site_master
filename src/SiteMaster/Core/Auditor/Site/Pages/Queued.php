<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Queued extends All
{
    public function getLimit()
    {
        return 'LIMIT 1';
    }

    public function getOrderBy()
    {
        return 'ORDER BY priority ASC, start_time ASC';
    }
}
