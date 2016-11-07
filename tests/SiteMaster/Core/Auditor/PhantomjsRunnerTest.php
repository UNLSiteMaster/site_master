<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;

class phantomjsRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testRun()
    {
        $runner = new PhantomjsRunner();
        
        $results = $runner->run(ScanDBTest::INTEGRATION_TESTING_URL);
        
        $this->assertArrayHasKey('page_title', $results['example']);
    }
}