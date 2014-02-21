<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use SiteMaster\Core\InvalidArgumentException;

class WithURI extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['uri'])) {
            throw new InvalidArgumentException('the uri must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        $where = "WHERE  uri_hash = '" . self::escapeString(md5($this->options['uri'], true)) . "'";

        if (isset($this->options['not_id'])) {
            $where .= " AND scanned_page.id != " . (int)$this->options['not_id'];
        }

        return $where;
    }

    public function getLimit()
    {
        if (isset($this->options['limit'])) {
            return 'LIMIT ' . (int)$this->options['limit'];
        }

        return '';
    }

    public function getOrderBy()
    {
        //We want the newest first
        return 'ORDER BY date_created DESC';
    }
}
