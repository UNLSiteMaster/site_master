<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

if (!isset($argv[1], $argv[2], $argv[3])) {
    echo "This script will add a user to every site within a group with the admin role" . PHP_EOL;
    echo "usage: php add_user_to_group_sites.php username provider group_name [remove]" . PHP_EOL;
    exit();
}

$user = \SiteMaster\Core\User\User::getByUIDAndProvider($argv[1], $argv[2]);

if (!$user) {
    echo 'Unable to find user' . PHP_EOL;
    exit();
}

$sites = new \SiteMaster\Core\Registry\Sites\WithGroup(['group_name' => $argv[3]]);

if (!count($sites)) {
    echo 'no sites were found for this group' . PHP_EOL;
}

foreach ($sites as $site) {
    /**
     * @var \SiteMaster\Core\Registry\Site $site
     */
    
    $membership = $site->getMembershipForUser($user);
    
    
    if (!isset($argv[4]) || $argv[4] !== 'remove') {
        //add the user
        if (!$membership) {
            //Need to add as a member
            $membership = \SiteMaster\Core\Registry\Site\Member::createMembership($user, $site);
            $membership->verify();
        }

        if (!$membership->isVerified()) {
            //Membership has been requested, but needs to be verified
            $membership->verify();
            continue;
        }
        
        //Already verified, nothing to do
        
    } else {
        //remove the user
        if ($membership) {
            $membership->delete();
        }
    }
}

echo 'done' . PHP_EOL;
exit();
