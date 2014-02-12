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
}