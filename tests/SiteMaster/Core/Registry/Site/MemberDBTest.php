<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;
use SiteMaster\Core\Registry\Site\Member;

class MemberDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function isVerified()
    {
        $this->setUpDB();
        
        $membership_user1_site1 = Member::getByUserIDAndSiteID(1, 1);
        $membership_user1_site2 = Member::getByUserIDAndSiteID(1, 2);
        $membership_user2_site1 = Member::getByUserIDAndSiteID(2, 1);
        $membership_user2_site2 = Member::getByUserIDAndSiteID(2, 2);
        
        $this->assertEquals(true, $membership_user1_site1->isVerified(), 'user1 should be verified on site1');
        $this->assertEquals(false, $membership_user1_site2->isVerified(), 'user1 should NOT be verified on site2');

        $this->assertEquals(false, $membership_user2_site1->isVerified(), 'user2 should NOT be verified on site1');
        $this->assertEquals(false, $membership_user2_site2->isVerified(), 'user2 should NOT be verified on site2');
    }

    /**
     * @test
     */
    public function verify()
    {
        $this->setUpDB();

        $membership_user2_site1 = Member::getByUserIDAndSiteID(2, 1);
        $membership_user2_site1->verify();

        $this->assertEquals(true, $membership_user2_site1->isVerified(), 'user2 should now be verified on site1');
        
        $this->assertNotEquals(false,  $membership_user2_site1->getRole('admin'), 'user2 should now have an admin role on site1');
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}

