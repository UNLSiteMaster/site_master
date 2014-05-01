<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Config;
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
     * @test
     */
    public function getPreviousScan()
    {
        $this->setUpDB();

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);
        
        $site->scheduleScan();
        
        $scan = $site->getLatestScan();
        
        $this->assertEquals(false, $scan->getPreviousScan(), 'There should not be a previous scan at this point');
        
        $scan->markAsComplete();

        $site->scheduleScan();
        
        $scan = $site->getLatestScan();
        
        $this->assertNotEquals(false, $scan->getPreviousScan(), 'Now, there should be a previous scan');
    }

    /**
     * Simulate a scan for a site.  Verify all results
     * This is an integration test rather than a unit test
     * 
     * @test
     * @group integration
     */
    public function scanGraded()
    {
        $this->setUpDB();
        
        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);
        
        //Schedule a scan
        $site->scheduleScan();
        
        $this->runScan();
        
        //get the scan
        $scan = $site->getLatestScan();
        
        $example_metric = Metric::getByMachineName('example');
        
        $this->assertEquals(Scan::STATUS_COMPLETE, $scan->status, 'the scan should be completed');
        
        //Loop over each page, and add some extra marks (so that we can verify `changes_since_last_scan` is being set correctly) 
        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $mark = Metric\Mark::getByMachineNameAndMetricID('test', $example_metric->id);
            $page->addMark($mark);
        }


        //Now, Schedule a new scan, so that we can compare changes_since_last_scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        $this->assertEquals(Scan::STATUS_COMPLETE, $scan->status, 'the scan should be completed');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(-1, $grade->changes_since_last_scan, 'there should be one less mark');
            $this->assertEquals(84.5, $grade->point_grade, 'the grade should be 84.5');
            $this->assertEquals(33.33, $grade->weight, 'the weight should be set to 33.33, as per the config');
            $this->assertEquals(28.16, $grade->weighted_grade);
            $this->assertEquals('B', $grade->letter_grade);
            
            $this->assertEquals(84.49, $page->percent_grade);
            $this->assertEquals(33.33, $page->points_available);
            $this->assertEquals(28.16, $page->point_grade);
            $this->assertEquals('B', $page->letter_grade);
            
            $this->assertEquals(2, $page->num_errors);
            $this->assertEquals(0, $page->num_notices);
        }
        
        //TODO: test the GPA
    }

    /**
     * Simulate a scan for a site that has pass/fail metrics.  Verify all results
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanPassFail()
    {
        $this->setUpDB();

        $metrics = new Metrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof \SiteMaster\Plugins\Example\Metric) {
                $metric->setOptions(array(
                    'pass_fail' => true,
                    'weight' => 100
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(0, $grade->point_grade, 'the grade should be 0');
            $this->assertEquals(GradingHelper::GRADE_NO_PASS, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_F, $page->letter_grade);
        }
    }

    /**
     * Simulate a scan when the config option SITE_PASS_FAIL is set to true.  Verify all results
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanSitePassFail()
    {
        $this->setUpDB();

        Config::set('SITE_PASS_FAIL', true);

        $metrics = new Metrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof \SiteMaster\Plugins\Example\Metric) {
                $metric->setOptions(array(
                    'pass_fail' => true,
                    'weight' => 100
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(0, $grade->point_grade, 'the grade should be 0');
            $this->assertEquals(GradingHelper::GRADE_NO_PASS, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_NO_PASS, $page->letter_grade);
        }
        
        $scan->reload();

        $this->assertEquals(0, $scan->gpa);

        Config::set('SITE_PASS_FAIL', false);
    }

    /**
     * Simulate a scan for a site that has metrics with custom points_available values.  Verify all results
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanPointsAvailable()
    {
        $this->setUpDB();

        $metrics = new Metrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof \SiteMaster\Plugins\Example\Metric) {
                $metric->setOptions(array(
                    'points_available' => 50,
                    'weight' => 33.33
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(34.5, $grade->point_grade);
            $this->assertEquals(GradingHelper::GRADE_D_PLUS, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_D_PLUS, $page->letter_grade);
        }
    }

    /**
     * Simulate a scan for a site that has incomplete metrics.  Verify all results
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanIncomplete()
    {
        $this->setUpDB();

        $metrics = new Metrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof \SiteMaster\Plugins\Example\Metric) {
                $metric->setOptions(array(
                    'simulate_incomplete' => true,
                    'weight' => 100
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $page->letter_grade);
        }
    }

    /**
     * Simulate a scan for a site that has incomplete metrics.  Verify all results
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanRedirects()
    {
        $this->setUpDB();

        $site = Site::createNewSite('http://unlcms.unl.edu/university-communications/sitemaster/');

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $found_uris = array();
        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $found_uris[] = $page->uri;
        }
        
        $this->assertNotContains('http://unlcms.unl.edu/university-communications/sitemaster/example-redirect-301', $found_uris, 'Should not have recoded the redirect');
        
        $this->assertEquals(1, count($found_uris), 'Should have only found one URI');
    }

    /**
     * Simulate a scan for a site that has incomplete metrics via exceptions.
     * This is an integration test rather than a unit test
     *
     * @test
     * @group integration
     */
    public function scanException()
    {
        $this->setUpDB();

        $metrics = new Metrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof \SiteMaster\Plugins\Example\Metric) {
                $metric->setOptions(array(
                    'simulate_exception' => true,
                    'weight' => 100
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();
        
        $this->assertNotEmpty($scan->end_time);

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $page->letter_grade);
        }
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

    /**
     * @test
     */
    public function getHotSpots()
    {
        $this->setUpDB();

        //Get the test site
        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Start simulating a scan
        $site->scheduleScan();

        //get the new scan
        $scan = $site->getLatestScan();
        
        //Ge the metric and mark to test with
        $metric = new \SiteMaster\Plugins\Example\Metric('example');
        $mark = $metric->getMark('test', 'Just a test', 10.5);
        
        //Simulate a page scan for the test page
        $page_1 = Page::createNewPage($scan->id, $site->id, self::INTEGRATION_TESTING_URL . 'test');
        
        $page_1->addMark($mark);
        $page_1->addMark($mark);
        $metric->grade($page_1, true);
        $page_1->grade();

        //Now do the same for a new page, simulating a single page scan with an improvement (less marks)
        $page_2 = Page::createNewPage($scan->id, $site->id, self::INTEGRATION_TESTING_URL . 'test');

        $page_2->addMark($mark);
        $metric->grade($page_2, true);
        $page_2->grade();

        //now Simulate a page scan for the new distinct page
        $page_3 = Page::createNewPage($scan->id, $site->id, self::INTEGRATION_TESTING_URL . 'test2');

        $page_3->addMark($mark);
        $metric->grade($page_3, true);
        $page_3->grade();

        //Get the hot spots
        $hot_spots = $scan->getHotSpots($metric->getMetricRecord()->id);

        $this->assertEquals(array(2, 3), $hot_spots->getInnerIterator()->getArrayCopy(), 'Only the newest page scans should be returned');
        
        //Now, fix /test so it has 100%.  It should not show up in the hot spots
        //Now do the same for a new page, simulating a single page scan with an improvement (less marks)
        $page_4 = Page::createNewPage($scan->id, $site->id, self::INTEGRATION_TESTING_URL . 'test');

        $metric->grade($page_4, true);
        $page_4->grade();

        //Get the hot spots
        $hot_spots = $scan->getHotSpots($metric->getMetricRecord()->id);

        $this->assertEquals(array(3), $hot_spots->getInnerIterator()->getArrayCopy(), 'Fixed pages should not show up in the list of hot sports');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
