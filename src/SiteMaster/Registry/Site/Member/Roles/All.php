<?php
namespace SiteMaster\Registry\Site\Member\Roles;

use DB\RecordList;
use SiteMaster\InvalidArgumentException;

class All extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['member_id'])) {
            throw new InvalidArgumentException('A member_id must be set', 500);
        }
        
        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL($options['member_id']),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Registry\Site\Member\Role';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($member_id)
    {
        //Build the list
        $sql = "SELECT site_member_roles.id
                FROM site_member_roles
                WHERE site_members_id = " . (int)$member_id;

        return $sql;
    }
}
