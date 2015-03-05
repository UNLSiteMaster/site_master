<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Config;
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
            'original_url' => $original_url,
            'limit'        => 1,
            'cached'       => $cached,
        ));

        foreach ($links as $link) {
            if ($link->original_url == $original_url) {
                return $link;
            }
        }

        return false;
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
        if ($latest_link = self::getByOriginalURL($original_url, false)) {
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
            
            //Verify data integrity, sometimes the DBA will switch 0 values to NULL values =/
            if (null == $link->original_status_code) {
                $link->original_status_code = 0;
            }

            if (null == $link->final_status_code) {
                $link->final_status_code = 0;
            }

            if (null == $link->original_curl_code) {
                $link->original_curl_code = 0;
            }

            if (null == $link->final_curl_code) {
                $link->final_curl_code = 0;
            }
            
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
        
        //Determine if the request is expired
        if ($now > $created->modify(Config::get('PAGE_LINK_TTL'))) {
            return true;
        }
        
        return false;
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

    /**
     * Determine if this link resulted in a CURL error
     * 
     * @return bool
     */
    public function isCurlError()
    {
        if ($this->original_curl_code && empty($this->original_status_code)) {
            return true;
        }
    
        return false;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return \SiteMaster\Core\Auditor\Site\Page::getByID($this->scanned_page_id);
    }
}
