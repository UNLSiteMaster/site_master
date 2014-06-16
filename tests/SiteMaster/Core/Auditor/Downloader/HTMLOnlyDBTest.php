<?php
namespace SiteMaster\Core\Auditor\Downloader;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site;

class HTMLOnlyDBTest extends DBTestCase
{
    /**
     * Check issue-82: https://github.com/UNLSiteMaster/site_master/issues/82
     * 
     * @test
     */
    public function checkIssue82() {
        $this->setUpDB();

        $site_url     = 'http://unlcms.unl.edu/university-communications/sitemaster/';
        $redirect_url = $site_url . 'issue-82';

        //Make sure that the site exists
        Site::createNewSite($site_url);
        
        $site = Site::getByBaseURL($site_url);
        
        $scan = Scan::createNewScan($site->id);
        $page_scan = Page::createNewPage($scan->id, $site->id, $redirect_url, array(
            'scan_type' => $scan->scan_type,
        ));
        
        $downloader = new HTMLOnly($site, $page_scan, $scan);
        $downloader->download($redirect_url);
        
        $page_scan->reload();
        $this->assertEquals($site_url, $page_scan->uri, 'Should have redirected to the home page with no fragment');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
