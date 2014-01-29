<?php
namespace SiteMaster\Core\Registry\Sites;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class ApprovedForUser extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['user_id'])) {
            throw new InvalidArgumentException('A user_id must be set', 500);
        }
        
        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL($options['user_id']),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Registry\Site';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($user_id)
    {
        //Build the list
        $sql = "SELECT sites.id
                FROM sites
                LEFT JOIN site_members ON (site_members.sites_id = sites.id)
                LEFT JOIN site_member_roles on (site_member_roles.site_members_id = site_members.id)
                WHERE site_members.users_id = " . (int)$user_id ."
                    AND site_member_roles.approved = 'YES'
                GROUP BY sites.id
                ORDER BY sites.base_url ASC";

        return $sql;
    }
}
