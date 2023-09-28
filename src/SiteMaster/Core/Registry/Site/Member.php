<?php
namespace SiteMaster\Core\Registry\Site;

use DB\Record;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\User\User;
use SiteMaster\Core\Util;

class Member extends Record
{
    public $id;                   //int required
    public $users_id;             //int required fk -> users
    public $sites_id;             //int required fk -> sites
    public $source;               //varchar
    public $date_added;           //datetime required
    public $verification_code;    //string required
    
    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_members';
    }

    /**
     * Get the membership for a user and site
     * 
     * @param $user_id
     * @param $site_id
     * @return bool|\SiteMaster\Core\Registry\Site\Member
     */
    public static function getByUserIDAndSiteID($user_id, $site_id)
    {
        return self::getByAnyField(__class__, 'users_id', $user_id, 'sites_id = ' . (int)$site_id);
    }

    /**
     * Get the site for this membership
     * 
     * @return false|\SiteMaster\Core\Registry\Site
     */
    public function getSite()
    {
        return Site::getByID($this->sites_id);
    }

    /**
     * Get the user for this membership
     * 
     * @return false|\SiteMaster\Core\User\User
     */
    public function getUser()
    {
        return User::getByID($this->users_id);
    }

    /**
     * Create a membership
     * 
     * While it is possible to create a membership with no roles, it is encouraged to add roles after the the
     * membership has been created.  They will only take affect once the membership has been approved.
     * 
     * @param User $user
     * @param Site $site
     * @param array $fields
     * @return bool|Member
     */
    public static function createMembership(User $user, Site $site, array $fields = array())
    {
        //Create base object
        $membership = new self();
        
        //Set optional fields
        $membership->synchronizeWithArray($fields);
        
        //Override with required fields and defaults
        $membership->users_id = $user->id;
        $membership->sites_id = $site->id;
        $membership->date_added = Util::epochToDateTime();
        
        //Create the verification code (could be improved with a secure salt)
        $membership->verification_code = md5($user->id . $site->id . rand(0, 1000));
        
        if (!$membership->insert()) {
            return false;
        }
        
        return $membership;
    }

    /**
     * @return Member\Roles\All
     */
    public function getRoles()
    {
        return new Member\Roles\All(array('member_id' => $this->id));
    }

    /**
     * Remove roles for this membership
     * 
     * @param array $role_ids
     */
    public function removeRoles(array $role_ids)
    {
        foreach ($role_ids as $role_id) {
            if (!$role = Member\Role::getByRoleIDANDMembershipID($role_id, $this->id)) {
                continue;
            }

            $role->delete();
        }
        
        //Check if we need to remove the membership because there are no roles left.
        $roles = $this->getRoles();
        if ($roles->count() == 0) {
            $this->delete();
        }
    }

    /**
     * Add roles for this membership
     *
     * @param array $role_ids an array containing role ids or names
     * @param string $approved
     * @throws \SiteMaster\Core\RuntimeException
     */
    public function addRoles(array $role_ids, $approved = 'NO')
    {
        foreach ($role_ids as $role_id) {
            //Get the role
            if (is_numeric($role_id)) {
                //Try to get by the role id
                $role = Role::getByID($role_id);
            } else {
                //Try to get by the role name
                $role = Role::getByRoleName($role_id);
            }
            
            if (!$role) {
                //Couldn't get the role... skip adding it
                continue;
            }

            // Check if we have hit the limit yet
            if (isset($role->max_number_per_site)) {
                $members_with_role = $this->countNumberOfUsersWithRole($role->id);

                // If we hit the limit do not let them add the role
                if ($members_with_role >= intval($role->max_number_per_site)) {
                    throw new RuntimeException('The maximum number of users with that role have already been assigned.', 400);
                }
            }

            // Check if the user has the role that this one is being distinct from
            if (isset($role->distinct_from)) {
                foreach ($this->getRoles() as $role_to_check) {
                    if ($role->distinct_from === $role_to_check->roles_id) {
                        throw new RuntimeException('This role is conflicting with another role.', 400);
                    }
                }
            }

            if (!Member\Role::createRoleForSiteMember($role, $this, array('approved' => $approved))) {
                throw new RuntimeException('Unable to create role ' . $role->role_name, 500);
            }
        }
    }

    /**
     * @param $role_id role id or name
     * @return bool|Member\Role
     */
    public function getRole($role_id)
    {
        return Member\Role::getByRoleIDANDMembershipID($role_id, $this->id);
    }

    /**
     * determine if this membership is verified
     * 
     * @return bool
     */
    public function isVerified()
    {
        if (!$role = $this->getRole('admin')) {
            return false;
        }
        
        return $role->isApproved();
    }

    /**
     * Determine if this member needs verification
     */
    public function needsVerification()
    {
        if (!$role = $this->getRole('admin')) {
            return false;
        }

        return !$role->isApproved();
    }

    /**
     * Verify this membership.
     * 
     * This will add the 'admin role' and also approve all pending roles.
     */
    public function verify()
    {
        //check if we need to add the admin role
        if (!$this->getRole('admin')) {
            $this->addRoles(array('admin'), 'YES');
        }
        
        foreach ($this->getRoles() as $role) {
            $role->approve();
        }
    }

    /**
     * Delete this member and all related data
     * 
     * @return bool
     */
    public function delete()
    {
        foreach ($this->getRoles() as $role) {
            $role->delete();
        }
        
        return parent::delete();
    }

    /**
     * Counts the number of users with that role on this site
     * 
     * @param int $role_id The role to count the users by
     * @return int
     */
    public function countNumberOfUsersWithRole(int $role_id):int
    {
        $members_with_role = new Members\WithRole(array('site_id' => $this->sites_id, 'role_id' => $role_id));
        return count($members_with_role);
    }
}
