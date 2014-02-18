<?php
namespace SiteMaster\Plugins\Metric_links;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use Sitemaster\Core\Util;

class LinkStatus extends Record
{
    public $id;             //int required
    public $url_hash;       //VARCHAR(256) NOT NULL, the md5 of the link url
    public $date_created;   //DATETIME NOT NULL, machine readable name
    public $http_code;      //INT(4)
    public $curl_code;      //INT(4)

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'metric_links_status';
    }

    /**
     * Create a new Link Status
     *
     * @param string $url the full url of the link
     * @param int $http_code the http code of the link
     * @param int $curl_code the curl code of the link
     * @param array $fields an associative array of field names and values to insert
     * @return bool|LinkStatus
     */
    public static function createLinkStatus($url, $http_code, $curl_code, array $fields = array())
    {
        $link = new self();
        $link->date_created = Util::epochToDateTime();
        $link->synchronizeWithArray($fields);

        $link->url_hash = md5($url);
        $link->http_code = $http_code;
        $link->curl_code = $curl_code;

        if (!$link->insert()) {
            return false;
        }

        return $link;
    }

    /**
     * Get by the link's url
     * 
     * This will also delete a record if it it is expired, and return false
     *
     * @param string $url the url of the link
     * @return bool|LinkStatus
     */
    public static function getByURL($url)
    {
        if (!$object = self::getByAnyField(__CLASS__, 'url', md5($url))) {
            return false;
        }

        //Make sure it is still valid 
        if ($object->isExpired()) {
            //delete it, because it is expired
            $object->delete();
            return false;
        }
        
        return $object;
    }

    /**
     * Determines if this record has expired
     * 
     * @return bool
     */
    public function isExpired()
    {
        $created = new \DateTime($this->date_created);
        $now = new \DateTime();
        $interval = $created->diff($now);
        
        if ($interval->h >= 1) {
            //Let it expire after an hour
            return true;
        }
    }
}
