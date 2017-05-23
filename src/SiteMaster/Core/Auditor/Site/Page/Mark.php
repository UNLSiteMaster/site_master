<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Auditor\Override;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Registry\Site\Member;

class Mark extends Record
{
    public $id;                    //int required
    public $marks_id;              //fk for marks.id NOT NULL
    public $scanned_page_id;       //fk for scanned_page.id NOT NULL
    public $points_deducted;       //DECIMAL(2,2) NOT NULL default=0
    public $context;               //TEXT
    public $line;                  //INT
    public $col;                   //INT
    public $value_found;           //TEXT (The incorrect value that was found)
    public $help_text;             //TEXT (help text in markdown format)

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'page_marks';
    }

    /**
     * Get the Mark record that this page mark is an instance of
     * 
     * @return false|\SiteMaster\Core\Auditor\Metric\Mark
     */
    public function getMark()
    {
        return \SiteMaster\Core\Auditor\Metric\Mark::getByID($this->marks_id);
    }

    /**
     * Create a new page mark
     *
     * @param int $marks_id the mark id that this page mark is an instance of
     * @param int $scanned_page_id the scanned page that this mark belongs to
     * @param double $points_deducted the total number of points deducted for this mark
     * @param array $fields an associative array of fields names and values to insert
     * @return bool|Mark
     */
    public static function createNewPageMark($marks_id, $scanned_page_id, $points_deducted, array $fields = array())
    {
        $mark = new self();
        $mark->synchronizeWithArray($fields);
        $mark->marks_id        = $marks_id;
        $mark->scanned_page_id = $scanned_page_id;
        $mark->points_deducted = $points_deducted;

        if (!$mark->insert()) {
            return false;
        }

        return $mark;
    }

    /**
     * Get the page for this mark
     * 
     * @return false|Page
     */
    public function getPage()
    {
        return Page::getByID($this->scanned_page_id);
    }

    /**
     * @return false|Override
     */
    public function hasOverride()
    {
        return Override::getMatchingRecord($this);
    }
}
