<?php
namespace SiteMaster\Core\Auditor\Metric\Marks;

use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class UniqueValueFoundDBTest extends DBTestCase
{

    /**
     * @test
     */
    public function uniqueValueFound()
    {
        $this->setUpDB();

        $metric = \SiteMaster\Core\Auditor\Metric::createNewMetric('test');
        $mark1 = Mark::createNewMark($metric->id, 'test1', 'test1');
        $mark2 = Mark::createNewMark($metric->id, 'test2', 'test2');
        $mark3 = Mark::createNewMark($metric->id, 'test3', 'test3');

        $site = \SiteMaster\Core\Registry\Site::getByBaseURL('http://www.test.com/');

        //Create a scan
        $scan1 = Scan::createNewScan($site->id);
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


        //Create another scan
        $scan2 = Scan::createNewScan($site->id);
        $page = Page::createNewPage($scan2->id, $site->id, 'http://www.test.com/', Page::FOUND_WITH_CRAWL);

        //Add marks
        $page->addMark($mark1, array(
            'value_found' => 'http://www.test.com/1'
        ));
        $page->addMark($mark2, array(
            'value_found' => 'http://www.test.com/2'
        ));

        //Check results
        $results = new UniqueValueFound(array(
            'metrics_id' => $metric->id,
            'scans_id' => $scan2->id
        ));

        //Make sure that the inner array is correct
        $this->assertEquals(array(
            'http://www.test.com/2' => 2,
            'http://www.test.com/1' => 1
        ), $results->getArrayCopy());
        
        //Make sure that we can grad by key
        $this->assertEquals(1, $results->offsetGet('http://www.test.com/1'));
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
