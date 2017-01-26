<?php
use SiteMaster\Core\Config;

require __DIR__ . '/../vendor/autoload.php';

$config_file = __DIR__ . '/../config.sample.php';
if (file_exists(__DIR__ . '/../config.inc.php')) {
    $config_file = __DIR__ . '/../config.inc.php';
}

@unlink(__DIR__.'/../plugins_testing.json');
@unlink(__DIR__.'/../tmp/sitemaster_headless_compiled.js');

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

//Test with these example groups
Config::set('GROUPS', [
    'group_1' => [
        'MATCHING' => [
            SiteMaster\Core\Registry\GroupHelper::generateDomainRegex('test.com')
        ],
        'SITE_PASS_FAIL' => false,
        'METRICS' => [
            'example' => [
                'weight' => 33.33,
                'test' => true,
            ]
        ],
    ],
    'group_2' => [
        'MATCHING' => [
            SiteMaster\Core\Registry\GroupHelper::generateDomainRegex('unlsitemaster.github.io')
        ],
        'SITE_PASS_FAIL' => true,
        'METRICS' => [
            'example' => [
                'weight' => 33.33,
                'test' => false,
            ]
        ],
    ],
    \SiteMaster\Core\Registry\GroupHelper::DEFAULT_GROUP_NAME => [
        'SITE_PASS_FAIL' => false,
        'METRICS' => [
            'example' => [
                'weight' => 33.33,
            ]
        ],
    ],
]);

Config::set('ENVIRONMENT', Config::ENVIRONMENT_TESTING);

//Ensure that SITE_PASS_FAIL is false by default.
Config::set('SITE_PASS_FAIL', false);

require_once(__DIR__ . '/../init_plugins.php');