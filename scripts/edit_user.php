<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");


if (isset($argv[1]) && 'list' == $argv[1]) {
    $users = new \SiteMaster\Core\Users\All();
    echo "uid\tprovider\temail" . PHP_EOL;
    foreach ($users as $user) {
        echo $user->uid . "\t" . $user->provider . "\t" . $user->email . PHP_EOL;
    }
    
    exit();
}

if (!isset($argv[1], $argv[2], $argv[3], $argv[4])) {
    echo "usage: php edit_user.php username provider key value" . PHP_EOL;
    echo "Or: php edit_user.php list" . PHP_EOL;
    exit();
}

$user = \SiteMaster\Core\User\User::getByUIDAndProvider($argv[1], $argv[2]);

if (!$user) {
    echo 'Unable to find user' . PHP_EOL;
    exit();
}

$user->$argv[3] = $argv[4];
$user->save();

echo 'User updated' . PHP_EOL;
exit();
