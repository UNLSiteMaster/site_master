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
    public $source;           //varchar(64) default = null

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_member_roles';
    }

    /**
     * @param $role_id - either the id of the role or it's name
     * @param $membership_id
     * @return bool
     */
    public static function getByRoleIDANDMembershipID($role_id, $membership_id)
    {
        if (!is_numeric($role_id) && $role = \SiteMaster\Core\Registry\Site\Role::getByRoleName($role_id)) {
            $role_id = $role->id;
        }
        
        return self::getByAnyField(__CLASS__, 'site_members_id', $membership_id, 'roles_id=' .(int)$role_id);
    }

    /**
     * Create a role for a site member
     *
     * @param \SiteMaster\Core\Registry\Site\Role $role
     * @param Member $member
     * @param array $fields
     * @return false|Role
     */
    public static function createRoleForSiteMember(\SiteMaster\Core\Registry\Site\Role $role, Member $member, $fields = array())
    {
        $membership_role = new self();
        $membership_role->approved = 'NO';
        $membership_role->synchronizeWithArray($fields);
        
        if ($member->isVerified()) {
            //Force approval if the member is verified
            $membership_role->approved = 'YES';
        }
        
        $membership_role->site_members_id = $member->id;
        $membership_role->roles_id = $role->id;
        
        if (!$membership_role->insert()) {
            return false;
        }
        
        return $membership_role;
    }

    /**
     * @return bool|\SiteMaster\Core\Registry\Site\Role
     */
    public function getRole()
    {
        return \SiteMaster\Core\Registry\Site\Role::getByID($this->roles_id);
    }

    /**
     * Determine if this role is approved
     * 
     * @return bool
     */
    public function isApproved()
    {
        if ($this->approved == 'YES') {
            return true;
        }
        
        return false;
    }

    /**
     * Approve this role
     */
    public function approve()
    {
        $this->approved = 'YES';
        $this->save();
    }

    /**
     * Ge the membership for this role
     * 
     * @return bool|\SiteMaster\Core\Registry\Site\Member
     */
    public function getMembership()
    {
        return Member::getByID($this->site_members_id);
    }

    /**
     * Get the user for this role
     * 
     * @return false|\SiteMaster\Core\User\User
     */
    public function getUser()
    {
        if (!$membership = $this->getMembership()) {
            return false;
        }
        
        return $membership->getUser();
    }
}
