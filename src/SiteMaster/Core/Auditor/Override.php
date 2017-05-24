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
     * @return false|Metric\Mark
     */
    public function getMark()
    {
        return Metric\Mark::getByID($this->marks_id);
    }

    /**
     * Create a new Override
     *
     * @param $url
     * @param $users_id
     * @param $reason
     *
     * @param Mark $page_mark
     * @return bool|Override
     */
    public static function createNewOverride($url, $users_id, $reason, Mark $page_mark)
    {
        $page = $page_mark->getPage();
        
        if ($page_mark->points_deducted !== '0.00') {
            throw new InvalidArgumentException('Overrides can only be created for notices');
        }
        
        if (self::getMatchingRecord($page_mark)) {
            throw new InvalidArgumentException('An override already exists that matches this page mark');
        }
        
        $record = new self();
        $record->sites_id = $page->sites_id;
        $record->users_id = $users_id;
        $record->marks_id = $page_mark->marks_id;
        $record->value_found = $page_mark->value_found;
        $record->url = $url;
        $record->reason = $reason;
        $record->date_created = Util::epochToDateTime();
        
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
        
        $page_scope_sql = "";
        if (empty($page_mark->context)) {
            $page_scope_sql .= "AND `context` IS NULL \n";
        } else {
            $page_scope_sql .= "AND `context` = '".RecordList::escapeString($page_mark->context)."'\n";
        }
        if (empty($page_mark->line)) {
            $page_scope_sql .= "AND line IS NULL \n";
        } else {
            $page_scope_sql .= "AND line = ".(int)$page_mark->line."\n";
        }
        if (empty($page_mark->col)) {
            $page_scope_sql .= "AND col IS NULL \n";
        } else {
            $page_scope_sql .= "AND col = ".(int)$page_mark->col."\n";
        }

        $sql = "SELECT id
                FROM overrides
                WHERE
                  sites_id = ".(int)$page->sites_id."
                  AND value_found = '".RecordList::escapeString($page_mark->value_found)."'
                  AND marks_id = ".(int)$page_mark->marks_id."
                  AND 
                  ((
                    #page scope
                    url = '".RecordList::escapeString($page->uri)."'
                    ".$page_scope_sql."
                  ) OR (
                    #site scope
                    url IS NULL
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
