<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site\Member;

class Override extends Record
{
    public $id; // INT UNSIGNED NOT NULL AUTO_INCREMENT,
    public $sites_id; // INT NOT NULL,
    public $users_id; // INT NOT NULL,
    public $date_created; // DATETIME NOT NULL,
    public $marks_id; // INT NOT NULL,
    public $url; //VARCHAR(2100) null
    public $context; // TEXT NULL,
    public $line; // INT NULL,
    public $col; // INT NULL,
    public $value_found; // TEXT NULL,
    public $reason; // TEXT NOT NULL,

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'overrides';
    }

    /**
     * Create a new Override
     *
     * @param $sites_id
     * @param $users_id
     * @param $marks_id
     * @param $reason
     * 
     * @param array $fields an associative array of field names and values to insert
     * @return bool|Override
     */
    public static function createNewMetric($sites_id, $users_id, $marks_id, $reason, $url, array $fields = array())
    {
        $record = new self();
        $record->synchronizeWithArray($fields);

        $record->sites_id = $sites_id;
        $record->users_id = $users_id;
        $record->marks_id = $marks_id;
        $record->url = $url;
        $record->reason = $reason;

        if (!$record->insert()) {
            return false;
        }

        return $record;
    }
}
