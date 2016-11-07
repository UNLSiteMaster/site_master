<?php
use SiteMaster\Core\Config;

require __DIR__ . '/../vendor/autoload.php';

$config_file = __DIR__ . '/../config.sample.php';
if (file_exists(__DIR__ . '/../config.inc.php')) {
    $config_file = __DIR__ . '/../config.inc.php';
}

@unlink(__DIR__.'/../plugins_testing.json');
@unlink(__DIR__.'/../scripts/sitemaster_phantom_complied_test.js');

require_once $config_file;

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

Config::set('ENVIRONMENT', Config::ENVIRONMENT_TESTING);

//Ensure that SITE_PASS_FAIL is false by default.
Config::set('SITE_PASS_FAIL', false);

require_once(__DIR__ . '/../init_plugins.php');