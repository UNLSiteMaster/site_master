<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class Link extends Record
{
    public $id;                    //int required
    public $date_created;          //date the link was created
    public $scanned_page_id;       //fk for scanned_page.id NOT NULL
    public $link_url;              //VARCHAR(2100) NOT NULL

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scanned_page_links';
    }

    /**
     * Create a new page link
     *
     * @param int $scanned_page_id the scanned page that this mark belongs to
     * @param string $link_url the absolute URL of the link
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|Mark
     */
    public static function createNewPageLink($scanned_page_id, $link_url, array $fields = array())
    {
        $link = new self();
        $link->synchronizeWithArray($fields);
        $link->scanned_page_id = $scanned_page_id;
        $link->date_created    = Util::epochToDateTime();
        $link->link_url        = $link_url;

        if (!$link->insert()) {
            return false;
        }

        return $link;
    }
}
