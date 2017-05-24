<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use DB\RecordList;
use SiteMaster\Core\Auditor\Site\Page\Mark;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site\Member;
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
        
        if ($mark->point_deduction !== '0.00') {
            throw new InvalidArgumentException('Overrides can only be created for notices');
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
        
        if (!empty($url)) {
            $record->context = $page_mark->context;
            $record->line = $page_mark->line;
            $record->col = $page_mark->col;
        }

        if (!$record->insert()) {
            return false;
        }

        return $record;
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
                  sites_id = ".(int)$page->sites_id."
                  AND value_found = '".RecordList::escapeString($page_mark->value_found)."'
                  AND marks_id = ".(int)$page_mark->marks_id."
                  AND 
                  ((
                    #element scope
                    scope = 'ELEMENT'
                    AND url = '".RecordList::escapeString($page->uri)."'
                    ".$element_scope_sql."
                  ) OR (
                    scope = 'PAGE'
                    AND url = '".RecordList::escapeString($page->uri)."'
                  ) OR (
                    #site scope
                    scope = 'SITE'
                  ))
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
