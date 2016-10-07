<?php
namespace SiteMaster\Plugins\Metric_links;

use SiteMaster\Core\Auditor\Logger\Links;
use SiteMaster\Core\Auditor\Parser\HTML5;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Util;

class MetricDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function markPage()
    {
        $this->setUpDB();

        $base_uri      = 'http://www.test.com/';

        $metric = new Metric('metric_links');
        $metric_record = $metric->getMetricRecord();
        $site = Site::getByBaseURL($base_uri);
        $scan = Scan::createNewScan($site->id);
        $page = Page::createNewPage($scan->id, $site->id, $base_uri, Page::FOUND_WITH_CRAWL);


        //Set up a spider that needs to be sent to the logger
        $parser = new HTML5();
        $spider = new \Spider(new \Spider_Downloader(), $parser, array(
                'respect_robots_txt'=>false,
                'use_effective_uris' => false)
        );
        $scan = Scan::createNewScan($site->id);

        //Set up the logger to test
        $logger = new Links($spider, $page);

        //Set up the xpath
        $html = file_get_contents(Util::getRootDir() . '/tests/data/link_logger_test.html');
        $xpath = $parser->parse($html);

        //log
        $logger->log($base_uri, 1, $xpath);
        
        $metric->markPage($page);

        $machine_names_found = array();
        foreach ($page->getMarks($metric_record->id) as $page_mark) {
            $mark = $page_mark->getMark();
            $machine_names_found[] = $mark->machine_name;
        }
        
        $expected =  array(
            'link_http_code_404',
            'link_http_code_404',
            'link_http_code_301'
        );

        sort($expected);
        sort($machine_names_found);
        
        $this->assertEquals($expected, $machine_names_found);
    }

    /**
     * @test
     */
    public function testLinkLimit()
    {
        $this->setUpDB();

        $base_uri = 'http://www.test.com/';

        $metric = new Metric('metric_links');
        $metric_record = $metric->getMetricRecord();
        $site = Site::getByBaseURL($base_uri);
        $scan = Scan::createNewScan($site->id);
        $page = Page::createNewPage($scan->id, $site->id, $base_uri, Page::FOUND_WITH_CRAWL);
        
        $limit_before = Config::get('LINK_SCAN_LIMIT');
        Config::set('LINK_SCAN_LIMIT', 2);

        //Set up a spider that needs to be sent to the logger
        $parser = new HTML5();
        $spider = new \Spider(new \Spider_Downloader(), $parser, array(
                'respect_robots_txt'=>false,
                'use_effective_uris' => false)
        );
        $scan = Scan::createNewScan($site->id);

        //Set up the logger to test
        $logger = new Links($spider, $page);

        //Set up the xpath
        $html = file_get_contents(Util::getRootDir() . '/tests/data/link_logger_test.html');
        $xpath = $parser->parse($html);

        //log
        $logger->log($base_uri, 1, $xpath);

        $metric->markPage($page);

        $machine_names_found = array();
        foreach ($page->getMarks($metric_record->id) as $page_mark) {
            $mark = $page_mark->getMark();
            $machine_names_found[] = $mark->machine_name;
        }
        
        $links = $page->getLinks();

        $this->assertEquals(3, count($links));
        $this->assertContains(Metric::MARK_LINK_LIMIT_HIT, $machine_names_found);

        Config::set('LINK_SCAN_LIMIT', $limit_before);
    }


    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
