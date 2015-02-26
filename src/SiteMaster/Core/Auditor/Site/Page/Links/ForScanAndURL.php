<?php
namespace SiteMaster\Core\Auditor\Site\Page\Links;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ForScanAndURL extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['scans_id'])) {
            throw new InvalidArgumentException('An scans_id must be set', 500);
        }

        if (!isset($options['url'])) {
            throw new InvalidArgumentException('A url must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        return 'WHERE
          (original_url_hash = "' . self::escapeString(md5($this->options['url'], true)) . '"
          OR final_url_hash = "' . self::escapeString(md5($this->options['url'], true)) .'")
          AND scans.id = ' . (int)$this->options['scans_id'];
    }
}
