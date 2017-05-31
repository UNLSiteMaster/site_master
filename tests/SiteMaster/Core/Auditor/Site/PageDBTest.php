<?php
namespace SiteMaster\Core\Auditor\Site;

use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class PageDBTest extends DBTestCase
{


    /**
     * @test
     */
    public function rescheduleScan()
    {
        $this->setUpDB();

        //Set up some dummy date
        $metric = Metric::getByMachineName('example');
        $mark1 = Mark::createNewMark($metric->id, 'test1', 'test1');
        $mark2 = Mark::createNewMark($metric->id, 'test2', 'test2');
        $mark3 = Mark::createNewMark($metric->id, 'test3', 'test3');

        $site = Site::getByBaseURL('http://www.test.com/');

        //Create a scan
        $scan1 = Scan::createNewScan($site);
        $page = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page->addMark($mark1, array(
            'value_found' => 'http://www.test.com/1'
        ));
        $page->addMark($mark2, array(
            'value_found' => 'http://www.test.com/2'
        ));
        $page->addMark($mark3, array(
            'value_found' => 'http://www.test.com/3'
        ));
        
        $page->markAsRunning();
        
        $new_page = $page->rescheduleScan();
        $marks = $new_page->getMarks($metric->id);
        
        $this->assertEquals($page->id, $new_page->id, 'ids should match');
        $this->assertEquals(0, $marks->count(), 'all marks should be deleted');
        $this->assertEquals(Page::STATUS_QUEUED, $new_page->status, 'status should be reset');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
