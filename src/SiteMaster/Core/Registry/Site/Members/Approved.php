<?php
namespace SiteMaster\Core\Registry\Site\Members;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class Approved extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['site_id'])) {
            throw new InvalidArgumentException('A site_id must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL($options['site_id']),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Registry\Site\Member';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($site_id)
    {
        //Build the list
        $sql = "SELECT site_members.id
                FROM site_members
                LEFT JOIN site_member_roles ON (site_members.id = site_member_roles.site_members_id)
                LEFT JOIN users ON (site_members.users_id = users.id)
                WHERE sites_id = " .  (int)$site_id . "
                    AND site_member_roles.approved = 'YES'
                GROUP BY site_members.id
                ORDER by users.first_name ASC";

        return $sql;
    }
}
