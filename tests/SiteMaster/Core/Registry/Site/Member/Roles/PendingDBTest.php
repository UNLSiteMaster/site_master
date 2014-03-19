<?php
namespace SiteMaster\Core\Registry\Site\Member\Roles;

use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;

class PendingDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function getPendingRolesForSite()
    {
        $site = \SiteMaster\Core\Registry\Site::getByBaseURL('http://www.test.com/test/');
        
        $pending = new Pending(array('site_id'=>$site->id));
        
        $this->assertEquals(array(4), $pending->getInnerIterator()->getArrayCopy(), 'The only pending roles.id should be 4');
    }


    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
