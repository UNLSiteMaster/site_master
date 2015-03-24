<?php
namespace SiteMaster\Core\Auditor\Scans;

/**
 * This class will compile a list of all scans that are queued, in the order in which they will be processed.
 * 
 * Class Queued
 * @package SiteMaster\Core\Auditor\Scans
 */
class Queued extends All
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT scans.id as id
                FROM scanned_page
                LEFT JOIN scans ON (scanned_page.scans_id = scans.id)
                WHERE scans.status = 'QUEUED'
                GROUP BY scans.id
                ORDER BY scanned_page.priority ASC, scanned_page.date_created ASC";

        return $sql;
    }
}
