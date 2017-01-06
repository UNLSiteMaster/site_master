<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;

class HeadlessRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testRunHeadless()
    {
        $runner = new HeadlessRunner();
        
        $results = $runner->run(ScanDBTest::INTEGRATION_TESTING_URL);
        
        $this->assertArrayHasKey('page_title', $results['example']);
    }
}