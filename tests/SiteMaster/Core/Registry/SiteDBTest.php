<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;

class SiteDBTest extends DBTestCase
{

    /**
     * @test
     */
    public function cleanScans()
    {
        $this->setUpDB();
        
        $site = Site::getByBaseURL('http://www.test.com/');
        $scan_1 = Scan::createNewScan($site->id, array(
            'status' => Scan::STATUS_COMPLETE,
            'date_created' => '2014-02-20'
        ));
        $scan_2 = Scan::createNewScan($site->id, array(
            'status' => Scan::STATUS_ERROR,
            'date_created' => '2014-02-21'
        ));
        $scan_3 = Scan::createNewScan($site->id, array(
            'status' => Scan::STATUS_COMPLETE,
            'date_created' => '2014-02-22'
        ));
        $page = Page::createNewPage($scan_1->id, $site->id, 'http://www.test.com/');

        $site->cleanScans();
        $this->assertNotEquals(false, Scan::getByID($scan_2->id));
        $this->assertNotEquals(false, Scan::getByID($scan_3->id));
        $this->assertEquals(false, Scan::getByID($scan_1->id));
        $this->assertEquals(false, Page::getByID($page->id));
    }


    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
