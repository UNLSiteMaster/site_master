<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Util;

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
        $sanitized_url = Page::sanitizeURI($this->options['uri']);
        
        $opposite_url = Util::makeAgnostic($sanitized_url);
        $scheme = parse_url($sanitized_url, PHP_URL_SCHEME);
        
        if ($scheme == 'http') {
            $opposite_url = 'https:'.$opposite_url;
        } else {
            $opposite_url = 'http:'.$opposite_url;
        }
        
        //Make it agnostic, so that the same page isn't scanned twice
        $where = "WHERE scans_id = " . (int)$this->options['scans_id'] . "
            AND (
              uri_hash = '" . self::escapeString(md5($sanitized_url, true)) . "'
              OR uri_hash = '" . self::escapeString(md5($opposite_url, true)) . "'
            )";
        
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
