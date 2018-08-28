<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;

class HeadlessRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testRunHeadless()
    {
        $runner = new HeadlessRunner();
        
        $results = $runner->run(ScanDBTest::INTEGRATION_TESTING_URL);

        $this->assertArrayHasKey('page_title', $results['example']);
        $this->assertArrayHasKey('core-page-analytics', $results);
        $this->assertArrayHasKey('core-links', $results);
        $this->assertTrue(in_array('http://example.org/generated-by-js', $results['core-links']), 'JS generated links should be included');
    }
}