<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

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
}
