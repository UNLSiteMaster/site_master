<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use DB\RecordList;
use SiteMaster\Core\Util;

class FeatureAnalytics extends Record
{
    public $id;              //int required
    public $unique_hash;     //VARCHAR(32) - a unique hash of the record
    public $data_type;       //ENUM 'ELEMENT', 'CLASS', 'ATTRIBUTE', 'SELECTOR'
    public $data_key;        //VARCHAR(512) NOT NULL
    public $data_value;      //VARCHAR(512) NOT NULL
    
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
        return 'feature_analytics';
    }

    /**
     * Create a new page analytic
     *
     * @param $data_type
     * @param $data_key
     * @param $data_value
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|FeatureAnalytics
     */
    public static function createNewRecord($data_type, $data_key, $data_value, array $fields = array())
    {
        $record = new self();

        $record->synchronizeWithArray($fields);
        $record->id = NULL;
        $record->data_type = strtoupper($data_type);
        $record->data_key = strtolower($data_key);
        $record->data_value = strtolower($data_value);
        $record->unique_hash = $record->generateUniqueHash();

        if (!$record->insert()) {
            return false;
        }

        return $record;
    }
    
    public function generateUniqueHash()
    {
        return self::generateUniqueHashFromValues($this->data_type, $this->data_key, $this->data_value);
    }

    /**
     * @param $data_type
     * @param $data_key
     * @param $data_value
     * @return string
     */
    public static function generateUniqueHashFromValues($data_type, $data_key, $data_value)
    {
        return md5(strtoupper($data_type).'-'.strtolower($data_key).'-'.strtolower($data_value));
    }

    /**
     * @param $data_type
     * @param $data_key
     * @param $data_value
     * @return FeatureAnalytics
     */
    public static function getByUniqueHash($data_type, $data_key, $data_value)
    {
        $hash = self::generateUniqueHashFromValues($data_type, $data_key, $data_value);
        
        return self::getByAnyField(__CLASS__, 'unique_hash', $hash);
    }

    /**
     * Get an array of feature IDs that match the desired query
     * 
     * @param $data_type
     * @param $data_key
     * @param $data_value
     * @param string $specificity
     * @return array|mixed
     */
    public static function getFeatureIds($data_type, $data_key, $data_value, $specificity = 'exact_match')
    {
        $mysqli = Util::getDB();

        $query = "
        SELECT feature_analytics.id
        FROM feature_analytics";

        switch ($specificity) {
            case 'key_begins_with':
                $query .= ' WHERE data_type = "'.RecordList::escapeString(strtoupper($data_type)).'"';
                $query .= ' AND data_key LIKE "'.RecordList::escapeString($data_key).'%"';
                $query .= ' AND data_value = "'.RecordList::escapeString(strtolower($data_value)).'"';
                break;
            default:
                //Exact match
                $query .= ' WHERE unique_hash = "'.self::generateUniqueHashFromValues($data_type, $data_key, $data_value).'"';
        }

        if (!$result = $mysqli->query($query)) {
            return [];
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);
        
        //Convert rows to just an array of ids
        $results = [];
        foreach ($rows as $data) {
            $results[] = $data['id'];
        }
        
        return $results;
    }
}
