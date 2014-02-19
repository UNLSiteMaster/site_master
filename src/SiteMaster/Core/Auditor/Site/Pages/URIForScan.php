<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use SiteMaster\Core\InvalidArgumentException;

class URIForScan extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('the scans_id must be set', 500);
        }

        if (!isset($options['uri'])) {
            throw new InvalidArgumentException('the uri must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return "WHERE scans_id = " . (int)$this->options['scans_id'] . "
            AND uri_hash = '" . self::escapeString(md5($this->options['uri'])) . "'";
    }

    public function getOrderBy()
    {
        //We want the newest first
        return 'ORDER BY date_created DESC';
    }
}
