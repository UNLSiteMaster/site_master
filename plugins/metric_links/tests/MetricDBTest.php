<?php
namespace SiteMaster\Plugins\Metric_links;

use SiteMaster\Core\Auditor\Logger\Links;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
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
        $page = Page::createNewPage($scan->id, $site->id, $base_uri);


        //Set up a spider that needs to be sent to the logger
        $parser = new \Spider_Parser();
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


    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
