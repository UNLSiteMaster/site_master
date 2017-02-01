<?php
use SiteMaster\Core\Config;

/**********************************************************************************************************************
 * php related settings
 */

ini_set('display_errors', true);

error_reporting(E_ALL);

Config::set('URL', '/'); //Trailing slash is important

/**********************************************************************************************************************
 * DB related settings
 */
Config::set('DB_HOST'     , 'localhost');
Config::set('DB_USER'     , 'sitemaster');
Config::set('DB_PASSWORD' , 'password');
Config::set('DB_NAME'     , 'sitemaster');

/**********************************************************************************************************************
 * Other settings, including theme
 */
Config::set('THEME', 'foundation');


/**********************************************************************************************************************
 * Plugin related settings
 */
Config::set('PLUGINS', array(
    'example' => array('setting'=>'value'),
    'theme_foundation' => array('setting'=>'value'),
    'auth_google' => array('setting'=>'value'),
));

/**********************************************************************************************************************
 * group settings
 */
Config::set('GROUPS', [
    //The key is the group's name (machine name). Each group config is its own array
    'default' => [
        //define custom metrics with with the 'METRICS' key, contents of this array match the 'PLUGINS' configuration option, but can only contain 'metric' plugins. Other plugins such as authentication or themes are applied to all groups.
        'METRICS' => [
            'example' => array(
                'setting' => 'value',
            ),
        ],
        //You can also set a few other settings on a group level
        //'SITE_PASS_FAIL' => false,
        //'SCAN_PAGE_LIMIT' => 5,
    ],
]);

/**********************************************************************************************************************
 * unit test settings
 */
Config::set('TEST_DB_HOST'     , 'localhost');
Config::set('TEST_DB_USER'     , 'sitemaster_test');
Config::set('TEST_DB_PASSWORD' , 'password');
Config::set('TEST_DB_NAME'     , 'sitemaster_test');

/**********************************************************************************************************************
 * TRAVIS settings
 */
if (getenv('TRAVIS')) {
    //
    Config::set('DB_HOST'     , '127.0.0.1');
    Config::set('DB_USER'     , 'travis');
    Config::set('DB_PASSWORD' , '');
    Config::set('DB_NAME'     , 'sitemaster_test');
    
    //Set  the config to match the production config for travis CI
    Config::set('TEST_DB_HOST'     , Config::get('DB_HOST'));
    Config::set('TEST_DB_USER'     , Config::get('DB_USER'));
    Config::set('TEST_DB_PASSWORD' , Config::get('DB_PASSWORD'));
    Config::set('TEST_DB_NAME'     , Config::get('DB_NAME'));
}