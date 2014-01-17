<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;

class RegistryDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function getClosestSite()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
        
        $registry = new Registry();


        $site = $registry->getClosestSite('http://www.fail.com/dir/dir/index.php?test', 'this should fail to find a site');
        $this->assertEquals(false, $site);
        
        $site = $registry->getClosestSite('http://www.test.com/dir/dir/index.php?test');
        $this->assertEquals('http://www.test.com/', $site->base_url);

        $site = $registry->getClosestSite('http://www.test.com/test/dir/index.php?test');
        $this->assertEquals('http://www.test.com/test/', $site->base_url);
    }
}
