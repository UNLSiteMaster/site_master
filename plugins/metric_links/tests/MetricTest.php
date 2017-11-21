<?php
namespace SiteMaster\Plugins\Metric_links;

class MetricTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getPointDeduction()
    {
        $metric = new Metric('metric_links');
        
        $metric->options['grading_method'] = Metric::GRADE_METHOD_DEFAULT;
        $this->assertEquals(20, $metric->getPointDeduction(404));
        $this->assertEquals(0, $metric->getPointDeduction(''));
        
        $metric->options['grading_method'] = Metric::GRADE_METHOD_NUMBER_OF_LINKS;
        $this->assertEquals(2, $metric->getPointDeduction(404));
        $this->assertEquals(0, $metric->getPointDeduction(''));
        
        $metric->options['grading_method'] = Metric::GRADE_METHOD_PASS_FAIL;
        $this->assertEquals(1, $metric->getPointDeduction(404));
        $this->assertEquals(0, $metric->getPointDeduction(''));
    }
}
