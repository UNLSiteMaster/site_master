<?php
namespace SiteMaster\Registry\Sites;

use DB\RecordList;

class All extends RecordList
{
    public function __construct(array $options = array())
    {
        $options['sql'] = $this->getSQL();

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Registry\Site';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT id
                FROM sites";

        return $sql;
    }
}
