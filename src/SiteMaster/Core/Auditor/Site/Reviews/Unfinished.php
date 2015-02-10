<?php
namespace SiteMaster\Core\Auditor\Site\Reviews;

use SiteMaster\Core\InvalidArgumentException;

class Unfinished extends All
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT id as id
                FROM site_reviews
                WHERE site_reviews.status != 'REVIEW_FINISHED'
                ORDER BY site_reviews.status DESC, site_reviews.date_scheduled DESC";

        return $sql;
    }

    public function getWhere()
    {
        return "WHERE scans_id = " . (int)$this->options['scans_id'];
    }
}
