<?php
namespace SiteMaster\Core\Auditor\Site\Reviews;

use SiteMaster\Core\Auditor\Site\Review;

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
                WHERE site_reviews.status != '" . Review::STATUS_REVIEW_FINISHED ."'
                ORDER BY site_reviews.status DESC, site_reviews.date_scheduled DESC";

        return $sql;
    }
}
