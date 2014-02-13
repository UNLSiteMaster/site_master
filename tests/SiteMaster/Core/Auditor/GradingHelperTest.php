<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;

class GradingHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function convertPercentToLetterGrade()
    {
        //Ensure that we are using the default grading system
        Config::set('GRADE_SCALE', false);
        
        $helper = new GradingHelper();

        $this->assertEquals(GradingHelper::GRADE_A_PLUS, $helper->convertPercentToLetterGrade('97.5'), 'Should be an A+');
        $this->assertEquals(GradingHelper::GRADE_A, $helper->convertPercentToLetterGrade('93', 'Should be an A'));
        $this->assertEquals(GradingHelper::GRADE_A, $helper->convertPercentToLetterGrade('96.99', 'Should be an A'));
        $this->assertEquals(GradingHelper::GRADE_C, $helper->convertPercentToLetterGrade('74', 'Should be a C'));
        $this->assertEquals(GradingHelper::GRADE_F, $helper->convertPercentToLetterGrade('59.99', 'Should be an F'));
    }

    /**
     * @test
     */
    public function getGradePoints()
    {
        $helper = new GradingHelper();

        $this->assertEquals(4.0, $helper->getGradePoints(GradingHelper::GRADE_A_PLUS));
        $this->assertEquals(4.0, $helper->getGradePoints(GradingHelper::GRADE_A));
        $this->assertEquals(3.0, $helper->getGradePoints(GradingHelper::GRADE_B));
    }

    /**
     * @test
     */
    public function convertLetterGradeToCSSClass()
    {
        $helper = new GradingHelper();

        $this->assertEquals('grade-a-plus', $helper->convertLetterGradeToCSSClass(GradingHelper::GRADE_A_PLUS));
    }
}