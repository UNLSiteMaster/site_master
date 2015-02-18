<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

/**
 * Class Link
 * @package SiteMaster\Core\Auditor\Site\Page
 */

class Link extends Record
{
    public $id;                    //int required
    public $date_created;          //date the link was created
    public $scanned_page_id;       //fk for scanned_page.id NOT NULL
    public $original_url;          //VARCHAR(2100) NOT NULL
    public $original_url_hash;     //BINARY(16) NOT NULL
    public $original_curl_code;    //VARCHAR(2100) NOT NULL
    public $original_status_code;  //VARCHAR(2100) NOT NULL
    public $final_url;             //VARCHAR(2100) NOT NULL
    public $final_url_hash;        //BINARY(16) NOT NULL
    public $final_curl_code;       //VARCHAR(2100) NOT NULL
    public $final_status_code;     //VARCHAR(2100) NOT NULL
    public $cached;                //TINYINT

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scanned_page_links';
    }
    
    public static function getByOriginalURL($original_url, $cached = null)
    {
        $links = new Links\ByOriginalURL(array(
            'original_url_hash' => md5($original_url, true),
            'limit'             => 1,
            'cached'            => $cached,
        ));

        if ($links->count() == 0) {
            return false;
        }

        $links->rewind();
        return $links->current();
    }

    /**
     * Create a new page link
     *
     * @param int $scanned_page_id the scanned page that this mark belongs to
     * @param string $original_url the absolute URL of the link
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|Mark
     */
    public static function createNewPageLink($scanned_page_id, $original_url, array $fields = array())
    {
        $link = new self();
        
        $insert_new = true;
        //Check if we already know about the url
        if ($latest_link = self::getByOriginalURL($original_url, true)) {
            $insert_new = false;
            if ($latest_link->isExpired()) {
                //The last result is expired, grab fresh data
                $insert_new = true;
            }
        }
        
        if ($insert_new) {
            $original_info = Util::getHTTPInfo($original_url);
            $final_info    = $original_info;
            
            if (in_array($original_info['http_code'], array(301, 302))) {
                $final_info = Util::getHTTPInfo($original_url, array(CURLOPT_FOLLOWLOCATION=>true));
            }

            $link->original_url         = $original_info['effective_url'];
            $link->original_url_hash    = md5($link->original_url, true);
            $link->original_curl_code   = $original_info['curl_code'];
            $link->original_status_code = $original_info['http_code'];
            $link->final_url            = $final_info['effective_url'];
            $link->final_url_hash       = md5($link->final_url, true);
            $link->final_curl_code      = $final_info['curl_code'];
            $link->final_status_code    = $final_info['http_code'];
            $link->cached               = 0; //indicate that this was a fresh request
        } else {
            $link->synchronizeWithArray($latest_link->toArray());
            $link->cached = 1;
        }

        $link->synchronizeWithArray($fields);
        $link->id              = NULL;
        $link->scanned_page_id = $scanned_page_id;
        $link->date_created    = Util::epochToDateTime();

        if (!$link->insert()) {
            return false;
        }

        return $link;
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

    /**
     * Determine if this link was a redirect
     * 
     * @return bool
     */
    public function isRedirect()
    {
        if (in_array($this->original_status_code, array(301, 302))) {
            return true;
        }
        
        return false;
    }
}
