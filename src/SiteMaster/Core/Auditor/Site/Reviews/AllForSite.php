<?php
namespace SiteMaster\Core\Auditor\Site\Reviews;

use SiteMaster\Core\InvalidArgumentException;

class AllForSite extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['sites_id'])) {
            throw new InvalidArgumentException('the sites_id must be set', 500);
        }

        parent::__construct($options);
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT id as id
                    FROM site_reviews
                    WHERE site_reviews.sites_id = " . (int)$this->options['sites_id'] . "
                    ORDER BY site_reviews.date_scheduled DESC";

        return $sql;
    }
}
