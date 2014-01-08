<?php
namespace SiteMaster\Registry\Site\Members;

use DB\RecordList;
use SiteMaster\Exception;

class WithRole extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['site_id'])) {
            throw new Exception('A site_id must be set', 500);
        }

        if (!isset($options['role_id'])) {
            throw new Exception('A role_id must be set', 500);
        }

        $options['sql'] = $this->getSQL($options['site_id'], $options['role_id']);

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Registry\Site\Member';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($site_id, $role_id)
    {
        //Build the list
        $sql = "SELECT id
                FROM site_members
                LEFT JOIN site_member_roles ON (site_members.id = site_member_roles.site_members_id)
                WHERE sites_id = " .  (int)$site_id . "
                AND site_members.status = 'PENDING'
                AND site_member_roles.roles_id = " . (int)$role_id;

        return $sql;
    }
}