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

        $base_uri      = 'http://www.test.com/';
        $redirect_url  = 'http://unlcms.unl.edu/university-communications/sitemaster/example-redirect-301';
        $not_found_url = 'http://unlcms.unl.edu/university-communications/sitemaster/example-404';
        $okay_url      = 'http://unlcms.unl.edu/university-communications/sitemaster/';

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
        $html = file_get_contents(Util::getRootDir() . '/tests/data/link_logger_test.html');
        $xpath = $parser->parse($html);

        //log
        $logger->log($base_uri, 1, $xpath);

        $expected_links = array(
            $redirect_url,
            $not_found_url,
            $okay_url,
        );
        
        $list = new AllForPage(array('scanned_page_id'=>$page->id));
        
        $found_links = array();
        foreach ($list as $found_link) {
            $found_links[] = $found_link->original_url;
        }

        //Sort arrays to ensure order is the same. (order does not matter for this test)
        sort($expected_links);
        sort($found_links);
        
        $this->assertEquals($expected_links, $found_links, 'All links should be logged');
        
        $redirect_link  = Page\Link::getByOriginalURL($redirect_url);
        $not_found_link = Page\Link::getByOriginalURL($not_found_url);
        $okay_link      = Page\Link::getByOriginalURL($okay_url);
        
        $this->assertEquals(301, $redirect_link->original_status_code);
        $this->assertEquals(200, $redirect_link->final_status_code);
        $this->assertEquals(404, $not_found_link->original_status_code);
        $this->assertEquals(200, $okay_link->original_status_code);
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
