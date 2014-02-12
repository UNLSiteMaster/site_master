<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class ScanDBTest extends DBTestCase
{
    const INTEGRATION_TESTING_URL = 'http://unlsitemaster.github.io/test_site/';
    /**
     * Test the scheduleScan method
     * 
     * @test
     */
    public function scheduleScan()
    {
        $this->setUpDB();
    }

    /**
     * Simulate a scan for a site.  Verify all results
     * This is an integration test rather than a unit test
     * 
     * @test
     * @group integration
     */
    public function scan()
    {
        $this->setUpDB();
        
        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);
        
        //Schedule a scan
        $site->scheduleScan();
        
        $this->runScan($site);
        
        //get the scan
        $scan = $site->getLatestScan();
        
        $example_metric = Metric::getByMachineName('example');
        
        $this->assertEquals(Scan::STATUS_COMPLETE, $scan->status, 'the scan should be completed');
        
        foreach ($scan->getPages() as $page) {
            /**
             * @var $page \SiteMaster\Core\Auditor\Site\Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(84.5, $grade->grade, 'the grade should be 84.5');
            $this->assertEquals(33.3, $grade->weight, 'the weight should be set to 33.3, as per the config');
            
            //TODO: test page grade
        }
        
        //TODO: test the overall grade
    }
    
    protected function runScan(Site $site)
    {
        //Create a mock worker to scan it
        $keep_scanning = true;
        while ($keep_scanning) {
            //Get the queue
            $queue = new Queued();

            if (!$queue->count()) {
                $keep_scanning = false;

                //Check again.
                continue;
            }

            /**
             * @var $page \SiteMaster\Core\Auditor\Site\Page
             */
            $queue->rewind();
            $page = $queue->current();

            $page->scan();

            sleep(1);
        }
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
