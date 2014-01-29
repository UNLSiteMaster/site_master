<?php
namespace SiteMaster\Core\Registry\Site;

use DB\Record;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\User\User;
use SiteMaster\Core\Util;

class Member extends Record
{
    public $id;                   //int required
    public $users_id;             //int required fk -> users
    public $sites_id;             //int required fk -> sites
    public $source;               //varchar
    public $verified;             //enum('YES', 'NO') required default = NO
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
    
    public static function getByUserIDAndSiteID($user_id, $site_id)
    {
        return self::getByAnyField(__class__, 'users_id', $user_id, 'sites_id = ' . (int)$site_id);
    }

    /**
     * Get the site for this membership
     * 
     * @return false|\SiteMaster\Registry\Site
     */
    public function getSite()
    {
        return Site::getByID($this->sites_id);
    }

    /**
     * Get the user for this membership
     * 
     * @return false|\SiteMaster\User\User
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
        $membership->verified = 'NO';
        
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
     * Approve a membership
     * 
     * This will also add a role of 'manager' if no managers exist
     * 
     * @return bool
     */
    public function approve()
    {
        $this->status = 'APPROVED';
        
        if (!$this->save()) {
            return false;
        }
        
        $manager_role = Role::getByRoleName('manager');
        
        $approvedMembers = new Members\WithRole($this->sites_id, $manager_role->id);
        
        if (count($approvedMembers) == 0) {
            Member\Role::createRole($manager_role, $this);
        }
        
        return true;
    }
    
    public function getRoles()
    {
        return new Member\Roles\All(array('member_id' => $this->id));
    }
}
