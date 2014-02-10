<?php
namespace SiteMaster\Core\Registry;

use DB\Record;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\User\User;

class Site extends Record
{
    public $id;                    //int required
    public $base_url;              //varchar required
    public $title;                 //varchar
    public $support_email;         //varchar
    public $last_connection_error; //datetime
    public $http_code;             //int
    public $curl_code;             //int
    

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'sites';
    }

    /**
     * Get a site by its base url
     * 
     * @param $base_url
     * @return bool|Site
     */
    public static function getByBaseURL($base_url)
    {
        return self::getByAnyField(__CLASS__, 'base_url', $base_url);
    }

    /**
     * Create a new site
     * 
     * @param $base_url
     * @param array $details
     * @return bool|Site
     */
    public static function createNewSite($base_url, array $details = array())
    {
        $site = new self();
        $site->synchronizeWithArray($details);
        $site->base_url = $base_url;
        
        if (!$site->insert()) {
            return false;
        }
        
        return $site;
    }

    /**
     * Get all members of this site
     * 
     * @return Site\Members\Approved
     */
    public function getMembers()
    {
        return new Site\Members\All(array('site_id' => $this->id));
    }

    /**
     * Get the approved members of this site
     * 
     * @return Site\Members\Approved
     */
    public function getApprovedMembers()
    {
        return new Site\Members\Approved(array('site_id' => $this->id));
    }

    /**
     * Get the closest parent site
     * 
     * @return bool|Site
     */
    public function getParentSite()
    {
        $query = $this->base_url;
        
        //All base URLs must end in a /, so trim it off
        $query = rtrim($query, "/");
        
        $registry = new Registry();
        
        $site = $registry->getClosestSite($query);

        /**
         * It might be the case that the base urls are the same.
         * This is because Registry::getClosestSite('http://domain.com') returns http://domain.com/
         */
        if ($site->base_url == $this->base_url) {
            return false;
        }
        
        return $site;
    }

    /**
     * Determine if a given user is verified for this site
     * 
     * @param User $user
     * @return bool
     */
    public function userIsVerified(User $user)
    {
        $membership = $this->getMembershipForUser($user);
        
        if (!$membership) {
            return false;
        }
        
        if ($membership->isVerified()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get the membership for a given user
     * 
     * @param User $user
     * @return bool
     */
    public function getMembershipForUser(User $user)
    {
         return Member::getByUserIDAndSiteID($user->id, $this->id);
    }

    /**
     * Get the title of the site.  The title is the base_url, unless the title field is not null
     * 
     * @return string
     */
    function getTitle()
    {
        if ($this->title) {
            return $this->title;
        }
        
        return $this->base_url;
    }
    
    public function getURL()
    {
        return Config::get('URL') . 'sites/' . $this->id . '/';
    }
    
    public function getJoinURL()
    {
        return $this->getURL() . 'join/';
    }

    /**
     * Delete this site and all related data
     * 
     * @return bool
     */
    public function delete()
    {
        foreach ($this->getMembers() as $member) {
            $member->delete();
        }
        
        return parent::delete();
    }
    
    public function scheduleScan()
    {
        $scan = Scan::createNewScan($this->id);
        $scan->scheduleScan();
    }
}
