<?php
namespace SiteMaster\Core\Auditor\Scans;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;

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
                WHERE scans.status = '" . Scan::STATUS_QUEUED . "'
                    AND scanned_page.status = '" . Page::STATUS_QUEUED ."'
                GROUP BY scans.id
                ORDER BY MIN(scanned_page.priority) ASC, MIN(scanned_page.date_created) ASC";

        return $sql;
    }

    /**
     * Get the position in the queue for a given scan
     * 
     * @param Scan $scan
     * @return mixed
     */
    public function getPositionOfScan(\SiteMaster\Core\Auditor\Scan $scan)
    {
        return array_search($scan->id, $this->getInnerIterator()->getArrayCopy());
    }
}
