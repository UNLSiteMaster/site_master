<?php
namespace SiteMaster\Core\Registry\Site\Roles;

use DB\RecordList;

class All extends RecordList
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
        $options['itemClass'] = '\SiteMaster\Core\Registry\Site\Role';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT id
                FROM roles
                ORDER by max_number_per_site asc, role_name ASC";

        return $sql;
    }
}
