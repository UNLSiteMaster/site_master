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
    public function scanGraded()
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
            $this->assertEquals(84.5, $grade->point_grade, 'the grade should be 84.5');
            $this->assertEquals(33.33, $grade->weight, 'the weight should be set to 33.33, as per the config');
            $this->assertEquals(28.16, $grade->weighted_grade);
            $this->assertEquals('B', $grade->letter_grade);
            
            $this->assertEquals(84.49, $page->percent_grade);
            $this->assertEquals(33.33, $page->points_available);
            $this->assertEquals(28.16, $page->point_grade);
            $this->assertEquals('B', $page->letter_grade);
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

        $this->runScan($site);

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page \SiteMaster\Core\Auditor\Site\Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(0, $grade->point_grade, 'the grade should be 0');
            $this->assertEquals(GradingHelper::GRADE_NO_PASS, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_F, $page->letter_grade);
        }
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
                    'available_points' => 50,
                    'weight' => 33.33
                ));
            }
        }

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();

        $this->runScan($site);

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page \SiteMaster\Core\Auditor\Site\Page
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

        $this->runScan($site);

        //get the scan
        $scan = $site->getLatestScan();

        $example_metric = Metric::getByMachineName('example');

        foreach ($scan->getPages() as $page) {
            /**
             * @var $page \SiteMaster\Core\Auditor\Site\Page
             */
            $grade = $page->getMetricGrade($example_metric->id);
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $grade->letter_grade);

            //The page should have an F grade because the only metric failed
            $this->assertEquals(GradingHelper::GRADE_INCOMPLETE, $page->letter_grade);
        }
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
