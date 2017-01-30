<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\DBTests\DBTestCase;

class MetricGradeDBTest extends DBTestCase
{

    /**
     * @test
     */
    public function testMarkTotals()
    {
        $this->setUpDB();

        //Set up some dummy data
        $example_metric = new \SiteMaster\Plugins\Example\Metric('example');
        $metric = $example_metric->getMetricRecord();
        $mark1 = Mark::createNewMark($metric->id, 'test1', 'test1', array(
            'point_deduction' => 1
        ));
        $mark2 = Mark::createNewMark($metric->id, 'test2', 'test2', array(
            'point_deduction' => 2
        ));
        $mark3 = Mark::createNewMark($metric->id, 'test3', 'test3', array(
            'point_deduction' => 0
        ));

        $site = Site::getByBaseURL('http://www.test.com/');

        //Create a scan
        $scan1 = Scan::createNewScan($site);
        $page1 = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page1->addMark($mark1);
        $page1->addMark($mark2);
        $page1->addMark($mark3);
        $grade1 = $example_metric->grade($page1, true);

        $page2 = Page::createNewPage($scan1->id, $site->id, 'http://www.test.com/1', Page::FOUND_WITH_CRAWL);
        //Add Marks
        $page2->addMark($mark1);
        $grade2 = $example_metric->grade($page2, true);

        $this->assertEquals(2, $grade1->num_errors);
        $this->assertEquals(1, $grade1->num_notices);

        $this->assertEquals(1, $grade2->num_errors);
        $this->assertEquals(0, $grade2->num_notices);
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
