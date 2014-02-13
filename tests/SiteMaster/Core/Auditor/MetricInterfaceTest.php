<?php
namespace SiteMaster\Core\Auditor;

class MetricInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getChangesSinceLastScan()
    {
        $metric = new  \SiteMaster\Plugins\Example\Metric('example');
        
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
        $metric = new  \SiteMaster\Plugins\Example\Metric('example');

        $this->assertEquals(17, $metric->computeWeightedGrade(85, 20));
        $this->assertEquals(85, $metric->computeWeightedGrade(85, 100));
        $this->assertEquals(100, $metric->computeWeightedGrade(100, 100));
        $this->assertEquals(17.11, $metric->computeWeightedGrade(85.54, 20));
    }
}