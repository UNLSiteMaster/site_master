<?php
namespace SiteMaster\Core\DBTests;

use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site\Role;
use SiteMaster\Core\User\User;

class BaseTestDataInstaller implements MockTestDataInstallerInterface
{
    /**
     * This function should execute commands to install mock data to the test database.
     */
    public function install() {
        //Create users
        $user1 = User::createUser(1, 'test', array(
            'first_name' => 'test1',
            'last_name' => 'test1',
            'email' => 'test1@test.com'
        ));
        $user2 = User::createUser('email@provider.com', 'test', array(
            'first_name' => 'test2',
            'last_name' => 'test2',
            'email' => 'test2@test.com'
        ));
        
        //create sites
        $site1 = Site::createNewSite('http://www.test.com/');
        $site2 = Site::createNewSite('http://www.test.com/test/');
        $site3 = Site::createNewSite('http://unlsitemaster.github.io/test_site/', array(
            'site_map_url' => 'http://unlsitemaster.github.io/test_site/sitemaster_site_map.xml'
        )); //Integration testing site
        
        //Create memberships
        $membership_user1_site1 = Member::createMembership($user1, $site1);
        $membership_user2_site1 = Member::createMembership($user2, $site1);
        $membership_user1_site2 = Member::createMembership($user1, $site2);
        $membership_user2_site2 = Member::createMembership($user2, $site2);
        $membership_user1_site3 = Member::createMembership($user1, $site3);
        
        //Get roles (should be installed by default)
        $admin = Role::getByRoleName('admin');
        $developer = Role::getByRoleName('developer');

        /**************************************
         * Site 1 memberships
         */
        Member\Role::createRoleForSiteMember($admin, $membership_user1_site1, array(
            'approved' => 'YES'
        ));
        
        //Should be auto-approved because they are an approved admin
        Member\Role::createRoleForSiteMember($developer, $membership_user1_site1);
        
        
        Member\Role::createRoleForSiteMember($developer, $membership_user2_site1, array(
            'approved' => 'YES'
        ));

        /**************************************
         * Site 2 memberships
         */
        Member\Role::createRoleForSiteMember($admin, $membership_user1_site2, array(
            'approved' => 'NO'
        ));
        Member\Role::createRoleForSiteMember($developer, $membership_user1_site2, array(
            'approved' => 'YES'
        ));
        Member\Role::createRoleForSiteMember($developer, $membership_user2_site2, array(
            'approved' => 'YES'
        ));

        /**************************************
         * Site 3 memberships
         */
        Member\Role::createRoleForSiteMember($admin, $membership_user1_site3, array(
            'approved' => 'YES'
        ));
    }
}