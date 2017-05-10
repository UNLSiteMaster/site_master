<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Auditor\FeatureAnalytics;
use SiteMaster\Core\Auditor\Site\Page;

/**
 * Class Link
 * @package SiteMaster\Core\Auditor\Site\Page
 */

class PageHasFeatureAnalytics extends Record
{
    public $id; //int required
    public $scanned_page_id; //FK required
    public $feature_analytics_id; //FK required
    public $num_instances; //INT required

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scanned_page_has_feature_analytics';
    }

    /**
     * Create a new page analytic
     *
     * @param $feature
     * @param int $scanned_page_id the scanned page that this mark belongs to
     * @param $num_instances
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|PageHasFeatureAnalytics
     */
    public static function createNewRecord(FeatureAnalytics $feature, $scanned_page_id, $num_instances, array $fields = array())
    {
        $record = new self();

        $record->synchronizeWithArray($fields);
        $record->id = NULL;
        $record->scanned_page_id = $scanned_page_id;
        $record->feature_analytics_id = $feature->id;
        $record->num_instances = $num_instances;

        if (!$record->insert()) {
            return false;
        }

        return $record;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return Page::getByID($this->scanned_page_id);
    }

    /**
     * @return FeatureAnalytics
     */
    public function getFeature()
    {
        return FeatureAnalytics::getByID($this->feature_analytics_id);
    }
}
