<?php
namespace SiteMaster\Core\Auditor\Site\Page;

class MetricGradeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    /**
     * @test
     */
    public function getPercentGrade()
    {
        $grade = new MetricGrade();
        
        $grade->point_grade = 84.5;
        $grade->points_available = 100;
        $this->assertEquals(84.5, $grade->getPercentGrade());

        $grade->point_grade = 0;
        $this->assertEquals(0, $grade->getPercentGrade());

        $grade->point_grade = 34.5;
        $grade->points_available = 50;
        $this->assertEquals(69, $grade->getPercentGrade());

        $grade->points_available = 0;
        $this->assertEquals(0, $grade->getPercentGrade());
    }
}