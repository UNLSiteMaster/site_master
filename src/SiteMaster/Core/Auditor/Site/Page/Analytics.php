<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

/**
 * Class Link
 * @package SiteMaster\Core\Auditor\Site\Page
 */

class Analytics extends Record
{
    public $id;              //int required
    public $scanned_page_id; //fk for scanned_page.id NOT NULL
    public $data_type;       //ENUM 'ELEMENT', 'CLASS', 'ATTRIBUTE', 'SELECTOR'
    public $data_key;        //VARCHAR(256) NOT NULL
    public $data_value;      //VARCHAR(512) NOT NULL
    public $num_instances;   //INT NOT NULL
    
    const DATA_TYPE_ELEMENT = 'ELEMENT';
    const DATA_TYPE_CLASS = 'CLASS';
    const DATA_TYPE_ATTRIBUTE = 'ATTRIBUTE';
    const DATA_TYPE_SELECTOR = 'SELECTOR';

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scanned_page_analytics';
    }

    /**
     * Create a new page analytic
     *
     * @param int $scanned_page_id the scanned page that this mark belongs to
     * @param $data_type
     * @param $num_instances
     * @param $data_key
     * @param $data_value
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|Analytics
     * @internal param string $original_url the absolute URL of the link
     */
    public static function createNewRecord($scanned_page_id, $data_type, $num_instances, $data_key, $data_value, array $fields = array())
    {
        $record = new self();

        $record->synchronizeWithArray($fields);
        $record->id = NULL;
        $record->scanned_page_id = $scanned_page_id;
        $record->data_type = $data_type;
        $record->num_instances = $num_instances;
        $record->data_key = $data_key;
        $record->data_value = $data_value;

        if (!$record->insert()) {
            return false;
        }

        return $record;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return Page::getByID($this->scanned_page_id);
    }
}
