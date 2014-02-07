<?php
namespace SiteMaster\Core\Scan\Site;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class Page extends Record
{
    public $id;                    //int required
    public $scans_id;              //fk for scans.id NOT NULL
    public $sites_id;              //fk for sites_id NOT NULL
    public $uri;                   //URI VARCHAR(256)
    public $scan_finished;         //ENUM('YES', 'NO') NOT NULL default='NO'
    public $grade;                 //DOUBLE(2,2) NOT NULL default=0
    public $start_time;            //DATETIME NOT NULL
    public $end_time;              //DATETIME
    public $title;                 //VARCHAR(256)
    public $letter_grade;          //VARCHAR(2)

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scanned_page';
    }

    /**
     * Get a page by its scan id and uri
     *
     * @param $scans_id
     * @param $uri
     * @internal param $base_url
     * @return bool|Page
     */
    public static function getByScanIDAndURI($scans_id, $uri)
    {
        return self::getByAnyField(__CLASS__, 'uri', $uri, 'scans_id=' . (int)$scans_id);
    }

    /**
     * Create a new page
     *
     * @param $scans_id
     * @param $sites_id
     * @param $uri
     * @param array $fields
     * @return bool|Page
     */
    public static function createNewPage($scans_id, $sites_id, $uri, array $fields = array())
    {
        $page = new self();
        $page->scan_finished = 'NO';
        $page->grade         = 0;
        $page->start_time    = Util::epochToDateTime();
        
        $page->synchronizeWithArray($fields);
        $page->scans_id = $scans_id;
        $page->sites_id = $sites_id;
        $page->uri      = $uri;

        if (!$page->insert()) {
            return false;
        }

        return $page;
    }
}
