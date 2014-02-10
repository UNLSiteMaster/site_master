<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
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

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'page_marks';
    }

    /**
     * Create a new page mark
     *
     * @param $marks_id
     * @param $scanned_page_id
     * @param $points_deducted
     * @param array $fields
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
}
