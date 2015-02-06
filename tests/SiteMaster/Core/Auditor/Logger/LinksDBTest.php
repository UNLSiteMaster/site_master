<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Auditor\Site\Page\Links\AllForPage;
use SiteMaster\Core\Util;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class LinksDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function linksLog()
    {
        $this->setUpDB();

        $base_uri = 'http://www.test.com/';

        //Get a test site
        $site = Site::getByBaseURL($base_uri);

        //Set up a spider that needs to be sent to the logger
        $parser = new \Spider_Parser();
        $spider = new \Spider(new \Spider_Downloader(), $parser, array(
                'respect_robots_txt'=>false,
                'use_effective_uris' => false)
        );
        $scan = Scan::createNewScan($site->id);
        
        //Create a new page scan for the base url
        $scan->scheduleScan();

        //Get that page scan
        $page = Page::getByScanIDAndURI($scan->id, $base_uri);

        //Set up the logger to test
        $logger = new Logger\Links($spider, $page);

        //Set up the xpath
        $html = file_get_contents(Util::getRootDir() . '/tests/data/multiple_sites.html');
        $xpath = $parser->parse($html);

        //log
        $logger->log($base_uri, 1, $xpath);

        $expected_links = array(
            'http://www.test.com/page2.html',
            'http://www.test.com/test/page2.html'
        );
        
        $list = new AllForPage(array('scanned_page_id'=>$page->id));
        
        $found_links = array();
        foreach ($list as $found_link) {
            $found_links[] = $found_link->link_url;
        }

        //Sort arrays to ensure order is the same. (order does not matter for this test)
        sort($expected_links);
        sort($found_links);
        
        $this->assertEquals($expected_links, $found_links, 'All links should be logged');
        
        //TODO: change the name of multiple_sites.html or make a new page, with other external links that should be logged.

    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
