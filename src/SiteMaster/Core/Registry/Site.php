<?php
namespace SiteMaster\Core\Registry;

use DB\Record;
use SiteMaster\Core\Auditor\Site\ScanForm;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\Auditor\Scans\AllForSite;
use SiteMaster\Core\Auditor\Scans\FinishedForSite;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\User\User;
use SiteMaster\Core\Util;

class Site extends Record
{
    public $id;                    //int required
    public $base_url;              //varchar required
    public $title;                 //varchar
    public $support_email;         //varchar
    public $last_connection_error; //datetime
    public $http_code;             //int
    public $curl_code;             //int
    public $production_status;     //ENUM('PRODUCTION', 'DEVELOPMENT', 'ARCHIVED') NOT NULL DEFAULT 'PRODUCTION'
    
    const PRODUCTION_STATUS_PRODUCTION  = 'PRODUCTION';
    const PRODUCTION_STATUS_DEVELOPMENT = 'DEVELOPMENT';
    const PRODUCTION_STATUS_ARCHIVED    = 'ARCHIVED';

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
        $site->production_status = self::PRODUCTION_STATUS_PRODUCTION;
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
        
        if (!$site = $registry->getClosestSite($query)) {
            //Couldn't find a parent site, return false.
            return false;
        }

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
     * @return bool|Member
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

    /**
     * Schedule a scan for this site
     *
     * @param string $scan_type the scan type, USER or AUTO, default: AUTO
     * @return bool - true on success, false if there is already a scan in the queue
     */
    public function scheduleScan($scan_type = Scan::SCAN_TYPE_AUTO)
    {
        $latest_scan = $this->getLatestScan();
        
        if ($latest_scan && !$latest_scan->isComplete()) {
            return false;
        }
        
        $scan = Scan::createNewScan($this->id, array(
            'scan_type' => $scan_type,
        ));
        
        $scan->scheduleScan();
        
        return true;
    }

    /**
     * Get the latest scan for this site
     *
     * @param bool $completed - get the latest complete scan
     * @return bool|Scan
     */
    public function getLatestScan($completed = false)
    {
        $db = Util::getDB();

        $sql = "SELECT *
                FROM scans
                WHERE sites_id = " . (int)$this->id . " ";
        
        if ($completed) {
            $sql .= " AND status = 'COMPLETE' ";
        }
        
        $sql .= "ORDER BY id DESC
                 LIMIT 1";

        if (!$result = $db->query($sql)) {
            return false;
        }

        if (!$data = $result->fetch_assoc()) {
            return false;
        }
        
        $object = new Scan();
        $object->synchronizeWithArray($data);
        return $object;
    }

    /**
     * Get all scans for this site
     * 
     * @return AllForSite
     */
    public function getScans()
    {
        return new AllForSite(array('sites_id'=>$this->id));
    }

    /**
     * Get all finished scans for this site
     *
     * @return AllForSite
     */
    public function getFinishedScans()
    {
        return new FinishedForSite(array('sites_id'=>$this->id));
    }

    /**
     * Reduce the total number of scans for this site to the max_history limit
     * 
     * @throws \SiteMaster\Core\RuntimeException
     */
    public function cleanScans()
    {
        $scans = $this->getFinishedScans();
        
        $i = 0;
        $max_scans = Config::get('MAX_HISTORY') + 2;
        
        if ($max_scans < 2) {
            throw new RuntimeException('max scans must be >= 2');
        }
        
        foreach ($scans as $scan) {
            $i++;
            if ($i <= $max_scans) {
                //Don't delete this one.
                continue;
            }

            $scan->delete();
        }
    }

    /**
     * @return ScanForm
     */
    public function getScanForm()
    {
        return new ScanForm(array('site_id'=>$this->id));
    }
}
