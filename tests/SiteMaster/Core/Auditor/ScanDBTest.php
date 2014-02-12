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
    public function scan1()
    {
        $this->setUpDB();
        
        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);
        
        //Schedule a scan
        $site->scheduleScan();
        
        $this->runScan($site);
        
        //get the scan
        $scan = $site->getLatestScan();
        
        $this->assertEquals(Scan::STATUS_COMPLETE, $scan->status);
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
