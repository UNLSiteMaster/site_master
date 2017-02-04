<?php
namespace SiteMaster\Core\Auditor\Site\Page\MetricGrades;

use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\DBTests\DBTestCase;

class ForScanAndMetricDBTest extends DBTestCase
{

    /**
     * @test
     */
    public function testPassFailMarksOrder()
    {
        $this->setUpDB();

        Config::set('SITE_PASS_FAIL', true);

        //Set up some dummy date
        $example_metric = new \SiteMaster\Plugins\Example\Metric('example');
        $metric = $example_metric->getMetricRecord();
        $mark1 = Mark::createNewMark($metric->id, 'test1', 'test1', array(
            'point_deduction' => 1
        ));
        $mark2 = Mark::createNewMark($metric->id, 'test2', 'test2', array(
            'point_deduction' => 1
        ));
        $mark3 = Mark::createNewMark($metric->id, 'test3', 'test3', array(
            'point_deduction' => 1
        ));

        $site = Site::getByBaseURL('http://www.test.com/');

        //Create a scan
        $scan1 = Scan::createNewScan($site);
        $page1 = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page1->addMark($mark1);
        $page1->addMark($mark2);
        $page1->addMark($mark3);
        $example_metric->grade($page1, true);

        $page2 = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/1', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page2->addMark($mark1);
        $example_metric->grade($page2, true);

        $page3 = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/3', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page3->addMark($mark1);
        $page3->addMark($mark2);
        $example_metric->grade($page3, true);

        $hot_spots = $scan1->getHotSpots($metric->id);
        
        $this->assertEquals(
            array($page1->id, $page3->id, $page2->id),
            $hot_spots->getInnerIterator()->getArrayCopy(),
            'Hot spots should be the correct order (most marks first)');

        Config::set('SITE_PASS_FAIL', false);
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
