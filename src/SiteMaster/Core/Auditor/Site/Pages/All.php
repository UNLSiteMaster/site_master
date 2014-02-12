<?php
namespace SiteMaster\Core\Auditor\Site\Pages;

use DB\RecordList;

class All extends RecordList
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
        return '';
    }

    public function getLimit()
    {
        return '';
    }
    
    public function getOrderBy()
    {
        return 'ORDER BY uri ASC';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT scanned_page.id
                FROM scanned_page
                " . $this->getWhere() . "
                " . $this->getOrderBy() . "
                " . $this->getLimit();

        return $sql;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Site\Page
     */
    function current() {
        return parent::current();
    }
}
