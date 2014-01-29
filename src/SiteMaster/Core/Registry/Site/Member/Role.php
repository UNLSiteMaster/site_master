<?php
namespace SiteMaster\Core\Registry\Site\Member;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;

class Role extends Record
{
    public $id;               //int required
    public $site_members_id;  //int required fk -> site_members
    public $roles_id;         //int required fk -> roles
    public $approved;         //ENUM('YES', 'NO') default = NO

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_member_roles';
    }
    
    public static function getByRoleIDANDMembershipID($role_id, $membership_id)
    {
        return self::getByAnyField(__CLASS__, 'site_members_id', $membership_id, 'roles_id=' .(int)$role_id);
    }

    /**
     * Create a role for a site member
     *
     * @param \SiteMaster\Core\Registry\Site\Role $role
     * @param Member $member
     * @param array $fields
     * @return bool
     */
    public static function createRoleForSiteMember(\SiteMaster\Core\Registry\Site\Role $role, Member $member, $fields = array())
    {
        $membership_role = new self();
        $membership_role->approved = 'NO';
        $membership_role->synchronizeWithArray($fields);
        $membership_role->site_members_id = $member->id;
        $membership_role->roles_id = $role->id;
        
        return $membership_role->insert();
    }

    /**
     * @return bool|\SiteMaster\Core\Registry\Site\Role
     */
    public function getRole()
    {
        return \SiteMaster\Core\Registry\Site\Role::getByID($this->roles_id);
    }

    /**
     * Approve this role
     */
    public function approve()
    {
        $this->approved = 'YES';
        $this->save();
    }
}
