<?php
namespace SiteMaster\Core\Auditor\Site\Page\Links;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ByOriginalURL extends All
{
    public function __construct(array $options = array())
    {
        if (!isset($options['original_url_hash'])) {
            throw new InvalidArgumentException('An original_url_hash must be set', 500);
        }

        parent::__construct($options);
    }

    public function getWhere()
    {
        $cached = '';
        if (isset($this->options['cached'])) {
            $cached = ' AND cached = ' . (int)$this->options['cached'];
        }
        
        return 'WHERE original_url_hash = "' . self::escapeString($this->options['original_url_hash']) . '"' . $cached;
    }
}
