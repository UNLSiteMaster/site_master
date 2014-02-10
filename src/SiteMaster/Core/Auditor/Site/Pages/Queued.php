<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use DB\RecordList;

class Queued extends RecordList
{
    public function __construct(array $options = array())
    {
        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page';
        $options['listClass'] = __CLASS__;

        return $options;
    }
    
    public function getLimit()
    {
        return 1;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT scanned_page.id
                FROM scanned_page
                WHERE status = 'QUEUED'
                ORDER BY priority ASC, start_time ASC
                LIMIT " . (int)$this->getLimit();

        return $sql;
    }
}
