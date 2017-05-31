<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use DB\RecordList;
use SiteMaster\Core\Auditor\Site\Page\Mark;
use SiteMaster\Core\Config;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Util;

class Override extends Record
{
    public $id; // INT UNSIGNED NOT NULL AUTO_INCREMENT,
    public $sites_id; // INT NOT NULL,
    public $users_id; // INT NOT NULL,
    public $date_created; // DATETIME NOT NULL,
    public $expires; //DATETIME NULL,
    public $marks_id; // INT NOT NULL,
    public $url; //VARCHAR(2100) null
    public $context; // TEXT NULL,
    public $line; // INT NULL,
    public $col; // INT NULL,
    public $value_found; // TEXT NULL,
    public $reason; // TEXT NOT NULL,
    public $scope; // ENUM('SITE', 'PAGE', 'ELEMENT') NOT NULL, default 'ELEMENT'
    
    
    const SCOPE_SITE = 'SITE';
    const SCOPE_PAGE = 'PAGE';
    const SCOPE_ELEMENT = 'ELEMENT';
    const SCOPE_GLOBAL = 'GLOBAL';

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'overrides';
    }

    /**
     * @return false|Metric\Mark
     */
    public function getMark()
    {
        return Metric\Mark::getByID($this->marks_id);
    }

    /**
     * Create a new Override
     *
     * @param $scope
     * @param $users_id
     * @param $reason
     *
     * @param Mark $page_mark
     * @return bool|Override
     */
    public static function createNewOverride($scope, $users_id, $reason, Mark $page_mark)
    {
        $page = $page_mark->getPage();
        $mark = $page_mark->getMark();
        
        if (!$page_mark->canBeOverridden()) {
            throw new InvalidArgumentException('Overrides can only be created for notices or if the metric allows overriding errors');
        }
        
        if (self::getMatchingRecord($page_mark)) {
            throw new InvalidArgumentException('An override already exists that matches this page mark');
        }
        
        $record = new self();
        $record->scope = $scope;
        $record->sites_id = $page->sites_id;
        $record->users_id = $users_id;
        $record->marks_id = $page_mark->marks_id;
        $record->value_found = $page_mark->value_found;
        $record->url = $page->uri;
        $record->reason = $reason;
        $record->date_created = Util::epochToDateTime();
        
        //set the expires
        if ($mark->allowsPermanentOverrides()) {
            //It never expires
            $record->expires = null;
        } else {
            $record->expires = Util::epochToDateTime(strtotime('+1 year'));
        }
        
        if ($scope === self::SCOPE_ELEMENT) {
            $record->context = $page_mark->context;
            $record->line = $page_mark->line;
            $record->col = $page_mark->col;
        }

        if (!$record->insert()) {
            return false;
        }
        
        $metric = $mark->getMetric();
        
        if ($metric && $record->getNumOfSiteOverrides() >= Config::get('NUM_SITES_FOR_GLOBAL_OVERRIDE') && $metric->allowGlobalOverrides()) {
            self::createGlobalOverride($mark->id, $page_mark->value_found);
        }

        return $record;
    }

    /**
     * @param $marks_id
     * @param $value_found
     * @return Override|False
     */
    public static function getGlobalOverride($marks_id, $value_found)
    {
        return self::getByAnyField(__CLASS__, 'value_found', $value_found, 'marks_id = ' . (int) $marks_id);
    }

    /**
     * Create a global override
     * 
     * @param $marks_id
     * @param $value_found
     * @return bool
     */
    public static function createGlobalOverride($marks_id, $value_found)
    {
        if (self::getGlobalOverride($marks_id, $value_found)) {
            //Override already exists
            return false;
        }
        
        $record = new self();
        $record->scope = self::SCOPE_GLOBAL;
        $record->marks_id = $marks_id;
        $record->value_found = $value_found;
        $record->date_created = Util::epochToDateTime();
        
        if (!$record->insert()) {
            return false;
        }
        
        return $record;
    }
    
    public function getNumOfSiteOverrides()
    {
        $db = Util::getDB();
        
        $sql = "SELECT count(id) as total
                FROM overrides
                WHERE 
                  value_found = '".RecordList::escapeString($this->value_found)."'
                  AND marks_id = ".(int)$this->marks_id;

        if (!$result = $db->query($sql)) {
            return false;
        }

        if ($result->num_rows === 0) {
            return false;
        }

        $row = $result->fetch_assoc();
        
        return $row['total'];
    }

    /**
     * @param Mark $page_mark
     * @return false|Override
     */
    public static function getMatchingRecord(Mark $page_mark)
    {
        $db = Util::getDB();
        
        $page = $page_mark->getPage();
        
        $element_scope_sql = "";
        if (empty($page_mark->context)) {
            $element_scope_sql .= "AND `context` IS NULL \n";
        } else {
            $element_scope_sql .= "AND `context` = '".RecordList::escapeString($page_mark->context)."'\n";
        }

        $sql = "SELECT id
                FROM overrides
                WHERE
                  (
                      #global scope (across all sites)
                      scope = 'GLOBAL'
                      AND value_found = '" . RecordList::escapeString($page_mark->value_found) . "'
                      AND marks_id = " . (int)$page_mark->marks_id . "
                  ) OR (
                      sites_id = " . (int)$page->sites_id . "
                      AND value_found = '" . RecordList::escapeString($page_mark->value_found) . "'
                      AND marks_id = " . (int)$page_mark->marks_id . "
                      AND 
                      ((
                        #element scope
                        scope = 'ELEMENT'
                        AND url = '" . RecordList::escapeString($page->uri) . "'
                        " . $element_scope_sql . "
                      ) OR (
                        scope = 'PAGE'
                        AND url = '" . RecordList::escapeString($page->uri) . "'
                      ) OR (
                        #site scope
                        scope = 'SITE'
                      ))
                  )
                LIMIT 1";

        if (!$result = $db->query($sql)) {
            return false;
        }
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $row = $result->fetch_assoc();

        return Override::getByID($row['id']);
    }
}
