<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Site\History\SiteHistory;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Config;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class OverrideDBTest extends DBTestCase
{

    const INTEGRATION_TESTING_URL = 'http://unlsitemaster.github.io/test_site/';
    
    /**
     * Test the getMatchingRecord method
     *
     * @test
     */
    public function getMatchingRecord()
    {
        $this->setUpDB();

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        //Run the scan so we can populate the marks tables with the example metric
        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        //Get the first page
        $pages = $scan->getPages();
        $pages->rewind();
        $page = $pages->current();
        
        $marks = $page->getMarks($example_metric->id);
        
        //Find the mark for the example page title, which is a notice
        $page_mark_notice = false;
        foreach($marks as $page_mark) {
            if ('example_page_title' === $page_mark->getMark()->machine_name) {
                $page_mark_notice = $page_mark;
                break;
            }
        }

        $this->assertNotEquals(false, $page_mark_notice, 'A notice should be found');
        
        //Test creating an override where scope = page
        $override = Override::createNewOverride(Override::SCOPE_ELEMENT, 1, 'test', $page_mark_notice);
        
        $this->assertNotEquals(false, $override, 'the override should have been created');
        
        $matching = Override::getMatchingRecord($page_mark_notice);
        
        $this->assertEquals($override, $matching, 'A matching override should have been found');
        
        //Now... test scope = page
        $override->delete();

        $override = Override::createNewOverride(Override::SCOPE_PAGE, 1, 'test', $page_mark_notice);

        $this->assertNotEquals(false, $override, 'the override should have been created');

        $matching = Override::getMatchingRecord($page_mark_notice);

        $this->assertEquals($override, $matching, 'A matching override should have been found');

        //Now... test scope = site
        $override->delete();

        $override = Override::createNewOverride(Override::SCOPE_SITE, 1, 'test', $page_mark_notice);

        $this->assertNotEquals(false, $override, 'the override should have been created');

        $matching = Override::getMatchingRecord($page_mark_notice);

        $this->assertEquals($override, $matching, 'A matching override should have been found');
    }

    protected function runScan()
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
             * @var $page Page
             */
            $queue->rewind();
            $page = $queue->current();

            $page->scan();

            sleep(1);
        }
    }
    
}