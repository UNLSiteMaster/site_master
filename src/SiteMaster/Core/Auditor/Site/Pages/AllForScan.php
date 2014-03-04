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

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT max(id) as id
                    FROM scanned_page
                    WHERE scanned_page.scans_id = " . (int)$this->options['scans_id'] . "
                    GROUP BY uri_hash
                    ORDER BY scanned_page.date_created DESC";

        return $sql;
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
