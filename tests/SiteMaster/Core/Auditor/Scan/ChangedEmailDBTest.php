<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class ChangedEmailDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function getTo()
    {
        $this->setUpDB();

        $site = Site::getByBaseURL('http://unlsitemaster.github.io/test_site/');
        $site->scheduleScan();
        $scan = $site->getLatestScan();
        
        $email = new ChangedEmail($scan);
        
        $this->assertEquals(array('test1@test.com' => '1'), $email->getTo());
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
