<?php
namespace SiteMaster\Core\Auditor\Scans;

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
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Scan';
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
        return 'ORDER BY date_created DESC';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT scans.id
                FROM scans
                " . $this->getWhere() . "
                " . $this->getOrderBy() . "
                " . $this->getLimit();

        return $sql;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Scan
     */
    function current() {
        return parent::current();
    }
}
