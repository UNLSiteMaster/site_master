<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

class Finished extends All
{
    public function getWhere()
    {
        return "WHERE status IN ('COMPLETE', 'ERROR')";
    }

    public function getLimit()
    {
        return '';
    }

    public function getOrderBy()
    {
        return 'ORDER BY end_time DESC';
    }
}
