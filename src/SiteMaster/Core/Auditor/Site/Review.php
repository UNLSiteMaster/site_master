<?php
namespace SiteMaster\Core\Auditor\Site;

use DB\Record;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;

class Review extends Record
{
    public $id;                    //int required
    public $sites_id;              //fk for sites.id
    public $creator_users_id;      //fk for users.id
    public $last_edited_users_id;  //fk for users.id
    public $date_created;          //DATETIME NOT NULL
    public $date_edited;           //DATETIME NOT NULL
    public $date_scheduled;        //DATETIME NOT NULL
    public $date_reviewed;         //DATETIME
    public $status;                //ENUM('SCHEDULED', 'IN_REVIEW', 'REVIEW_FINISHED')
    public $internal_notes;        //LONGTEXT
    public $public_notes;          //LONGTEXT
    public $result;                //ENUM('OKAY', 'NEEDS WORK')

    const STATUS_SCHEDULED       = 'CREATED';
    const STATUS_IN_REVIEW       = 'QUEUED';
    const STATUS_REVIEW_FINISHED = 'RUNNING';
    
    const RESULT_OKAY       = 'COMPLETE';
    const RESULT_NEEDS_WORK = 'ERROR';

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_reviews';
    }

    /**
     * Create a new Review
     *
     * @param int $sites_id the site id
     * @param int $users_id the id of the user who is creating the review
     * @param string $date_scheduled the date that the review is scheduled to take place 'YYYY-MM-DD'
     * @param array $fields an associative array of field names and values to insert
     * @return bool|Scan
     */
    public static function createNewReview($sites_id, $users_id, $date_scheduled, array $fields = array())
    {
        $review = new self();
        $review->status       = self::STATUS_SCHEDULED;
        $review->date_created = Util::epochToDateTime();
        $review->date_edited  = Util::epochToDateTime();
        
        $review->synchronizeWithArray($fields);
        $review->date_scheduled       = $date_scheduled;
        $review->creator_users_id     = $users_id;
        $review->last_edited_users_id = $users_id;
        $review->sites_id             = $sites_id;

        if (!$review->insert()) {
            return false;
        }

        return $review;
    }

    /**
     * Get the site for this review
     *
     * @return bool|\SiteMaster\Core\Registry\Site
     */
    public function getSite()
    {
        return Site::getByID($this->sites_id);
    }

    /**
     * Get the URL for this review
     * 
     * @return bool|string
     */
    public function getURL()
    {
        if (false == $this->id) {
            return false;
        }
        
        return $this->getSite()->getURL() . 'reviews/' . $this->id . '/';
    }

    /**
     * Get the Edit URL for this review
     *
     * @return bool|string
     */
    public function getEditURL()
    {
        if (false == $this->id) {
            return false;
        }

        return $this->getSite()->getURL() . 'reviews/' . $this->id . '/edit/';
    }
}
