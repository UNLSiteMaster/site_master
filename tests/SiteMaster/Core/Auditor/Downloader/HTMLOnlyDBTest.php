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
        $page_scan = Page::createNewPage($scan->id, $site->id, $redirect_url, Page::FOUND_WITH_CRAWL, array(
            'scan_type' => $scan->scan_type,
        ));
        
        $downloader = new HTMLOnly($site, $page_scan, $scan);
        $downloader->download($redirect_url);
        
        $page_scan->reload();
        $this->assertEquals($site_url, $page_scan->uri, 'Should have redirected to the home page with no fragment');
    }

    /**
     * Check issue-82: https://github.com/UNLSiteMaster/site_master/issues/100
     *
     * @test
     */
    public function checkIssue100() {
        $this->setUpDB();

        $site_url = 'http://marketplace.unl.edu/';

        //Make sure that the site exists
        Site::createNewSite($site_url);

        $site = Site::getByBaseURL($site_url);

        $scan = Scan::createNewScan($site->id);
        $page_scan = Page::createNewPage($scan->id, $site->id, $site_url, Page::FOUND_WITH_CRAWL, array(
            'scan_type' => $scan->scan_type,
        ));

        $downloader = new HTMLOnly($site, $page_scan, $scan);
        
        $exception_created = false;
        try {
            $downloader->download($site_url);
        } catch (DownloadException $e) {
            //This is okay, we want this to happen.
            $exception_created = true;
        }
        
        $site->reload();

        $this->assertEquals(true, $exception_created, 'exception should be thrown');
        $this->assertEquals('https://marketplace.unl.edu/', $site->base_url, 'The base_url should be changed to https');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
