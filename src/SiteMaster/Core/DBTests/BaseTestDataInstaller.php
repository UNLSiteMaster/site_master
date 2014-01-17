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
        $user2 = User::createUser(2, 'test', array(
            'first_name' => 'test2',
            'last_name' => 'test2',
            'email' => 'test2@test.com'
        ));
        
        //create sites
        $site1 = Site::createNewSite('http://www.test.com/');
        $site2 = Site::createNewSite('http://www.test.com/test/');
        
        //Create memberships
        $membership1 = Member::createMembership($user1, $site1, array(
            'status' => 'APPROVED'
        ));
        $membership2 = Member::createMembership($user2, $site1, array(
            'status' => 'APPROVED'
        ));
        $membership3 = Member::createMembership($user1, $site2, array(
            'status' => 'PENDING'
        ));
        $membership4 = Member::createMembership($user2, $site2, array(
            'status' => 'APPROVED'
        ));
        
        //Get roles (should be installed by default)
        $manager = Role::getByRoleName('manager');
        $developer = Role::getByRoleName('developer');
        
        //add membership roles
        Member\Role::createRoleForSiteMember($manager, $membership1);
        Member\Role::createRoleForSiteMember($developer, $membership1);
        Member\Role::createRoleForSiteMember($manager, $membership2);
        Member\Role::createRoleForSiteMember($manager, $membership3);
        Member\Role::createRoleForSiteMember($manager, $membership4);
    }
}