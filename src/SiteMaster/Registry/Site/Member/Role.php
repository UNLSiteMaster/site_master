<?php
namespace SiteMaster\Registry\Site\Member;

use DB\Record;
use SiteMaster\Registry\Site\Member;

class Role extends Record
{
    public $id;               //int required
    public $site_members_id;  //int required fk -> site_members
    public $roles_id;         //int required fk -> roles

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_member_roles';
    }

    /**
     * Create a role for a site member
     * 
     * @param \SiteMaster\Registry\Site\Role $role
     * @param Member $member
     * @return bool
     */
    public static function createRole(\SiteMaster\Registry\Site\Role $role, Member $member)
    {
        $role = new self();
        $role->site_members_id = $member->id;
        $role->roles_id = $role->id;
        
        return $role->insert();
    }
}
