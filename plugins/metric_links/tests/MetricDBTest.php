<?php
namespace SiteMaster\Plugins\Metric_links;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class MetricDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function markPage()
    {
        $this->setUpDB();

        $metric = new Metric('metric_links');
        $metric_record = $metric->getMetricRecord();
        $site = Site::getByBaseURL('http://www.test.com/');
        $scan = Scan::createNewScan($site->id);
        $page = Page::createNewPage($scan->id, $site->id, 'http://test.com/test');
        
        $metric->markPage($page, array(
            'http://unlcms.unl.edu/university-communications/sitemaster/example-404',
            'http://unlcms.unl.edu/university-communications/sitemaster/example-404',
            'http://unlcms.unl.edu/university-communications/sitemaster/example-redirect-301',
            'http://unlcms.unl.edu/university-communications/sitemaster/'
        ));

        $machine_names_found = array();
        foreach ($page->getMarks($metric_record->id) as $page_mark) {
            $mark = $page_mark->getMark();
            $machine_names_found[] = $mark->machine_name;
        }
        
        $this->assertEquals(
            array(
                'link_http_code_404',
                'link_http_code_404',
                'link_http_code_301'
            ),
        $machine_names_found);
    }


    public function setUpDB()
    {
        $plugin = new Plugin();
        
        //Uninstall plugin data
        $plugin->onUninstall();
        
        //clean and install base db
        $this->cleanDB();
        $this->installBaseDB();
        
        //Install plugin data
        $plugin->onInstall();
        
        //Install basic moc data
        $this->installMockData(new BaseTestDataInstaller());
    }
}