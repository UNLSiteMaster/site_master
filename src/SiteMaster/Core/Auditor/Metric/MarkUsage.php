<?php
namespace SiteMaster\Core\Auditor\Metric;

use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Registry\Site\Member;

class MarkUsage
{
    protected $mark_id;
    
    protected $count;
    
    public function __construct($mark_id, $count)
    {
        $this->mark_id = $mark_id;
        $this->count   = $count;
    }
    
    public function getMark()
    {
        return Mark::getByID($this->mark_id);
    }
    
    public function getCount()
    {
        return $this->count;
    }
}
