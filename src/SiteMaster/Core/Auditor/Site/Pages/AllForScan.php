<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\InvalidArgumentException;

class AllForScan extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('the scans_id must be set', 500);
        }
        
        parent::__construct($options);
    }
    
    public function getWhere()
    {
        return "WHERE scans_id = " . (int)$this->options['scans_id'];
    }

    /**
     * Get the scan
     *
     * @return false|Scan
     */
    public function getScan()
    {
        return Scan::getByID($this->options['scans_id']);
    }
}
