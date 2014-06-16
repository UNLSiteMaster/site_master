<?php
namespace SiteMaster\Core\Auditor\Downloader;

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
        $installed_version = curl_version();
        if (version_compare($installed_version['version'], '7.20.0') >= 0) {
            $this->markTestSkipped('Skipping check of issue-82 because CURL version is > 7.20.0');
        }
        
        $this->setUpDB();


        $site_url     = 'http://unlcms.unl.edu/university-communications/sitemaster/';
        $redirect_url = $site_url . 'issue-82';

        $this->setExpectedException(
            'SiteMaster\\Core\\UnexpectedValueException', 'Redirect found with a fragment: ' . $site_url . 'node/1#test  This leads to a CURL bug, so scheduling a new download.  Rescheduling without fragment: ' . $site_url . 'node/1'
        );

        //Make sure that the site exists
        Site::createNewSite($site_url);
        
        $site = Site::getByBaseURL($site_url);
        
        $scan = \SiteMaster\Core\Auditor\Scan::createNewScan($site->id);
        $page_scan = Page::createNewPage($scan->id, $site->id, $redirect_url, array(
            'scan_type' => $scan->scan_type,
        ));
        
        $downloader = new HTMLOnly($site, $page_scan, $scan);
        $downloader->download($redirect_url);
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
