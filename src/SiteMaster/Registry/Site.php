<?php
namespace SiteMaster\Registry;

use DB\Record;

class Site extends Record
{
    public $id;               //int required
    public $base_url;         //varchar required
    public $title;            //varchar
    public $support_email;    //varchar

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
    
    public function getMembers()
    {
        
    }
}
