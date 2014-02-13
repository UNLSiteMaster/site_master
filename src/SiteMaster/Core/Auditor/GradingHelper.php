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
    const GRADE_F             = 'F';
    const GRADE_INCOMPLETE    = 'I';
    const GRADE_PASS          = 'P';
    const GRADE_NO_PASS       = 'NP';
    const GRADE_NOT_REPORTING = 'NR';

    /**
     * Convert a percent grade to a letter grade
     * 
     * @param double $percent the percent to convert
     * @return string the letter grade for that percent
     */
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

    /**
     * Get the grade points for a given letter grade
     * 
     * @param string $letter_grade the letter grade
     * @return double the grade points
     */
    public function getGradePoints($letter_grade)
    {
        $points = array(
            self::GRADE_A_PLUS  => 4.0,
            self::GRADE_A       => 4.0,
            self::GRADE_A_MINUS => 3.67,
            self::GRADE_B_PLUS  => 3.33,
            self::GRADE_B       => 3.0,
            self::GRADE_B_MINUS => 2.67,
            self::GRADE_C_PLUS  => 2.33,
            self::GRADE_C       => 2.0,
            self::GRADE_C_MINUS => 1.67,
            self::GRADE_D_PLUS  => 1.33,
            self::GRADE_D       => 1.0,
            self::GRADE_D_MINUS => 0.67,
            self::GRADE_F       => 0,
        );
        
        if (isset($points[$letter_grade])) {
            return $points[$letter_grade];
        }
        
        return 0;
    }

    /**
     * get the css class name for a letter grade
     * 
     * @param string $letter_grade
     * @return string the associated css class
     */
    public function convertLetterGradeToCSSClass($letter_grade)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = $class->getConstants();
        
        foreach ($constants as $name=>$value) {
            if ($value == $letter_grade) {
                return strtolower(str_replace('_', '-', $name));
            }
        }
        
        return 'grade-unknown';
    }

    /**
     * Determine if a given letter grade counts toward the GPA
     * 
     * @param string $letter_grade the letter grade to check
     * @return bool
     */
    public function countsTowardGPA($letter_grade)
    {
        $does_not_count = array(
            self::GRADE_INCOMPLETE,
            self::GRADE_NOT_REPORTING,
            self::GRADE_PASS,
            self::GRADE_NO_PASS
        );
        
        if (in_array($letter_grade, $does_not_count)) {
            return false;
        }
        
        return true;
    }

    /**
     * Calculate the gpa from a set of letter grades
     * 
     * @param array $letter_grades an array of letter grades
     * @return float
     */
    public function calculateGPA(array $letter_grades)
    {
        if (empty($letter_grades)) {
            return 0;
        }
        
        $grade_points = array();
        
        foreach ($letter_grades as $letter_grade) {
            if (!$this->countsTowardGPA($letter_grade)) {
                continue;
            }
            
            $grade_points[] = $this->getGradePoints($letter_grade);
        }
        
        if (empty($grade_points)) {
            return 0;
        }
        
        return round(array_sum($grade_points) / count($grade_points), 2);
    }
}