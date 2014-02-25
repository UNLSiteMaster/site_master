<?php
use SiteMaster\Core\Config;

require __DIR__ . '/../vendor/autoload.php';

$config_file = __DIR__ . '/../config.sample.php';
if (file_exists(__DIR__ . '/../config.inc.php')) {
    $config_file = __DIR__ . '/../config.inc.php';
}

require_once $config_file;

if (getenv('TRAVIS')) {
    //Set config for travis CI
    Config::set('TEST_DB_HOST'     , '127.0.0.1');
    Config::set('TEST_DB_USER'     , 'travis');
    Config::set('TEST_DB_PASSWORD' , '');
    Config::set('TEST_DB_NAME'     , 'sitemaster_test');
}

//Prevent tests from running on the production db
Config::set('DB_HOST'     , '');
Config::set('DB_USER'     , '');
Config::set('DB_PASSWORD' , '');
Config::set('DB_NAME'     , '');

//Only test with the default plugins
Config::set('PLUGINS', array(
    'example' => array('weight'=>33.33),
    'theme_foundation' => array('setting'=>'value'),
));

require_once(__DIR__ . '/../init_plugins.php');