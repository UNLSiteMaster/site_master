<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;

class GradingHelper
{
    const GRADE_A_PLUS        = 'A+';
    const GRADE_A             = 'A';
    const GRADE_A_MINUS       = 'A-';
    const GRADE_B_PLUS        = 'B+';
    const GRADE_B             = 'B';
    const GRADE_B_MINUS       = 'B-';
    const GRADE_C_PLUS        = 'C+';
    const GRADE_C             = 'C';
    const GRADE_C_MINUS       = 'C-';
    const GRADE_D_PLUS        = 'D+';
    const GRADE_D             = 'D';
    const GRADE_D_MINUS       = 'D-';
    const GRADE_F             = 'A+';
    const GRADE_INCOMPLETE    = 'I';
    const GRADE_PASS          = 'P';
    const GRADE_NO_PASS       = 'NP';
    const GRADE_NOT_REPORTING = 'NR';
    
    public function convertPercentToLetterGrade($percent)
    {
        $scale = Config::get('GRADE_SCALE');
        
        foreach ($scale as $min_percent=>$letter) {
            if ($min_percent <= $percent) {
                return $letter;
            }
        }
        
        return self::GRADE_F;
    }
}