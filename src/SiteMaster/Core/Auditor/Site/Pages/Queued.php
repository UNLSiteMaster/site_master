<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use DB\RecordList;

class Queued extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
        
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
    
    public function getWhere()
    {
        $sql = "WHERE status = 'QUEUED'";
        
        if (isset($this->options['scans_id'])) {
            $sql .= " AND scans_id = " . (int)$this->options['scans_id'];
        }
        
        return $sql;
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
                " . $this->getWhere() . "
                ORDER BY priority ASC, start_time ASC
                LIMIT " . (int)$this->getLimit();

        return $sql;
    }
}