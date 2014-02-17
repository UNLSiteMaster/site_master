<?php
namespace SiteMaster\Core\Auditor;

class MetricInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getChangesSinceLastScan()
    {
        $metric = new \SiteMaster\Plugins\Example\Metric('example');
        
        $this->assertEquals(10, $metric->getChangesSinceLastScan(0, 10));
        $this->assertEquals(-10, $metric->getChangesSinceLastScan(10, 0));
        $this->assertEquals(-5, $metric->getChangesSinceLastScan(15, 10));
        $this->assertEquals(5, $metric->getChangesSinceLastScan(10, 15));
    }

    /**
     * @test
     */
    public function computeWeightedGrade()
    {
        $metric = new \SiteMaster\Plugins\Example\Metric('example');

        $this->assertEquals(17, $metric->computeWeightedGrade(85, 100, 20));
        $this->assertEquals(85, $metric->computeWeightedGrade(85, 100, 100));
        $this->assertEquals(100, $metric->computeWeightedGrade(100, 100, 100));
        $this->assertEquals(17.11, $metric->computeWeightedGrade(85.54, 100, 20));
    }

    /**
     * @test
     */
    public function computeLetterGrade()
    {
        //simulate a normal grading method
        $metric = new \SiteMaster\Plugins\Example\Metric('example');
        $grade = new Site\Page\MetricGrade();
        $grade->point_grade = 80;
        $grade->points_available = 100;
        $this->assertEquals(GradingHelper::GRADE_B_MINUS, $metric->computeLetterGrade($grade));
        
        //simulate an incomplete
        $metric = new \SiteMaster\Plugins\Example\Metric('example',
            array('simulate_incomplete'=>true)
        );
        $grade = new Site\Page\MetricGrade();
        $grade->incomplete = 'YES';
        $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $metric->computeLetterGrade($grade));

        //simulate a pass/fail
        $metric = new \SiteMaster\Plugins\Example\Metric('example',
            array('pass_fail'=>true)
        );
        $grade = new Site\Page\MetricGrade();
        $grade->pass_fail = 'YES';
        $grade->point_grade = 80;
        $this->assertEquals(GradingHelper::GRADE_NO_PASS, $metric->computeLetterGrade($grade));
        $grade->point_grade = 100;
        $grade->points_available = 100;
        $this->assertEquals(GradingHelper::GRADE_PASS, $metric->computeLetterGrade($grade));
    }
}