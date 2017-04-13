<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Parser\HTML5;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class SchedulerDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function schedulerLog()
    {
        $this->setUpDB();
        
        //Get a test site
        $site = Site::getByBaseURL('http://www.test.com/');
        
        //Set up a spider that needs to be sent to the logger
        $parser = new HTML5();
        $spider = new \Spider(new \Spider_Downloader(), $parser, array(
            'respect_robots_txt'=>false,
            'use_effective_uris' => false)
        );
        $scan = Scan::createNewScan($site);
        
        //Set up the logger to test
        $logger = new Logger\Scheduler($spider, $scan, $site);
        
        //Set up the xpath
        $html = file_get_contents(Util::getRootDir() . '/tests/data/multiple_sites.html');
        $xpath = $parser->parse($html);
        
        
        //log
        $logger->log('http://www.test.com/', 1, $xpath);

        $this->assertNotEquals(false, Page::getByScanIDAndURI($scan->id, 'http://www.test.com/page2.html'), 'we should find this page');
        $this->assertEquals(false, Page::getByScanIDAndURI($scan->id, 'http://www.test.com/test/page2.html'), 'this is a page of a sub-site and should not be scheduled');
        $this->assertEquals(false, Page::getByScanIDAndURI($scan->id, 'https://www.test.com/page2.html'), 'The same page with (but with https) or vice versa should not be scheduled again');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
